<?php

namespace App\Middleware;

use App\Config\AppConfig;

class Security
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $lifetime = (int) AppConfig::get('session.lifetime', 2592000);
        $secure = (bool) AppConfig::get('session.secure', false);
        $httpOnly = (bool) AppConfig::get('session.httponly', true);
        $sameSite = (string) AppConfig::get('session.samesite', 'Lax');
        $sessionName = (string) AppConfig::get('session.name', 'UPSESSID');

        session_name($sessionName);
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => '/',
            'domain' => '',
            'secure' => $secure || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => $httpOnly,
            'samesite' => $sameSite,
        ]);

        session_start();
    }

    public static function getBasePath(): string
    {
        return (string) AppConfig::get('urlBase', 'http://localhost');
    }

    public static function checkAuth(): void
    {
        self::startSession();

        if (!isset($_SESSION['user_email']) || empty($_SESSION['user_email'])) {
            self::rejectUnauthorized('Não autenticado');
        }

        $timeout = (int) AppConfig::get('session.lifetime', 2592000);
        $lastActivity = (int)($_SESSION['last_activity'] ?? $_SESSION['login_time'] ?? 0);

        if ($lastActivity > 0 && (time() - $lastActivity) > $timeout) {
            self::destroySession();
            self::rejectUnauthorized('Sessão expirada');
        }

        $_SESSION['last_activity'] = time();
    }

    public static function getAuthUser(): array
    {
        self::checkAuth();
        return [
            'email' => $_SESSION['user_email'] ?? null,
            'nome' => $_SESSION['user_name'] ?? null,
            'id' => $_SESSION['user_id'] ?? null,
        ];
    }

    public static function destroySession(): void
    {
        self::startSession();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'] ?: '/',
                'domain' => $params['domain'] ?? '',
                'secure' => (bool)($params['secure'] ?? false),
                'httponly' => (bool)($params['httponly'] ?? true),
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }

        session_destroy();
    }

    private static function rejectUnauthorized(string $message): void
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'mensagem' => $message]);
        exit;
    }
}
