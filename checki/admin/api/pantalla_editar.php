<?php

$id   = $GLOBALS['pantalla_id_param'];
$body = json_decode(file_get_contents('php://input'), true);

// Campos base (siempre disponibles desde v1)
$campos_base = [];
$vals_base   = [];

if (isset($body['nombre'])) {
    $nombre = trim($body['nombre']);
    if (!$nombre) Response::adminError(400, 'El nombre no puede estar vacío');
    $campos_base[] = 'nombre = ?';
    $vals_base[]   = $nombre;
}

if (array_key_exists('salon_id', $body)) {
    $campos_base[] = 'salon_id = ?';
    $vals_base[]   = ($body['salon_id'] !== '' && $body['salon_id'] !== null) ? (int)$body['salon_id'] : null;
}

if (isset($body['activa'])) {
    $campos_base[] = 'activa = ?';
    $vals_base[]   = (int)(bool)$body['activa'];
}

// Campos v2 (modo, retorno_en)
$campos_v2 = $campos_base;
$vals_v2   = $vals_base;

if (isset($body['modo'])) {
    if (!in_array($body['modo'], ['agenda','imagen','video','apagada'], true)) {
        Response::adminError(400, 'Modo inválido');
    }
    $campos_v2[] = 'modo = ?';
    $vals_v2[]   = $body['modo'];
}

if (array_key_exists('retorno_minutos', $body)) {
    $min = ($body['retorno_minutos'] !== null && $body['retorno_minutos'] !== '') ? (int)$body['retorno_minutos'] : 0;
    if ($min > 0) {
        $campos_v2[] = 'retorno_en = DATE_ADD(NOW(), INTERVAL ? MINUTE)';
        $vals_v2[]   = $min;
    } else {
        $campos_v2[] = 'retorno_en = NULL';
    }
}

// Campos v3 (loop_video, video_fit)
$campos_v3 = $campos_v2;
$vals_v3   = $vals_v2;

if (isset($body['loop_video'])) {
    $campos_v3[] = 'loop_video = ?';
    $vals_v3[]   = (int)(bool)$body['loop_video'];
}

if (isset($body['video_fit'])) {
    if (!in_array($body['video_fit'], ['contain','cover'], true)) {
        Response::adminError(400, 'video_fit inválido');
    }
    $campos_v3[] = 'video_fit = ?';
    $vals_v3[]   = $body['video_fit'];
}

if (!$campos_v3) Response::adminError(400, 'Sin campos para actualizar');

$db = DB::get();

// Intentar v3, luego v2, luego base
$intentos = [
    [$campos_v3, $vals_v3],
    [$campos_v2, $vals_v2],
    [$campos_base, $vals_base],
];

foreach ($intentos as [$campos, $vals]) {
    if (!$campos) continue;
    try {
        $vals[] = $id;
        $db->prepare("UPDATE agenda_pantallas SET " . implode(', ', $campos) . " WHERE id = ?")
           ->execute($vals);
        Response::json(['ok' => true]);
        exit;
    } catch (\PDOException $e) {
        // Columnas de esta versión no existen, probar versión anterior
    }
}

Response::adminError(500, 'No se pudo actualizar la pantalla');
