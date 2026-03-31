<?php
namespace Controllers;

use App\Middleware\RateLimiter;
use App\Middleware\Security;
use App\Repository\InstagramTokenRepository;
use App\Repository\UsuarioRepository;
use App\Services\InstagramService;

class LoginController
{
    private RateLimiter $rateLimiter;

    public function __construct()
    {
        $this->rateLimiter = new RateLimiter();
    }

    public function authenticate(): void
    {
        header('Content-Type: application/json');

        $clientIp = $this->getClientIp();
        if (!$this->rateLimiter->isAllowed($clientIp)) {
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'mensagem' => 'Muitas tentativas de login. Tente novamente em alguns minutos.'
            ]);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Dados inválidos']);
            return;
        }

        $email = trim(mb_strtolower((string)($data['email'] ?? '')));
        $senha = (string)($data['senha'] ?? '');

        if ($email === '' || $senha === '') {
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

            if (!$usuario || empty($usuario['senha']) || !password_verify($senha, (string)$usuario['senha'])) {
                $this->rateLimiter->recordAttempt($clientIp);
                http_response_code(401);
                echo json_encode(['success' => false, 'mensagem' => 'E-mail ou senha incorretos']);
                return;
            }

            $ativo = (bool)($usuario['ativo'] ?? false);
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

            Security::startSession();
            session_regenerate_id(true);

            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $usuario['nome'] ?? null;
            $_SESSION['user_id'] = $usuario['id'] ?? null;
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();

            $this->rateLimiter->clearAttempts($clientIp);
            $this->syncInstagramData($email);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'mensagem' => 'Login realizado com sucesso',
                'ativo' => $ativo,
                'planoSelecionado' => $planoSelecionado,
                'usuario' => [
                    'id' => $usuario['id'] ?? null,
                    'email' => $usuario['email'] ?? $email,
                    'nome' => $usuario['nome'] ?? null,
                    'empresa' => $usuario['empresa'] ?? null
                ]
            ]);
        } catch (\Throwable $e) {
            error_log('[Login] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => 'Erro ao processar login']);
        }
    }

    public function validate(): void
    {
        header('Content-Type: application/json');

        Security::checkAuth();

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'usuario' => Security::getAuthUser()
        ]);
    }

    private function syncInstagramData(string $email): void
    {
        try {
            $tokenRepo = new InstagramTokenRepository();
            $tokenData = $tokenRepo->getByEmail($email);
            if (!$tokenData || empty($tokenData['access_token'])) {
                return;
            }

            $instagramService = new InstagramService();
            $instagramService->getAccountMetrics($email);
            $instagramService->getRecentPosts($email, 20);
        } catch (\Throwable $e) {
            error_log('[Instagram Sync] ' . $e->getMessage());
        }
    }

    private function getClientIp(): string
    {
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
        return filter_var($remoteAddr, FILTER_VALIDATE_IP) ? $remoteAddr : '0.0.0.0';
    }
}
