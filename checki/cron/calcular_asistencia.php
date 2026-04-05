<?php

/**
 * CLI — Calcular asistencia
 *
 * Uso:
 *   php cron/calcular_asistencia.php --evento=1
 *   php cron/calcular_asistencia.php --evento=1 --dia=3
 *   php cron/calcular_asistencia.php --evento=1 --solo-si-requerido
 *
 * Cron (cada 5 minutos, detecta cambios de agenda pendientes):
 *   */5 * * * * php /var/www/cron/calcular_asistencia.php --evento=1 --solo-si-requerido
 */

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/api/config/db.php';
require_once BASE_PATH . '/api/src/DB.php';
require_once BASE_PATH . '/api/src/AsistenciaCalculator.php';

// ─── Parsear argumentos CLI ───────────────────────────────
$opts = getopt('', ['evento:', 'dia:', 'solo-si-requerido']);

$evento_id        = (int)($opts['evento'] ?? EVENTO_ID_ACTIVO);
$dia_id           = isset($opts['dia']) ? (int)$opts['dia'] : null;
$soloSiRequerido  = isset($opts['solo-si-requerido']);

$db         = DB::get();
$calculator = new AsistenciaCalculator($db);

// ─── Verificar si es necesario ────────────────────────────
if ($soloSiRequerido && !$calculator->necesitaRecalculo($evento_id)) {
    log_msg("Sin cambios pendientes. Nada que recalcular.");
    exit(0);
}

// ─── Ejecutar ─────────────────────────────────────────────
log_msg("Iniciando cálculo — evento=$evento_id" . ($dia_id ? " dia=$dia_id" : " (todos los días)"));
$inicio = microtime(true);

$resultado = $calculator->calcular($evento_id, $dia_id);

$elapsed = round(microtime(true) - $inicio, 2);

log_msg("Completado en {$elapsed}s:");
log_msg("  Charlas procesadas : {$resultado['charlas_procesadas']}");
log_msg("  Registros calculados: {$resultado['registros_calculados']}");

if (!empty($resultado['errores'])) {
    log_msg("  ERRORES (" . count($resultado['errores']) . "):");
    foreach ($resultado['errores'] as $e) {
        log_msg("    - $e");
    }
}

exit(empty($resultado['errores']) ? 0 : 1);

// ─────────────────────────────────────────────────────────
function log_msg(string $msg): void
{
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
}
