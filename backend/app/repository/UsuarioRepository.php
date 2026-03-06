<?php

namespace App\Repository;

use PDO;
use DataBase\Connection\database;

class UsuarioRepository
{
    private function getConnection(): PDO
    {
        return database::getConnection();
    }

    public function add(string $email, string $nome, ?string $senha = null, ?string $empresa = null)
    {
        $conn = $this->getConnection();
        $stmt = $conn->prepare('CALL USUARIO_CONTROLLER(:acao, :param_email, :param_nome, :param_senha, :param_empresa)');
        $stmt->execute([
            ':acao' => 'add',
            ':param_email' => $email,
            ':param_nome' => $nome,
            ':param_senha' => $senha,
            ':param_empresa' => $empresa
        ]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $result;
    }

    public function update(string $email, ?string $nome = null, ?string $senha = null, ?string $empresa = null)
    {
        $stmt = $this->getConnection()->prepare('CALL USUARIO_CONTROLLER(:acao, :param_email, :param_nome, :param_senha, :param_empresa)');
        $stmt->execute([
            ':acao' => 'update',
            ':param_email' => $email,
            ':param_nome' => $nome,
            ':param_senha' => $senha,
            ':param_empresa' => $empresa
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readAll()
    {
        $stmt = $this->getConnection()->prepare('CALL USUARIO_CONTROLLER(:acao, NULL, NULL, NULL, NULL)');
        $stmt->execute([':acao' => 'read']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(string $email)
    {
        $stmt = $this->getConnection()->prepare('CALL USUARIO_CONTROLLER(:acao, :param_email, NULL, NULL, NULL)');
        $stmt->execute([
            ':acao' => 'delete',
            ':param_email' => $email
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByEmail(string $email)
    {
        $stmt = $this->getConnection()->prepare('SELECT email, nome, senha, empresa FROM usuario WHERE email = :email');
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}