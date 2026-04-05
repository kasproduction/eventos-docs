<?php

$evento_id = EVENTO_ID_ACTIVO;
$db        = DB::get();

$stmtDias = $db->prepare("
    SELECT id, fecha, nombre, orden
    FROM   dias_evento
    WHERE  evento_id = ?
    ORDER  BY orden
");
$stmtDias->execute([$evento_id]);

$stmtSalones = $db->prepare("
    SELECT id, nombre, capacidad, activo
    FROM   salones
    WHERE  evento_id = ?
    ORDER  BY id
");
$stmtSalones->execute([$evento_id]);

$stmtTotems = $db->prepare("
    SELECT t.id, t.salon_id, t.nombre, t.tipo, t.activo, t.ip_local,
           t.ultimo_ping,
           TIMESTAMPDIFF(SECOND, t.ultimo_ping, NOW()) AS segundos_desde_ping,
           s.nombre AS salon_nombre
    FROM   totems t
    JOIN   salones s ON s.id = t.salon_id
    WHERE  t.evento_id = ?
    ORDER  BY t.salon_id, t.id
");
$stmtTotems->execute([$evento_id]);

// ── Pantallas — fallback por versión de migración ─────────────────────────────
$pantallas = [];
try {
    // v3: incluye video_path, loop_video, video_fit
    $stmt = $db->prepare("
        SELECT p.id, p.nombre, p.salon_id, p.activa,
               p.modo, p.imagen_path, p.video_path, p.loop_video, p.video_fit, p.retorno_en,
               s.nombre AS salon_nombre
        FROM   agenda_pantallas p
        LEFT   JOIN salones s ON s.id = p.salon_id
        ORDER  BY p.id
    ");
    $stmt->execute();
    $pantallas = $stmt->fetchAll();
} catch (\PDOException $e) {
    try {
        // v2: incluye modo, imagen_path, retorno_en
        $stmt = $db->prepare("
            SELECT p.id, p.nombre, p.salon_id, p.activa,
                   p.modo, p.imagen_path, p.retorno_en,
                   s.nombre AS salon_nombre
            FROM   agenda_pantallas p
            LEFT   JOIN salones s ON s.id = p.salon_id
            ORDER  BY p.id
        ");
        $stmt->execute();
        $pantallas = $stmt->fetchAll();
    } catch (\PDOException $e) {
        try {
            // v1: columnas originales
            $stmt = $db->prepare("
                SELECT p.id, p.nombre, p.salon_id, p.activa,
                       s.nombre AS salon_nombre
                FROM   agenda_pantallas p
                LEFT   JOIN salones s ON s.id = p.salon_id
                ORDER  BY p.id
            ");
            $stmt->execute();
            $pantallas = $stmt->fetchAll();
        } catch (\PDOException $e) {
            // Tabla aún no existe
        }
    }
}

// ── Config override — separado para que no dependa de las pantallas ───────────
$override         = 'none';
$override_imagen  = null;
$override_video   = null;
$override_loop    = true;
$override_vfit    = 'contain';
$override_retorno = null;
try {
    $stmtConf = $db->prepare("
        SELECT clave, valor FROM configuracion
        WHERE  clave IN (
            'agenda_override','agenda_override_imagen','agenda_override_retorno_en',
            'agenda_override_video','agenda_override_loop_video','agenda_override_video_fit'
        )
    ");
    $stmtConf->execute();
    foreach ($stmtConf->fetchAll(PDO::FETCH_ASSOC) as $row) {
        switch ($row['clave']) {
            case 'agenda_override':            $override         = $row['valor'] ?? 'none';    break;
            case 'agenda_override_imagen':     $override_imagen  = $row['valor'];              break;
            case 'agenda_override_video':      $override_video   = $row['valor'];              break;
            case 'agenda_override_loop_video': $override_loop    = $row['valor'] !== '0';      break;
            case 'agenda_override_video_fit':  $override_vfit    = $row['valor'] ?? 'contain'; break;
            case 'agenda_override_retorno_en': $override_retorno = $row['valor'];              break;
        }
    }
} catch (\PDOException $e) {
    // Tabla configuracion no tiene estas claves aún
}

Response::json([
    'dias'                => $stmtDias->fetchAll(),
    'salones'             => $stmtSalones->fetchAll(),
    'totems'              => $stmtTotems->fetchAll(),
    'pantallas'           => $pantallas,
    'agenda_override'     => $override,
    'override_imagen'     => $override_imagen,
    'override_video'      => $override_video,
    'override_loop_video' => $override_loop,
    'override_video_fit'  => $override_vfit,
    'override_retorno_en' => $override_retorno,
]);
