<?php

require_once __DIR__ . '/../../api/src/ExportService.php';

$formato   = $_GET['formato']  ?? 'detalle';   // detalle | por_asistente | por_charla | resumen
$dia_id    = (int)($_GET['dia_id'] ?? 0) ?: null;
$evento_id = EVENTO_ID_ACTIVO;

$db      = DB::get();
$service = new ExportService($db);

// Nombre del archivo
$sufijo   = $dia_id ? "_dia{$dia_id}" : '_completo';
$fecha    = date('Ymd_His');
$nombres  = [
    'detalle'       => "asistencia_detalle{$sufijo}_{$fecha}.csv",
    'por_asistente' => "asistencia_por_asistente{$sufijo}_{$fecha}.csv",
    'por_charla'    => "asistencia_por_charla{$sufijo}_{$fecha}.csv",
    'resumen'       => "asistencia_resumen{$sufijo}_{$fecha}.csv",
];

if (!array_key_exists($formato, $nombres)) {
    http_response_code(400);
    echo json_encode(['error' => 'Formato inválido. Opciones: detalle, por_asistente, por_charla, resumen']);
    exit;
}

// Generar el stream CSV
$stream = match ($formato) {
    'detalle'       => $service->generarDetalle($evento_id, $dia_id),
    'por_asistente' => $service->generarPorAsistente($evento_id, $dia_id),
    'por_charla'    => $service->generarPorCharla($evento_id, $dia_id),
    'resumen'       => $service->generarResumen($evento_id, $dia_id),
};

// Headers para descarga
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nombres[$formato] . '"');
header('Cache-Control: no-cache');

fpassthru($stream);
fclose($stream);
exit;
