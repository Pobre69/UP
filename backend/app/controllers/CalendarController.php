<?php

namespace Controllers;

use App\Middleware\Security;
use App\Repository\CalendarRepository;

class CalendarController
{
    private CalendarRepository $calendarRepo;

    public function __construct()
    {
        $this->calendarRepo = new CalendarRepository();
    }

    public function getCalendarData(): void
    {
        header('Content-Type: application/json');

        Security::checkAuth();
        $email = (string) Security::getAuthUser()['email'];

        $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
        $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');

        if ($month < 1 || $month > 12) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Mês inválido']);
            return;
        }

        try {
            $contentByDate = $this->calendarRepo->getAllContentByMonth($email, $year, $month);
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => [
                    'year' => $year,
                    'month' => $month,
                    'content' => $contentByDate,
                ]
            ]);
        } catch (\Throwable $e) {
            error_log('[Calendar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => 'Erro ao carregar calendário']);
        }
    }

    public function schedulePost(): void
    {
        header('Content-Type: application/json');

        Security::checkAuth();
        $email = (string) Security::getAuthUser()['email'];
        $data = json_decode(file_get_contents('php://input'), true);

        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Dados inválidos']);
            return;
        }

        $contentType = strtoupper(trim((string)($data['content_type'] ?? '')));
        $scheduledDate = trim((string)($data['scheduled_date'] ?? ''));
        $caption = isset($data['caption']) ? trim((string)$data['caption']) : null;
        $mediaUrl = isset($data['media_url']) ? trim((string)$data['media_url']) : null;

        if ($contentType === '' || $scheduledDate === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Dados obrigatórios faltando']);
            return;
        }

        if (!in_array($contentType, ['POST', 'STORY', 'REEL', 'CAROUSEL'], true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Tipo de conteúdo inválido']);
            return;
        }

        if (strtotime($scheduledDate) === false) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Data de agendamento inválida']);
            return;
        }

        try {
            $this->calendarRepo->schedulePost($email, $contentType, $caption ?: null, $mediaUrl ?: null, $scheduledDate);
            http_response_code(201);
            echo json_encode(['success' => true, 'mensagem' => 'Post agendado com sucesso']);
        } catch (\Throwable $e) {
            error_log('[Calendar Schedule] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => 'Erro ao agendar post']);
        }
    }
}
