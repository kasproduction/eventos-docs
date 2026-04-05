<?php

$salon_id = $GLOBALS['salon_id_param'] ?? 0;
if (!$salon_id) {
    Response::error(400, 'salon_id requerido');
}

$db = DB::get();

$stmt = $db->prepare("SELECT id FROM salones WHERE id = ? AND evento_id = ?");
$stmt->execute([$salon_id, EVENTO_ID_ACTIVO]);
if (!$stmt->fetch()) {
    Response::error(404, 'Salón no encontrado');
}

// Bloquear solo si tiene charlas futuras o de hoy (no canceladas)
// Las charlas pasadas no impiden archivar — el historial se conserva
$stmt = $db->prepare("
    SELECT COUNT(*) FROM charlas c
    JOIN   dias_evento d ON d.id = c.dia_evento_id
    WHERE  c.salon_id = ? AND d.evento_id = ? AND c.cancelada = 0
    AND    d.fecha >= CURDATE()
");
$stmt->execute([$salon_id, EVENTO_ID_ACTIVO]);
if ((int)$stmt->fetchColumn() > 0) {
    Response::error(409, 'El salón tiene charlas pendientes. Cancélalas primero desde la Agenda.');
}

// Soft delete — conserva historial de movimientos
$db->prepare("UPDATE salones SET activo = 0 WHERE id = ?")->execute([$salon_id]);

Response::json(['ok' => true]);
