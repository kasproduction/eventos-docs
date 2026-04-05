<?php

if (empty($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
    Response::adminError(400, 'No se recibió ninguna imagen');
}

$ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
    Response::adminError(400, 'Extensión no permitida. Usa jpg, png o webp.');
}

// Calcular ruta base
$base = realpath(__DIR__ . '/../../uploads/pantallas');
if (!$base) {
    mkdir(__DIR__ . '/../../uploads/pantallas', 0755, true);
    $base = realpath(__DIR__ . '/../../uploads/pantallas');
}

$dir = $base . DIRECTORY_SEPARATOR . 'override';
if (!is_dir($dir)) mkdir($dir, 0755, true);

$filename = 'imagen.' . $ext;
$dest     = $dir . DIRECTORY_SEPARATOR . $filename;

if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $dest)) {
    Response::adminError(500, 'Error al guardar el archivo');
}

// Calcular retorno_en
$retorno_en = null;
$minutos    = isset($_POST['retorno_minutos']) ? (int)$_POST['retorno_minutos'] : 0;
if ($minutos > 0) {
    $stmt = DB::get()->query("SELECT DATE_ADD(NOW(), INTERVAL {$minutos} MINUTE)");
    $retorno_en = $stmt->fetchColumn();
}

$db = DB::get();
$db->prepare("
    INSERT INTO configuracion (clave, valor) VALUES ('agenda_override', 'imagen')
    ON DUPLICATE KEY UPDATE valor = 'imagen'
")->execute();

$db->prepare("
    INSERT INTO configuracion (clave, valor) VALUES ('agenda_override_imagen', ?)
    ON DUPLICATE KEY UPDATE valor = ?
")->execute([$filename, $filename]);

$db->prepare("
    INSERT INTO configuracion (clave, valor) VALUES ('agenda_override_retorno_en', ?)
    ON DUPLICATE KEY UPDATE valor = ?
")->execute([$retorno_en, $retorno_en]);

Response::json(['ok' => true]);
