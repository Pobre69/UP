<?php
namespace Controllers;

use App\Repository\DashboardRepository;
use App\Repository\EngagementRepository;
use App\Middleware\Security;

class DashboardController
{
    private DashboardRepository $dashboardRepo;
    private EngagementRepository $engagementRepo;

    public function __construct()
    {
        $this->dashboardRepo = new DashboardRepository();
        $this->engagementRepo = new EngagementRepository();
    }

    public function getDashboardData()
    {
        header('Content-Type: application/json');

        Security::checkAuth();
        $user = Security::getAuthUser();
        $email = (string)$user['email'];
        $range = strtolower((string)($_GET['range'] ?? 'mensal'));
        $days = match ($range) {
            'diario' => 7,
            'semanal' => 28,
            default => 90,
        };

        try {
            $stats = $this->dashboardRepo->getDashboardStats($email) ?: [
                'followers_count' => 0,
                'profile_views' => 0,
                'reach' => 0,
                'impressions' => 0,
                'engagement_rate' => 0.0,
            ];

            $delta = $this->dashboardRepo->getStatsDelta($email);
            $growthPct = $this->dashboardRepo->getGrowthPercentile($email);
            $followersSeries = $this->dashboardRepo->getFollowersGrowth($email, $days);
            $reachSeries = $this->dashboardRepo->getReachSeries($email, $days);
            $engagement = $this->dashboardRepo->getEngagementSummary($email);
            $bestStory = $this->engagementRepo->getBestStory($email);

            $seriesDates = array_map(function(array $row): string {
                $date = (string)($row['date'] ?? '');
                return $date ? date('d/m', strtotime($date)) : '';
            }, !empty($followersSeries) ? $followersSeries : $reachSeries);

            $response = [
                'success' => true,
                'data' => [
                    'user' => [
                        'handle' => $user['nome'] ?? 'Usuário',
                        'email' => $email,
                    ],
                    'profileGrowthPct' => (float)$growthPct,
                    'stats' => [
                        'seguidores' => [
                            'value' => (int)($stats['followers_count'] ?? 0),
                            'delta' => $this->calculateDelta($delta['current_followers'] ?? 0, $delta['prev_followers'] ?? 0),
                        ],
                        'cliquesPerfil' => [
                            'value' => (int)($stats['profile_views'] ?? 0),
                            'delta' => $this->calculateDelta($delta['current_views'] ?? 0, $delta['prev_views'] ?? 0),
                        ],
                        'alcanceTotal' => [
                            'value' => (int)($stats['reach'] ?? 0),
                            'delta' => $this->calculateDelta($delta['current_reach'] ?? 0, $delta['prev_reach'] ?? 0),
                        ],
                        'impressoes' => [
                            'value' => (int)($stats['impressions'] ?? 0),
                            'delta' => $this->calculateDelta($delta['current_impressions'] ?? 0, $delta['prev_impressions'] ?? 0),
                        ],
                        'engajamento' => [
                            'value' => (float)($stats['engagement_rate'] ?? 0),
                            'delta' => $this->calculateDelta($delta['current_engagement'] ?? 0, $delta['prev_engagement'] ?? 0),
                        ],
                    ],
                    'seguidoresSerie' => array_map('intval', array_column($followersSeries, 'followers_count')),
                    'alcanceSerie' => array_map('intval', array_column($reachSeries, 'reach')),
                    'chartDates' => array_values(array_filter($seriesDates)),
                    'engajamentoResumo' => [
                        'curtidasMedia' => (int)round($engagement['avg_likes'] ?? 0),
                        'comentariosMedios' => (int)round($engagement['avg_comments'] ?? 0),
                        'compartilhamentos' => (int)round($engagement['avg_shares'] ?? 0),
                        'alcanceMedio' => (int)round((($stats['reach'] ?? 0) / max((int)($engagement['total_posts'] ?? 1), 1))),
                        'melhorStory' => (int)$bestStory,
                    ],
                ],
            ];

            http_response_code(200);
            echo json_encode($response);
        } catch (\Throwable $e) {
            error_log('[Dashboard] Erro: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'mensagem' => 'Erro ao carregar dashboard: ' . $e->getMessage(),
            ]);
        }
    }

    private function calculateDelta($current, $previous)
    {
        $current = (float)$current;
        $previous = (float)$previous;

        if ($previous == 0.0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
