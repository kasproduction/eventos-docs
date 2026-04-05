<?php

$evento_id  = EVENTO_ID_ACTIVO;
$dia_id     = (int)($_GET['dia_id'] ?? 0);
$db         = DB::get();

// Días del evento
$diasStmt = $db->prepare("
    SELECT id, fecha, nombre, orden
    FROM   dias_evento
    WHERE  evento_id = ?
    ORDER  BY orden
");
$diasStmt->execute([$evento_id]);
$dias = $diasStmt->fetchAll();

// Si no se especifica día, usar el día actual o el primero
if (!$dia_id) {
    foreach ($dias as $d) {
        if ($d['fecha'] === date('Y-m-d')) {
            $dia_id = (int)$d['id'];
            break;
        }
    }
    if (!$dia_id && !empty($dias)) {
        $dia_id = (int)$dias[0]['id'];
    }
}

// Charlas del día con conteo de asistentes actuales
$charlasStmt = $db->prepare("
    SELECT
        c.id,
        c.titulo,
        c.ponente,
        c.hora_inicio,
        c.hora_fin,
        c.hora_inicio_original,
        c.hora_fin_original,
        c.umbral_min_asistencia,
        c.buffer_pre_inicio,
        c.cancelada,
        c.orden_en_dia,
        s.id   AS salon_id,
        s.nombre AS salon_nombre,
        COALESCE(ea.dentro, 0) AS asistentes_dentro,
        CASE
            WHEN c.cancelada = 1                      THEN 'cancelada'
            WHEN c.hora_fin   < NOW()                  THEN 'finalizada'
            WHEN c.hora_inicio <= NOW()
             AND c.hora_fin   >= NOW()                 THEN 'en_curso'
            ELSE                                           'proxima'
        END AS estado
    FROM charlas c
    JOIN salones s ON s.id = c.salon_id
    LEFT JOIN (
        SELECT salon_id, COUNT(*) AS dentro
        FROM   estado_asistentes
        WHERE  estado = 'dentro'
        GROUP  BY salon_id
    ) ea ON ea.salon_id = c.salon_id
    WHERE c.dia_evento_id = ?
    ORDER BY c.orden_en_dia, c.hora_inicio
");
$charlasStmt->execute([$dia_id]);
$charlas = $charlasStmt->fetchAll();

// Todos los salones activos del evento (para modales de nueva charla y retrasar)
$salonesStmt = $db->prepare("
    SELECT id, nombre
    FROM   salones
    WHERE  evento_id = ?
      AND  activo = 1
    ORDER  BY id
");
$salonesStmt->execute([$evento_id]);
$salones = $salonesStmt->fetchAll();

// Cambios de agenda del día (para mostrar historial)
$cambiosStmt = $db->prepare("
    SELECT
        ac.charla_id,
        ac.campo_modificado,
        ac.valor_anterior,
        ac.valor_nuevo,
        ac.motivo,
        ac.timestamp,
        o.nombre AS operador
    FROM agenda_cambios ac
    JOIN operadores o ON o.id = ac.operador_id
    JOIN charlas c ON c.id = ac.charla_id
    WHERE c.dia_evento_id = ?
    ORDER BY ac.timestamp DESC
    LIMIT 20
");
$cambiosStmt->execute([$dia_id]);
$cambios = $cambiosStmt->fetchAll();

Response::json([
    'dias'      => $dias,
    'dia_id'    => $dia_id,
    'charlas'   => $charlas,
    'cambios'   => $cambios,
    'salones'   => $salones,
]);
