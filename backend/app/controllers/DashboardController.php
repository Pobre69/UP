<?php

namespace Controllers;

use App\Repository\DashboardRepository;
use App\Repository\InstagramMetricsRepository;
use App\Middleware\Security;

class DashboardController
{
    private DashboardRepository $dashboardRepo;
    private InstagramMetricsRepository $metricsRepo;

    public function __construct()
    {
        $this->dashboardRepo = new DashboardRepository();
        $this->metricsRepo = new InstagramMetricsRepository();
    }

    public function getDashboardData()
    {
        header('Content-Type: application/json');
        
        Security::checkAuth();
        $user = Security::getAuthUser();
        $email = $user['email'];

        try {
            $stats = $this->dashboardRepo->getDashboardStats($email);
            $delta = $this->dashboardRepo->getStatsDelta($email);
            $growthPct = $this->dashboardRepo->getGrowthPercentile($email);
            $followersSeries = $this->dashboardRepo->getFollowersGrowth($email, 30);
            $reachSeries = $this->dashboardRepo->getReachSeries($email, 30);
            $engagement = $this->dashboardRepo->getEngagementSummary($email);

            $response = [
                'success' => true,
                'data' => [
                    'user' => [
                        'handle' => $user['nome'] ?? 'Usuário',
                        'email' => $email
                    ],
                    'profileGrowthPct' => $growthPct,
                    'stats' => [
                        'seguidores' => [
                            'value' => $stats['followers_count'] ?? 0,
                            'delta' => $this->calculateDelta($delta['current_followers'] ?? 0, $delta['prev_followers'] ?? 0)
                        ],
                        'cliquesPerfil' => [
                            'value' => $stats['profile_views'] ?? 0,
                            'delta' => $this->calculateDelta($delta['current_views'] ?? 0, $delta['prev_views'] ?? 0)
                        ],
                        'alcanceTotal' => [
                            'value' => $stats['reach'] ?? 0,
                            'delta' => $this->calculateDelta($delta['current_reach'] ?? 0, $delta['prev_reach'] ?? 0)
                        ],
                        'impressoes' => [
                            'value' => $stats['impressions'] ?? 0,
                            'delta' => $this->calculateDelta($delta['current_impressions'] ?? 0, $delta['prev_impressions'] ?? 0)
                        ],
                        'engajamento' => [
                            'value' => $stats['engagement_rate'] ?? 0,
                            'delta' => $this->calculateDelta($delta['current_engagement'] ?? 0, $delta['prev_engagement'] ?? 0)
                        ]
                    ],
                    'seguidoresSerie' => array_column($followersSeries, 'followers_count'),
                    'alcanceSerie' => array_column($reachSeries, 'reach'),
                    'engajamentoResumo' => [
                        'curtidasMedia' => round($engagement['avg_likes'] ?? 0),
                        'comentariosMedios' => round($engagement['avg_comments'] ?? 0),
                        'compartilhamentos' => 0,
                        'alcanceMedio' => round(($stats['reach'] ?? 0) / max($engagement['total_posts'] ?? 1, 1)),
                        'melhorStory' => 0
                    ]
                ]
            ];

            http_response_code(200);
            echo json_encode($response);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    private function calculateDelta($current, $previous)
    {
        if ($previous == 0) return 0;
        return round((($current - $previous) / $previous) * 100, 1);
    }
}
