<?php

/**
 * ImportService — orquesta la importación al core MySQL.
 *
 * No sabe de dónde vienen los datos. Recibe DTOs del contrato estándar
 * y los inserta/actualiza en la base de datos.
 *
 * Uso:
 *   $adapter = new CsvAdapter();
 *   $dtos    = $adapter->parseAsistentes($csvString);
 *   $result  = ImportService::importarAsistentes($dtos, $evento_id, $adapter->nombre());
 */
class ImportService
{
    // ─────────────────────────────────────────────────────────
    // ASISTENTES
    // ─────────────────────────────────────────────────────────

    /**
     * @param  AsistenteDTO[] $dtos
     * @return array { insertados, actualizados, omitidos, errores[] }
     */
    public static function importarAsistentes(
        array  $dtos,
        int    $evento_id,
        string $fuente
    ): array {
        $db = DB::get();

        $insertados   = 0;
        $actualizados = 0;
        $omitidos     = 0;
        $errores      = [];

        $stmt = $db->prepare("
            INSERT INTO asistentes
                (evento_id, uid_qr, nombre, email, empresa, external_id, fuente, metadata_json)
            VALUES
                (:evento_id, :uid_qr, :nombre, :email, :empresa, :external_id, :fuente, :metadata_json)
            ON DUPLICATE KEY UPDATE
                nombre       = VALUES(nombre),
                email        = VALUES(email),
                empresa      = VALUES(empresa),
                external_id  = VALUES(external_id),
                fuente       = VALUES(fuente),
                metadata_json = VALUES(metadata_json),
                updated_at   = CURRENT_TIMESTAMP
        ");

        foreach ($dtos as $i => $dto) {
            if (!$dto->esValido()) {
                $omitidos++;
                $errores[] = "DTO $i inválido: uid_qr o nombre vacío";
                continue;
            }

            try {
                // Detectar si ya existe para reportar inserción vs actualización
                $existe = $db->prepare("
                    SELECT id FROM asistentes
                    WHERE evento_id = ? AND uid_qr = ?
                    LIMIT 1
                ");
                $existe->execute([$evento_id, $dto->uid_qr]);
                $esNuevo = !$existe->fetch();

                $stmt->execute([
                    ':evento_id'     => $evento_id,
                    ':uid_qr'        => $dto->uid_qr,
                    ':nombre'        => $dto->nombre,
                    ':email'         => $dto->email,
                    ':empresa'       => $dto->empresa,
                    ':external_id'   => $dto->external_id,
                    ':fuente'        => $fuente,
                    ':metadata_json' => $dto->metadata ? json_encode($dto->metadata, JSON_UNESCAPED_UNICODE) : null,
                ]);

                $esNuevo ? $insertados++ : $actualizados++;

            } catch (Throwable $e) {
                $omitidos++;
                $errores[] = "uid_qr={$dto->uid_qr}: " . $e->getMessage();
            }
        }

        $resultado = compact('insertados', 'actualizados', 'omitidos', 'errores');

        self::logSync($db, $evento_id, $fuente, 'asistentes', $insertados + $actualizados, count($errores));

        return $resultado;
    }

    // ─────────────────────────────────────────────────────────
    // AGENDA
    // ─────────────────────────────────────────────────────────

    /**
     * Importa charlas para un día específico del evento.
     * Si la charla ya existe (mismo título + salón + día), la actualiza.
     *
     * @param  CharlaDTO[] $dtos
     * @return array { insertadas, actualizadas, omitidas, errores[] }
     */
    public static function importarAgenda(
        array  $dtos,
        int    $dia_evento_id,
        string $fuente
    ): array {
        $db = DB::get();

        // Obtener evento_id desde el día
        $stmt = $db->prepare("SELECT evento_id FROM dias_evento WHERE id = ? LIMIT 1");
        $stmt->execute([$dia_evento_id]);
        $dia = $stmt->fetch();

        if (!$dia) {
            return ['insertadas' => 0, 'actualizadas' => 0, 'omitidas' => count($dtos), 'errores' => ['dia_evento_id no encontrado']];
        }

        $evento_id  = (int) $dia['evento_id'];
        $insertadas = $actualizadas = $omitidas = 0;
        $errores    = [];

        foreach ($dtos as $i => $dto) {
            if (!$dto->esValido()) {
                $omitidas++;
                $errores[] = "DTO $i inválido";
                continue;
            }

            try {
                // Resolver salon_id por nombre dentro del evento
                $salonStmt = $db->prepare("
                    SELECT id FROM salones
                    WHERE evento_id = ? AND nombre = ?
                    LIMIT 1
                ");
                $salonStmt->execute([$evento_id, $dto->salon_nombre]);
                $salon = $salonStmt->fetch();

                if (!$salon) {
                    // Crear salón si no existe
                    $db->prepare("
                        INSERT INTO salones (evento_id, nombre) VALUES (?, ?)
                    ")->execute([$evento_id, $dto->salon_nombre]);
                    $salon_id = (int) $db->lastInsertId();
                } else {
                    $salon_id = (int) $salon['id'];
                }

                // ¿Ya existe esta charla? (mismo título + salón + día)
                $existe = $db->prepare("
                    SELECT id FROM charlas
                    WHERE dia_evento_id = ? AND salon_id = ? AND titulo = ?
                    LIMIT 1
                ");
                $existe->execute([$dia_evento_id, $salon_id, $dto->titulo]);
                $charlaExistente = $existe->fetch();

                if ($charlaExistente) {
                    // Actualizar — guarda auditoría automáticamente en agenda_cambios
                    // si los horarios cambiaron (esto lo hará el endpoint admin en Fase 4)
                    $db->prepare("
                        UPDATE charlas
                        SET hora_inicio  = ?,
                            hora_fin     = ?,
                            ponente      = ?,
                            orden_en_dia = ?
                        WHERE id = ?
                    ")->execute([
                        $dto->hora_inicio,
                        $dto->hora_fin,
                        $dto->ponente,
                        $dto->orden_en_dia,
                        (int) $charlaExistente['id'],
                    ]);
                    $actualizadas++;
                } else {
                    $db->prepare("
                        INSERT INTO charlas
                            (dia_evento_id, salon_id, titulo, ponente,
                             hora_inicio, hora_fin,
                             hora_inicio_original, hora_fin_original,
                             orden_en_dia)
                        VALUES
                            (?, ?, ?, ?,
                             ?, ?,
                             ?, ?,
                             ?)
                    ")->execute([
                        $dia_evento_id, $salon_id, $dto->titulo, $dto->ponente,
                        $dto->hora_inicio, $dto->hora_fin,
                        $dto->hora_inicio, $dto->hora_fin,   // original = igual al inicio
                        $dto->orden_en_dia,
                    ]);
                    $insertadas++;
                }

            } catch (Throwable $e) {
                $omitidas++;
                $errores[] = "'{$dto->titulo}': " . $e->getMessage();
            }
        }

        $resultado = compact('insertadas', 'actualizadas', 'omitidas', 'errores');

        self::logSync($db, $evento_id, $fuente, 'agenda', $insertadas + $actualizadas, count($errores));

        return $resultado;
    }

    // ─────────────────────────────────────────────────────────
    // LOG
    // ─────────────────────────────────────────────────────────

    private static function logSync(PDO $db, int $evento_id, string $fuente, string $tipo, int $registros, int $errores): void
    {
        try {
            $db->prepare("
                INSERT INTO sync_log (evento_id, fuente, tipo, registros, errores)
                VALUES (?, ?, ?, ?, ?)
            ")->execute([$evento_id, $fuente, $tipo, $registros, $errores]);
        } catch (Throwable) {
            // No interrumpir el flujo si falla el log
        }
    }
}
