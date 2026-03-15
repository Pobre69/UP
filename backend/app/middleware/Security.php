<?php

namespace App\Middleware;

use PDO;

class Security
{
    private bool $isLogado;
    private string $email_Usuario;
    private array $config;
    private ?PDO $conn = null;

    private function setConfig()
    {
        $configPath = __DIR__ . '/../../config/config.json';
        $configJson = file_get_contents($configPath);
        $this->config = json_decode($configJson, true);
    }

    public function startSession()
    {
        self::setConfig();
        $config = $this->config;
        
        require_once __DIR__ . '/../../config/database/database.php';
        
        $DB_info = $config['database']['UP'] ?? [];
        try {
            $db = new \DataBase\Connection\database();
            $db->setConnection(
                $DB_info['host'],
                $DB_info['username'],
                $DB_info['password'],
                $DB_info['database'],
                'default'
            );
            $this->conn = \DataBase\Connection\database::getConnection();
        } catch (\PDOException $e) {
            error_log("Falha ao conectar ao banco: " . $e->getMessage());
            $this->conn = null;
        }
        
        if(session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 60 * 60 * 24 * 30,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]); 
            session_start();
        }
    }

    public function getBasePath(): string
    {
        self::setConfig();
        $config = $this->config;
        return $config['urlBase'];
    }

    public static function checkAuth()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_email']) || empty($_SESSION['user_email'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'mensagem' => 'Não autenticado']);
            exit;
        }
    }

    public static function getAuthUser()
    {
        self::checkAuth();
        return [
            'email' => $_SESSION['user_email'] ?? null,
            'nome' => $_SESSION['user_name'] ?? null
        ];
    }
}
