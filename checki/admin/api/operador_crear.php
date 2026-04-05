<?php
// POST /api/operador  — solo admin
if (!Auth::esAdmin()) { Response::adminError(403, 'Solo administradores'); }

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$nombre = trim($body['nombre'] ?? '');
$rol    = $body['rol'] ?? 'operador';
$pin    = trim($body['pin'] ?? '');

if ($nombre === '')          { Response::adminError(400, 'El nombre es obligatorio'); }
if (strlen($pin) < 4)        { Response::adminError(400, 'El PIN debe tener al menos 4 dígitos'); }
if (!in_array($rol, ['admin', 'supervisor', 'viewer'])) { Response::adminError(400, 'Rol inválido'); }

$hash = password_hash($pin, PASSWORD_BCRYPT);
$db   = DB::get();
$stmt = $db->prepare("
    INSERT INTO operadores (evento_id, nombre, rol, pin, activo)
    VALUES (?, ?, ?, ?, 1)
");
$stmt->execute([EVENTO_ID_ACTIVO, $nombre, $rol, $hash]);

Response::json(['id' => (int)$db->lastInsertId(), 'ok' => true]);
