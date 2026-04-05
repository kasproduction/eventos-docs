<?php

/**
 * ExportService — genera reportes de asistencia en múltiples formatos.
 *
 * Formatos disponibles:
 *
 *  detalle        Una fila por asistente+charla. Fácil de filtrar en Excel.
 *  por_asistente  Matriz: asistente = fila, charla = columna (S/N + minutos).
 *  por_charla     Una fila por charla con totales y porcentajes.
 *  resumen        Resumen ejecutivo del evento completo o de un día.
 *
 * Todos los CSV incluyen BOM UTF-8 para que Excel los abra bien.
 * Separador: punto y coma (;) — compatible con Excel en locales de español.
 */
class ExportService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // ─────────────────────────────────────────────────────────────────────
    // DETALLE  — una fila por asistente × charla
    // ─────────────────────────────────────────────────────────────────────

    /**
     * @return resource  stream listo para fpassthru() o fclose()
     */
    public function generarDetalle(int $evento_id, ?int $dia_id = null)
    {
        $where  = 'WHERE ac.evento_id = ?';
        $params = [$evento_id];
        if ($dia_id) { $where .= ' AND ac.dia_evento_id = ?'; $params[] = $dia_id; }

        $rows = $this->query("
            SELECT
                a.nombre,
                a.email,
                a.empresa,
                a.uid_qr,
                a.fuente,
                d.fecha,
                d.nombre              AS dia_nombre,
                d.orden               AS dia_orden,
                s.nombre              AS salon,
                c.titulo              AS charla,
                TIME(c.hora_inicio)   AS hora_inicio,
                TIME(c.hora_fin)      AS hora_fin,
                TIME(ac.checkin_real) AS checkin,
                COALESCE(TIME(ac.checkout_real), '') AS checkout,
                ac.minutos_presentes,
                CASE ac.cuenta_asistencia WHEN 1 THEN 'SI' ELSE 'NO' END AS cuenta,
                ac.calidad_dato
            FROM asistencia_calculada ac
            JOIN asistentes  a ON a.id  = ac.asistente_id
            JOIN charlas     c ON c.id  = ac.charla_id
            JOIN dias_evento d ON d.id  = ac.dia_evento_id
            JOIN salones     s ON s.id  = c.salon_id
            $where
            ORDER BY a.nombre, d.orden, c.hora_inicio
        ", $params);

        $headers = [
            'Nombre', 'Email', 'Empresa', 'QR', 'Fuente',
            'Fecha', 'Día', 'Nro día',
            'Salón', 'Charla', 'Hora inicio', 'Hora fin',
            'Check-in', 'Check-out', 'Minutos', 'Cuenta asistencia', 'Calidad dato',
        ];

        return $this->toCsv($headers, $rows);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POR ASISTENTE  — matriz asistente × charla
    // ─────────────────────────────────────────────────────────────────────

    public function generarPorAsistente(int $evento_id, ?int $dia_id = null)
    {
        // Obtener la lista de charlas (columnas del Excel)
        $whereCharla  = $dia_id ? 'AND c.dia_evento_id = ?' : '';
        $paramsCharla = $dia_id ? [$evento_id, $dia_id] : [$evento_id];

        $charlas = $this->query("
            SELECT c.id, c.titulo, d.fecha, TIME(c.hora_inicio) AS inicio, s.nombre AS salon
            FROM charlas c
            JOIN dias_evento d ON d.id = c.dia_evento_id
            JOIN salones s ON s.id = c.salon_id
            WHERE d.evento_id = ? AND c.cancelada = 0 $whereCharla
            ORDER BY d.orden, c.hora_inicio
        ", $paramsCharla);

        if (empty($charlas)) {
            return $this->toCsv(['Sin datos'], []);
        }

        // Construir headers dinámicos
        $headers = ['Nombre', 'Email', 'Empresa', 'Total charlas', 'Total minutos'];
        foreach ($charlas as $c) {
            $headers[] = "[{$c['fecha']}] {$c['salon']} — {$c['titulo']} ({$c['inicio']})";
        }

        // Obtener todos los asistentes con resultados
        $whereAc  = $dia_id ? 'AND ac.dia_evento_id = ?' : '';
        $paramsAc = $dia_id ? [$evento_id, $dia_id] : [$evento_id];

        $asistentes = $this->query("
            SELECT DISTINCT a.id, a.nombre, a.email, a.empresa
            FROM asistencia_calculada ac
            JOIN asistentes a ON a.id = ac.asistente_id
            WHERE ac.evento_id = ? $whereAc
            ORDER BY a.nombre
        ", $paramsAc);

        // Obtener mapa asistente_id → charla_id → datos
        $paramsMap = $dia_id ? [$evento_id, $dia_id] : [$evento_id];
        $resultados = $this->query("
            SELECT asistente_id, charla_id, cuenta_asistencia, minutos_presentes, calidad_dato
            FROM asistencia_calculada
            WHERE evento_id = ? " . ($dia_id ? 'AND dia_evento_id = ?' : ''),
            $paramsMap
        );

        $mapa = [];
        foreach ($resultados as $r) {
            $mapa[$r['asistente_id']][$r['charla_id']] = $r;
        }

        $rows = [];
        foreach ($asistentes as $ast) {
            $aid           = $ast['id'];
            $total_charlas = 0;
            $total_minutos = 0;
            $row           = [$ast['nombre'], $ast['email'] ?? '', $ast['empresa'] ?? ''];

            $celdas = [];
            foreach ($charlas as $c) {
                $cid = $c['id'];
                if (isset($mapa[$aid][$cid])) {
                    $r = $mapa[$aid][$cid];
                    $celdas[] = ($r['cuenta_asistencia'] ? 'SI' : 'NO')
                                . ' (' . $r['minutos_presentes'] . 'min'
                                . ($r['calidad_dato'] !== 'real' ? ' *' : '')
                                . ')';
                    if ($r['cuenta_asistencia']) {
                        $total_charlas++;
                    }
                    $total_minutos += $r['minutos_presentes'];
                } else {
                    $celdas[] = '';
                }
            }

            $row[] = $total_charlas;
            $row[] = $total_minutos;
            $rows[] = array_merge($row, $celdas);
        }

        return $this->toCsv($headers, $rows);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POR CHARLA  — una fila por charla con totales
    // ─────────────────────────────────────────────────────────────────────

    public function generarPorCharla(int $evento_id, ?int $dia_id = null)
    {
        $where  = $dia_id ? 'AND c.dia_evento_id = ?' : '';
        $params = $dia_id ? [$evento_id, $dia_id] : [$evento_id];

        $rows = $this->query("
            SELECT
                d.fecha,
                d.nombre                                                       AS dia_nombre,
                s.nombre                                                       AS salon,
                c.titulo,
                c.ponente,
                TIME(c.hora_inicio)                                            AS hora_inicio,
                TIME(c.hora_fin)                                               AS hora_fin,
                c.umbral_min_asistencia,
                COUNT(ac.asistente_id)                                         AS total_registros,
                COALESCE(SUM(ac.cuenta_asistencia), 0)                        AS total_cuentan,
                COALESCE(ROUND(AVG(ac.minutos_presentes), 1), 0)              AS promedio_min,
                COALESCE(MAX(ac.minutos_presentes), 0)                        AS max_min,
                COALESCE(MIN(NULLIF(ac.minutos_presentes, 0)), 0)             AS min_min,
                COALESCE(SUM(CASE WHEN ac.calidad_dato='inferido' THEN 1 END), 0) AS inferidos,
                COALESCE(SUM(CASE WHEN ac.calidad_dato='corregido' THEN 1 END), 0) AS corregidos
            FROM charlas c
            JOIN dias_evento d ON d.id = c.dia_evento_id
            JOIN salones s ON s.id = c.salon_id
            LEFT JOIN asistencia_calculada ac ON ac.charla_id = c.id
            WHERE d.evento_id = ? AND c.cancelada = 0 $where
            GROUP BY c.id, d.fecha, d.nombre, s.nombre, c.titulo, c.ponente,
                     c.hora_inicio, c.hora_fin, c.umbral_min_asistencia
            ORDER BY d.orden, c.hora_inicio
        ", $params);

        // Añadir % de asistencia como columna calculada
        $filas = [];
        foreach ($rows as $r) {
            $pct    = $r['total_registros'] > 0
                    ? round(($r['total_cuentan'] / $r['total_registros']) * 100, 1)
                    : 0;
            $r['pct_asistencia'] = $pct . '%';
            $filas[] = array_values($r);
        }

        $headers = [
            'Fecha', 'Día', 'Salón', 'Charla', 'Ponente',
            'Hora inicio', 'Hora fin', 'Umbral mín (min)',
            'Registros', 'Cuentan', 'Promedio min', 'Máx min', 'Mín min',
            'Inferidos', 'Corregidos', '% Asistencia',
        ];

        return $this->toCsv($headers, $filas);
    }

    // ─────────────────────────────────────────────────────────────────────
    // RESUMEN  — una fila por asistente con totales agregados
    // ─────────────────────────────────────────────────────────────────────

    public function generarResumen(int $evento_id, ?int $dia_id = null)
    {
        $where  = $dia_id ? 'AND ac.dia_evento_id = ?' : '';
        $params = $dia_id ? [$evento_id, $dia_id] : [$evento_id];

        // Total días del evento (para el resumen)
        $total_dias = (int)$this->queryScalar(
            'SELECT COUNT(*) FROM dias_evento WHERE evento_id = ?',
            [$evento_id]
        );

        $rows = $this->query("
            SELECT
                a.nombre,
                a.email,
                a.empresa,
                a.fuente,
                COUNT(DISTINCT ac.dia_evento_id)                               AS dias_asistidos,
                $total_dias                                                    AS dias_totales,
                COALESCE(SUM(ac.cuenta_asistencia), 0)                        AS charlas_completas,
                COALESCE(SUM(CASE WHEN ac.cuenta_asistencia=0
                    AND ac.minutos_presentes > 0 THEN 1 END), 0)              AS charlas_parciales,
                COALESCE(SUM(ac.minutos_presentes), 0)                        AS total_minutos,
                COALESCE(SUM(CASE WHEN ac.calidad_dato='inferido' THEN 1 END), 0) AS inferidos,
                COALESCE(SUM(CASE WHEN ac.calidad_dato='real' THEN 1 END), 0) AS reales
            FROM asistentes a
            LEFT JOIN asistencia_calculada ac ON ac.asistente_id = a.id
                AND ac.evento_id = a.evento_id $where
            WHERE a.evento_id = ?
            GROUP BY a.id, a.nombre, a.email, a.empresa, a.fuente
            ORDER BY charlas_completas DESC, a.nombre
        ", array_merge($params, [$evento_id]));

        $headers = [
            'Nombre', 'Email', 'Empresa', 'Fuente',
            'Días asistidos', 'Días totales evento',
            'Charlas completas', 'Charlas parciales', 'Total minutos',
            'Datos inferidos (sin checkout)', 'Datos reales',
        ];

        return $this->toCsv($headers, $rows);
    }

    // ─────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Construye un stream CSV en memoria con BOM UTF-8.
     *
     * @param  string[]  $headers
     * @param  array[]   $rows    filas ya como arrays de escalares
     * @return resource
     */
    private function toCsv(array $headers, array $rows)
    {
        $out = fopen('php://temp', 'r+b');

        // BOM — Excel lo usa para detectar UTF-8
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, $headers, ';');

        foreach ($rows as $row) {
            // Si la fila es un array asociativo, tomar solo los valores
            fputcsv($out, array_values((array)$row), ';');
        }

        rewind($out);
        return $out;
    }

    private function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function queryScalar(string $sql, array $params = []): mixed
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}
