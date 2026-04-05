<?php

/**
 * CLI — Fin de jornada
 *
 * Ejecutar al final de cada día del evento. Hace dos cosas:
 *  1. Auto-checkout para todos los asistentes que siguen "dentro"
 *     (metodo: auto_fin_jornada, flag: inferido)
 *  2. Recalcula asistencia completa del día
 *
 * Uso:
 *   php cron/fin_jornada.php --evento=1
 *   php cron/fin_jornada.php --evento=1 --dia=3   ← solo ese día
 *
 * Cron (23:00 todos los días):
 *   0 23 * * * php /var/www/cron/fin_jornada.php --evento=1
 */

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/api/config/db.php';
require_once BASE_PATH . '/api/src/DB.php';
require_once BASE_PATH . '/api/src/AsistenciaCalculator.php';

$opts      = getopt('', ['evento:', 'dia:']);
$evento_id = (int)($opts['evento'] ?? EVENTO_ID_ACTIVO);
$dia_id    = isset($opts['dia']) ? (int)$opts['dia'] : null;

$db         = DB::get();
$calculator = new AsistenciaCalculator($db);

log_msg("=== FIN DE JORNADA === evento=$evento_id" . ($dia_id ? " dia=$dia_id" : ''));

// ─── Paso 1: Auto-checkout ────────────────────────────────
log_msg("Paso 1: Auto-checkout de asistentes que siguen dentro...");
$checkouts = $calculator->autoCheckoutFinJornada($evento_id, $dia_id);
log_msg("  Auto-checkouts generados: $checkouts");

// ─── Paso 2: Calcular asistencia ─────────────────────────
log_msg("Paso 2: Calculando asistencia...");
$inicio    = microtime(true);
$resultado = $calculator->calcular($evento_id, $dia_id);
$elapsed   = round(microtime(true) - $inicio, 2);

log_msg("  Charlas procesadas  : {$resultado['charlas_procesadas']}");
log_msg("  Registros generados : {$resultado['registros_calculados']}");
log_msg("  Tiempo              : {$elapsed}s");

if (!empty($resultado['errores'])) {
    log_msg("  ERRORES:");
    foreach ($resultado['errores'] as $e) {
        log_msg("    - $e");
    }
}

log_msg("=== FIN ===");
exit(empty($resultado['errores']) ? 0 : 1);

function log_msg(string $msg): void
{
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
}
