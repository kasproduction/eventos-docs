<?php

if (!Auth::esAdmin()) {
    Response::adminError(403, 'Solo el administrador puede resetear el evento');
}

$input = json_decode(file_get_contents('php://input'), true);
$confirmado = $input['confirmado'] ?? false;

if (!$confirmado) {
    Response::error(400, 'Debes confirmar el reset del evento');
}

$db = DB::get();

try {
    $db->beginTransaction();

    $tablas = [
        'asistencia_calculada',
        'agenda_cambios',
        'movimientos',
        'estado_asistentes',
        'sync_log',
        'asistentes',
        'charlas',
        'dias_evento',
        'totems',
        'salones',
        'operadores',
        'eventos'
    ];

    foreach ($tablas as $tabla) {
        $db->exec("TRUNCATE TABLE {$tabla}");
    }

    $sql = file_get_contents(__DIR__ . '/../../setup/reset_demo.sql');
    $db->exec($sql);

    $db->commit();

    Response::json([
        'ok' => true,
        'mensaje' => 'Evento reseteado a demo Tech Summit 2026',
        'detalle' => '4 salones, 3 días, 23 charlas, 50 asistentes, 8 totems'
    ]);

} catch (Exception $e) {
    $db->rollBack();
    Response::error(500, 'Error al resetear: ' . $e->getMessage());
}
