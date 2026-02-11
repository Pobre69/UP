<?php

namespace App\Repository;

use PDO;

class UsuarioRepository
{
    private PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function add(string $email, string $nome, ?string $senha = null, ?string $empresa = null)
    {
        $stmt = $this->conn->prepare('CALL USUARIO_CONTROLLER(:acao, :param_email, :param_nome, :param_senha, :param_empresa)');
        $stmt->execute([
            ':acao' => 'add',
            ':param_email' => $email,
            ':param_nome' => $nome,
            ':param_senha' => $senha,
            ':param_empresa' => $empresa
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update(string $email, ?string $nome = null, ?string $senha = null, ?string $empresa = null)
    {
        $stmt = $this->conn->prepare('CALL USUARIO_CONTROLLER(:acao, :param_email, :param_nome, :param_senha, :param_empresa)');
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
        $stmt = $this->conn->prepare('CALL USUARIO_CONTROLLER(:acao, NULL, NULL, NULL, NULL)');
        $stmt->execute([':acao' => 'read']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(string $email)
    {
        $stmt = $this->conn->prepare('CALL USUARIO_CONTROLLER(:acao, :param_email, NULL, NULL, NULL)');
        $stmt->execute([
            ':acao' => 'delete',
            ':param_email' => $email
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}