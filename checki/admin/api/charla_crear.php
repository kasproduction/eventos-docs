<?php

$input = json_decode(file_get_contents('php://input'), true);

$dia_id      = (int)($input['dia_evento_id'] ?? 0);
$salon_id    = (int)($input['salon_id']      ?? 0);
$titulo      = trim($input['titulo']         ?? '');
$ponente     = trim($input['ponente']        ?? '');
$hora_inicio = trim($input['hora_inicio']    ?? '');
$hora_fin    = trim($input['hora_fin']       ?? '');
$umbral      = (int)($input['umbral_min_asistencia'] ?? 15);
$buffer      = (int)($input['buffer_pre_inicio']     ?? 15);

if (!$dia_id || !$salon_id || !$titulo || !$hora_inicio || !$hora_fin) {
    Response::error(400, 'Faltan campos: dia_evento_id, salon_id, titulo, hora_inicio, hora_fin');
}

$db = DB::get();

// Validar que el día y el salón pertenezcan al evento activo
$checkDia = $db->prepare("SELECT id FROM dias_evento WHERE id = ? AND evento_id = ? LIMIT 1");
$checkDia->execute([$dia_id, EVENTO_ID_ACTIVO]);
if (!$checkDia->fetch()) {
    Response::error(404, 'Día no encontrado en el evento');
}

$checkSalon = $db->prepare("SELECT id FROM salones WHERE id = ? AND evento_id = ? AND activo = 1 LIMIT 1");
$checkSalon->execute([$salon_id, EVENTO_ID_ACTIVO]);
if (!$checkSalon->fetch()) {
    Response::error(404, 'Salón no encontrado o inactivo');
}

// Orden = último de ese salón ese día + 1
$stmt = $db->prepare("
    SELECT COALESCE(MAX(orden_en_dia), 0) + 1
    FROM charlas
    WHERE dia_evento_id = ? AND salon_id = ?
");
$stmt->execute([$dia_id, $salon_id]);
$orden = (int)$stmt->fetchColumn();

$db->prepare("
    INSERT INTO charlas
        (dia_evento_id, salon_id, titulo, ponente,
         hora_inicio, hora_fin,
         hora_inicio_original, hora_fin_original,
         umbral_min_asistencia, buffer_pre_inicio, orden_en_dia)
    VALUES
        (?, ?, ?, ?,
         ?, ?,
         ?, ?,
         ?, ?, ?)
")->execute([
    $dia_id, $salon_id, $titulo, $ponente ?: null,
    $hora_inicio, $hora_fin,
    $hora_inicio, $hora_fin,
    $umbral, $buffer, $orden,
]);

Response::json(['ok' => true, 'id' => (int)$db->lastInsertId()]);
