<?php

$totem_id = $GLOBALS['totem_id_param'] ?? 0;
if (!$totem_id) {
    Response::error(400, 'totem_id requerido');
}

$db = DB::get();

$stmt = $db->prepare("SELECT id FROM totems WHERE id = ? AND evento_id = ?");
$stmt->execute([$totem_id, EVENTO_ID_ACTIVO]);
if (!$stmt->fetch()) {
    Response::error(404, 'Tótem no encontrado');
}

$stmt = $db->prepare("SELECT COUNT(*) FROM movimientos WHERE totem_id = ?");
$stmt->execute([$totem_id]);

if ((int)$stmt->fetchColumn() > 0) {
    // Tiene historial — solo desactivar para conservar trazabilidad
    $db->prepare("UPDATE totems SET activo = 0 WHERE id = ?")->execute([$totem_id]);
    Response::json([
        'ok'          => true,
        'advertencia' => 'El tótem tenía movimientos registrados — se desactivó para conservar la trazabilidad.',
    ]);
} else {
    $db->prepare("DELETE FROM totems WHERE id = ?")->execute([$totem_id]);
    Response::json(['ok' => true]);
}
