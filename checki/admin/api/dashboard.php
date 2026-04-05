<?php

$evento_id = EVENTO_ID_ACTIVO;
$db        = DB::get();

// ─── Salones: conteo, capacidad, charla activa ────────────
$stmtSalones = $db->prepare("
    SELECT
        s.id,
        s.nombre,
        s.capacidad,
        COALESCE(e.personas_dentro, 0) AS personas_dentro,
        c.titulo        AS charla_titulo,
        c.ponente       AS charla_ponente,
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
$stmtSalones->execute([$evento_id]);
$salones     = $stmtSalones->fetchAll();
$totalDentro = array_sum(array_column($salones, 'personas_dentro'));

// ─── Checkins de hoy ──────────────────────────────────────
$stmtHoy = $db->prepare("
    SELECT COUNT(*) FROM movimientos
    WHERE evento_id = ?
      AND tipo      = 'checkin'
      AND DATE(timestamp) = CURDATE()
      AND metodo NOT IN ('auto_cambio_salon','auto_fin_jornada')
");
$stmtHoy->execute([$evento_id]);
$checkinsHoy = (int)$stmtHoy->fetchColumn();

// ─── Total registrados ────────────────────────────────────
$stmtTotal = $db->prepare("
    SELECT COUNT(*) FROM asistentes WHERE evento_id = ? AND activo = 1
");
$stmtTotal->execute([$evento_id]);
$totalAsistentes = (int)$stmtTotal->fetchColumn();

// ─── Últimas 15 lecturas (sin técnicos) ───────────────────
$stmtLecturas = $db->prepare("
    SELECT
        a.nombre,
        s.nombre    AS salon,
        m.tipo,
        TIME(m.timestamp) AS hora
    FROM   movimientos m
    JOIN   asistentes  a ON a.id = m.asistente_id
    JOIN   salones     s ON s.id = m.salon_id
    WHERE  m.evento_id = ?
      AND  m.metodo NOT IN ('auto_cambio_salon','auto_fin_jornada')
    ORDER  BY m.timestamp DESC
    LIMIT  15
");
$stmtLecturas->execute([$evento_id]);
$lecturas = $stmtLecturas->fetchAll();

Response::json([
    'timestamp'        => date('Y-m-d H:i:s'),
    'salones'          => $salones,
    'total_dentro'     => $totalDentro,
    'checkins_hoy'     => $checkinsHoy,
    'total_asistentes' => $totalAsistentes,
    'lecturas'         => $lecturas,
]);
