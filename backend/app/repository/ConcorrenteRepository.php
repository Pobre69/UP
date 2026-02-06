<?php

namespace App\Repository;

use PDO;

class ConcorrenteRepository
{
    private PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function add(string $email, ?string $descricao = null)
    {
        $stmt = $this->conn->prepare('CALL CONCORRENTE_CONTROLLER(:acao, :param_email, :param_descricao)');
        $stmt->execute([
            ':acao' => 'add',
            ':param_email' => $email,
            ':param_descricao' => $descricao
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update(string $email, ?string $descricao = null)
    {
        $stmt = $this->conn->prepare('CALL CONCORRENTE_CONTROLLER(:acao, :param_email, :param_descricao)');
        $stmt->execute([
            ':acao' => 'update',
            ':param_email' => $email,
            ':param_descricao' => $descricao
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readAll()
    {
        $stmt = $this->conn->prepare('CALL CONCORRENTE_CONTROLLER(:acao, NULL, NULL)');
        $stmt->execute([':acao' => 'read']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(string $email)
    {
        $stmt = $this->conn->prepare('CALL CONCORRENTE_CONTROLLER(:acao, :param_email, NULL)');
        $stmt->execute([
            ':acao' => 'delete',
            ':param_email' => $email
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
