<?php

namespace Controllers;

use App\Repository\EngagementRepository;
use App\Middleware\Security;

class EngagementController
{
    private EngagementRepository $engagementRepo;

    public function __construct()
    {
        $this->engagementRepo = new EngagementRepository();
    }

    public function getEngagementData()
    {
        header('Content-Type: application/json');
        
        Security::checkAuth();
        $user = Security::getAuthUser();
        $email = $user['email'];

        try {
            $stats = $this->engagementRepo->getEngagementStats($email);
            $avgReach = $this->engagementRepo->getAverageReach($email);
            $bestStory = $this->engagementRepo->getBestStory($email);
            $engagementRate = $this->engagementRepo->getEngagementRate($email);
            $postsPerformance = $this->engagementRepo->getPostsPerformance($email, 20);
            $distribution = $this->engagementRepo->getEngagementDistribution($email);

            $totalEngagement = ($distribution['total_likes'] ?? 0) + 
                              ($distribution['total_comments'] ?? 0) + 
                              ($distribution['total_shares'] ?? 0);

            $response = [
                'success' => true,
                'data' => [
                    'stats' => [
                        'avgLikes' => round($stats['avg_likes'] ?? 0),
                        'avgComments' => round($stats['avg_comments'] ?? 0),
                        'avgShares' => round($stats['avg_shares'] ?? 0),
                        'avgReach' => $avgReach,
                        'engagementRate' => round($engagementRate, 2),
                        'bestStoryViews' => $bestStory
                    ],
                    'postsPerformance' => $postsPerformance,
                    'distribution' => [
                        [
                            'label' => 'Curtidas',
                            'value' => $distribution['total_likes'] ?? 0,
                            'percent' => $totalEngagement > 0 ? round((($distribution['total_likes'] ?? 0) / $totalEngagement) * 100, 1) : 0
                        ],
                        [
                            'label' => 'Comentários',
                            'value' => $distribution['total_comments'] ?? 0,
                            'percent' => $totalEngagement > 0 ? round((($distribution['total_comments'] ?? 0) / $totalEngagement) * 100, 1) : 0
                        ],
                        [
                            'label' => 'Compartilhamentos',
                            'value' => $distribution['total_shares'] ?? 0,
                            'percent' => $totalEngagement > 0 ? round((($distribution['total_shares'] ?? 0) / $totalEngagement) * 100, 1) : 0
                        ]
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
}
