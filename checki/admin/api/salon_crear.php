<?php

$input     = json_decode(file_get_contents('php://input'), true);
$nombre    = trim($input['nombre']    ?? '');
$capacidad = ($input['capacidad'] !== null && $input['capacidad'] !== '')
             ? (int)$input['capacidad'] : null;

if (!$nombre) {
    Response::error(400, 'nombre requerido');
}

$db = DB::get();
$db->prepare("
    INSERT INTO salones (evento_id, nombre, capacidad, activo)
    VALUES (?, ?, ?, 1)
")->execute([EVENTO_ID_ACTIVO, $nombre, $capacidad]);

Response::json(['ok' => true, 'id' => (int)$db->lastInsertId()]);
