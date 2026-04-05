<?php

/**
 * GET /admin/api/plantillas?tipo=asistentes|agenda
 *
 * Descarga una plantilla CSV lista para rellenar y subir.
 */

$tipo = $_GET['tipo'] ?? 'asistentes';

if (!in_array($tipo, ['asistentes', 'agenda'], true)) {
    Response::error(400, 'tipo debe ser "asistentes" o "agenda"');
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="plantilla_' . $tipo . '.csv"');
header('Cache-Control: no-cache');

// BOM
echo "\xEF\xBB\xBF";

$out = fopen('php://output', 'w');

if ($tipo === 'asistentes') {
    // Encabezado
    fputcsv($out, ['uid_qr', 'nombre', 'email', 'empresa', 'external_id'], ';');
    // Filas de ejemplo comentadas con #
    fputcsv($out, ['# Columnas obligatorias: uid_qr, nombre. El resto es opcional.'], ';');
    fputcsv($out, ['# uid_qr = el string exacto que emite el lector QR (código del badge)'], ';');
    fputcsv($out, ['# external_id = ID en tu sistema origen (Eventbrite, etc.)'], ';');
    // Ejemplos reales
    fputcsv($out, ['A3F29B8C', 'Juan García',   'juan@empresa.com',  'Empresa SA', '1001'], ';');
    fputcsv($out, ['D7E12C44', 'María López',   'maria@empresa.com', 'Empresa SA', '1002'], ';');
    fputcsv($out, ['9B3FA210', 'Carlos Ruiz',   '',                  '',           ''],    ';');
} else {
    // Agenda
    fputcsv($out, ['fecha', 'salon', 'titulo', 'ponente', 'hora_inicio', 'hora_fin'], ';');
    fputcsv($out, ['# fecha en formato YYYY-MM-DD'], ';');
    fputcsv($out, ['# hora_inicio / hora_fin en HH:MM o YYYY-MM-DD HH:MM:SS'], ';');
    fputcsv($out, ['# salon debe coincidir exactamente con el nombre del salón en el sistema'], ';');
    fputcsv($out, ['2025-03-10', 'Salón A', 'Innovación en IA',         'Dr. García',  '08:00', '09:00'], ';');
    fputcsv($out, ['2025-03-10', 'Salón A', 'Transformación Digital',   'Ing. López',  '09:00', '10:00'], ';');
    fputcsv($out, ['2025-03-10', 'Salón B', 'Workshop Liderazgo',       'Lic. Pérez',  '08:00', '10:00'], ';');
    fputcsv($out, ['2025-03-11', 'Salón A', 'Cierre y Networking',      '',            '17:00', '18:00'], ';');
}

fclose($out);
exit;
