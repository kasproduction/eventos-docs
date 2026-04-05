<?php

require_once __DIR__ . '/../../api/src/AsistenciaCalculator.php';

$input     = json_decode(file_get_contents('php://input'), true);
$evento_id = (int)($input['evento_id'] ?? 0) ?: EVENTO_ID_ACTIVO;
$dia_id    = (int)($input['dia_id']    ?? 0) ?: null;
$con_auto_checkout = (bool)($input['auto_checkout'] ?? false);

$db         = DB::get();
$calculator = new AsistenciaCalculator($db);

$auto_checkouts = 0;

// Auto-checkout opcional (normalmente solo se activa desde fin_jornada cron)
if ($con_auto_checkout) {
    $auto_checkouts = $calculator->autoCheckoutFinJornada($evento_id, $dia_id);
}

$resultado = $calculator->calcular($evento_id, $dia_id);

Response::json([
    'ok'                  => true,
    'charlas_procesadas'  => $resultado['charlas_procesadas'],
    'registros_calculados'=> $resultado['registros_calculados'],
    'auto_checkouts'      => $auto_checkouts,
    'errores'             => $resultado['errores'],
]);
