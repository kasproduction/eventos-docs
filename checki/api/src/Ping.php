<?php

// Si el tótem manda su ID, registrar el ping
$totem_id = filter_input(INPUT_GET, 'totem_id', FILTER_VALIDATE_INT);

if ($totem_id) {
    $db = DB::get();
    $db->prepare("UPDATE totems SET ultimo_ping = NOW(3) WHERE id = ? AND activo = 1")
       ->execute([$totem_id]);
}

Response::json([
    'ok'        => true,
    'timestamp' => date('Y-m-d H:i:s'),
]);
