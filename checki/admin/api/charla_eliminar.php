<?php

$charla_id = $GLOBALS['charla_id'] ?? 0;
if (!$charla_id) {
    Response::error(400, 'charla_id requerido');
}

$db = DB::get();

$stmt = $db->prepare("SELECT id FROM charlas WHERE id = ?");
$stmt->execute([$charla_id]);
if (!$stmt->fetch()) {
    Response::error(404, 'Charla no encontrada');
}

// Si ya tiene asistencia calculada no eliminamos — sugerir cancelar
$stmt = $db->prepare("SELECT COUNT(*) FROM asistencia_calculada WHERE charla_id = ?");
$stmt->execute([$charla_id]);
if ((int)$stmt->fetchColumn() > 0) {
    Response::error(409, 'La charla tiene asistencia calculada. Usa "Cancelar charla" en lugar de eliminarla para conservar la trazabilidad.');
}

// Limpiar audit trail antes de eliminar
$db->prepare("DELETE FROM agenda_cambios WHERE charla_id = ?")->execute([$charla_id]);
$db->prepare("DELETE FROM charlas WHERE id = ?")->execute([$charla_id]);

Response::json(['ok' => true]);
