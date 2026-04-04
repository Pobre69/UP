<?php

namespace Controllers;

use App\Config\AppConfig;
use App\Middleware\Security;
use App\Repository\UsuarioRepository;

class PaymentWebhookController
{
    public function handleWebhook($request, $response)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $status = $data['status'] ?? null;
        $email = $data['customer']['email'] ?? null;

        if (!$email) {
            return $response->json(['error' => 'Email não encontrado'], 400);
        }

        if ($status === 'paid' || $status === 'approved') {

            $usuario = Usuario::where('email', $email)->first();

            if ($usuario) {

                $usuario->ativo = 1;
                $usuario->save();

                $plano = $data['product']['name'] ?? 'basico';

                app(PlanRepository::class)->ativarPlano($usuario->id, $plano);

                return $response->json(['success' => true]);
            }
        }

        return $response->json(['ignored' => true]);
    }

    public function verificarPagamento(): void
    {
        header('Content-Type: application/json');

        Security::startSession();
        $authUser = null;

        try {
            $authUser = Security::getAuthUser();
        } catch (\Throwable $e) {
            $authUser = null;
        }

        $pending = Security::getPendingPayment();
        $email = (string)($authUser['email'] ?? $pending['email'] ?? '');

        if ($email === '') {
            http_response_code(401);
            echo json_encode(['success' => false, 'mensagem' => 'Nenhum pagamento pendente encontrado']);
            return;
        }

        try {
            $usuarioRepo = new UsuarioRepository();
            $usuario = $usuarioRepo->getByEmail($email);

            if (!$usuario) {
                http_response_code(404);
                echo json_encode(['success' => false, 'mensagem' => 'Usuário não encontrado']);
                return;
            }

            $ativo = (bool)($usuario['ativo'] ?? false);
            if ($ativo && !isset($authUser['email'])) {
                Security::authenticateUser([
                    'email' => $email,
                    'nome' => $usuario['nome'] ?? null,
                    'id' => $usuario['id'] ?? null,
                ]);
            }

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'ativo' => $ativo,
                'planoSelecionado' => $usuario['plano_selecionado'] ?? ($pending['planoSelecionado'] ?? null)
            ]);
        } catch (\Throwable $e) {
            error_log('[Verificar Pagamento] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => 'Erro ao verificar status']);
        }
    }

    private function isSignatureValid(string $payload): bool
    {
        $secret = (string) AppConfig::get('security.webhookSecret', '');
        $baseUrl = (string) AppConfig::get('urlBase', '');
        $isLocal = str_contains($baseUrl, 'localhost') || str_contains($baseUrl, '127.0.0.1');

        if ($secret === '') {
            return $isLocal;
        }

        $signature = (string)($_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '');
        if ($signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected, $signature);
    }
}
