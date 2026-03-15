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
        
        // Verificar se as colunas existem
        $columns = $conn->query("SHOW COLUMNS FROM usuario")->fetchAll(PDO::FETCH_COLUMN);
        $hasAtivo = in_array('ativo', $columns);
        $hasPlano = in_array('plano_selecionado', $columns);
        
        if ($hasAtivo && $hasPlano) {
            $stmt = $conn->prepare('INSERT INTO usuario (email, nome, senha, empresa, plano_selecionado) VALUES (:email, :nome, :senha, :empresa, :plano)');
            $stmt->execute([
                ':email' => $email,
                ':nome' => $nome,
                ':senha' => $senha,
                ':empresa' => $empresa,
                ':plano' => $planoSelecionado
            ]);
        } else {
            $stmt = $conn->prepare('INSERT INTO usuario (email, nome, senha, empresa) VALUES (:email, :nome, :senha, :empresa)');
            $stmt->execute([
                ':email' => $email,
                ':nome' => $nome,
                ':senha' => $senha,
                ':empresa' => $empresa
            ]);
        }
        
        return $stmt->rowCount();
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
        $conn = $this->getConnection();
        
        // Verificar se as colunas existem
        $columns = $conn->query("SHOW COLUMNS FROM usuario")->fetchAll(PDO::FETCH_COLUMN);
        $hasAtivo = in_array('ativo', $columns);
        $hasPlano = in_array('plano_selecionado', $columns);
        
        if ($hasAtivo && $hasPlano) {
            $stmt = $conn->prepare('SELECT email, nome, senha, empresa, ativo, plano_selecionado FROM usuario WHERE email = :email');
        } else {
            $stmt = $conn->prepare('SELECT email, nome, senha, empresa FROM usuario WHERE email = :email');
        }
        
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Adicionar valores padrão se as colunas não existirem
        if ($result && !$hasAtivo) {
            $result['ativo'] = true; // Se não tem coluna, considera ativo
        }
        if ($result && !$hasPlano) {
            $result['plano_selecionado'] = null;
        }
        
        return $result;
    }

    public function ativarConta(string $email)
    {
        $conn = $this->getConnection();
        
        // Verificar se a coluna existe
        $columns = $conn->query("SHOW COLUMNS FROM usuario")->fetchAll(PDO::FETCH_COLUMN);
        
        if (in_array('ativo', $columns)) {
            $stmt = $conn->prepare('UPDATE usuario SET ativo = TRUE WHERE email = :email');
            $stmt->execute([':email' => $email]);
            return $stmt->rowCount();
        }
        
        return 0;
    }
}