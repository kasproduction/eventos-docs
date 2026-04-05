<?php

$input    = json_decode(file_get_contents('php://input'), true);
$salon_id = (int)($input['salon_id'] ?? 0);
$dia_id   = (int)($input['dia_id']   ?? 0);
$minutos  = (int)($input['minutos']  ?? 0);

if (!$salon_id || !$dia_id || $minutos === 0) {
    Response::error(400, 'salon_id, dia_id y minutos son requeridos');
}

if (abs($minutos) > 240) {
    Response::error(400, 'El delta máximo es 240 minutos');
}

$db     = DB::get();
$stmt   = $db->prepare("
    SELECT id, hora_inicio, hora_fin
    FROM   charlas
    WHERE  salon_id      = ?
      AND  dia_evento_id = ?
      AND  cancelada     = 0
");
$stmt->execute([$salon_id, $dia_id]);
$charlas = $stmt->fetchAll();

if (empty($charlas)) {
    Response::error(404, 'No hay charlas para ese salón y día');
}

$db->beginTransaction();
try {
    $operador_id = Auth::operadorId();
    $signo       = $minutos > 0 ? '+' : '-';
    $abs         = abs($minutos);

    foreach ($charlas as $c) {
        $nuevo_inicio = date('Y-m-d H:i:s', strtotime("{$c['hora_inicio']} {$signo}{$abs} minutes"));
        $nuevo_fin    = date('Y-m-d H:i:s', strtotime("{$c['hora_fin']} {$signo}{$abs} minutes"));

        // Auditoría
        foreach (['hora_inicio' => [$c['hora_inicio'], $nuevo_inicio], 'hora_fin' => [$c['hora_fin'], $nuevo_fin]] as $campo => [$anterior, $nuevo]) {
            $db->prepare("
                INSERT INTO agenda_cambios
                    (charla_id, campo_modificado, valor_anterior, valor_nuevo, motivo, operador_id)
                VALUES (?, ?, ?, ?, ?, ?)
            ")->execute([
                $c['id'], $campo, $anterior, $nuevo,
                "Retraso masivo {$minutos} min — salón {$salon_id}",
                $operador_id,
            ]);
        }

        // Actualizar (hora_inicio_original no se toca)
        $db->prepare("
            UPDATE charlas
            SET hora_inicio = ?, hora_fin = ?
            WHERE id = ?
        ")->execute([$nuevo_inicio, $nuevo_fin, $c['id']]);
    }

    $db->commit();
} catch (Throwable $e) {
    $db->rollBack();
    Response::error(500, 'Error al retrasar agenda');
}

Response::json(['ok' => true, 'charlas_afectadas' => count($charlas), 'minutos' => $minutos]);
