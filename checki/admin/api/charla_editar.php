<?php

$charla_id = $GLOBALS['charla_id'] ?? 0;
if (!$charla_id) {
    Response::error(400, 'charla_id requerido');
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    Response::error(400, 'Body JSON inválido');
}

$db = DB::get();

// Leer charla actual — valida que pertenezca al evento activo
$stmt = $db->prepare("
    SELECT c.* FROM charlas c
    JOIN dias_evento d ON d.id = c.dia_evento_id
    WHERE c.id = ? AND d.evento_id = ? LIMIT 1
");
$stmt->execute([$charla_id, EVENTO_ID_ACTIVO]);
$charla = $stmt->fetch();
if (!$charla) {
    Response::error(404, 'Charla no encontrada');
}

$camposEditables = ['hora_inicio', 'hora_fin', 'titulo', 'ponente', 'cancelada', 'salon_id'];
$cambios         = [];

foreach ($camposEditables as $campo) {
    if (!isset($input[$campo])) {
        continue;
    }

    $nuevo   = (string) $input[$campo];
    $anterior = (string) $charla[$campo];

    if ($nuevo === $anterior) {
        continue;
    }

    $cambios[] = [
        'campo'    => $campo,
        'anterior' => $anterior,
        'nuevo'    => $nuevo,
    ];
}

if (empty($cambios)) {
    Response::json(['ok' => true, 'cambios' => 0]);
}

// Si se cambia salon_id, validar que el nuevo salón pertenezca al evento activo
foreach ($cambios as $c) {
    if ($c['campo'] === 'salon_id') {
        $chkSalon = $db->prepare("SELECT id FROM salones WHERE id = ? AND evento_id = ? AND activo = 1 LIMIT 1");
        $chkSalon->execute([(int)$c['nuevo'], EVENTO_ID_ACTIVO]);
        if (!$chkSalon->fetch()) {
            Response::error(404, 'Salón no encontrado o inactivo');
        }
        break;
    }
}

$db->beginTransaction();
try {
    foreach ($cambios as $c) {
        // 1. Guardar en agenda_cambios antes de tocar la charla
        $db->prepare("
            INSERT INTO agenda_cambios
                (charla_id, campo_modificado, valor_anterior, valor_nuevo, motivo, operador_id)
            VALUES
                (?, ?, ?, ?, ?, ?)
        ")->execute([
            $charla_id,
            $c['campo'],
            $c['anterior'],
            $c['nuevo'],
            $input['motivo'] ?? null,
            Auth::operadorId(),
        ]);

        // 2. Actualizar la charla (hora_inicio_original NUNCA se toca)
        $db->prepare("UPDATE charlas SET {$c['campo']} = ? WHERE id = ?")
           ->execute([$c['nuevo'], $charla_id]);
    }

    $db->commit();
} catch (Throwable $e) {
    $db->rollBack();
    Response::error(500, 'Error al guardar cambios');
}

Response::json(['ok' => true, 'cambios' => count($cambios)]);
