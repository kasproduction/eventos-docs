<?php

$id = $GLOBALS['pantalla_id_param'];
$db = DB::get();

// Verificar que existe la pantalla
$stmt = $db->prepare("SELECT id FROM agenda_pantallas WHERE id = ?");
$stmt->execute([$id]);
if (!$stmt->fetch()) Response::adminError(404, 'Pantalla no encontrada');

if (empty($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
    Response::adminError(400, 'No se recibió ninguna imagen');
}

$ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
    Response::adminError(400, 'Extensión no permitida. Usa jpg, png o webp.');
}

// Calcular ruta base — la carpeta uploads/pantallas está en la raíz del proyecto
$base = realpath(__DIR__ . '/../../uploads/pantallas');
if (!$base) {
    // Crear el directorio si no existe aún
    mkdir(__DIR__ . '/../../uploads/pantallas', 0755, true);
    $base = realpath(__DIR__ . '/../../uploads/pantallas');
}

$dir = $base . DIRECTORY_SEPARATOR . $id;
if (!is_dir($dir)) mkdir($dir, 0755, true);

$filename = 'imagen.' . $ext;
$dest     = $dir . DIRECTORY_SEPARATOR . $filename;

if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $dest)) {
    Response::adminError(500, 'Error al guardar el archivo');
}

$db->prepare("UPDATE agenda_pantallas SET imagen_path = ? WHERE id = ?")
   ->execute([$filename, $id]);

Response::json(['ok' => true, 'imagen_path' => $filename]);
