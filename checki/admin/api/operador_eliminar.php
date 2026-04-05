<?php
// DELETE /api/operador/{id}  — solo admin
if (!Auth::esAdmin()) { Response::adminError(403, 'Solo administradores'); }

$id = (int)($GLOBALS['operador_id_param'] ?? 0);
if (!$id) { Response::adminError(400, 'ID inválido'); }

// No puede eliminarse a sí mismo
if (Auth::operadorId() === $id) {
    Response::adminError(400, 'No puedes eliminar tu propia cuenta');
}

$db   = DB::get();
$stmt = $db->prepare("DELETE FROM operadores WHERE id = ? AND evento_id = ?");
$stmt->execute([$id, EVENTO_ID_ACTIVO]);

if ($stmt->rowCount() === 0) { Response::adminError(404, 'Operador no encontrado'); }

Response::json(['ok' => true]);
