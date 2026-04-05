<?php

$totem_id = $GLOBALS['totem_id_param'] ?? 0;
if (!$totem_id) {
    Response::error(400, 'totem_id requerido');
}

$input = json_decode(file_get_contents('php://input'), true);
$db    = DB::get();

$stmt = $db->prepare("SELECT * FROM totems WHERE id = ? AND evento_id = ?");
$stmt->execute([$totem_id, EVENTO_ID_ACTIVO]);
$totem = $stmt->fetch();
if (!$totem) {
    Response::error(404, 'Tótem no encontrado');
}

$salon_id = array_key_exists('salon_id', $input)
            ? (int)$input['salon_id']
            : (int)$totem['salon_id'];
$nombre   = trim($input['nombre'] ?? $totem['nombre']);
$tipo     = $input['tipo']        ?? $totem['tipo'];
$ip_local = array_key_exists('ip_local', $input)
            ? (trim($input['ip_local']) ?: null)
            : $totem['ip_local'];
$activo   = isset($input['activo']) ? (int)(bool)$input['activo'] : (int)$totem['activo'];

if (!$nombre) {
    Response::error(400, 'nombre requerido');
}
if (!in_array($tipo, ['entrada', 'salida', 'bidireccional'], true)) {
    Response::error(400, 'tipo debe ser: entrada | salida | bidireccional');
}
if ($salon_id !== (int)$totem['salon_id']) {
    $chkSalon = $db->prepare("SELECT id FROM salones WHERE id = ? AND evento_id = ? LIMIT 1");
    $chkSalon->execute([$salon_id, EVENTO_ID_ACTIVO]);
    if (!$chkSalon->fetch()) {
        Response::error(404, 'Salón no encontrado en el evento activo');
    }
}

$db->prepare("
    UPDATE totems SET salon_id = ?, nombre = ?, tipo = ?, ip_local = ?, activo = ? WHERE id = ?
")->execute([$salon_id, $nombre, $tipo, $ip_local, $activo, $totem_id]);

Response::json(['ok' => true]);
