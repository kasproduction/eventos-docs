<?php

$salon_id = $GLOBALS['salon_id_param'] ?? 0;
if (!$salon_id) {
    Response::error(400, 'salon_id requerido');
}

$input = json_decode(file_get_contents('php://input'), true);
$db    = DB::get();

$stmt = $db->prepare("SELECT * FROM salones WHERE id = ? AND evento_id = ?");
$stmt->execute([$salon_id, EVENTO_ID_ACTIVO]);
$salon = $stmt->fetch();
if (!$salon) {
    Response::error(404, 'Salón no encontrado');
}

$nombre    = trim($input['nombre'] ?? $salon['nombre']);
$capacidad = array_key_exists('capacidad', $input)
    ? (($input['capacidad'] !== null && $input['capacidad'] !== '') ? (int)$input['capacidad'] : null)
    : $salon['capacidad'];
$activo    = isset($input['activo']) ? (int)(bool)$input['activo'] : (int)$salon['activo'];

if (!$nombre) {
    Response::error(400, 'nombre requerido');
}

$db->prepare("
    UPDATE salones SET nombre = ?, capacidad = ?, activo = ? WHERE id = ?
")->execute([$nombre, $capacidad, $activo, $salon_id]);

Response::json(['ok' => true]);
