<?php

$evento_id = EVENTO_ID_ACTIVO;
$db        = DB::get();

// ─── Personas dentro por salón + charla activa ───────────
$salones = $db->prepare("
    SELECT
        s.id,
        s.nombre,
        COALESCE(e.personas_dentro, 0) AS personas_dentro,
        c.titulo       AS charla_titulo,
        c.ponente      AS charla_ponente,
        TIME(c.hora_inicio) AS charla_inicio,
        TIME(c.hora_fin)    AS charla_fin,
        GREATEST(0, TIMESTAMPDIFF(MINUTE, NOW(), c.hora_fin)) AS minutos_restantes
    FROM salones s
    LEFT JOIN (
        SELECT salon_id, COUNT(*) AS personas_dentro
        FROM   estado_asistentes
        WHERE  estado = 'dentro'
        GROUP  BY salon_id
    ) e ON e.salon_id = s.id
    LEFT JOIN charlas c
        ON  c.salon_id     = s.id
        AND c.cancelada    = 0
        AND c.hora_inicio <= NOW()
        AND c.hora_fin    >= NOW()
    WHERE s.evento_id = ?
      AND s.activo    = 1
    ORDER BY s.id
");
$salones->execute([$evento_id]);
$dataSalones = $salones->fetchAll();

// ─── Últimas lecturas (paginadas) ────────────────────────
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 30;
$offset   = ($page - 1) * $perPage;

$totalStmt = $db->prepare("SELECT COUNT(*) FROM movimientos WHERE evento_id = ?");
$totalStmt->execute([$evento_id]);
$totalLecturas = (int)$totalStmt->fetchColumn();
$totalPages    = max(1, (int)ceil($totalLecturas / $perPage));

$lecturas = $db->prepare("
    SELECT
        a.nombre,
        s.nombre    AS salon,
        m.tipo,
        m.metodo,
        m.flags,
        TIME(m.timestamp) AS hora,
        t.nombre    AS totem
    FROM   movimientos m
    JOIN        asistentes  a ON a.id = m.asistente_id
    JOIN        salones     s ON s.id = m.salon_id
    LEFT JOIN   totems      t ON t.id = m.totem_id
    WHERE  m.evento_id = ?
    ORDER  BY m.timestamp DESC
    LIMIT  $perPage OFFSET $offset
");
$lecturas->execute([$evento_id]);
$dataLecturas = $lecturas->fetchAll();

// ─── Estado tótems (último ping implícito: última lectura) ─
$totems = $db->prepare("
    SELECT
        t.id,
        t.nombre,
        t.tipo,
        t.ip_local,
        s.nombre AS salon,
        MAX(m.timestamp) AS ultima_actividad
    FROM  totems t
    JOIN  salones s ON s.id = t.salon_id
    LEFT  JOIN movimientos m ON m.totem_id = t.id
    WHERE t.evento_id = ?
      AND t.activo    = 1
    GROUP BY t.id, t.nombre, t.tipo, t.ip_local, s.nombre
    ORDER BY t.id
");
$totems->execute([$evento_id]);
$dataTotems = $totems->fetchAll();

Response::json([
    'timestamp'      => date('Y-m-d H:i:s'),
    'salones'        => $dataSalones,
    'lecturas'       => $dataLecturas,
    'lecturas_page'  => $page,
    'lecturas_pages' => $totalPages,
    'lecturas_total' => $totalLecturas,
    'totems'         => $dataTotems,
]);
