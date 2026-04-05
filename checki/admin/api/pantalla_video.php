<?php

$id = $GLOBALS['pantalla_id_param'];
$db = DB::get();

$stmt = $db->prepare("SELECT id FROM agenda_pantallas WHERE id = ?");
$stmt->execute([$id]);
if (!$stmt->fetch()) Response::adminError(404, 'Pantalla no encontrada');

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

$dir = $base . DIRECTORY_SEPARATOR . $id;
if (!is_dir($dir)) mkdir($dir, 0755, true);

$filename = 'video.' . $ext;
$dest     = $dir . DIRECTORY_SEPARATOR . $filename;

if (!move_uploaded_file($_FILES['video']['tmp_name'], $dest)) {
    Response::adminError(500, 'Error al guardar el archivo');
}

$db->prepare("UPDATE agenda_pantallas SET video_path = ? WHERE id = ?")
   ->execute([$filename, $id]);

Response::json(['ok' => true, 'video_path' => $filename]);
