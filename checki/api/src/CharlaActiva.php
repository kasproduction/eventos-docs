<?php

$db = DB::get();

// Acepta salon_id directo o totem_id (para los tótems Unity que solo conocen su ID)
$salon_id = filter_input(INPUT_GET, 'salon_id', FILTER_VALIDATE_INT);
$totem_id = filter_input(INPUT_GET, 'totem_id', FILTER_VALIDATE_INT);

if (!$salon_id && $totem_id) {
    $stmt = $db->prepare("SELECT salon_id FROM totems WHERE id = ? AND activo = 1 LIMIT 1");
    $stmt->execute([$totem_id]);
    $totem    = $stmt->fetch();
    $salon_id = $totem ? (int) $totem['salon_id'] : null;
}

if (!$salon_id) {
    Response::error(400, 'salon_id o totem_id requerido');
}

// ── Charla activa ahora ───────────────────────────────────────────────────────
$stmt = $db->prepare("
    SELECT c.id, c.titulo, c.ponente, c.hora_inicio, c.hora_fin
    FROM   charlas c
    JOIN   dias_evento d ON d.id = c.dia_evento_id
    WHERE  c.salon_id    = ?
      AND  c.cancelada   = 0
      AND  c.hora_inicio <= NOW()
      AND  c.hora_fin    >= NOW()
    LIMIT 1
");
$stmt->execute([$salon_id]);
$charla = $stmt->fetch() ?: null;

// ── Próxima charla (siempre — incluso cuando hay charla activa) ──────────────
// Con charla activa: busca la siguiente que empiece >= hora_fin (puede ser inmediata).
// Sin charla activa: busca la siguiente que empiece > NOW().
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

Response::json([
    'charla'  => $charla,
    'proxima' => $proxima,
]);
