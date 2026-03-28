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
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'mensagem' => 'Não autenticado']);
            exit;
        }
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
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
        }
        session_destroy();
    }
}
