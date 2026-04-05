<?php

class Auth
{
    private const SESSION_KEY = 'admin_operador_id';

    public static function iniciar(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function requerirLogin(): void
    {
        self::iniciar();
        if (empty($_SESSION[self::SESSION_KEY])) {
            $base = self::basePath();
            header("Location: {$base}/login");
            exit;
        }
    }

    public static function login(string $nombre, string $pin, int $evento_id): bool
    {
        self::iniciar();
        $db   = DB::get();
        $stmt = $db->prepare("
            SELECT id, nombre, rol, pin
            FROM   operadores
            WHERE  evento_id = ?
              AND  activo    = 1
              AND  LOWER(nombre) = LOWER(?)
        ");
        $stmt->execute([$evento_id, trim($nombre)]);
        $op = $stmt->fetch();

        if ($op && password_verify($pin, $op['pin'])) {
            $_SESSION[self::SESSION_KEY] = $op['id'];
            $_SESSION['admin_nombre']    = $op['nombre'];
            $_SESSION['admin_rol']       = $op['rol'];
            return true;
        }
        return false;
    }

    public static function logout(): void
    {
        self::iniciar();
        session_destroy();
        $base = self::basePath();
        header("Location: {$base}/login");
        exit;
    }

    // Detecta si corre en subfolder (/checkin/admin) o en vhost (/admin)
    private static function basePath(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/admin';
        preg_match('#^(.*?/admin)#', $uri, $m);
        return $m[1] ?? '/admin';
    }

    public static function operadorId(): int
    {
        return (int) ($_SESSION[self::SESSION_KEY] ?? 0);
    }

    public static function rol(): string
    {
        return $_SESSION['admin_rol'] ?? '';
    }

    public static function esAdmin(): bool
    {
        return self::rol() === 'admin';
    }

    public static function esViewer(): bool
    {
        return self::rol() === 'viewer';
    }

    // admin y supervisor pueden editar; viewer solo lectura
    public static function puedeEditar(): bool
    {
        return in_array(self::rol(), ['admin', 'supervisor']);
    }

    // Para endpoints API — devuelve 401 en lugar de redirect
    public static function requerirLoginApi(): void
    {
        self::iniciar();
        if (empty($_SESSION[self::SESSION_KEY])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }
    }
}
