<?php

require_once __DIR__ . '/../api/config/db.php';
require_once __DIR__ . '/../api/src/DB.php';
require_once __DIR__ . '/src/Auth.php';

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Soporta tanto virtual host (/admin/...) como subfolder (/checkin/admin/...)
$path   = preg_replace('#^.*?/admin#', '', $uri);
$path   = rtrim($path, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

// ─── Rutas públicas ───────────────────────────────────────
if ($path === '/login') {
    if ($method === 'POST') {
        Auth::iniciar();
        $nombre    = trim($_POST['nombre'] ?? '');
        $pin       = trim($_POST['pin'] ?? '');
        $evento_id = (int)($_POST['evento_id'] ?? EVENTO_ID_ACTIVO);
        // Detectar base path para soportar subfolder y vhost
        $uri  = $_SERVER['REQUEST_URI'] ?? '/admin/login';
        preg_match('#^(.*?/admin)#', $uri, $m);
        $base = $m[1] ?? '/admin';

        if (Auth::login($nombre, $pin, $evento_id)) {
            header("Location: {$base}/");
        } else {
            header("Location: {$base}/login?error=1");
        }
        exit;
    }
    require __DIR__ . '/views/login.php';
    exit;
}

if ($path === '/logout') {
    Auth::logout();
}

// ─── Rutas API (JSON) ─────────────────────────────────────
if (str_starts_with($path, '/api')) {
    header('Content-Type: application/json; charset=utf-8');

    require_once __DIR__ . '/../api/src/Response.php';
    require_once __DIR__ . '/../api/src/adapters/contracts/AsistenteDTO.php';
    require_once __DIR__ . '/../api/src/adapters/contracts/CharlaDTO.php';
    require_once __DIR__ . '/../api/src/adapters/AdapterInterface.php';
    require_once __DIR__ . '/../api/src/adapters/CsvAdapter.php';
    require_once __DIR__ . '/../api/src/adapters/JsonAdapter.php';
    require_once __DIR__ . '/../api/src/adapters/ImportService.php';

    Auth::requerirLoginApi();

    // viewer: solo endpoints de lectura permitidos
    $apiPath = preg_replace('#^/api#', '', $path);
    if (Auth::esViewer()) {
        $apiPermitidaViewer =
            ($method === 'GET' && $apiPath === '/monitor')                  ||
            ($method === 'GET' && $apiPath === '/dashboard')                ||
            ($method === 'GET' && str_starts_with($apiPath, '/reporte'))    ||
            ($method === 'GET' && $apiPath === '/exportar')                 ||
            ($method === 'GET' && $apiPath === '/agenda')                   ||
            ($method === 'GET' && str_starts_with($apiPath, '/agenda'))     ||
            ($method === 'GET' && $apiPath === '/asistentes');
        if (!$apiPermitidaViewer) {
            Response::adminError(403, 'Sin permisos para esta acción');
        }
    }

    $apiPath = rtrim($apiPath, '/') ?: '/';

    match (true) {
        $method === 'GET'   && $apiPath === '/monitor'                => require __DIR__ . '/api/monitor.php',
        $method === 'GET'   && $apiPath === '/dashboard'               => require __DIR__ . '/api/dashboard.php',
        $method === 'GET'   && $apiPath === '/asistentes'             => require __DIR__ . '/api/asistentes.php',
        $method === 'POST'  && $apiPath === '/movimiento-manual'      => require __DIR__ . '/api/movimiento_manual.php',
        $method === 'GET'   && $apiPath === '/agenda'                 => require __DIR__ . '/api/agenda.php',
        $method === 'PATCH'  && preg_match('#^/charla/(\d+)$#', $apiPath, $m) => (function() use ($m) {
            $GLOBALS['charla_id'] = (int)$m[1];
            require __DIR__ . '/api/charla_editar.php';
        })(),
        $method === 'DELETE' && preg_match('#^/charla/(\d+)$#', $apiPath, $m) => (function() use ($m) {
            $GLOBALS['charla_id'] = (int)$m[1];
            require __DIR__ . '/api/charla_eliminar.php';
        })(),
        $method === 'POST'  && $apiPath === '/charla'                 => require __DIR__ . '/api/charla_crear.php',
        $method === 'POST'  && $apiPath === '/agenda/retrasar'        => require __DIR__ . '/api/agenda_retrasar.php',
        $method === 'POST'  && $apiPath === '/recalcular-asistencia'  => require __DIR__ . '/api/recalcular.php',
        $method === 'GET'   && str_starts_with($apiPath, '/reporte')  => require __DIR__ . '/api/reportes.php',
        $method === 'GET'   && $apiPath === '/exportar'               => require __DIR__ . '/api/exportar.php',
        $method === 'POST'  && $apiPath === '/importar'               => require __DIR__ . '/api/importar.php',
        $method === 'POST'  && $apiPath === '/importar-preview'       => require __DIR__ . '/api/importar_preview.php',
        $method === 'GET'   && $apiPath === '/plantillas'             => require __DIR__ . '/api/plantillas.php',
        $method === 'GET'   && $apiPath === '/sync-historial'         => require __DIR__ . '/api/sync_historial.php',
        // ── Configuración: salones ─────────────────────────────
        $method === 'GET'   && $apiPath === '/configuracion'          => require __DIR__ . '/api/configuracion.php',
        // ── Mi cuenta ─────────────────────────────────────────
        $method === 'PATCH'  && $apiPath === '/mi-cuenta'             => require __DIR__ . '/api/mi_cuenta.php',
        // ── Usuarios / operadores ──────────────────────────────
        $method === 'GET'   && $apiPath === '/operadores'             => require __DIR__ . '/api/operador_listar.php',
        $method === 'POST'  && $apiPath === '/operador'               => require __DIR__ . '/api/operador_crear.php',
        $method === 'PATCH'  && preg_match('#^/operador/(\d+)$#', $apiPath, $m) => (function() use ($m) {
            $GLOBALS['operador_id_param'] = (int)$m[1];
            require __DIR__ . '/api/operador_editar.php';
        })(),
        $method === 'DELETE' && preg_match('#^/operador/(\d+)$#', $apiPath, $m) => (function() use ($m) {
            $GLOBALS['operador_id_param'] = (int)$m[1];
            require __DIR__ . '/api/operador_eliminar.php';
        })(),
        $method === 'POST'  && $apiPath === '/salon'                  => require __DIR__ . '/api/salon_crear.php',
        $method === 'PATCH'  && preg_match('#^/salon/(\d+)$#', $apiPath, $m) => (function() use ($m) {
            $GLOBALS['salon_id_param'] = (int)$m[1];
            require __DIR__ . '/api/salon_editar.php';
        })(),
        $method === 'DELETE' && preg_match('#^/salon/(\d+)$#', $apiPath, $m) => (function() use ($m) {
            $GLOBALS['salon_id_param'] = (int)$m[1];
            require __DIR__ . '/api/salon_eliminar.php';
        })(),
        // ── Configuración: días ────────────────────────────────
        $method === 'POST'  && $apiPath === '/dia'                    => require __DIR__ . '/api/dia_crear.php',
        $method === 'PATCH'  && preg_match('#^/dia/(\d+)$#', $apiPath, $m) => (function() use ($m) {
            $GLOBALS['dia_id_param'] = (int)$m[1];
            require __DIR__ . '/api/dia_editar.php';
        })(),
        $method === 'DELETE' && preg_match('#^/dia/(\d+)$#', $apiPath, $m) => (function() use ($m) {
            $GLOBALS['dia_id_param'] = (int)$m[1];
            require __DIR__ . '/api/dia_eliminar.php';
        })(),
        // ── Pantallas de agenda ────────────────────────────────
        $method === 'POST'  && $apiPath === '/pantalla'                   => require __DIR__ . '/api/pantalla_crear.php',
        $method === 'PATCH'  && preg_match('#^/pantalla/(\d+)$#', $apiPath, $m) => (function() use ($m) {
            $GLOBALS['pantalla_id_param'] = (int)$m[1];
            require __DIR__ . '/api/pantalla_editar.php';
        })(),
        $method === 'DELETE' && preg_match('#^/pantalla/(\d+)$#', $apiPath, $m) => (function() use ($m) {
            $GLOBALS['pantalla_id_param'] = (int)$m[1];
            require __DIR__ . '/api/pantalla_eliminar.php';
        })(),
        $method === 'POST'   && preg_match('#^/pantalla/(\d+)/imagen$#', $apiPath, $m) => (function() use ($m) {
            $GLOBALS['pantalla_id_param'] = (int)$m[1];
            require __DIR__ . '/api/pantalla_imagen.php';
        })(),
        $method === 'POST'   && preg_match('#^/pantalla/(\d+)/video$#', $apiPath, $m) => (function() use ($m) {
            $GLOBALS['pantalla_id_param'] = (int)$m[1];
            require __DIR__ . '/api/pantalla_video.php';
        })(),
        $method === 'POST'   && $apiPath === '/agenda-override-imagen'    => require __DIR__ . '/api/override_imagen.php',
        $method === 'POST'   && $apiPath === '/agenda-override-video'     => require __DIR__ . '/api/override_video.php',
        $method === 'PATCH'  && $apiPath === '/agenda-override'           => require __DIR__ . '/api/agenda_override.php',
        // ── Configuración: tótems ──────────────────────────────
        $method === 'POST'  && $apiPath === '/totem'                  => require __DIR__ . '/api/totem_crear.php',
        $method === 'PATCH'  && preg_match('#^/totem/(\d+)$#', $apiPath, $m) => (function() use ($m) {
            $GLOBALS['totem_id_param'] = (int)$m[1];
            require __DIR__ . '/api/totem_editar.php';
        })(),
        $method === 'DELETE' && preg_match('#^/totem/(\d+)$#', $apiPath, $m) => (function() use ($m) {
            $GLOBALS['totem_id_param'] = (int)$m[1];
            require __DIR__ . '/api/totem_eliminar.php';
        })(),
        $method === 'POST'  && preg_match('#^/totem/(\d+)/tipo$#', $apiPath, $m2) => (function() use ($m2) {
            $GLOBALS['totem_id_param'] = (int)$m2[1];
            require __DIR__ . '/api/totem_tipo.php';
        })(),
        // ── Demo / Reset ─────────────────────────────────────────
        $method === 'POST'  && $apiPath === '/reset-demo'              => require __DIR__ . '/api/reset_demo.php',
        default => Response::error(404, 'Endpoint no encontrado'),
    };
    exit;
}

// ─── Vistas web ───────────────────────────────────────────
Auth::requerirLogin();

// viewer: solo monitor y reportes
$rutasViewer = ['/', '/monitor', '/dashboard', '/reportes'];
if (Auth::esViewer() && !in_array($path, $rutasViewer)) {
    preg_match('#^(.*?/admin)#', $_SERVER['REQUEST_URI'], $_rb);
    header('Location: ' . ($_rb[1] ?? '/admin') . '/');
    exit;
}

match (true) {
    $path === '/' || $path === '/monitor'  => require __DIR__ . '/views/monitor.php',
    $path === '/dashboard'                 => require __DIR__ . '/views/dashboard.php',
    $path === '/dashboard_v2'              => require __DIR__ . '/views/dashboard_v2.php',
    $path === '/agenda'                    => require __DIR__ . '/views/agenda.php',
    $path === '/asistentes'               => require __DIR__ . '/views/asistentes.php',
    $path === '/reportes'                 => require __DIR__ . '/views/reportes.php',
    $path === '/importar'                 => require __DIR__ . '/views/importar.php',
    $path === '/configuracion'            => require __DIR__ . '/views/configuracion.php',
    $path === '/usuarios'                 => require __DIR__ . '/views/usuarios.php',
    default => (function() { http_response_code(404); echo '404'; })(),
};
