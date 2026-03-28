<?php

namespace DataBase\Connection;

use PDO;
use PDOException;
use Routes\Acess;

class database
{
    private static ?PDO $conn = null;
    private static array $conns = [];
    private static array $settings = [];
    private static string $host = 'localhost';
    private static string $usuario = 'root';
    private static string $senha = '';
    private static string $banco = 'UP';

    public function setConnection(string $host, string $usuario, string $senha, string $banco, string $name = 'default'): PDO
    {
        self::$settings[$name] = compact('host', 'usuario', 'senha', 'banco');

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
        if ($name === 'default' && self::$conn instanceof PDO) {
            return self::$conn;
        }
        if ($name !== 'default' && isset(self::$conns[$name]) && self::$conns[$name] instanceof PDO) {
            return self::$conns[$name];
        }

        $settings = $name === 'default'
            ? ['host' => self::$host, 'usuario' => self::$usuario, 'senha' => self::$senha, 'banco' => self::$banco]
            : (self::$settings[$name] ?? null);

        if ($settings === null) {
            throw new \InvalidArgumentException("Configuração para conexão '{$name}' não encontrada.");
        }

        $host = $settings['host'];
        $usuario = $settings['usuario'];
        $senha = $settings['senha'];
        $banco = $settings['banco'];

        self::ensureDatabaseExists($host, $usuario, $senha, $banco);

        $pdo = new PDO(
            "mysql:host={$host};dbname={$banco};charset=utf8mb4",
            $usuario,
            $senha,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        if ($name === 'default') {
            self::$conn = $pdo;
        } else {
            self::$conns[$name] = $pdo;
        }

        return $pdo;
    }

    private static function ensureDatabaseExists(string $host, string $usuario, string $senha, string $banco): void
    {
        try {
            $tempConn = new PDO("mysql:host={$host};charset=utf8mb4", $usuario, $senha, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            throw $e;
        }

        $stmt = $tempConn->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :schema');
        $stmt->execute([':schema' => $banco]);
        $exists = (bool) $stmt->fetchColumn();

        if ($exists) {
            return;
        }

        $tempConn->exec("CREATE DATABASE `{$banco}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $tempConn->exec("USE `{$banco}`");

        foreach (Acess::sqlAcess() as $sqlFile) {
            if (!is_file($sqlFile)) {
                continue;
            }
            $sql = file_get_contents($sqlFile);
            if ($sql === false || trim($sql) === '') {
                continue;
            }
            $tempConn->exec($sql);
        }
    }
}
