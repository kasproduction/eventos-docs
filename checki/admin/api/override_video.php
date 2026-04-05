<?php

if (empty($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
    Response::adminError(400, 'No se recibió ningún video');
}

$ext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['mp4', 'webm'], true)) {
    Response::adminError(400, 'Extensión no permitida. Usa mp4 o webm.');
}

$base = realpath(__DIR__ . '/../../uploads/pantallas');
if (!$base) {
    mkdir(__DIR__ . '/../../uploads/pantallas', 0755, true);
    $base = realpath(__DIR__ . '/../../uploads/pantallas');
}

$dir = $base . DIRECTORY_SEPARATOR . 'override';
if (!is_dir($dir)) mkdir($dir, 0755, true);

$filename = 'video.' . $ext;
$dest     = $dir . DIRECTORY_SEPARATOR . $filename;

if (!move_uploaded_file($_FILES['video']['tmp_name'], $dest)) {
    Response::adminError(500, 'Error al guardar el archivo');
}

$loop    = isset($_POST['loop_video']) ? (int)(bool)$_POST['loop_video'] : 1;
$fit     = in_array($_POST['video_fit'] ?? '', ['contain','cover'], true) ? $_POST['video_fit'] : 'contain';
$minutos = isset($_POST['retorno_minutos']) ? (int)$_POST['retorno_minutos'] : 0;

$retorno_en = null;
if ($minutos > 0) {
    $stmt = DB::get()->query("SELECT DATE_ADD(NOW(), INTERVAL {$minutos} MINUTE)");
    $retorno_en = $stmt->fetchColumn();
}

$db = DB::get();
$pairs = [
    ['agenda_override',            'video'],
    ['agenda_override_video',      $filename],
    ['agenda_override_loop_video', (string)$loop],
    ['agenda_override_video_fit',  $fit],
    ['agenda_override_retorno_en', $retorno_en],
];
foreach ($pairs as [$clave, $valor]) {
    $db->prepare("
        INSERT INTO configuracion (clave, valor) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE valor = ?
    ")->execute([$clave, $valor, $valor]);
}

Response::json(['ok' => true]);
