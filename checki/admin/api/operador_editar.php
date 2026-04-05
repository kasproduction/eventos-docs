<?php
// PATCH /api/operador/{id}  — solo admin
if (!Auth::esAdmin()) { Response::adminError(403, 'Solo administradores'); }

$id   = (int)($GLOBALS['operador_id_param'] ?? 0);
if (!$id) { Response::adminError(400, 'ID inválido'); }

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$db     = DB::get();

// Verificar que el operador pertenece al evento
$check = $db->prepare("SELECT id, rol FROM operadores WHERE id = ? AND evento_id = ?");
$check->execute([$id, EVENTO_ID_ACTIVO]);
$op = $check->fetch();
if (!$op) { Response::adminError(404, 'Operador no encontrado'); }

$miId     = Auth::operadorId();
$esPropioUsuario = ($miId === $id);

$sets  = [];
$binds = [];

if (isset($body['nombre']) && trim($body['nombre']) !== '') {
    $sets[]  = 'nombre = ?';
    $binds[] = trim($body['nombre']);
}
// Si es su propia cuenta, ignorar cambio de rol silenciosamente (protección)
if (!$esPropioUsuario && isset($body['rol']) && in_array($body['rol'], ['admin', 'supervisor', 'viewer'])) {
    $sets[]  = 'rol = ?';
    $binds[] = $body['rol'];
}
if (isset($body['activo'])) {
    // No desactivar la propia cuenta
    if ($esPropioUsuario && !(int)$body['activo']) {
        Response::adminError(400, 'No puedes desactivarte a ti mismo');
    }
    $sets[]  = 'activo = ?';
    $binds[] = (int)(bool)$body['activo'];
}
if (isset($body['pin']) && strlen(trim($body['pin'])) >= 4) {
    $sets[]  = 'pin = ?';
    $binds[] = password_hash(trim($body['pin']), PASSWORD_BCRYPT);
}

if (empty($sets)) { Response::adminError(400, 'Nada que actualizar'); }

$binds[] = $id;
$db->prepare("UPDATE operadores SET " . implode(', ', $sets) . " WHERE id = ?")
   ->execute($binds);

Response::json(['ok' => true]);
