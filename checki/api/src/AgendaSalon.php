<?php

$salon_id = filter_input(INPUT_GET, 'salon_id', FILTER_VALIDATE_INT);
if (!$salon_id) Response::error(400, 'salon_id requerido');

$db = DB::get();

// ── Salón ─────────────────────────────────────────────────────────────────────
$stmt = $db->prepare("SELECT id, nombre FROM salones WHERE id = ? AND activo = 1");
$stmt->execute([$salon_id]);
$salon = $stmt->fetch();
if (!$salon) Response::error(404, 'Salón no encontrado');

// ── Charla activa ahora ───────────────────────────────────────────────────────
$stmt = $db->prepare("
    SELECT c.id, c.titulo, c.ponente, c.hora_inicio, c.hora_fin
    FROM   charlas c
    JOIN   dias_evento d ON d.id = c.dia_evento_id
    WHERE  c.salon_id  = ?
      AND  c.cancelada = 0
      AND  c.hora_inicio <= NOW()
      AND  c.hora_fin    >= NOW()
    LIMIT 1
");
$stmt->execute([$salon_id]);
$charla = $stmt->fetch() ?: null;

// ── Próxima charla ────────────────────────────────────────────────────────────
if ($charla) {
    $stmt = $db->prepare("
        SELECT c.id, c.titulo, c.ponente, c.hora_inicio, c.hora_fin
        FROM   charlas c
        JOIN   dias_evento d ON d.id = c.dia_evento_id
        WHERE  c.salon_id  = ?
          AND  c.cancelada = 0
          AND  c.hora_inicio >= ?
          AND  c.id != ?
          AND  DATE(c.hora_inicio) = CURDATE()
        ORDER  BY c.hora_inicio ASC
        LIMIT  1
    ");
    $stmt->execute([$salon_id, $charla['hora_fin'], $charla['id']]);
} else {
    $stmt = $db->prepare("
        SELECT c.id, c.titulo, c.ponente, c.hora_inicio, c.hora_fin
        FROM   charlas c
        JOIN   dias_evento d ON d.id = c.dia_evento_id
        WHERE  c.salon_id  = ?
          AND  c.cancelada = 0
          AND  c.hora_inicio > NOW()
          AND  DATE(c.hora_inicio) = CURDATE()
        ORDER  BY c.hora_inicio ASC
        LIMIT  1
    ");
    $stmt->execute([$salon_id]);
}
$proxima = $stmt->fetch() ?: null;

// ── Todas las charlas del salón agrupadas por día ─────────────────────────────
$stmt = $db->prepare("
    SELECT d.id   AS dia_id,
           d.fecha,
           d.nombre AS dia_nombre,
           c.id, c.titulo, c.ponente,
           c.hora_inicio, c.hora_fin, c.cancelada
    FROM   dias_evento d
    JOIN   charlas c ON c.dia_evento_id = d.id
    WHERE  c.salon_id = ?
    ORDER  BY d.fecha ASC, c.hora_inicio ASC
");
$stmt->execute([$salon_id]);
$rows = $stmt->fetchAll();

$dias = [];
foreach ($rows as $row) {
    $key = $row['dia_id'];
    if (!isset($dias[$key])) {
        $dias[$key] = [
            'id'      => (int) $row['dia_id'],
            'fecha'   => $row['fecha'],
            'nombre'  => $row['dia_nombre'],
            'charlas' => [],
        ];
    }
    $dias[$key]['charlas'][] = [
        'id'          => (int) $row['id'],
        'titulo'      => $row['titulo'],
        'ponente'     => $row['ponente'] ?? '',
        'hora_inicio' => $row['hora_inicio'],
        'hora_fin'    => $row['hora_fin'],
        'cancelada'   => (bool) $row['cancelada'],
    ];
}

Response::json([
    'salon'   => $salon,
    'charla'  => $charla,
    'proxima' => $proxima,
    'dias'    => array_values($dias),
    'now'     => date('Y-m-d H:i:s'),
]);
