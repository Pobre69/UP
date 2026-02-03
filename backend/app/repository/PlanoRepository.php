<?php

namespace App\Repository;

use PDO;

class PlanoRepository
{
    private PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function add(?int $id, string $nome, int $valor)
    {
        $stmt = $this->conn->prepare('CALL PLANO_CONTROLLER(:acao, :param_id, :param_nome, :param_valor)');
        $stmt->execute([
            ':acao' => 'add',
            ':param_id' => $id,
            ':param_nome' => $nome,
            ':param_valor' => $valor
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update(int $id, string $nome, ?int $novo_valor)
    {
        $stmt = $this->conn->prepare('CALL PLANO_CONTROLLER(:acao, :param_id, :param_nome, :param_valor)');
        $stmt->execute([
            ':acao' => 'update',
            ':param_id' => $id,
            ':param_nome' => $nome,
            ':param_valor' => $novo_valor
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readAll()
    {
        $stmt = $this->conn->prepare('CALL PLANO_CONTROLLER(:acao, NULL, NULL, NULL)');
        $stmt->execute([':acao' => 'read']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(int $id, string $nome)
    {
        $stmt = $this->conn->prepare('CALL PLANO_CONTROLLER(:acao, :param_id, :param_nome, NULL)');
        $stmt->execute([
            ':acao' => 'delete',
            ':param_id' => $id,
            ':param_nome' => $nome
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
