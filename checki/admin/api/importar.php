<?php

// Detectar tipo de importación
$tipo_import = $_POST['tipo'] ?? '';   // asistentes | agenda
$fuente      = $_POST['fuente'] ?? 'csv';  // csv | json
$evento_id   = EVENTO_ID_ACTIVO;
$dia_id      = (int)($_POST['dia_id']    ?? 0);   // requerido para agenda

if (!in_array($tipo_import, ['asistentes', 'agenda'], true)) {
    Response::error(400, 'tipo debe ser "asistentes" o "agenda"');
}

// Leer archivo o body raw
$raw = '';

if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
    $raw = file_get_contents($_FILES['archivo']['tmp_name']);
} else {
    $raw = file_get_contents('php://input');
}

if (empty($raw)) {
    Response::error(400, 'No se recibió contenido para importar');
}

// Instanciar adaptador
$adapter = match ($fuente) {
    'json'  => new JsonAdapter(),
    default => new CsvAdapter(delimiter: 'auto'),
};

// Parsear y importar
if ($tipo_import === 'asistentes') {
    $dtos      = $adapter->parseAsistentes($raw);
    $resultado = ImportService::importarAsistentes($dtos, $evento_id, $adapter->nombre());

    Response::json([
        'ok'          => true,
        'tipo'        => 'asistentes',
        'total_dtos'  => count($dtos),
        'insertados'  => $resultado['insertados'],
        'actualizados'=> $resultado['actualizados'],
        'omitidos'    => $resultado['omitidos'],
        'errores'     => $resultado['errores'],
    ]);
}

if ($tipo_import === 'agenda') {
    if (!$dia_id) {
        Response::error(400, 'dia_id es requerido para importar agenda');
    }
    $chkDia = DB::get()->prepare("SELECT id FROM dias_evento WHERE id = ? AND evento_id = ? LIMIT 1");
    $chkDia->execute([$dia_id, $evento_id]);
    if (!$chkDia->fetch()) {
        Response::error(404, 'Día no encontrado en el evento activo');
    }

    $dtos      = $adapter->parseAgenda($raw);
    $resultado = ImportService::importarAgenda($dtos, $dia_id, $adapter->nombre());

    Response::json([
        'ok'          => true,
        'tipo'        => 'agenda',
        'total_dtos'  => count($dtos),
        'insertadas'  => $resultado['insertadas'],
        'actualizadas'=> $resultado['actualizadas'],
        'omitidas'    => $resultado['omitidas'],
        'errores'     => $resultado['errores'],
    ]);
}
