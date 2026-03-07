<?php

namespace Controllers;

use App\Repository\UsuarioRepository;
use App\Repository\InstagramTokenRepository;
use App\Services\InstagramService;

class LoginController
{
    public function authenticate() {
        header('Content-Type: application/json');
        
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Dados inválidos']);
            return;
        }
        
        $email = trim($data['email'] ?? '');
        $senha = $data['senha'] ?? '';
        
        if (empty($email) || empty($senha)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Email e senha são obrigatórios']);
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'E-mail inválido']);
            return;
        }
        
        try {
            $usuarioRepo = new UsuarioRepository();
            $usuario = $usuarioRepo->getByEmail($email);
            
            if (!$usuario || empty($usuario['senha'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'mensagem' => 'E-mail ou senha incorretos']);
                return;
            }
            
            if (password_verify($senha, $usuario['senha'])) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $usuario['nome'];
                
                $this->syncInstagramData($email);
                
                $ativo = $usuario['ativo'] ?? false;
                $planoSelecionado = $usuario['plano_selecionado'] ?? null;
                
                http_response_code(200);
                echo json_encode([
                    'success' => true, 
                    'mensagem' => 'Login realizado com sucesso',
                    'ativo' => $ativo,
                    'planoSelecionado' => $planoSelecionado,
                    'usuario' => [
                        'email' => $usuario['email'],
                        'nome' => $usuario['nome'],
                        'empresa' => $usuario['empresa'] ?? null
                    ]
                ]);
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'mensagem' => 'E-mail ou senha incorretos']);
            }
        } catch (\Exception $e) {
            error_log('[Login] Erro: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => 'Erro ao processar login']);
        }
    }

    private function syncInstagramData(string $email)
    {
        try {
            $tokenRepo = new InstagramTokenRepository();
            $tokenData = $tokenRepo->getByEmail($email);
            
            if ($tokenData && !empty($tokenData['access_token'])) {
                $instagramService = new InstagramService();
                
                // Buscar métricas da conta
                $instagramService->getAccountMetrics($email);
                
                // Buscar posts recentes
                $instagramService->getRecentPosts($email, 20);
            }
        } catch (\Exception $e) {
            error_log('Erro ao sincronizar dados do Instagram: ' . $e->getMessage());
        }
    }
}
