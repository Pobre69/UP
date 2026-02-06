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
            $this->conn = new PDO(
                "mysql:host={$DB_info['host']};dbname={$DB_info['database']};charset={$DB_info['charset']}",
                $DB_info['username'],
                $DB_info['password']
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            error_log("Falha ao conectar ao banco: " . $e->getMessage());
            $this->conn = null;
        }
        
        if(!isset($_SESSION)) {
            session_set_cookie_params([
                'lifetime' => 60 * 60 * 24 * 30,
                'path' => '/',
                'domain' => $_SERVER['HTTP_HOST'],
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]); 
            session_start();
        }
    }
    public function VerificarUsuario(): array
    {
        if ($this->conn === null) {
            $this->email_Usuario = '';
            $this->isLogado = false;
            return ['Logado' => $this->isLogado, 'Email' => $this->email_Usuario];
        }

        if (isset($_SESSION['email']) && !empty($_SESSION['email'])) {
            $valor = $_SESSION['email'];
            $stmt = $this->conn->prepare("SELECT COUNT(email) as total FROM usuario WHERE email = :pesquisa");
            $stmt->execute([':pesquisa' => $valor]);
            $count = (int) $stmt->fetchColumn();
        } else {
            $count = 0;
        }

        if ($count > 0) {
            $this->email_Usuario = $_SESSION['email'];
            $this->isLogado = true;
        } else {
            $this->email_Usuario = '';
            $this->isLogado = false;
        }
        $return['Logado'] = $this->isLogado;
        $return['Email'] = $this->email_Usuario;
        return $return;
    }
    public function setUsuario(string $email)
    {
        $_SESSION['email'] = $email;
        self::VerificarUsuario();
    }
    public function unsetUsuario()
    {
        unset($_SESSION['email']);
        self::VerificarUsuario();
    }
    public function getBasePath(): string
    {
        self::setConfig();
        $config = $this->config;
        return $config['urlBase'];
    }
}
