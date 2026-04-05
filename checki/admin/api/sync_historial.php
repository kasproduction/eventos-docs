<?php

/**
 * GET /admin/api/sync-historial
 * Devuelve el historial de importaciones del evento.
 */

$evento_id = EVENTO_ID_ACTIVO;
$db        = DB::get();

$stmt = $db->prepare("
    SELECT
        id,
        fuente,
        tipo,
        registros,
        errores,
        CASE WHEN errores = 0 THEN 'ok' ELSE 'con_errores' END AS estado,
        timestamp
    FROM sync_log
    WHERE evento_id = ?
    ORDER BY timestamp DESC
    LIMIT 50
");
$stmt->execute([$evento_id]);

Response::json(['historial' => $stmt->fetchAll()]);
