<?php

namespace App\Repository;

use PDO;
use DataBase\Connection\database;

class RequestsRepository
{
    private function getConnection(): PDO
    {
        return database::getConnection();
    }

    public function createRequest(string $email, string $titulo, string $tipo, string $texto)
    {
        try {
            $stmt = $this->getConnection()->prepare(
                'INSERT INTO feedBack (usuario_email, titulo, tipo, texto) 
                 VALUES (:email, :titulo, :tipo, :texto)'
            );
            return $stmt->execute([
                ':email' => $email,
                ':titulo' => $titulo,
                ':tipo' => $tipo,
                ':texto' => $texto
            ]);
        } catch (\PDOException $e) {
            error_log('Erro SQL em createRequest: ' . $e->getMessage());
            throw new \Exception('Erro ao criar solicitação: ' . $e->getMessage());
        }
    }

    public function getUserRequests(string $email)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT 
                id,
                titulo,
                tipo,
                texto,
                status,
                created_at
             FROM feedBack 
             WHERE usuario_email = :email
             ORDER BY created_at DESC'
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRequestById(int $id, string $email)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT * FROM feedBack 
             WHERE id = :id AND usuario_email = :email'
        );
        $stmt->execute([
            ':id' => $id,
            ':email' => $email
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
