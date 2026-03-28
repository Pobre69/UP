<?php

namespace Controllers;

use App\Repository\ReportsRepository;
use App\Middleware\Security;

class ReportsController
{
    private ReportsRepository $reportsRepo;

    public function __construct()
    {
        $this->reportsRepo = new ReportsRepository();
    }

    public function getReportsData()
    {
        header('Content-Type: application/json');
        
        Security::checkAuth();
        $user = Security::getAuthUser();
        $email = $user['email'];

        $days = isset($_GET['days']) ? (int)$_GET['days'] : 30;

        try {
            $followersEvolution = $this->reportsRepo->getFollowersEvolution($email, $days);
            $reachEvolution = $this->reportsRepo->getReachEvolution($email, $days);
            $last7Days = $this->reportsRepo->getLast7DaysSummary($email);
            $summary = $this->reportsRepo->getOverallSummary($email);

            $response = [
                'success' => true,
                'data' => [
                    'evolution' => [
                        'followers' => $followersEvolution,
                        'reach' => $reachEvolution
                    ],
                    'last7Days' => $last7Days,
                    'summary' => [
                        'currentFollowers' => $summary['current_followers'] ?? 0,
                        'initialFollowers' => $summary['initial_followers'] ?? 0,
                        'growthPercentage' => $summary['growth_percentage'] ?? 0,
                        'avgReach' => round($summary['avg_reach'] ?? 0),
                        'avgEngagement' => round($summary['avg_engagement'] ?? 0, 2),
                        'totalPosts' => $summary['total_posts'] ?? 0
                    ]
                ]
            ];

            http_response_code(200);
            echo json_encode($response);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => 'Erro ao gerar relatório']);
        }
    }

    public function exportReport()
    {
        Security::checkAuth();
        $user = Security::getAuthUser();
        $email = $user['email'];

        try {
            $followersEvolution = $this->reportsRepo->getFollowersEvolution($email, 30);
            $summary = $this->reportsRepo->getOverallSummary($email);

            // Gerar CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=relatorio_' . date('Y-m-d') . '.csv');

            $output = fopen('php://output', 'w');
            
            // Cabeçalho
            fputcsv($output, ['Relatório de Performance - ' . $user['nome']]);
            fputcsv($output, ['Gerado em: ' . date('d/m/Y H:i:s')]);
            fputcsv($output, []);
            
            // Resumo
            fputcsv($output, ['Resumo Geral']);
            fputcsv($output, ['Seguidores Atuais', $summary['current_followers'] ?? 0]);
            fputcsv($output, ['Crescimento', ($summary['growth_percentage'] ?? 0) . '%']);
            fputcsv($output, ['Alcance Médio', round($summary['avg_reach'] ?? 0)]);
            fputcsv($output, ['Engajamento Médio', ($summary['avg_engagement'] ?? 0) . '%']);
            fputcsv($output, []);
            
            // Evolução
            fputcsv($output, ['Data', 'Seguidores']);
            foreach ($followersEvolution as $row) {
                fputcsv($output, [$row['date'], $row['followers_count']]);
            }

            fclose($output);
            exit;
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => 'Erro ao gerar relatório']);
        }
    }
}
