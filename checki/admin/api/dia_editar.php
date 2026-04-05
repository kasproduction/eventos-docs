<?php

$dia_id = $GLOBALS['dia_id_param'] ?? 0;
if (!$dia_id) {
    Response::error(400, 'dia_id requerido');
}

$input = json_decode(file_get_contents('php://input'), true);
$db    = DB::get();

$stmt = $db->prepare("SELECT * FROM dias_evento WHERE id = ? AND evento_id = ?");
$stmt->execute([$dia_id, EVENTO_ID_ACTIVO]);
$dia = $stmt->fetch();
if (!$dia) {
    Response::error(404, 'Día no encontrado');
}

$fecha  = trim($input['fecha']  ?? $dia['fecha']);
$nombre = array_key_exists('nombre', $input) ? trim($input['nombre']) : $dia['nombre'];

if (!$fecha) {
    Response::error(400, 'fecha requerida');
}

try {
    $db->prepare("
        UPDATE dias_evento SET fecha = ?, nombre = ? WHERE id = ?
    ")->execute([$fecha, $nombre ?: null, $dia_id]);
} catch (PDOException $e) {
    if (str_contains($e->getMessage(), 'Duplicate') || str_contains($e->getMessage(), 'duplicate')) {
        Response::error(409, 'Ya existe un día con esa fecha');
    }
    throw $e;
}

Response::json(['ok' => true]);
