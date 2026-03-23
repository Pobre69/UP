<?php
namespace Controllers;
use App\Repository\UsuarioRepository;
use App\Repository\InstagramTokenRepository;
use App\Services\InstagramService;
use App\Middleware\Security;
use App\Middleware\RateLimiter;

class LoginController
{
    private RateLimiter $rateLimiter;
    
    public function __construct()
    {
        $this->rateLimiter = new RateLimiter();
    }
    
    public function authenticate() {
        header('Content-Type: application/json');
        
        error_log("[Login] Endpoint /auth/login chamado");
        
        // Verificar rate limiting
        $clientIp = $this->getClientIp();
        if (!$this->rateLimiter->isAllowed($clientIp)) {
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'mensagem' => 'Muitas tentativas de login. Tente novamente em alguns minutos.'
            ]);
            return;
        }
        
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Dados inválidos']);
            return;
        }
        
        $email = trim($data['email'] ?? '');
        $senha = $data['senha'] ?? '';
        
        error_log("[Login] Tentativa de login para email: " . substr($email, 0, 3) . "***");
        
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
            
            error_log("[Login] Usuário encontrado: " . ($usuario ? 'sim' : 'não'));
            
            if (!$usuario || empty($usuario['senha'])) {
                $this->rateLimiter->recordAttempt($clientIp);
                http_response_code(401);
                echo json_encode(['success' => false, 'mensagem' => 'E-mail ou senha incorretos']);
                return;
            }
            
            if (!password_verify($senha, $usuario['senha'])) {
                $this->rateLimiter->recordAttempt($clientIp);
                error_log("[Login] Senha incorreta para email: " . substr($email, 0, 3) . "***");
                http_response_code(401);
                echo json_encode(['success' => false, 'mensagem' => 'E-mail ou senha incorretos']);
                return;
            }
            
            $ativo = $usuario['ativo'] ?? false;
            $planoSelecionado = $usuario['plano_selecionado'] ?? null;
            
            if (!$ativo) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'inativo' => true,
                    'planoSelecionado' => $planoSelecionado,
                    'mensagem' => 'Sua conta ainda não foi ativada. Por favor, conclua o pagamento para ativar sua conta.'
                ]);
                return;
            }
            
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Regenerar session ID para evitar session fixation
            session_regenerate_id(true);
            
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $usuario['nome'];
            $_SESSION['user_id'] = $usuario['id'] ?? null;
            $_SESSION['login_time'] = time();
            
            // Limpar tentativas de login falhadas
            $this->rateLimiter->clearAttempts($clientIp);
            
            $this->syncInstagramData($email);
            
            error_log("[Login] Login bem-sucedido para email: " . substr($email, 0, 3) . "***");
            
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
        } catch (\Exception $e) {
            error_log('[Login] Erro: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => 'Erro ao processar login']);
        }
    }
    
    public function validate() {
        header('Content-Type: application/json');
        
        Security::checkAuth();
        $user = Security::getAuthUser();
        
        // Verificar se a sessão não expirou
        $loginTime = $_SESSION['login_time'] ?? 0;
        $sessionTimeout = 30 * 24 * 60 * 60; // 30 dias
        
        if (time() - $loginTime > $sessionTimeout) {
            session_destroy();
            http_response_code(401);
            echo json_encode(['success' => false, 'mensagem' => 'Sessão expirada']);
            return;
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'usuario' => $user
        ]);
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
    
    private function getClientIp(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
        
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }
}
