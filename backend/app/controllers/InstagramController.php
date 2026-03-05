<?php

namespace Controllers;

use App\Services\InstagramService;
use App\Repository\InstagramMetricsRepository;
use App\Repository\InstagramPostRepository;
use App\Middleware\Security;

class InstagramController
{
    private InstagramService $service;
    private InstagramMetricsRepository $metricsRepo;
    private InstagramPostRepository $postRepo;

    public function __construct()
    {
        $this->service = new InstagramService();
        $this->metricsRepo = new InstagramMetricsRepository();
        $this->postRepo = new InstagramPostRepository();
    }

    public function connectAccount()
    {
        header('Content-Type: application/json');
        
        Security::checkAuth();
        $user = Security::getAuthUser();
        $email = $user['email'];
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['access_token'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Access token é obrigatório']);
            return;
        }

        try {
            $this->service->saveToken($email, $data['access_token']);
            http_response_code(200);
            echo json_encode(['success' => true, 'mensagem' => 'Conta conectada com sucesso']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    public function getMetrics()
    {
        header('Content-Type: application/json');
        
        Security::checkAuth();
        $user = Security::getAuthUser();
        $email = $user['email'];

        try {
            $metrics = $this->service->getAccountMetrics($email);
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $metrics]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    public function getPosts()
    {
        header('Content-Type: application/json');
        
        Security::checkAuth();
        $user = Security::getAuthUser();
        $email = $user['email'];
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

        try {
            $posts = $this->service->getRecentPosts($email, $limit);
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $posts]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    public function getMetricsHistory()
    {
        header('Content-Type: application/json');
        
        Security::checkAuth();
        $user = Security::getAuthUser();
        $email = $user['email'];

        try {
            $history = $this->metricsRepo->getHistoryByEmail($email);
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $history]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    public function refreshToken()
    {
        header('Content-Type: application/json');
        
        Security::checkAuth();
        $user = Security::getAuthUser();
        $email = $user['email'];

        try {
            $newToken = $this->service->refreshToken($email);
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $newToken]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    public function disconnectAccount()
    {
        header('Content-Type: application/json');
        
        Security::checkAuth();
        $user = Security::getAuthUser();
        $email = $user['email'];

        try {
            $this->tokenRepo->delete($email);
            http_response_code(200);
            echo json_encode(['success' => true, 'mensagem' => 'Conta desconectada com sucesso']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    public function getConnectionStatus()
    {
        header('Content-Type: application/json');
        
        Security::checkAuth();
        $user = Security::getAuthUser();
        $email = $user['email'];

        try {
            $tokenData = $this->tokenRepo->getByEmail($email);
            $isConnected = $tokenData !== false && !empty($tokenData);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => [
                    'connected' => $isConnected,
                    'username' => $isConnected ? $tokenData['instagram_username'] : null,
                    'user_id' => $isConnected ? $tokenData['instagram_user_id'] : null
                ]
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => $e->getMessage()]);
        }
    }
}
