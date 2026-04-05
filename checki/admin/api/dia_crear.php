<?php

$input  = json_decode(file_get_contents('php://input'), true);
$fecha  = trim($input['fecha']  ?? '');
$nombre = trim($input['nombre'] ?? '');

if (!$fecha) {
    Response::error(400, 'fecha requerida');
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || !checkdate(
    (int)substr($fecha, 5, 2),
    (int)substr($fecha, 8, 2),
    (int)substr($fecha, 0, 4)
)) {
    Response::error(400, 'fecha debe tener formato YYYY-MM-DD válido');
}

$db = DB::get();

$stmt = $db->prepare("SELECT COALESCE(MAX(orden), 0) + 1 FROM dias_evento WHERE evento_id = ?");
$stmt->execute([EVENTO_ID_ACTIVO]);
$orden = (int)$stmt->fetchColumn();

try {
    $db->prepare("
        INSERT INTO dias_evento (evento_id, fecha, nombre, orden)
        VALUES (?, ?, ?, ?)
    ")->execute([EVENTO_ID_ACTIVO, $fecha, $nombre ?: null, $orden]);
} catch (PDOException $e) {
    if (str_contains($e->getMessage(), 'Duplicate') || str_contains($e->getMessage(), 'duplicate')) {
        Response::error(409, 'Ya existe un día con esa fecha');
    }
    throw $e;
}

Response::json(['ok' => true, 'id' => (int)$db->lastInsertId()]);
