<?php

$dia_id = $GLOBALS['dia_id_param'] ?? 0;
if (!$dia_id) {
    Response::error(400, 'dia_id requerido');
}

$db = DB::get();

$stmt = $db->prepare("SELECT id FROM dias_evento WHERE id = ? AND evento_id = ?");
$stmt->execute([$dia_id, EVENTO_ID_ACTIVO]);
if (!$stmt->fetch()) {
    Response::error(404, 'Día no encontrado');
}

$stmt = $db->prepare("SELECT COUNT(*) FROM charlas WHERE dia_evento_id = ?");
$stmt->execute([$dia_id]);
if ((int)$stmt->fetchColumn() > 0) {
    Response::error(409, 'El día tiene charlas asociadas. Elimínalas primero desde la Agenda.');
}

$db->prepare("DELETE FROM dias_evento WHERE id = ?")->execute([$dia_id]);

Response::json(['ok' => true]);
