<?php

namespace Controllers;

use App\Middleware\Security;
use App\Repository\InstagramMetricsRepository;
use App\Repository\InstagramPostRepository;
use App\Repository\InstagramTokenRepository;
use App\Services\InstagramService;

class InstagramController
{
    private InstagramService $service;
    private InstagramMetricsRepository $metricsRepo;
    private InstagramPostRepository $postRepo;
    private InstagramTokenRepository $tokenRepo;

    public function __construct()
    {
        $this->service = new InstagramService();
        $this->metricsRepo = new InstagramMetricsRepository();
        $this->postRepo = new InstagramPostRepository();
        $this->tokenRepo = new InstagramTokenRepository();
    }

    public function connectAccount(): void
    {
        header('Content-Type: application/json');
        Security::checkAuth();
        $email = (string) Security::getAuthUser()['email'];
        $data = json_decode(file_get_contents('php://input'), true);

        if (!is_array($data) || empty($data['access_token'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Access token é obrigatório']);
            return;
        }

        try {
            $this->service->saveToken($email, (string)$data['access_token']);
            http_response_code(200);
            echo json_encode(['success' => true, 'mensagem' => 'Conta conectada com sucesso']);
        } catch (\Throwable $e) {
            error_log('[Instagram connect] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => 'Não foi possível conectar a conta']);
        }
    }

    public function getMetrics(): void
    {
        header('Content-Type: application/json');
        Security::checkAuth();
        $email = (string) Security::getAuthUser()['email'];

        try {
            $metrics = $this->service->getAccountMetrics($email);
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $metrics]);
        } catch (\Throwable $e) {
            error_log('[Instagram metrics] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => 'Não foi possível carregar as métricas']);
        }
    }

    public function getPosts(): void
    {
        header('Content-Type: application/json');
        Security::checkAuth();
        $email = (string) Security::getAuthUser()['email'];
        $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 20;

        try {
            $posts = $this->service->getRecentPosts($email, $limit);
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $posts]);
        } catch (\Throwable $e) {
            error_log('[Instagram posts] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => 'Não foi possível carregar os posts']);
        }
    }

    public function getMetricsHistory(): void
    {
        header('Content-Type: application/json');
        Security::checkAuth();
        $email = (string) Security::getAuthUser()['email'];

        try {
            $history = $this->metricsRepo->getHistoryByEmail($email);
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $history]);
        } catch (\Throwable $e) {
            error_log('[Instagram metrics history] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => 'Não foi possível carregar o histórico']);
        }
    }

    public function refreshToken(): void
    {
        header('Content-Type: application/json');
        Security::checkAuth();
        $email = (string) Security::getAuthUser()['email'];

        try {
            $newToken = $this->service->refreshToken($email);
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $newToken]);
        } catch (\Throwable $e) {
            error_log('[Instagram refresh] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => 'Não foi possível renovar o token']);
        }
    }

    public function disconnectAccount(): void
    {
        header('Content-Type: application/json');
        Security::checkAuth();
        $email = (string) Security::getAuthUser()['email'];

        try {
            $this->tokenRepo->delete($email);
            http_response_code(200);
            echo json_encode(['success' => true, 'mensagem' => 'Conta desconectada com sucesso']);
        } catch (\Throwable $e) {
            error_log('[Instagram disconnect] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => 'Não foi possível desconectar a conta']);
        }
    }

    public function getConnectionStatus(): void
    {
        header('Content-Type: application/json');
        Security::checkAuth();
        $email = (string) Security::getAuthUser()['email'];

        try {
            $tokenData = $this->tokenRepo->getByEmail($email);
            $isConnected = $tokenData !== false && !empty($tokenData);
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => [
                    'connected' => $isConnected,
                    'username' => $isConnected ? ($tokenData['instagram_username'] ?? null) : null,
                    'user_id' => $isConnected ? ($tokenData['instagram_user_id'] ?? null) : null,
                ]
            ]);
        } catch (\Throwable $e) {
            error_log('[Instagram status] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => 'Não foi possível verificar a conexão']);
        }
    }
}
