<?php

namespace Controllers;

use App\Services\InstagramService;
use App\Repository\InstagramMetricsRepository;
use App\Repository\InstagramPostRepository;

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
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['email']) || !isset($data['access_token'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Email e access_token são obrigatórios']);
            return;
        }

        try {
            $this->service->saveToken($data['email'], $data['access_token']);
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
        
        $email = $_GET['email'] ?? null;
        
        if (!$email) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Email é obrigatório']);
            return;
        }

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
        
        $email = $_GET['email'] ?? null;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        
        if (!$email) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Email é obrigatório']);
            return;
        }

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
        
        $email = $_GET['email'] ?? null;
        
        if (!$email) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Email é obrigatório']);
            return;
        }

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
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['email'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Email é obrigatório']);
            return;
        }

        try {
            $newToken = $this->service->refreshToken($data['email']);
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $newToken]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => $e->getMessage()]);
        }
    }
}
