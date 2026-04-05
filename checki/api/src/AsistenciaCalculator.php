<?php

/**
 * AsistenciaCalculator
 *
 * Calcula asistencia_calculada a partir de movimientos × agenda.
 * Es la única fuente de lógica de cálculo — tanto el cron como el
 * endpoint admin/api/recalcular.php usan esta clase.
 *
 * Principios:
 *  - Los movimientos son inmutables (hechos físicos).
 *  - La agenda es mutable — los horarios pueden cambiar.
 *  - asistencia_calculada es completamente regenerable.
 *  - El cálculo trabaja con intervalos de presencia, no con scans individuales.
 */
class AsistenciaCalculator
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PUNTO DE ENTRADA PRINCIPAL
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Calcula asistencia para un evento completo o un día específico.
     *
     * @return array { charlas_procesadas, registros_calculados, auto_checkouts, errores[] }
     */
    public function calcular(int $evento_id, ?int $dia_id = null): array
    {
        $charlas         = $this->getCharlas($evento_id, $dia_id);
        $calculados      = 0;
        $errores         = [];

        // Limpiar resultados anteriores del alcance
        $this->limpiarResultados($evento_id, $dia_id);

        foreach ($charlas as $charla) {
            try {
                $n = $this->calcularCharla($charla, $evento_id);
                $calculados += $n;
            } catch (Throwable $e) {
                $errores[] = "Charla #{$charla['id']}: " . $e->getMessage();
            }

            // Marcar agenda_cambios como procesado
            $this->db->prepare("
                UPDATE agenda_cambios SET recalculo_requerido = 0 WHERE charla_id = ?
            ")->execute([$charla['id']]);
        }

        return [
            'charlas_procesadas' => count($charlas),
            'registros_calculados' => $calculados,
            'errores' => $errores,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AUTO-CHECKOUT FIN DE JORNADA  (escenario 01)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Genera checkouts automáticos para todos los asistentes que siguen
     * "dentro" al terminar el día. Se usa antes de calcular asistencia.
     *
     * El timestamp del auto-checkout = hora_fin de la última charla del salón + 15 min buffer.
     * Si no hay charlas ese día, usa fin_dia (23:59 o configurable).
     *
     * @return int  número de auto-checkouts generados
     */
    public function autoCheckoutFinJornada(int $evento_id, ?int $dia_id = null): int
    {
        $generated = 0;

        // Obtener fin de jornada por salón
        $sql = "
            SELECT
                s.id   AS salon_id,
                t.id   AS totem_id,
                DATE_ADD(
                    COALESCE(MAX(c.hora_fin), CONCAT(d.fecha, ' 23:59:00')),
                    INTERVAL 15 MINUTE
                ) AS fin_jornada
            FROM salones s
            JOIN totems t ON t.salon_id = s.id AND t.activo = 1
            JOIN dias_evento d ON d.evento_id = s.evento_id
            LEFT JOIN charlas c ON c.salon_id = s.id AND c.dia_evento_id = d.id AND c.cancelada = 0
            WHERE s.evento_id = ?
        ";
        $params = [$evento_id];

        if ($dia_id) {
            $sql    .= ' AND d.id = ?';
            $params[] = $dia_id;
        } else {
            $sql .= ' AND d.fecha <= CURDATE()';
        }

        $sql .= ' GROUP BY s.id, t.id, d.id, d.fecha';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $salones = $stmt->fetchAll();

        foreach ($salones as $salon) {
            // Asistentes que siguen "dentro" en este salón
            $dentro = $this->db->prepare("
                SELECT ea.asistente_id, a.evento_id
                FROM   estado_asistentes ea
                JOIN   asistentes a ON a.id = ea.asistente_id
                WHERE  ea.salon_id = ?
                  AND  ea.estado   = 'dentro'
                  AND  a.evento_id = ?
            ");
            $dentro->execute([$salon['salon_id'], $evento_id]);
            $asistentes = $dentro->fetchAll();

            foreach ($asistentes as $ast) {
                $this->db->beginTransaction();
                try {
                    $fin = $salon['fin_jornada'];

                    $ins = $this->db->prepare("
                        INSERT INTO movimientos
                            (evento_id, asistente_id, totem_id, salon_id, tipo,
                             timestamp, metodo, flags)
                        VALUES
                            (?, ?, ?, ?, 'checkout',
                             ?, 'auto_fin_jornada', 'inferido')
                    ");
                    $ins->execute([
                        $ast['evento_id'], $ast['asistente_id'],
                        $salon['totem_id'], $salon['salon_id'], $fin,
                    ]);
                    $mov_id = (int)$this->db->lastInsertId();

                    $this->db->prepare("
                        UPDATE estado_asistentes
                        SET estado = 'fuera', ultimo_movimiento_id = ?, updated_at = ?
                        WHERE asistente_id = ? AND salon_id = ?
                    ")->execute([$mov_id, $fin, $ast['asistente_id'], $salon['salon_id']]);

                    $this->db->commit();
                    $generated++;
                } catch (Throwable $e) {
                    $this->db->rollBack();
                }
            }
        }

        return $generated;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VERIFICAR SI HAY RECÁLCULO PENDIENTE
    // ─────────────────────────────────────────────────────────────────────────

    public function necesitaRecalculo(int $evento_id): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM agenda_cambios ac
            JOIN charlas c ON c.id = ac.charla_id
            JOIN dias_evento d ON d.id = c.dia_evento_id
            WHERE d.evento_id = ? AND ac.recalculo_requerido = 1
        ");
        $stmt->execute([$evento_id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CÁLCULO POR CHARLA
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Calcula asistencia de TODOS los asistentes para una charla.
     *
     * Algoritmo:
     *  1. Obtener todos los movimientos del salón en la ventana temporal.
     *  2. Agrupar por asistente → construir intervalos (checkin, checkout).
     *  3. Para cada intervalo, calcular overlap con la ventana de la charla.
     *  4. Sumar minutos totales de overlap.
     *  5. cuenta_asistencia = minutos >= umbral.
     *
     * Escenario 07 (charlas consecutivas sin salir):
     *  Cubierto automáticamente — si el intervalo del asistente abarca
     *  múltiples charlas, aparecerá en cada una con el overlap correcto.
     *
     * Salones espejo (charla_padre_id):
     *  Los movimientos de todos los salones hijos también cuentan.
     */
    private function calcularCharla(array $charla, int $evento_id): int
    {
        $charla_id    = (int)$charla['id'];
        $salon_id     = (int)$charla['salon_id'];
        $hora_inicio  = strtotime($charla['hora_inicio']);
        $hora_fin     = strtotime($charla['hora_fin']);
        $buffer       = (int)$charla['buffer_pre_inicio'] * 60;  // segundos
        $umbral       = (int)$charla['umbral_min_asistencia'];    // minutos
        $dia_id       = (int)$charla['dia_evento_id'];

        $ventana_inicio = $hora_inicio - $buffer;
        $ventana_fin    = $hora_fin;

        // Salones que cuentan para esta charla (propio + hijos espejo)
        $salonesIds = $this->getSalonesParaCharla($charla_id, $salon_id);
        $placeholders = implode(',', array_fill(0, count($salonesIds), '?'));

        // Todos los movimientos en la ventana de esta charla
        $params = array_merge([$evento_id], $salonesIds, [
            date('Y-m-d H:i:s.000', $ventana_inicio),
            date('Y-m-d H:i:s.999', $ventana_fin),
        ]);

        $stmt = $this->db->prepare("
            SELECT asistente_id, tipo, timestamp
            FROM   movimientos
            WHERE  evento_id  = ?
              AND  salon_id   IN ($placeholders)
              AND  timestamp  >= ?
              AND  timestamp  <= ?
            ORDER  BY asistente_id, timestamp
        ");
        $stmt->execute($params);
        $movimientos = $stmt->fetchAll();

        if (empty($movimientos)) {
            return 0;
        }

        // Agrupar por asistente
        $porAsistente = [];
        foreach ($movimientos as $m) {
            $porAsistente[(int)$m['asistente_id']][] = $m;
        }

        // También capturar asistentes que entraron ANTES de la ventana y no salieron
        // (checkin anterior al buffer, aún dentro al inicio de la ventana)
        $previos = $this->db->prepare("
            SELECT DISTINCT ea.asistente_id
            FROM estado_asistentes ea
            JOIN asistentes a ON a.id = ea.asistente_id
            WHERE ea.salon_id   IN ($placeholders)
              AND ea.estado     = 'dentro'
              AND a.evento_id   = ?
        ");
        $previos->execute(array_merge($salonesIds, [$evento_id]));
        foreach ($previos->fetchAll() as $p) {
            $aid = (int)$p['asistente_id'];
            if (!isset($porAsistente[$aid])) {
                // Obtener su último checkin antes de la ventana
                $uc = $this->db->prepare("
                    SELECT timestamp FROM movimientos
                    WHERE asistente_id = ? AND salon_id IN ($placeholders)
                      AND tipo = 'checkin' AND timestamp < ?
                    ORDER BY timestamp DESC LIMIT 1
                ");
                $uc->execute(array_merge(
                    [$aid], $salonesIds,
                    [date('Y-m-d H:i:s.000', $ventana_inicio)]
                ));
                $row = $uc->fetch();
                if ($row) {
                    $porAsistente[$aid][] = ['asistente_id' => $aid, 'tipo' => 'checkin', 'timestamp' => $row['timestamp']];
                }
            }
        }

        $insertados = 0;

        foreach ($porAsistente as $asistente_id => $movs) {
            $intervalos = $this->construirIntervalos($movs, $ventana_fin);

            $minutos_total = 0;
            $primer_checkin = null;
            $ultimo_checkout = null;
            $tiene_checkout_real = false;

            foreach ($intervalos as [$ci_ts, $co_ts, $es_inferido]) {
                // Overlap con la ventana de la charla
                $overlap_inicio = max($ci_ts, $ventana_inicio);
                $overlap_fin    = min($co_ts, $ventana_fin);

                if ($overlap_fin <= $overlap_inicio) {
                    continue;
                }

                $minutos_total += (int)(($overlap_fin - $overlap_inicio) / 60);

                if ($primer_checkin === null) {
                    $primer_checkin = date('Y-m-d H:i:s', $ci_ts);
                }
                $ultimo_checkout = $es_inferido ? null : date('Y-m-d H:i:s', $co_ts);
                if (!$es_inferido) {
                    $tiene_checkout_real = true;
                }
            }

            if ($primer_checkin === null || $minutos_total <= 0) {
                continue;
            }

            $cuenta   = $minutos_total >= $umbral ? 1 : 0;
            $calidad  = $tiene_checkout_real ? 'real' : 'inferido';

            try {
                $this->db->prepare("
                    INSERT INTO asistencia_calculada
                        (evento_id, asistente_id, charla_id, dia_evento_id,
                         checkin_real, checkout_real, minutos_presentes,
                         cuenta_asistencia, calidad_dato, calculado_at)
                    VALUES
                        (?, ?, ?, ?,
                         ?, ?, ?,
                         ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE
                        checkin_real      = VALUES(checkin_real),
                        checkout_real     = VALUES(checkout_real),
                        minutos_presentes = VALUES(minutos_presentes),
                        cuenta_asistencia = VALUES(cuenta_asistencia),
                        calidad_dato      = VALUES(calidad_dato),
                        calculado_at      = NOW()
                ")->execute([
                    $evento_id, $asistente_id, $charla_id, $dia_id,
                    $primer_checkin, $ultimo_checkout, $minutos_total,
                    $cuenta, $calidad,
                ]);
                $insertados++;
            } catch (Throwable) {
                // continuar con siguiente asistente
            }
        }

        return $insertados;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Convierte una lista de movimientos ordenados cronológicamente
     * en intervalos [checkin_ts, checkout_ts, es_inferido].
     *
     * Si el último checkin no tiene checkout → se usa fin_ventana como cierre
     * y se marca como inferido.
     */
    private function construirIntervalos(array $movs, int $fin_ventana): array
    {
        $intervalos    = [];
        $pending_ts    = null;

        foreach ($movs as $m) {
            $ts = strtotime($m['timestamp']);

            if ($m['tipo'] === 'checkin') {
                // Si había un checkin sin cerrar, cerrarlo como inferido
                if ($pending_ts !== null) {
                    $intervalos[] = [$pending_ts, $fin_ventana, true];
                }
                $pending_ts = $ts;

            } elseif ($m['tipo'] === 'checkout' && $pending_ts !== null) {
                $intervalos[] = [$pending_ts, $ts, false];
                $pending_ts   = null;
            }
        }

        // Checkin pendiente al final → inferido
        if ($pending_ts !== null) {
            $intervalos[] = [$pending_ts, $fin_ventana, true];
        }

        return $intervalos;
    }

    /**
     * Devuelve el salon_id propio + salon_ids de salas espejo (charla_padre_id).
     *
     * @return int[]
     */
    private function getSalonesParaCharla(int $charla_id, int $salon_id): array
    {
        // Salas espejo: charlas que tienen este charla como padre
        $stmt = $this->db->prepare("
            SELECT salon_id FROM charlas
            WHERE charla_padre_id = ? AND cancelada = 0
        ");
        $stmt->execute([$charla_id]);
        $hijos = $stmt->fetchAll(PDO::FETCH_COLUMN);

        return array_unique(array_merge([$salon_id], $hijos));
    }

    private function getCharlas(int $evento_id, ?int $dia_id): array
    {
        $sql = "
            SELECT c.id, c.salon_id, c.dia_evento_id, c.charla_padre_id,
                   c.hora_inicio, c.hora_fin,
                   c.umbral_min_asistencia, c.buffer_pre_inicio
            FROM   charlas c
            JOIN   dias_evento d ON d.id = c.dia_evento_id
            WHERE  d.evento_id = ?
              AND  c.cancelada = 0
        ";
        $params = [$evento_id];

        if ($dia_id) {
            $sql    .= ' AND c.dia_evento_id = ?';
            $params[] = $dia_id;
        }

        $sql .= ' ORDER BY c.hora_inicio';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function limpiarResultados(int $evento_id, ?int $dia_id): void
    {
        if ($dia_id) {
            $this->db->prepare("
                DELETE FROM asistencia_calculada
                WHERE evento_id = ? AND dia_evento_id = ?
            ")->execute([$evento_id, $dia_id]);
        } else {
            $this->db->prepare("
                DELETE FROM asistencia_calculada WHERE evento_id = ?
            ")->execute([$evento_id]);
        }
    }
}
