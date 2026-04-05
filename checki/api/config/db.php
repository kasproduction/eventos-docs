<?php

// ─── Conexión ──────────────────────────────────────────────
define('DB_HOST',    '127.0.0.1');
define('DB_PORT',    3306);
define('DB_NAME',    'checkin_system');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

// ─── Lógica de negocio ────────────────────────────────────
define('DEBOUNCE_SEGUNDOS', 5);     // Ignorar mismo UID+salón en este intervalo
define('EVENTO_ID_ACTIVO',  1);     // ID del evento que está corriendo ahora
