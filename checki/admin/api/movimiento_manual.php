<?php

$input       = json_decode(file_get_contents('php://input'), true);
$asistente_id = (int)($input['asistente_id'] ?? 0);
$salon_id     = (int)($input['salon_id']     ?? 0);
$tipo         = $input['tipo']    ?? '';
$pin          = trim($input['pin']    ?? '');
$motivo       = trim($input['motivo'] ?? '');

if (!$asistente_id || !$salon_id || !in_array($tipo, ['checkin', 'checkout'], true) || $pin === '') {
    Response::error(400, 'Faltan campos requeridos: asistente_id, salon_id, tipo, pin');
}
if ($motivo === '') {
    Response::error(400, 'El motivo es obligatorio para correcciones manuales');
}

// Verificar PIN del operador activo en sesión
$db  = DB::get();
$op  = $db->prepare("SELECT id, pin FROM operadores WHERE id = ? AND activo = 1 LIMIT 1");
$op->execute([Auth::operadorId()]);
$operador = $op->fetch();

if (!$operador || !password_verify($pin, $operador['pin'])) {
    Response::error(403, 'PIN incorrecto');
}

// Obtener evento_id del asistente
$ast = $db->prepare("SELECT evento_id FROM asistentes WHERE id = ? LIMIT 1");
$ast->execute([$asistente_id]);
$asistente = $ast->fetch();
if (!$asistente) {
    Response::error(404, 'Asistente no encontrado');
}

$evento_id = (int)$asistente['evento_id'];
$mt        = microtime(true);
$now       = date('Y-m-d H:i:s', (int)$mt) . '.' . sprintf('%03d', ($mt - floor($mt)) * 1000);

// Obtener totem_id del salón si existe (NULL permitido en movimientos manuales)
$totemStmt = $db->prepare("SELECT id FROM totems WHERE salon_id = ? AND activo = 1 LIMIT 1");
$totemStmt->execute([$salon_id]);
$totem = $totemStmt->fetch();
$totem_id = $totem ? (int)$totem['id'] : null;

$db->beginTransaction();
try {
    // INSERT movimiento manual
    $stmt = $db->prepare("
        INSERT INTO movimientos
            (evento_id, asistente_id, totem_id, salon_id, tipo,
             timestamp, metodo, operador_id, flags, notas)
        VALUES
            (?, ?, ?, ?, ?,
             ?, 'manual', ?, 'corregido', ?)
    ");
    $stmt->execute([
        $evento_id, $asistente_id, $totem_id, $salon_id, $tipo,
        $now, Auth::operadorId(), $motivo ?: null,
    ]);
    $movimiento_id = (int)$db->lastInsertId();

    // UPSERT estado_asistentes
    $nuevo_estado = ($tipo === 'checkin') ? 'dentro' : 'fuera';
    $db->prepare("
        INSERT INTO estado_asistentes
            (asistente_id, salon_id, estado, ultimo_movimiento_id, updated_at)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            estado               = VALUES(estado),
            ultimo_movimiento_id = VALUES(ultimo_movimiento_id),
            updated_at           = VALUES(updated_at)
    ")->execute([$asistente_id, $salon_id, $nuevo_estado, $movimiento_id, $now]);

    $db->commit();
} catch (Throwable $e) {
    $db->rollBack();
    Response::error(500, 'Error al registrar movimiento');
}

Response::json(['ok' => true, 'tipo' => $tipo, 'movimiento_id' => $movimiento_id]);
