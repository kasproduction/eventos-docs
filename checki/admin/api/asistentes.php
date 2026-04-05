<?php

$evento_id = EVENTO_ID_ACTIVO;
$db        = DB::get();

$pagina  = max(1, (int)($_GET['pagina'] ?? 1));
$limite  = min(100, max(10, (int)($_GET['limite'] ?? 50)));
$offset  = ($pagina - 1) * $limite;
$buscar  = trim($_GET['q'] ?? '');
$estado  = $_GET['estado'] ?? '';   // dentro | fuera | ''

$where  = ['a.evento_id = ?'];
$params = [$evento_id];

if ($buscar !== '') {
    $where[]  = '(a.nombre LIKE ? OR a.email LIKE ? OR a.uid_qr LIKE ?)';
    $like     = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $buscar) . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($estado === 'dentro' || $estado === 'fuera') {
    $where[]  = 'COALESCE(ea_max.estado, "fuera") = ?';
    $params[] = $estado;
}

$whereStr = implode(' AND ', $where);

// Estado actual = si está "dentro" en algún salón
$total = $db->prepare("
    SELECT COUNT(*) FROM asistentes a
    LEFT JOIN (
        SELECT asistente_id, MAX(salon_id) AS salon_id, estado
        FROM   estado_asistentes WHERE estado = 'dentro' GROUP BY asistente_id
    ) ea_max ON ea_max.asistente_id = a.id
    WHERE $whereStr
");
$total->execute($params);
$totalRegistros = (int)$total->fetchColumn();

$stmt = $db->prepare("
    SELECT
        a.id,
        a.nombre,
        a.email,
        a.empresa,
        a.uid_qr,
        a.fuente,
        COALESCE(ea_max.estado, 'fuera') AS estado,
        s.nombre AS salon_actual,
        MAX(m.timestamp) AS ultimo_movimiento
    FROM asistentes a
    LEFT JOIN (
        SELECT asistente_id, salon_id, estado
        FROM   estado_asistentes WHERE estado = 'dentro'
    ) ea_max ON ea_max.asistente_id = a.id
    LEFT JOIN salones s ON s.id = ea_max.salon_id
    LEFT JOIN movimientos m ON m.asistente_id = a.id AND m.evento_id = a.evento_id
    WHERE $whereStr
    GROUP BY a.id, a.nombre, a.email, a.empresa, a.uid_qr, a.fuente, ea_max.estado, s.nombre
    ORDER BY a.nombre
    LIMIT $limite OFFSET $offset
");
$stmt->execute($params);
$asistentes = $stmt->fetchAll();

Response::json([
    'total'      => $totalRegistros,
    'pagina'     => $pagina,
    'limite'     => $limite,
    'paginas'    => (int)ceil($totalRegistros / $limite),
    'asistentes' => $asistentes,
]);
