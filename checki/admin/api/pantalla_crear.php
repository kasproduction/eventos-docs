<?php

$body       = json_decode(file_get_contents('php://input'), true);
$nombre     = trim($body['nombre'] ?? '');
$salon_id   = isset($body['salon_id']) && $body['salon_id'] !== '' ? (int)$body['salon_id'] : null;
$modo       = in_array($body['modo'] ?? '', ['agenda','imagen','video','apagada'], true) ? $body['modo'] : 'agenda';
$loop_video = isset($body['loop_video']) ? (int)(bool)$body['loop_video'] : 1;
$video_fit  = in_array($body['video_fit'] ?? '', ['contain','cover'], true) ? $body['video_fit'] : 'contain';
$minutos    = isset($body['retorno_minutos']) ? (int)$body['retorno_minutos'] : 0;

if (!$nombre) Response::adminError(400, 'El nombre es obligatorio');

$db  = DB::get();
$id  = null;

// v3: con video_path, loop_video, video_fit
try {
    $campos       = 'nombre, salon_id, modo, loop_video, video_fit, activa';
    $placeholders = '?, ?, ?, ?, ?, ?';
    $vals         = [$nombre, $salon_id, $modo, $loop_video, $video_fit, 1];
    if ($minutos > 0) {
        $campos       .= ', retorno_en';
        $placeholders .= ', DATE_ADD(NOW(), INTERVAL ? MINUTE)';
        $vals[]        = $minutos;
    }
    $db->prepare("INSERT INTO agenda_pantallas ({$campos}) VALUES ({$placeholders})")->execute($vals);
    $id = (int)$db->lastInsertId();
} catch (\PDOException $e) {
    // v2: con modo, sin columnas de video
    try {
        $campos       = 'nombre, salon_id, modo, activa';
        $placeholders = '?, ?, ?, ?';
        $vals         = [$nombre, $salon_id, $modo, 1];
        if ($minutos > 0) {
            $campos       .= ', retorno_en';
            $placeholders .= ', DATE_ADD(NOW(), INTERVAL ? MINUTE)';
            $vals[]        = $minutos;
        }
        $db->prepare("INSERT INTO agenda_pantallas ({$campos}) VALUES ({$placeholders})")->execute($vals);
        $id = (int)$db->lastInsertId();
    } catch (\PDOException $e) {
        // v1: columnas originales
        $db->prepare("INSERT INTO agenda_pantallas (nombre, salon_id, activa) VALUES (?, ?, 1)")
           ->execute([$nombre, $salon_id]);
        $id = (int)$db->lastInsertId();
    }
}

Response::json(['ok' => true, 'id' => $id]);
