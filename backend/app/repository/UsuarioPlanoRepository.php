<?php

namespace App\Repository;

use PDO;

class UsuarioPlanoRepository
{
    private PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function add(string $usuario_email, int $plano_id, string $plano_nome, string $data_inicial, string $data_final)
    {
        $stmt = $this->conn->prepare('CALL USUARIO_PLANO_CONTROLLER(:acao, :param_usuario_email, :param_plano_id, :param_plano_nome, :param_data_inicial, :param_data_final)');
        $stmt->execute([
            ':acao' => 'add',
            ':param_usuario_email' => $usuario_email,
            ':param_plano_id' => $plano_id,
            ':param_plano_nome' => $plano_nome,
            ':param_data_inicial' => $data_inicial,
            ':param_data_final' => $data_final
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update(string $usuario_email, int $plano_id, string $plano_nome, ?string $nova_data_inicial = null, ?string $nova_data_final = null)
    {
        $stmt = $this->conn->prepare('CALL USUARIO_PLANO_CONTROLLER(:acao, :param_usuario_email, :param_plano_id, :param_plano_nome, :param_data_inicial, :param_data_final)');
        $stmt->execute([
            ':acao' => 'update',
            ':param_usuario_email' => $usuario_email,
            ':param_plano_id' => $plano_id,
            ':param_plano_nome' => $plano_nome,
            ':param_data_inicial' => $nova_data_inicial,
            ':param_data_final' => $nova_data_final
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readAll()
    {
        $stmt = $this->conn->prepare('CALL USUARIO_PLANO_CONTROLLER(:acao, NULL, NULL, NULL, NULL, NULL)');
        $stmt->execute([':acao' => 'read']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(string $usuario_email, int $plano_id, string $plano_nome)
    {
        $stmt = $this->conn->prepare('CALL USUARIO_PLANO_CONTROLLER(:acao, :param_usuario_email, :param_plano_id, :param_plano_nome, NULL, NULL)');
        $stmt->execute([
            ':acao' => 'delete',
            ':param_usuario_email' => $usuario_email,
            ':param_plano_id' => $plano_id,
            ':param_plano_nome' => $plano_nome
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
