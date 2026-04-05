<?php

$input    = json_decode(file_get_contents('php://input'), true);
$salon_id = (int)($input['salon_id'] ?? 0);
$nombre   = trim($input['nombre']   ?? '');
$tipo     = $input['tipo']          ?? 'bidireccional';
$ip_local = trim($input['ip_local'] ?? '') ?: null;

if (!$salon_id || !$nombre) {
    Response::error(400, 'salon_id y nombre son requeridos');
}
if (!in_array($tipo, ['entrada', 'salida', 'bidireccional'], true)) {
    Response::error(400, 'tipo debe ser: entrada | salida | bidireccional');
}

$db = DB::get();

$stmt = $db->prepare("SELECT id FROM salones WHERE id = ? AND evento_id = ? AND activo = 1");
$stmt->execute([$salon_id, EVENTO_ID_ACTIVO]);
if (!$stmt->fetch()) {
    Response::error(404, 'Salón no encontrado o inactivo');
}

$db->prepare("
    INSERT INTO totems (evento_id, salon_id, nombre, tipo, activo, ip_local)
    VALUES (?, ?, ?, ?, 1, ?)
")->execute([EVENTO_ID_ACTIVO, $salon_id, $nombre, $tipo, $ip_local]);

Response::json(['ok' => true, 'id' => (int)$db->lastInsertId()]);
