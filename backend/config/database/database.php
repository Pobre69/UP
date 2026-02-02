<?php

namespace DataBase\Connection;

use PDO;
use PDOException;

class database {

    private static ?PDO $conn = null;
    private static array $conns = [];
    private static array $settings = [];
    private static string $host;
    private static string $usuario;
    private static string $senha;
    private static string $banco;

    public function setConnection(string $host, string $usuario, string $senha, string $banco, string $name = 'default'): PDO
    {
        self::$settings[$name] = [
            'host' => $host,
            'usuario' => $usuario,
            'senha' => $senha,
            'banco' => $banco
        ];

        if ($name === 'default') {
            self::$host = $host;
            self::$usuario = $usuario;
            self::$senha = $senha;
            self::$banco = $banco;
        }

        return self::getConnection($name);
    }

    public static function getConnection(string $name = 'default'): PDO 
    {
        if ($name === 'default' && self::$conn !== null) {
            return self::$conn;
        }

        if ($name !== 'default' && isset(self::$conns[$name]) && self::$conns[$name] instanceof PDO) {
            return self::$conns[$name];
        }

        try {
            if ($name === 'default') {
                $host = self::$host;
                $usuario = self::$usuario;
                $senha = self::$senha;
                $banco = self::$banco;
            } else {
                if (!isset(self::$settings[$name])) {
                    throw new \InvalidArgumentException("Configuração para conexão '$name' não encontrada.");
                }
                $s = self::$settings[$name];
                $host = $s['host'];
                $usuario = $s['usuario'];
                $senha = $s['senha'];
                $banco = $s['banco'];
            }

            try {
                $tempConn = new PDO("mysql:host=$host", $usuario, $senha);
                $tempConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Access denied') !== false && $usuario === 'root') {
                    try {
                        $tempConn = new PDO("mysql:host=$host", $usuario, '');
                        $tempConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $senha = '';
                        if ($name === 'default') {
                            self::$senha = '';
                        } else {
                            self::$settings[$name]['senha'] = '';
                        }
                    } catch (PDOException $e2) {
                        throw $e2;
                    }
                } else {
                    throw $e;
                }
            }

            $dsn = "mysql:host=$host;dbname=$banco;charset=utf8mb4";
            $pdo = new PDO($dsn, $usuario, $senha, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);

            if ($name === 'default') {
                self::$conn = $pdo;
                self::$conns['default'] = $pdo;
            } else {
                self::$conns[$name] = $pdo;
            }

            return $pdo;

        } catch (PDOException $e) {
            error_log("Erro na conexão com o banco: " . $e->getMessage());
            throw new \RuntimeException("Erro na conexão com o banco de dados. Verifique se o MySQL está rodando e as credenciais estão corretas.");
        }
    }
    public static function Start_DataBase(array $Routes): void{
        $host = self::$host;
        $usuario = self::$usuario;
        $senha = self::$senha;
        $banco = self::$banco;
        try {
            $tempConn = new PDO("mysql:host=$host", $usuario, $senha);
            $tempConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Start_DataBase: falha ao conectar ao servidor MySQL: " . $e->getMessage());
            
            if (strpos($e->getMessage(), 'Access denied') !== false && $usuario === 'root') {
                try {
                    $tempConn = new PDO("mysql:host=$host", $usuario, '');
                    $tempConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $senha = '';
                    self::$senha = '';
                } catch (PDOException $e2) {
                    error_log("Start_DataBase: fallback também falhou: " . $e2->getMessage());
                    return;
                }
            } else {
                return;
            }
        }

        $dbExists = $tempConn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$banco'")->fetchColumn();
        
        if (!$dbExists) {
            $tempConn->exec("CREATE DATABASE IF NOT EXISTS `$banco` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $tempConn->exec("USE `$banco`");
            
            foreach ($Routes as $file) {
                if (file_exists($file)) {
                    $sql = file_get_contents($file);
                    $tempConn->exec($sql);
                }
            }
        }
    }
}
