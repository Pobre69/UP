<?php

namespace Controllers;

use App\Middleware\Security;
use App\Repository\InstagramTokenRepository;
use App\Services\InstagramService;

class AppController
{
    private InstagramService $instagramService;
    private InstagramTokenRepository $tokenRepository;

    public function __construct()
    {
        $this->instagramService = new InstagramService();
        $this->tokenRepository = new InstagramTokenRepository();
    }

    public function sync(): void
    {
        header('Content-Type: application/json');
        Security::checkAuth();
        $email = (string) Security::getAuthUser()['email'];

        try {
            $token = $this->tokenRepository->getByEmail($email);
            if (!$token || empty($token['access_token']) || empty($token['instagram_user_id'])) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'synced' => false,
                        'connected' => false,
                        'metricsUpdated' => false,
                        'postsUpdated' => false,
                        'message' => 'Conta autenticada sem integração do Instagram vinculada.'
                    ]
                ]);
                return;
            }

            $metrics = $this->instagramService->getAccountMetrics($email);
            $posts = $this->instagramService->getRecentPosts($email, 20);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => [
                    'synced' => true,
                    'connected' => true,
                    'metricsUpdated' => true,
                    'postsUpdated' => true,
                    'metricsCount' => is_array($metrics) ? count($metrics) : 0,
                    'postsCount' => is_array($posts) ? count($posts) : 0,
                    'message' => 'Dados atualizados com sucesso.'
                ]
            ]);
        } catch (\Throwable $e) {
            error_log('[App sync] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'mensagem' => 'Erro ao sincronizar os dados do aplicativo'
            ]);
        }
    }
}
