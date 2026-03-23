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
            
            // Se não houver dados, retornar estrutura vazia com valores padrão
            if (!$stats) {
                $stats = [
                    'followers_count' => 0,
                    'profile_views' => 0,
                    'reach' => 0,
                    'impressions' => 0,
                    'engagement_rate' => 0.0
                ];
            }
            
            // Validar tipos de dados
            $stats['followers_count'] = (int)($stats['followers_count'] ?? 0);
            $stats['profile_views'] = (int)($stats['profile_views'] ?? 0);
            $stats['reach'] = (int)($stats['reach'] ?? 0);
            $stats['impressions'] = (int)($stats['impressions'] ?? 0);
            $stats['engagement_rate'] = (float)($stats['engagement_rate'] ?? 0.0);
            
            $delta = $this->dashboardRepo->getStatsDelta($email);
            $growthPct = $this->dashboardRepo->getGrowthPercentile($email);
            $followersSeries = $this->dashboardRepo->getFollowersGrowth($email, 30);
            $reachSeries = $this->dashboardRepo->getReachSeries($email, 30);
            $engagement = $this->dashboardRepo->getEngagementSummary($email);
            
            // Validar dados de série
            if (!is_array($followersSeries)) {
                $followersSeries = [];
            }
            if (!is_array($reachSeries)) {
                $reachSeries = [];
            }
            
            // Gerar datas para os gráficos
            $chartDates = $this->generateChartDates(count($followersSeries));
            
            $response = [
                'success' => true,
                'data' => [
                    'user' => [
                        'handle' => $user['nome'] ?? 'Usuário',
                        'email' => $email
                    ],
                    'profileGrowthPct' => (float)$growthPct,
                    'stats' => [
                        'seguidores' => [
                            'value' => $stats['followers_count'],
                            'delta' => $this->calculateDelta(
                                $delta['current_followers'] ?? 0,
                                $delta['prev_followers'] ?? 0
                            )
                        ],
                        'cliquesPerfil' => [
                            'value' => $stats['profile_views'],
                            'delta' => $this->calculateDelta(
                                $delta['current_views'] ?? 0,
                                $delta['prev_views'] ?? 0
                            )
                        ],
                        'alcanceTotal' => [
                            'value' => $stats['reach'],
                            'delta' => $this->calculateDelta(
                                $delta['current_reach'] ?? 0,
                                $delta['prev_reach'] ?? 0
                            )
                        ],
                        'impressoes' => [
                            'value' => $stats['impressions'],
                            'delta' => $this->calculateDelta(
                                $delta['current_impressions'] ?? 0,
                                $delta['prev_impressions'] ?? 0
                            )
                        ],
                        'engajamento' => [
                            'value' => (float)$stats['engagement_rate'],
                            'delta' => $this->calculateDelta(
                                $delta['current_engagement'] ?? 0,
                                $delta['prev_engagement'] ?? 0
                            )
                        ]
                    ],
                    'seguidoresSerie' => array_map('intval', array_column($followersSeries, 'followers_count')),
                    'alcanceSerie' => array_map('intval', array_column($reachSeries, 'reach')),
                    'chartDates' => $chartDates,
                    'engajamentoResumo' => [
                        'curtidasMedia' => (int)round($engagement['avg_likes'] ?? 0),
                        'comentariosMedios' => (int)round($engagement['avg_comments'] ?? 0),
                        'compartilhamentos' => (int)round($engagement['avg_shares'] ?? 0),
                        'alcanceMedio' => (int)round(
                            ($stats['reach'] ?? 0) / max($engagement['total_posts'] ?? 1, 1)
                        ),
                        'melhorStory' => 0
                    ]
                ]
            ];
            
            http_response_code(200);
            echo json_encode($response);
        } catch (\Exception $e) {
            error_log('[Dashboard] Erro: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'mensagem' => 'Erro ao carregar dashboard: ' . $e->getMessage()
            ]);
        }
    }
    
    private function generateChartDates(int $count)
    {
        $dates = [];
        for ($i = $count - 1; $i >= 0; $i--) {
            $dates[] = date('d/m', strtotime("-$i days"));
        }
        return $dates;
    }
    
    private function calculateDelta($current, $previous)
    {
        $current = (float)$current;
        $previous = (float)$previous;
        
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return round((($current - $previous) / $previous) * 100, 1);
    }
}
