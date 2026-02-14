<?php

namespace App\Repository;

use PDO;
use DataBase\Connection\database;

class ConcorrenteRepository
{
    private function getConnection(): PDO
    {
        return database::getConnection();
    }

    public function add(string $email, ?string $descricao = null)
    {
        $stmt = $this->getConnection()->prepare('CALL CONCORRENTE_CONTROLLER(:acao, :param_email, :param_descricao)');
        $stmt->execute([
            ':acao' => 'add',
            ':param_email' => $email,
            ':param_descricao' => $descricao
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update(string $email, ?string $descricao = null)
    {
        $stmt = $this->getConnection()->prepare('CALL CONCORRENTE_CONTROLLER(:acao, :param_email, :param_descricao)');
        $stmt->execute([
            ':acao' => 'update',
            ':param_email' => $email,
            ':param_descricao' => $descricao
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readAll()
    {
        $stmt = $this->getConnection()->prepare('CALL CONCORRENTE_CONTROLLER(:acao, NULL, NULL)');
        $stmt->execute([':acao' => 'read']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(string $email)
    {
        $stmt = $this->getConnection()->prepare('CALL CONCORRENTE_CONTROLLER(:acao, :param_email, NULL)');
        $stmt->execute([
            ':acao' => 'delete',
            ':param_email' => $email
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
