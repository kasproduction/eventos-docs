<?php

if (!Auth::esAdmin()) {
    Response::error(403, 'Solo administradores pueden cambiar el tipo de tótem');
}

$totem_id = $GLOBALS['totem_id_param'] ?? 0;
$input    = json_decode(file_get_contents('php://input'), true);
$tipo     = $input['tipo'] ?? '';

if (!in_array($tipo, ['entrada', 'salida', 'bidireccional'], true)) {
    Response::error(400, 'tipo debe ser: entrada | salida | bidireccional');
}

$db   = DB::get();
$stmt = $db->prepare("UPDATE totems SET tipo = ? WHERE id = ?");
$stmt->execute([$tipo, $totem_id]);

if ($stmt->rowCount() === 0) {
    Response::error(404, 'Tótem no encontrado');
}

Response::json(['ok' => true, 'totem_id' => $totem_id, 'tipo' => $tipo]);
