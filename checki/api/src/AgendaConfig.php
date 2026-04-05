<?php

$screen_id = filter_input(INPUT_GET, 'screen_id', FILTER_VALIDATE_INT);
$db        = DB::get();

// ── Helper: URL con cache-buster ──────────────────────────────────────────────
function mediaUrl(string $subpath): string {
    $uri    = $_SERVER['REQUEST_URI'] ?? '';
    $base   = preg_replace('#/api/.*$#', '', $uri);
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $url    = $scheme . '://' . $host . $base . '/uploads/pantallas/' . $subpath;
    $file   = __DIR__ . '/../../uploads/pantallas/' . $subpath;
    if (file_exists($file)) {
        $url .= '?t=' . filemtime($file);
    }
    return $url;
}

// ── Helper: reset override a 'none' ──────────────────────────────────────────
function resetOverride(PDO $db): void {
    try {
        $db->prepare("INSERT INTO configuracion (clave, valor) VALUES ('agenda_override', 'none') ON DUPLICATE KEY UPDATE valor = 'none'")->execute();
        $db->prepare("INSERT INTO configuracion (clave, valor) VALUES ('agenda_override_retorno_en', NULL) ON DUPLICATE KEY UPDATE valor = NULL")->execute();
    } catch (\PDOException $e) { /* silencioso */ }
}

// ── Leer override global (con try/catch) ──────────────────────────────────────
$conf = [];
try {
    $stmt = $db->prepare("
        SELECT clave, valor FROM configuracion
        WHERE  clave IN (
            'agenda_override','agenda_override_imagen','agenda_override_retorno_en',
            'agenda_override_video','agenda_override_loop_video','agenda_override_video_fit'
        )
    ");
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $conf[$row['clave']] = $row['valor'];
    }
} catch (\PDOException $e) { /* tabla aún no existe */ }

$override         = $conf['agenda_override']            ?? 'none';
$override_imagen  = $conf['agenda_override_imagen']     ?? null;
$override_video   = $conf['agenda_override_video']      ?? null;
$override_loop    = ($conf['agenda_override_loop_video'] ?? '1') !== '0';
$override_vfit    = $conf['agenda_override_video_fit']  ?? 'contain';
$override_retorno = $conf['agenda_override_retorno_en'] ?? null;

// ── Verificar auto-retorno del override ───────────────────────────────────────
if ($override_retorno) {
    try {
        if (new DateTime($override_retorno) <= new DateTime()) {
            resetOverride($db);
            $override = 'none';
        }
    } catch (\Exception $e) { /* fecha inválida */ }
}

// ── Aplicar override si está activo ───────────────────────────────────────────
if ($override === 'off') {
    Response::json(['modo' => 'apagada', 'salon_id' => null, 'apagada' => true]);
    exit;
}
if ($override === 'imagen') {
    $url = $override_imagen ? mediaUrl('override/' . $override_imagen) : null;
    Response::json(['modo' => 'imagen', 'imagen_url' => $url, 'salon_id' => null, 'apagada' => false]);
    exit;
}
if ($override === 'video') {
    $url = $override_video ? mediaUrl('override/' . $override_video) : null;
    Response::json(['modo' => 'video', 'video_url' => $url, 'loop' => $override_loop, 'video_fit' => $override_vfit, 'salon_id' => null, 'apagada' => false]);
    exit;
}
if ($override !== 'none' && ctype_digit((string)$override)) {
    Response::json(['modo' => 'agenda', 'salon_id' => (int)$override, 'apagada' => false]);
    exit;
}

// ── Sin screen_id → error limpio ─────────────────────────────────────────────
if (!$screen_id) {
    Response::error(400, 'screen_id requerido');
    exit;
}

// ── Leer pantalla — fallback por versión de migración ────────────────────────
$pantalla = null;
$sqls = [
    "SELECT id, nombre, salon_id, modo, imagen_path, video_path, loop_video, video_fit, retorno_en, activa FROM agenda_pantallas WHERE id = ?",
    "SELECT id, nombre, salon_id, modo, imagen_path, retorno_en, activa FROM agenda_pantallas WHERE id = ?",
    "SELECT id, nombre, salon_id, activa FROM agenda_pantallas WHERE id = ?",
];
foreach ($sqls as $sql) {
    try {
        $s = $db->prepare($sql);
        $s->execute([$screen_id]);
        $row = $s->fetch(PDO::FETCH_ASSOC);
        if ($row !== false) { $pantalla = $row; }
        break; // query ejecutó OK aunque no haya fila
    } catch (\PDOException $e) {
        // columnas no existen, probar versión anterior
    }
}

if (!$pantalla) {
    Response::error(404, 'Pantalla no encontrada');
    exit;
}

// ── Verificar auto-retorno por pantalla ───────────────────────────────────────
$retorno_en = $pantalla['retorno_en'] ?? null;
if ($retorno_en) {
    try {
        if (new DateTime($retorno_en) <= new DateTime()) {
            try {
                $db->prepare("UPDATE agenda_pantallas SET modo = 'agenda', retorno_en = NULL WHERE id = ?")->execute([$screen_id]);
            } catch (\PDOException $e) { /* columna no existe aún */ }
            $pantalla['modo']       = 'agenda';
            $pantalla['retorno_en'] = null;
        }
    } catch (\Exception $e) { /* fecha inválida */ }
}

// ── Devolver según modo ───────────────────────────────────────────────────────
$modo = ((int)($pantalla['activa'] ?? 0)) ? ($pantalla['modo'] ?? 'agenda') : 'apagada';

if ($modo === 'imagen') {
    $img_path = $pantalla['imagen_path'] ?? null;
    $url = $img_path ? mediaUrl($screen_id . '/' . $img_path) : null;
    Response::json(['modo' => 'imagen', 'imagen_url' => $url, 'salon_id' => null, 'apagada' => false]);
    exit;
}

if ($modo === 'video') {
    $vid_path = $pantalla['video_path'] ?? null;
    $url  = $vid_path ? mediaUrl($screen_id . '/' . $vid_path) : null;
    $loop = ((int)($pantalla['loop_video'] ?? 1)) !== 0;
    $fit  = in_array($pantalla['video_fit'] ?? '', ['contain','cover'], true) ? $pantalla['video_fit'] : 'contain';
    Response::json(['modo' => 'video', 'video_url' => $url, 'loop' => $loop, 'video_fit' => $fit, 'salon_id' => null, 'apagada' => false]);
    exit;
}

if ($modo === 'apagada') {
    Response::json(['modo' => 'apagada', 'salon_id' => null, 'apagada' => true]);
    exit;
}

// modo === 'agenda'
$salon_id = ((int)($pantalla['activa'] ?? 0) && $pantalla['salon_id']) ? (int)$pantalla['salon_id'] : null;
Response::json([
    'modo'     => $salon_id ? 'agenda' : 'apagada',
    'salon_id' => $salon_id,
    'apagada'  => $salon_id === null,
]);
