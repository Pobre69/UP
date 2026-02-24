<?php

namespace Controllers;

use App\Repository\ServiceStatusRepository;
use App\Middleware\Security;

class ServiceStatusController
{
    private ServiceStatusRepository $statusRepo;

    public function __construct()
    {
        $this->statusRepo = new ServiceStatusRepository();
    }

    public function getServiceStatus()
    {
        header('Content-Type: application/json');
        
        Security::checkAuth();
        $user = Security::getAuthUser();
        $email = $user['email'];

        try {
            $summary = $this->statusRepo->getStatusSummary($email);
            $content = $this->statusRepo->getAllContent($email);

            $response = [
                'success' => true,
                'data' => [
                    'summary' => [
                        'review' => $summary['review'],
                        'planned' => $summary['planned'],
                        'scheduled' => $summary['scheduled'],
                        'published' => $summary['published']
                    ],
                    'content' => $content
                ]
            ];

            http_response_code(200);
            echo json_encode($response);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    public function createContent()
    {
        header('Content-Type: application/json');
        
        Security::checkAuth();
        $user = Security::getAuthUser();
        $email = $user['email'];

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['title']) || !isset($data['content_type'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Título e tipo de conteúdo são obrigatórios']);
            return;
        }

        try {
            $this->statusRepo->createContent(
                $email,
                $data['title'],
                $data['content_type'],
                $data['description'] ?? null,
                $data['status'] ?? 'planned'
            );

            http_response_code(201);
            echo json_encode(['success' => true, 'mensagem' => 'Conteúdo criado com sucesso']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    public function updateContentStatus()
    {
        header('Content-Type: application/json');
        
        Security::checkAuth();
        $user = Security::getAuthUser();
        $email = $user['email'];

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id']) || !isset($data['status'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'ID e status são obrigatórios']);
            return;
        }

        // Validar status
        $statusValidos = ['review', 'planned', 'scheduled', 'published'];
        if (!in_array($data['status'], $statusValidos)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Status inválido']);
            return;
        }

        try {
            $this->statusRepo->updateContentStatus(
                $data['id'],
                $email,
                $data['status'],
                $data['scheduled_date'] ?? null,
                $data['published_date'] ?? null
            );

            http_response_code(200);
            echo json_encode(['success' => true, 'mensagem' => 'Status atualizado com sucesso']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => $e->getMessage()]);
        }
    }
}
