<?php

namespace Controllers;

use App\Repository\UsuarioRepository;
use App\Repository\InstagramTokenRepository;
use App\Services\InstagramService;

class LoginController
{
    public function authenticate() {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Dados inválidos']);
            return;
        }
        
        $email = trim($data['email'] ?? '');
        $senha = trim($data['senha'] ?? '');
        
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
            
            if (!$usuario) {
                http_response_code(401);
                echo json_encode(['success' => false, 'mensagem' => 'Credenciais inválidas']);
                return;
            }
            
            if (empty($usuario['senha'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'mensagem' => 'Usuário sem senha cadastrada']);
                return;
            }
            
            if (password_verify($senha, $usuario['senha'])) {
                session_start();
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $usuario['nome'];
                
                // Buscar dados do Instagram automaticamente
                $this->syncInstagramData($email);
                
                http_response_code(200);
                echo json_encode([
                    'success' => true, 
                    'mensagem' => 'Login realizado com sucesso',
                    'usuario' => [
                        'email' => $usuario['email'],
                        'nome' => $usuario['nome'],
                        'empresa' => $usuario['empresa']
                    ]
                ]);
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'mensagem' => 'Credenciais inválidas']);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => 'Erro ao fazer login: ' . $e->getMessage()]);
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
