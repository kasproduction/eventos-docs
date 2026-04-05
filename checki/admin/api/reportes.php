<?php

$db        = DB::get();
$evento_id = EVENTO_ID_ACTIVO;
$uri       = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path      = preg_replace('#^.*?/admin/api/reporte#', '', $uri);

// ─── GET /reporte/asistente/{id} ──────────────────────────
if (preg_match('#^/asistente/(\d+)$#', $path, $m)) {
    $asistente_id = (int)$m[1];

    $info = $db->prepare("SELECT id, nombre, email, empresa FROM asistentes WHERE id = ? AND evento_id = ? LIMIT 1");
    $info->execute([$asistente_id, $evento_id]);
    $asistente = $info->fetch();
    if (!$asistente) {
        Response::error(404, 'Asistente no encontrado');
    }

    $charlas = $db->prepare("
        SELECT
            ac.charla_id,
            c.titulo,
            c.hora_inicio,
            c.hora_fin,
            d.fecha,
            d.nombre AS dia_nombre,
            s.nombre AS salon_nombre,
            ac.checkin_real,
            ac.checkout_real,
            ac.minutos_presentes,
            ac.cuenta_asistencia,
            ac.calidad_dato
        FROM asistencia_calculada ac
        JOIN charlas     c ON c.id  = ac.charla_id
        JOIN dias_evento d ON d.id  = ac.dia_evento_id
        JOIN salones     s ON s.id  = c.salon_id
        WHERE ac.asistente_id = ?
          AND ac.evento_id    = ?
        ORDER BY d.orden, c.hora_inicio
    ");
    $charlas->execute([$asistente_id, $evento_id]);

    Response::json([
        'asistente' => $asistente,
        'charlas'   => $charlas->fetchAll(),
    ]);
}

// ─── GET /reporte/charla/{id} ─────────────────────────────
if (preg_match('#^/charla/(\d+)$#', $path, $m)) {
    $charla_id = (int)$m[1];

    $charla = $db->prepare("
        SELECT c.*, s.nombre AS salon_nombre
        FROM charlas c
        JOIN salones s ON s.id = c.salon_id
        JOIN dias_evento d ON d.id = c.dia_evento_id
        WHERE c.id = ? AND d.evento_id = ? LIMIT 1
    ");
    $charla->execute([$charla_id, $evento_id]);
    $dataCharla = $charla->fetch();
    if (!$dataCharla) {
        Response::error(404, 'Charla no encontrada');
    }

    $asistentes = $db->prepare("
        SELECT
            a.id, a.nombre, a.email, a.empresa,
            ac.checkin_real, ac.checkout_real,
            ac.minutos_presentes, ac.cuenta_asistencia, ac.calidad_dato
        FROM asistencia_calculada ac
        JOIN asistentes a ON a.id = ac.asistente_id
        WHERE ac.charla_id = ?
        ORDER BY ac.cuenta_asistencia DESC, ac.minutos_presentes DESC
    ");
    $asistentes->execute([$charla_id]);

    Response::json([
        'charla'     => $dataCharla,
        'asistentes' => $asistentes->fetchAll(),
    ]);
}

// ─── GET /reporte/dia/{dia_id} ────────────────────────────
if (preg_match('#^/dia/(\d+)$#', $path, $m)) {
    $dia_id = (int)$m[1];

    $dia = $db->prepare("SELECT * FROM dias_evento WHERE id = ? LIMIT 1");
    $dia->execute([$dia_id]);
    $dataDia = $dia->fetch();
    if (!$dataDia) {
        Response::error(404, 'Día no encontrado');
    }

    $resumen = $db->prepare("
        SELECT
            c.id AS charla_id,
            c.titulo,
            s.nombre AS salon,
            c.hora_inicio,
            c.hora_fin,
            COUNT(ac.asistente_id)                              AS total_registros,
            SUM(ac.cuenta_asistencia)                          AS total_cuentan,
            ROUND(AVG(ac.minutos_presentes), 1)                AS promedio_minutos,
            SUM(CASE WHEN ac.calidad_dato = 'inferido' THEN 1 ELSE 0 END) AS inferidos
        FROM charlas c
        JOIN salones s ON s.id = c.salon_id
        LEFT JOIN asistencia_calculada ac ON ac.charla_id = c.id
        WHERE c.dia_evento_id = ?
          AND c.cancelada     = 0
        GROUP BY c.id, c.titulo, s.nombre, c.hora_inicio, c.hora_fin
        ORDER BY c.hora_inicio
    ");
    $resumen->execute([$dia_id]);

    $unicos = $db->prepare("
        SELECT COUNT(DISTINCT asistente_id) AS asistentes_unicos
        FROM asistencia_calculada
        WHERE dia_evento_id = ?
    ");
    $unicos->execute([$dia_id]);

    Response::json([
        'dia'               => $dataDia,
        'charlas'           => $resumen->fetchAll(),
        'asistentes_unicos' => (int)$unicos->fetchColumn(),
    ]);
}

Response::error(404, 'Endpoint de reporte no encontrado');
