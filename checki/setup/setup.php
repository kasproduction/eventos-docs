<?php
/**
 * setup.php — Crea el operador administrador inicial
 *
 * Ejecutar UNA vez después de importar schema.sql y seed.sql.
 * Luego eliminar este archivo del servidor de producción.
 *
 * Uso CLI:  php setup/setup.php
 * Uso web:  http://localhost/checkin/setup/setup.php
 */

// ── Configuración ─────────────────────────────────────────
define('ADMIN_NOMBRE', 'Administrador');
define('ADMIN_PIN',    '1234');          // Cambia esto antes de ejecutar
define('ADMIN_ROL',    'admin');

// ── Conexión ──────────────────────────────────────────────
require_once __DIR__ . '/../api/config/db.php';
require_once __DIR__ . '/../api/src/DB.php';

$db = DB::get();

// ── Obtener evento activo ─────────────────────────────────
$stmt = $db->prepare("SELECT id, nombre FROM eventos WHERE id = ?");
$stmt->execute([EVENTO_ID_ACTIVO]);
$evento = $stmt->fetch();

if (!$evento) {
    die("ERROR: No existe un evento con ID " . EVENTO_ID_ACTIVO . ". Ejecuta seed.sql primero.\n");
}

// ── Verificar si ya existe un admin ──────────────────────
$check = $db->prepare("SELECT id, nombre FROM operadores WHERE evento_id = ? AND rol = 'admin' LIMIT 1");
$check->execute([EVENTO_ID_ACTIVO]);
$existente = $check->fetch();

if ($existente) {
    $msg = "Ya existe un administrador: [{$existente['id']}] {$existente['nombre']} — no se creó uno nuevo.";
    if (php_sapi_name() === 'cli') {
        echo "⚠  $msg\n";
    } else {
        echo "<p style='font-family:monospace; color:#b45309; background:#fffbeb; padding:1rem; border-radius:8px'>⚠ $msg</p>";
    }
    exit;
}

// ── Crear admin ───────────────────────────────────────────
$hash = password_hash(ADMIN_PIN, PASSWORD_BCRYPT);
$ins  = $db->prepare("
    INSERT INTO operadores (evento_id, nombre, rol, pin, activo)
    VALUES (?, ?, ?, ?, 1)
");
$ins->execute([EVENTO_ID_ACTIVO, ADMIN_NOMBRE, ADMIN_ROL, $hash]);
$newId = $db->lastInsertId();

// ── Resultado ────────────────────────────────────────────
$lines = [
    "✓ Operador admin creado",
    "  ID      : $newId",
    "  Nombre  : " . ADMIN_NOMBRE,
    "  PIN     : " . ADMIN_PIN,
    "  Evento  : [{$evento['id']}] {$evento['nombre']}",
    "",
    "⚠ ELIMINA este archivo del servidor de producción.",
];

if (php_sapi_name() === 'cli') {
    echo implode("\n", $lines) . "\n";
} else {
    echo '<pre style="font-family:monospace; background:#f0fdf4; color:#166534; padding:1.5rem; border-radius:8px; border:1px solid #bbf7d0; line-height:1.7">';
    echo htmlspecialchars(implode("\n", $lines));
    echo '</pre>';
}
