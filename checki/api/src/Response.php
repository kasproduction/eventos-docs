<?php

class Response
{
    public static function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Respuesta de error estandarizada para el tótem
    public static function error(int $status, string $mensaje): never
    {
        self::json([
            'tipo'    => 'error',
            'color'   => 'rojo',
            'mensaje' => $mensaje,
        ], $status);
    }

    // Respuesta de error para el panel admin (JS espera { error: '...' })
    public static function adminError(int $status, string $mensaje): never
    {
        self::json(['ok' => false, 'error' => $mensaje], $status);
    }
}
