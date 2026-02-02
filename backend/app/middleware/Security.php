<?php

namespace Routes;

use DataBase\Connection\DB_Connection;
use PDO;

class Security
{
    private bool $isLogado;
    private int $ID_Usuario;
    private array $config;
    private ?PDO $conn = null;

    private function setConfig()
    {
        $configPath = __DIR__ . '/../config.json';
        $configJson = file_get_contents($configPath);
        $this->config = json_decode($configJson, true);
    }

    public function startSession()
    {
        self::setConfig();
        $config = $this->config;
        $DB_connection = new DB_Connection();
        $DB_info = $config['database'];
        try {
            $this->conn = $DB_connection->setConnection(
                $DB_info['host'] ?? 'localhost',
                $DB_info['username'] ?? 'root',
                $DB_info['password'] ?? '',
                $DB_info['database'] ?? ''
            );
        } catch (\RuntimeException $e) {
            error_log("Falha ao conectar ao banco: " . $e->getMessage());
            $this->conn = null;
        }
        if(!isset($_SESSION)) {
            session_set_cookie_params([
                'lifetime' => 60 * 60 * 24 * 30, // 7 dias
                'path' => '/',
                'domain' => $_SERVER['HTTP_HOST'],
                'secure' => isset($_SERVER['HTTPS']), // true se HTTPS
                'httponly' => true, // mais seguro
                'samesite' => 'Lax'
            ]); 
            session_start();
        }
    }
    public function VerificarUsuario(): array
    {
        if ($this->conn === null) {
            $this->ID_Usuario = 0;
            $this->isLogado = false;
            return ['Logado' => $this->isLogado, 'ID' => $this->ID_Usuario];
        }

        if (isset($_SESSION['id']) && $_SESSION['id'] > 0) {
            $valor = $_SESSION['id'];
            $stmt = $this->conn->prepare("SELECT COUNT(ID) as total FROM Show_Usuario WHERE ID = :pesquisa");
            $stmt->execute([':pesquisa' => $valor]);
            $count = (int) $stmt->fetchColumn();
        } else {
            $count = 0;
        }

        if ($count > 0) {
            $this->ID_Usuario = $_SESSION['id'];
            $this->isLogado = true;
        } else {
            $this->ID_Usuario = 0;
            $this->isLogado = false;
        }
        $return['Logado'] = $this->isLogado;
        $return['ID'] = $this->ID_Usuario;
        return $return;
    }
    public function setUsuario(int $ID_Usuario)
    {
        $_SESSION['id'] = $ID_Usuario;
        self::VerificarUsuario();
    }
    public function unsetUsuario()
    {
        unset($_SESSION['id']);
        self::VerificarUsuario();
    }
    public function getBasePath(): string
    {
        self::setConfig();
        $config = $this->config;
        return $config['urlBase'];
    }
}