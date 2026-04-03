<?php

namespace App\Middleware;

use App\Config\AppConfig;

class Security
{
    private const LOCAL_HOSTS = ['localhost', '127.0.0.1'];

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

    public static function getTrustedOrigins(): array
    {
        $frontendUrl = (string) AppConfig::get('frontendUrl', 'http://localhost:5173');
        $baseUrl = self::getBasePath();

        $origins = [
            rtrim($frontendUrl, '/'),
            rtrim($baseUrl, '/'),
            'http://localhost:5173',
            'http://127.0.0.1:5173',
            'http://localhost',
            'http://127.0.0.1',
        ];

        $requestOrigin = trim((string)($_SERVER['HTTP_ORIGIN'] ?? ''));
        if ($requestOrigin !== '' && self::isTrustedOrigin($requestOrigin)) {
            $origins[] = rtrim($requestOrigin, '/');
        }

        return array_values(array_filter(array_unique($origins)));
    }

    public static function isTrustedOrigin(string $origin): bool
    {
        $origin = rtrim(trim($origin), '/');
        if ($origin === '') {
            return false;
        }

        $trustedOrigins = [
            rtrim((string) AppConfig::get('frontendUrl', 'http://localhost:5173'), '/'),
            rtrim(self::getBasePath(), '/'),
            'http://localhost:5173',
            'http://127.0.0.1:5173',
            'http://localhost',
            'http://127.0.0.1',
        ];

        if (in_array($origin, array_filter(array_unique($trustedOrigins)), true)) {
            return true;
        }

        $originHost = parse_url($origin, PHP_URL_HOST);
        if (is_string($originHost) && in_array($originHost, self::LOCAL_HOSTS, true)) {
            return true;
        }

        $baseHosts = [
            parse_url((string) AppConfig::get('frontendUrl', 'http://localhost:5173'), PHP_URL_HOST),
            parse_url(self::getBasePath(), PHP_URL_HOST),
        ];

        return is_string($originHost) && in_array($originHost, array_filter(array_unique($baseHosts)), true);
    }

    public static function enforceTrustedOrigin(): void
    {
        $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return;
        }

        $origin = trim((string)($_SERVER['HTTP_ORIGIN'] ?? ''));
        if ($origin === '') {
            return;
        }

        if (!self::isTrustedOrigin($origin)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'mensagem' => 'Origem da requisição não autorizada']);
            exit;
        }
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

    public static function storePendingPayment(array $userData): void
    {
        self::startSession();
        $_SESSION['pending_payment_email'] = $userData['email'] ?? null;
        $_SESSION['pending_payment_name'] = $userData['nome'] ?? null;
        $_SESSION['pending_payment_id'] = $userData['id'] ?? null;
        $_SESSION['pending_payment_plan'] = $userData['planoSelecionado'] ?? null;
        $_SESSION['pending_payment_started_at'] = time();
    }

    public static function getPendingPayment(): array
    {
        self::startSession();

        return [
            'email' => $_SESSION['pending_payment_email'] ?? null,
            'nome' => $_SESSION['pending_payment_name'] ?? null,
            'id' => $_SESSION['pending_payment_id'] ?? null,
            'planoSelecionado' => $_SESSION['pending_payment_plan'] ?? null,
            'startedAt' => $_SESSION['pending_payment_started_at'] ?? null,
        ];
    }

    public static function clearPendingPayment(): void
    {
        self::startSession();
        unset(
            $_SESSION['pending_payment_email'],
            $_SESSION['pending_payment_name'],
            $_SESSION['pending_payment_id'],
            $_SESSION['pending_payment_plan'],
            $_SESSION['pending_payment_started_at']
        );
    }

    public static function authenticateUser(array $userData): void
    {
        self::startSession();
        session_regenerate_id(false);
        $_SESSION['user_email'] = $userData['email'] ?? null;
        $_SESSION['user_name'] = $userData['nome'] ?? null;
        $_SESSION['user_id'] = $userData['id'] ?? null;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        self::clearPendingPayment();
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
