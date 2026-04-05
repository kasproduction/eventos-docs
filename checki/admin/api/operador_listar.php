<?php
// GET /api/operadores  — solo admin
if (!Auth::esAdmin()) { Response::adminError(403, 'Solo administradores'); }

$db   = DB::get();
$stmt = $db->prepare("
    SELECT id, nombre, rol, activo,
           LEFT(pin, 0) AS pin   -- nunca exponer el hash
    FROM   operadores
    WHERE  evento_id = ?
    ORDER  BY id
");
$stmt->execute([EVENTO_ID_ACTIVO]);
$rows = $stmt->fetchAll();

// Devolver sin el campo pin (ya es vacío, pero lo quitamos explícitamente)
$data = array_map(function($r) {
    return ['id' => $r['id'], 'nombre' => $r['nombre'], 'rol' => $r['rol'], 'activo' => (int)$r['activo']];
}, $rows);

Response::json(['operadores' => $data]);
