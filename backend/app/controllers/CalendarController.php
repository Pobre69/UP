<?php

namespace Controllers;

use App\Repository\CalendarRepository;
use App\Middleware\Security;

class CalendarController
{
    private CalendarRepository $calendarRepo;

    public function __construct()
    {
        $this->calendarRepo = new CalendarRepository();
    }

    public function getCalendarData()
    {
        header('Content-Type: application/json');
        
        Security::checkAuth();
        $user = Security::getAuthUser();
        $email = $user['email'];

        $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
        $month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');

        try {
            $contentByDate = $this->calendarRepo->getAllContentByMonth($email, $year, $month);

            $response = [
                'success' => true,
                'data' => [
                    'year' => $year,
                    'month' => $month,
                    'content' => $contentByDate
                ]
            ];

            http_response_code(200);
            echo json_encode($response);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    public function schedulePost()
    {
        header('Content-Type: application/json');
        
        Security::checkAuth();
        $user = Security::getAuthUser();
        $email = $user['email'];

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['content_type']) || !isset($data['scheduled_date'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Dados obrigatórios faltando']);
            return;
        }

        try {
            $stmt = $this->calendarRepo->getConnection()->prepare(
                'INSERT INTO scheduled_posts (email, content_type, caption, media_url, scheduled_date) 
                 VALUES (:email, :content_type, :caption, :media_url, :scheduled_date)'
            );
            
            $stmt->execute([
                ':email' => $email,
                ':content_type' => $data['content_type'],
                ':caption' => $data['caption'] ?? null,
                ':media_url' => $data['media_url'] ?? null,
                ':scheduled_date' => $data['scheduled_date']
            ]);

            http_response_code(201);
            echo json_encode(['success' => true, 'mensagem' => 'Post agendado com sucesso']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => $e->getMessage()]);
        }
    }
}
