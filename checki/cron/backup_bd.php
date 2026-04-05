<?php

/**
 * CLI — Backup automático de base de datos
 *
 * Genera un volcado comprimido (.sql.gz) y lo guarda localmente.
 * Si hay un USB montado en la ruta configurada, copia ahí también.
 * Rota automáticamente: conserva solo los últimos N backups.
 *
 * Uso:
 *   php cron/backup_bd.php
 *   php cron/backup_bd.php --dir=/backups/checkin   ← sobreescribe directorio
 *
 * Cron (cada hora):
 *   0 * * * * php /var/www/html/cron/backup_bd.php >> /var/log/checkin_backup.log 2>&1
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/api/config/db.php';

// ─── Configuración ────────────────────────────────────────────
$opts = getopt('', ['dir:']);

$BACKUP_DIR     = $opts['dir'] ?? BASE_PATH . '/backups';   // directorio local
$USB_DIR        = '/media/usb/checkin_backups';             // ruta USB (Linux mount point)
$RETENER        = 48;                                        // cuántos backups conservar (48h a 1/hora)
$MYSQLDUMP_BIN  = 'mysqldump';                              // ruta al binario (ajustar si no está en PATH)

// ─── Inicialización ───────────────────────────────────────────
$ts       = date('Y-m-d_H-i');
$filename = "checkin_{$ts}.sql.gz";
$tmpFile  = sys_get_temp_dir() . "/{$filename}";

log_msg("=== BACKUP BD === {$ts}");

// ─── Crear directorio local si no existe ──────────────────────
if (!is_dir($BACKUP_DIR)) {
    if (!mkdir($BACKUP_DIR, 0750, true)) {
        log_msg("ERROR: No se pudo crear el directorio: {$BACKUP_DIR}");
        exit(1);
    }
}

// ─── Construir comando mysqldump ──────────────────────────────
$host     = escapeshellarg(DB_HOST);
$port     = (int) DB_PORT;
$dbname   = escapeshellarg(DB_NAME);
$user     = escapeshellarg(DB_USER);
$pass     = DB_PASS !== '' ? '-p' . escapeshellarg(DB_PASS) : '';

$cmd = "{$MYSQLDUMP_BIN} -h {$host} -P {$port} -u {$user} {$pass}"
     . " --single-transaction --routines --triggers"
     . " --add-drop-table --complete-insert"
     . " {$dbname} | gzip -9 > " . escapeshellarg($tmpFile)
     . " 2>&1";

// ─── Ejecutar ─────────────────────────────────────────────────
$output   = [];
$exitCode = 0;
exec($cmd, $output, $exitCode);

if ($exitCode !== 0 || !file_exists($tmpFile) || filesize($tmpFile) === 0) {
    log_msg("ERROR: mysqldump falló (exit={$exitCode})");
    if ($output) log_msg("  Detalle: " . implode(' ', $output));
    exit(1);
}

$sizeKb = round(filesize($tmpFile) / 1024, 1);
log_msg("  Volcado generado: {$filename} ({$sizeKb} KB)");

// ─── Copiar a directorio local ────────────────────────────────
$destLocal = "{$BACKUP_DIR}/{$filename}";
if (!copy($tmpFile, $destLocal)) {
    log_msg("ERROR: No se pudo copiar a {$destLocal}");
    unlink($tmpFile);
    exit(1);
}
log_msg("  Guardado en: {$destLocal}");

// ─── Copiar a USB si está montado ─────────────────────────────
if (is_dir($USB_DIR) && is_writable($USB_DIR)) {
    $destUsb = "{$USB_DIR}/{$filename}";
    if (copy($tmpFile, $destUsb)) {
        log_msg("  Copiado a USB: {$destUsb}");
    } else {
        log_msg("  AVISO: USB montado pero no se pudo copiar a {$destUsb}");
    }
} else {
    log_msg("  USB no disponible — solo backup local");
}

// ─── Limpiar tmp ──────────────────────────────────────────────
unlink($tmpFile);

// ─── Rotar backups locales (conservar últimos N) ──────────────
$archivos = glob("{$BACKUP_DIR}/checkin_*.sql.gz");
if ($archivos && count($archivos) > $RETENER) {
    sort($archivos); // orden ascendente → los más viejos primero
    $aEliminar = array_slice($archivos, 0, count($archivos) - $RETENER);
    foreach ($aEliminar as $viejo) {
        unlink($viejo);
        log_msg("  Rotado (eliminado): " . basename($viejo));
    }
}

// ─── Rotar backups en USB también ────────────────────────────
if (is_dir($USB_DIR) && is_writable($USB_DIR)) {
    $archivosUsb = glob("{$USB_DIR}/checkin_*.sql.gz");
    if ($archivosUsb && count($archivosUsb) > $RETENER) {
        sort($archivosUsb);
        $aEliminarUsb = array_slice($archivosUsb, 0, count($archivosUsb) - $RETENER);
        foreach ($aEliminarUsb as $viejo) {
            unlink($viejo);
        }
    }
}

log_msg("  Total backups locales: " . count(glob("{$BACKUP_DIR}/checkin_*.sql.gz")));
log_msg("=== FIN ===");
exit(0);

// ─── Helper ───────────────────────────────────────────────────
function log_msg(string $msg): void
{
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
}
