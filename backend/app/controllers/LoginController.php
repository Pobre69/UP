<?php

namespace Controllers;

use App\Repository\UsuarioRepository;
use App\Repository\InstagramTokenRepository;
use App\Services\InstagramService;

class LoginController
{
    public function authenticate() {
        error_log("[Login] Iniciando autenticação");
        header('Content-Type: application/json');
        
        $rawInput = file_get_contents('php://input');
        error_log("[Login] Input recebido: " . $rawInput);
        
        $data = json_decode($rawInput, true);
        
        if (!$data) {
            error_log("[Login] ERRO: JSON inválido");
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Dados inválidos']);
            return;
        }
        
        error_log("[Login] Dados parseados: " . json_encode($data));
        
        $email = trim($data['email'] ?? '');
        $senha = trim($data['senha'] ?? '');
        
        error_log("[Login] Email: " . $email);
        error_log("[Login] Senha length: " . strlen($senha));
        
        if (empty($email) || empty($senha)) {
            error_log("[Login] ERRO: Campos vazios");
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Email e senha são obrigatórios']);
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            error_log("[Login] ERRO: Email inválido");
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'E-mail inválido']);
            return;
        }
        
        try {
            $usuarioRepo = new UsuarioRepository();
            $usuario = $usuarioRepo->getByEmail($email);
            
            error_log("[Login] Usuario encontrado: " . ($usuario ? 'SIM' : 'NAO'));
            
            if ($usuario) {
                error_log("[Login] Usuario data: " . json_encode($usuario));
                error_log("[Login] Senha no banco existe: " . (!empty($usuario['senha']) ? 'SIM' : 'NAO'));
            }
            
            if (!$usuario || empty($usuario['senha'])) {
                error_log("[Login] ERRO: Usuario não encontrado ou sem senha");
                http_response_code(401);
                echo json_encode(['success' => false, 'mensagem' => 'E-mail ou senha incorretos']);
                return;
            }
            
            $verifica = password_verify($senha, $usuario['senha']);
            error_log("[Login] Password verify result: " . ($verifica ? 'TRUE' : 'FALSE'));
            error_log("[Login] Senha fornecida: " . $senha);
            error_log("[Login] Hash no banco: " . $usuario['senha']);
            
            if ($verifica) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $usuario['nome'];
                
                error_log("[Login] Sessão criada com sucesso");
                
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
                error_log("[Login] ERRO: Senha incorreta");
                http_response_code(401);
                echo json_encode(['success' => false, 'mensagem' => 'E-mail ou senha incorretos']);
            }
        } catch (\Exception $e) {
            error_log("[Login] EXCEÇÃO: " . $e->getMessage());
            error_log("[Login] Stack: " . $e->getTraceAsString());
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
