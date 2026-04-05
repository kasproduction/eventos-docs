<?php

$body  = json_decode(file_get_contents('php://input'), true);
$valor = $body['override'] ?? 'none';

// Valores válidos: 'none', 'off', 'imagen', o un integer positivo (salon_id)
if ($valor !== 'none' && $valor !== 'off' && $valor !== 'imagen' && !ctype_digit((string)$valor)) {
    Response::adminError(400, 'Valor de override inválido');
}

try {
    $db = DB::get();
    $db->prepare("
        INSERT INTO configuracion (clave, valor) VALUES ('agenda_override', ?)
        ON DUPLICATE KEY UPDATE valor = ?
    ")->execute([$valor, $valor]);

    // retorno_minutos opcional
    $retorno_en = null;
    $minutos    = isset($body['retorno_minutos']) ? (int)$body['retorno_minutos'] : 0;
    if ($minutos > 0) {
        $stmt = $db->query("SELECT DATE_ADD(NOW(), INTERVAL {$minutos} MINUTE)");
        $retorno_en = $stmt->fetchColumn();
    }

    $db->prepare("
        INSERT INTO configuracion (clave, valor) VALUES ('agenda_override_retorno_en', ?)
        ON DUPLICATE KEY UPDATE valor = ?
    ")->execute([$retorno_en, $retorno_en]);

} catch (\PDOException $e) {
    Response::adminError(500, 'Ejecuta setup/migrate_agenda_pantallas.sql antes de usar esta función');
}

Response::json(['ok' => true, 'override' => $valor]);
