<?php

namespace Controllers;

use App\Repository\RequestsRepository;
use App\Middleware\Security;

class RequestsController
{
    private RequestsRepository $requestsRepo;

    public function __construct()
    {
        $this->requestsRepo = new RequestsRepository();
    }

    public function getRequests()
    {
        header('Content-Type: application/json');
        
        Security::checkAuth();
        $user = Security::getAuthUser();
        $email = $user['email'];

        try {
            $requests = $this->requestsRepo->getUserRequests($email);

            $response = [
                'success' => true,
                'data' => $requests
            ];

            http_response_code(200);
            echo json_encode($response);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => 'Erro ao carregar solicitações']);
        }
    }

    public function createRequest()
    {
        header('Content-Type: application/json');
        
        try {
            Security::checkAuth();
            $user = Security::getAuthUser();
            $email = $user['email'];

            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['titulo']) || !isset($data['texto'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'mensagem' => 'Título e texto são obrigatórios']);
                return;
            }

            $tipo = $data['tipo'] ?? 'Feedback';
            
            // Validar tipo
            $tiposValidos = ['Alteração', 'Ideia', 'Feedback'];
            if (!in_array($tipo, $tiposValidos)) {
                $tipo = 'Feedback';
            }

            $result = $this->requestsRepo->createRequest(
                $email,
                $data['titulo'],
                $tipo,
                $data['texto']
            );

            if ($result) {
                http_response_code(201);
                echo json_encode(['success' => true, 'mensagem' => 'Solicitação enviada com sucesso']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'mensagem' => 'Erro ao salvar solicitação']);
            }
        } catch (\Exception $e) {
            error_log('Erro em createRequest: ' . 'Erro ao carregar solicitações');
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => 'Erro ao processar solicitação']);
        }
    }
}
