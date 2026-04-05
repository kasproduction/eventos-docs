<?php
// PATCH /api/mi-cuenta — cualquier operador logueado puede cambiar su propio PIN
$body = json_decode(file_get_contents('php://input'), true) ?? [];
$pin  = trim($body['pin_nuevo'] ?? '');
$pin_actual = trim($body['pin_actual'] ?? '');

if ($pin_actual === '') { Response::adminError(400, 'Debes confirmar tu PIN actual'); }
if (strlen($pin) < 4)  { Response::adminError(400, 'El nuevo PIN debe tener al menos 4 dígitos'); }

$db   = DB::get();
$stmt = $db->prepare("SELECT pin FROM operadores WHERE id = ? AND activo = 1");
$stmt->execute([Auth::operadorId()]);
$op   = $stmt->fetch();

if (!$op || !password_verify($pin_actual, $op['pin'])) {
    Response::adminError(400, 'PIN actual incorrecto');
}

$db->prepare("UPDATE operadores SET pin = ? WHERE id = ?")
   ->execute([password_hash($pin, PASSWORD_BCRYPT), Auth::operadorId()]);

Response::json(['ok' => true]);
