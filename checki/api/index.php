<?php

header('Content-Type: application/json; charset=utf-8');

// Solo acceso desde red local
// header('Access-Control-Allow-Origin: 192.168.1.*');

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/src/DB.php';
require_once __DIR__ . '/src/Response.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Soporta tanto virtual host (/api/...) como subfolder (/checkin/api/...)
$path = preg_replace('#^.*?/api#', '', $uri);
$path = rtrim($path, '/') ?: '/';

// ─── Rutas ────────────────────────────────────────────────
match (true) {

    $method === 'POST' && $path === '/lectura'        => require __DIR__ . '/src/Lectura.php',
    $method === 'GET'  && $path === '/ping'            => require __DIR__ . '/src/Ping.php',
    $method === 'GET'  && $path === '/charla-activa'  => require __DIR__ . '/src/CharlaActiva.php',
    $method === 'GET'  && $path === '/agenda-salon'   => require __DIR__ . '/src/AgendaSalon.php',
    $method === 'GET'  && $path === '/agenda-config'  => require __DIR__ . '/src/AgendaConfig.php',

    default => Response::error(404, 'Endpoint no encontrado'),

};
