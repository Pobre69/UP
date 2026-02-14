<?php

namespace App\Repository;

use PDO;
use DataBase\Connection\database;

class PlanoRepository
{
    private function getConnection(): PDO
    {
        return database::getConnection();
    }

    public function add(?int $id, string $nome, int $valor)
    {
        $stmt = $this->getConnection()->prepare('CALL PLANO_CONTROLLER(:acao, :param_id, :param_nome, :param_valor)');
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
        $stmt = $this->getConnection()->prepare('CALL PLANO_CONTROLLER(:acao, :param_id, :param_nome, :param_valor)');
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
        $stmt = $this->getConnection()->prepare('CALL PLANO_CONTROLLER(:acao, NULL, NULL, NULL)');
        $stmt->execute([':acao' => 'read']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(int $id, string $nome)
    {
        $stmt = $this->getConnection()->prepare('CALL PLANO_CONTROLLER(:acao, :param_id, :param_nome, NULL)');
        $stmt->execute([
            ':acao' => 'delete',
            ':param_id' => $id,
            ':param_nome' => $nome
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
