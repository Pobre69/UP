<?php
namespace App\Repository;

use DataBase\Connection\database;
use PDO;

class UsuarioRepository
{
    private function getConnection(): PDO
    {
        return database::getConnection();
    }

    public function add(string $email, string $nome, ?string $senha = null, ?string $empresa = null, ?string $planoSelecionado = null): int
    {
        $conn = $this->getConnection();

        if ($this->emailExists($email)) {
            throw new \Exception('Este e-mail já está cadastrado');
        }

        if ($email === '' || $nome === '') {
            throw new \Exception('Email e nome são obrigatórios');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('E-mail inválido');
        }

        $stmt = $conn->prepare(
            'INSERT INTO usuario (email, nome, senha, empresa, plano_selecionado, ativo)
             VALUES (:email, :nome, :senha, :empresa, :plano, 0)'
        );

        $result = $stmt->execute([
            ':email' => $email,
            ':nome' => $nome,
            ':senha' => $senha,
            ':empresa' => $empresa,
            ':plano' => $planoSelecionado
        ]);

        if (!$result) {
            throw new \Exception('Erro ao inserir usuário no banco de dados');
        }

        return (int) $conn->lastInsertId();
    }

    public function update(string $email, ?string $nome = null, ?string $senha = null, ?string $empresa = null): array
    {
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('E-mail inválido');
        }

        $stmt = $this->getConnection()->prepare(
            'CALL USUARIO_CONTROLLER(:acao, :param_email, :param_nome, :param_senha, :param_empresa)'
        );

        $result = $stmt->execute([
            ':acao' => 'update',
            ':param_email' => $email,
            ':param_nome' => $nome ?? '',
            ':param_senha' => $senha ?? '',
            ':param_empresa' => $empresa ?? ''
        ]);

        if (!$result) {
            throw new \Exception('Erro ao atualizar usuário');
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readAll(): array
    {
        $stmt = $this->getConnection()->prepare('CALL USUARIO_CONTROLLER(:acao, NULL, NULL, NULL, NULL)');
        if (!$stmt->execute([':acao' => 'read'])) {
            throw new \Exception('Erro ao buscar usuários');
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(string $email): array
    {
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('E-mail inválido');
        }

        $stmt = $this->getConnection()->prepare(
            'CALL USUARIO_CONTROLLER(:acao, :param_email, NULL, NULL, NULL)'
        );

        if (!$stmt->execute([':acao' => 'delete', ':param_email' => $email])) {
            throw new \Exception('Erro ao deletar usuário');
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByEmail(string $email): ?array
    {
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('E-mail inválido');
        }

        $stmt = $this->getConnection()->prepare(
            'SELECT id, email, nome, senha, empresa, ativo, plano_selecionado FROM usuario WHERE email = :email LIMIT 1'
        );

        if (!$stmt->execute([':email' => $email])) {
            throw new \Exception('Erro ao buscar usuário');
        }

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            return null;
        }

        $result['id'] = isset($result['id']) ? (int) $result['id'] : null;
        $result['ativo'] = (bool)($result['ativo'] ?? false);
        $result['plano_selecionado'] = $result['plano_selecionado'] ?? null;

        return $result;
    }

    public function ativarConta(string $email): int
    {
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('E-mail inválido');
        }

        $stmt = $this->getConnection()->prepare('UPDATE usuario SET ativo = 1 WHERE email = :email');
        if (!$stmt->execute([':email' => $email])) {
            throw new \Exception('Erro ao ativar conta');
        }
        return $stmt->rowCount();
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->getConnection()->prepare('SELECT COUNT(*) FROM usuario WHERE email = :email');
        $stmt->execute([':email' => $email]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
