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
    
    public function add(string $email, string $nome, ?string $senha = null, ?string $empresa = null, ?string $planoSelecionado = null)
    {
        $conn = $this->getConnection();
        
        // Validar se email já existe
        if ($this->emailExists($email)) {
            throw new \Exception('Este e-mail já está cadastrado');
        }
        
        // Validar entrada
        if (empty($email) || empty($nome)) {
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
        
        return $stmt->rowCount();
    }
    
    public function update(string $email, ?string $nome = null, ?string $senha = null, ?string $empresa = null)
    {
        // Validar entrada
        if (empty($email)) {
            throw new \Exception('Email é obrigatório');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
    
    public function readAll()
    {
        $stmt = $this->getConnection()->prepare(
            'CALL USUARIO_CONTROLLER(:acao, NULL, NULL, NULL, NULL)'
        );
        
        $result = $stmt->execute([':acao' => 'read']);
        
        if (!$result) {
            throw new \Exception('Erro ao buscar usuários');
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function delete(string $email)
    {
        // Validar entrada
        if (empty($email)) {
            throw new \Exception('Email é obrigatório');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('E-mail inválido');
        }
        
        $stmt = $this->getConnection()->prepare(
            'CALL USUARIO_CONTROLLER(:acao, :param_email, NULL, NULL, NULL)'
        );
        
        $result = $stmt->execute([
            ':acao' => 'delete',
            ':param_email' => $email
        ]);
        
        if (!$result) {
            throw new \Exception('Erro ao deletar usuário');
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getByEmail(string $email)
    {
        // Validar entrada
        if (empty($email)) {
            throw new \Exception('Email é obrigatório');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('E-mail inválido');
        }
        
        $conn = $this->getConnection();
        
        $stmt = $conn->prepare(
            'SELECT email, nome, senha, empresa, ativo, plano_selecionado, id FROM usuario WHERE email = :email'
        );
        
        $result = $stmt->execute([':email' => $email]);
        
        if (!$result) {
            throw new \Exception('Erro ao buscar usuário');
        }
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Se as colunas não existirem, adicionar defaults
        if ($result) {
            $result['ativo'] = $result['ativo'] ?? false;
            $result['plano_selecionado'] = $result['plano_selecionado'] ?? null;
            $result['id'] = $result['id'] ?? null;
        }
        
        return $result;
    }
    
    public function ativarConta(string $email)
    {
        // Validar entrada
        if (empty($email)) {
            throw new \Exception('Email é obrigatório');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('E-mail inválido');
        }
        
        $conn = $this->getConnection();
        
        $stmt = $conn->prepare('UPDATE usuario SET ativo = 1 WHERE email = :email');
        
        $result = $stmt->execute([':email' => $email]);
        
        if (!$result) {
            throw new \Exception('Erro ao ativar conta');
        }
        
        return $stmt->rowCount();
    }
    
    public function emailExists(string $email): bool
    {
        $conn = $this->getConnection();
        
        $stmt = $conn->prepare('SELECT COUNT(*) FROM usuario WHERE email = :email');
        $stmt->execute([':email' => $email]);
        
        return $stmt->fetchColumn() > 0;
    }
}
