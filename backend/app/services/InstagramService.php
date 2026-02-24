<?php

namespace App\Services;

use Facebook\Facebook;
use Facebook\Exceptions\FacebookSDKException;
use App\Repository\InstagramTokenRepository;
use App\Repository\InstagramMetricsRepository;
use App\Repository\InstagramPostRepository;

class InstagramService
{
    private Facebook $fb;
    private InstagramTokenRepository $tokenRepo;
    private InstagramMetricsRepository $metricsRepo;
    private InstagramPostRepository $postRepo;
    private string $appId;
    private string $appSecret;
    private string $storagePath;

    public function __construct()
    {
        $this->appId = $_ENV['INSTAGRAM_APP_ID'] ?? '';
        $this->appSecret = $_ENV['INSTAGRAM_APP_SECRET'] ?? '';
        $this->storagePath = __DIR__ . '/../../storage/instagram/';
        
        $this->fb = new Facebook([
            'app_id' => $this->appId,
            'app_secret' => $this->appSecret,
            'default_graph_version' => 'v18.0',
        ]);

        $this->tokenRepo = new InstagramTokenRepository();
        $this->metricsRepo = new InstagramMetricsRepository();
        $this->postRepo = new InstagramPostRepository();
    }

    private function saveToStorage(string $filename, array $data)
    {
        $filepath = $this->storagePath . $filename;
        file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function loadFromStorage(string $filename)
    {
        $filepath = $this->storagePath . $filename;
        if (file_exists($filepath)) {
            return json_decode(file_get_contents($filepath), true);
        }
        return null;
    }

    public function saveToken(string $email, string $accessToken)
    {
        try {
            $response = $this->fb->get('/me?fields=id,username', $accessToken);
            $user = $response->getGraphUser();
            
            $expiresAt = date('Y-m-d H:i:s', strtotime('+60 days'));
            
            $tokenData = [
                'email' => $email,
                'access_token' => $accessToken,
                'expires_at' => $expiresAt,
                'instagram_user_id' => $user['id'],
                'instagram_username' => $user['username']
            ];
            
            $this->saveToStorage($email . '_token.json', $tokenData);
            
            return $this->tokenRepo->save(
                $email,
                $accessToken,
                $expiresAt,
                $user['id'],
                $user['username']
            );
        } catch (FacebookSDKException $e) {
            throw new \Exception('Erro ao salvar token: ' . $e->getMessage());
        }
    }

    public function getAccountMetrics(string $email)
    {
        $tokenData = $this->tokenRepo->getByEmail($email);
        if (!$tokenData) {
            throw new \Exception('Token não encontrado');
        }

        try {
            // Buscar dados básicos da conta
            $response = $this->fb->get(
                '/' . $tokenData['instagram_user_id'] . '?fields=followers_count,follows_count,media_count',
                $tokenData['access_token']
            );
            
            $data = $response->getDecodedBody();
            
            // Buscar insights da conta (requer permissões especiais)
            $profileViews = 0;
            $reach = 0;
            $impressions = 0;
            
            try {
                $insightsResponse = $this->fb->get(
                    '/' . $tokenData['instagram_user_id'] . '/insights?metric=profile_views,reach,impressions&period=day',
                    $tokenData['access_token']
                );
                $insights = $insightsResponse->getDecodedBody()['data'] ?? [];
                
                foreach ($insights as $insight) {
                    if ($insight['name'] === 'profile_views') {
                        $profileViews = end($insight['values'])['value'] ?? 0;
                    } elseif ($insight['name'] === 'reach') {
                        $reach = end($insight['values'])['value'] ?? 0;
                    } elseif ($insight['name'] === 'impressions') {
                        $impressions = end($insight['values'])['value'] ?? 0;
                    }
                }
            } catch (\Exception $e) {
                error_log('Erro ao buscar insights: ' . $e->getMessage());
            }
            
            // Calcular taxa de engajamento
            $posts = $this->postRepo->getByEmail($email, 10);
            $totalEngagement = 0;
            $totalFollowers = $data['followers_count'] ?? 1;
            
            foreach ($posts as $post) {
                $totalEngagement += ($post['like_count'] + $post['comments_count']);
            }
            
            $engagementRate = count($posts) > 0 ? ($totalEngagement / (count($posts) * $totalFollowers)) * 100 : 0;
            
            $this->metricsRepo->save(
                $email,
                $data['followers_count'] ?? 0,
                $data['follows_count'] ?? 0,
                $data['media_count'] ?? 0,
                round($engagementRate, 2),
                $profileViews,
                $reach,
                $impressions
            );

            $this->saveToStorage($email . '_metrics_' . date('Y-m-d') . '.json', array_merge($data, [
                'profile_views' => $profileViews,
                'reach' => $reach,
                'impressions' => $impressions,
                'engagement_rate' => $engagementRate
            ]));

            return array_merge($data, [
                'profile_views' => $profileViews,
                'reach' => $reach,
                'impressions' => $impressions,
                'engagement_rate' => $engagementRate
            ]);
        } catch (FacebookSDKException $e) {
            throw new \Exception('Erro ao buscar métricas: ' . $e->getMessage());
        }
    }

    public function getRecentPosts(string $email, int $limit = 20)
    {
        $tokenData = $this->tokenRepo->getByEmail($email);
        if (!$tokenData) {
            throw new \Exception('Token não encontrado');
        }

        try {
            $response = $this->fb->get(
                '/' . $tokenData['instagram_user_id'] . '/media?fields=id,caption,media_type,media_url,permalink,timestamp,like_count,comments_count&limit=' . $limit,
                $tokenData['access_token']
            );
            
            $posts = $response->getDecodedBody()['data'] ?? [];
            
            foreach ($posts as $post) {
                // Buscar insights do post
                $sharesCount = 0;
                $savedCount = 0;
                $reach = 0;
                $impressions = 0;
                
                try {
                    $insightsResponse = $this->fb->get(
                        '/' . $post['id'] . '/insights?metric=shares,saved,reach,impressions',
                        $tokenData['access_token']
                    );
                    $insights = $insightsResponse->getDecodedBody()['data'] ?? [];
                    
                    foreach ($insights as $insight) {
                        if ($insight['name'] === 'shares') {
                            $sharesCount = $insight['values'][0]['value'] ?? 0;
                        } elseif ($insight['name'] === 'saved') {
                            $savedCount = $insight['values'][0]['value'] ?? 0;
                        } elseif ($insight['name'] === 'reach') {
                            $reach = $insight['values'][0]['value'] ?? 0;
                        } elseif ($insight['name'] === 'impressions') {
                            $impressions = $insight['values'][0]['value'] ?? 0;
                        }
                    }
                } catch (\Exception $e) {
                    error_log('Erro ao buscar insights do post: ' . $e->getMessage());
                }
                
                $this->postRepo->save(
                    $post['id'],
                    $email,
                    $post['caption'] ?? null,
                    $post['media_type'],
                    $post['media_url'] ?? null,
                    $post['permalink'] ?? null,
                    $post['timestamp'] ?? null,
                    $post['like_count'] ?? 0,
                    $post['comments_count'] ?? 0,
                    $sharesCount,
                    $savedCount
                );
                
                // Salvar insights separadamente
                if ($reach > 0 || $impressions > 0) {
                    $this->savePostInsights($post['id'], $reach, $impressions, $savedCount);
                }
            }

            $this->saveToStorage($email . '_posts_' . date('Y-m-d') . '.json', $posts);

            return $posts;
        } catch (FacebookSDKException $e) {
            throw new \Exception('Erro ao buscar posts: ' . $e->getMessage());
        }
    }

    private function savePostInsights(string $postId, int $reach, int $impressions, int $saved)
    {
        try {
            $stmt = $this->postRepo->getConnection()->prepare(
                'INSERT INTO instagram_post_insights (post_id, reach, impressions, saved) 
                 VALUES (:post_id, :reach, :impressions, :saved)'
            );
            $stmt->execute([
                ':post_id' => $postId,
                ':reach' => $reach,
                ':impressions' => $impressions,
                ':saved' => $saved
            ]);
        } catch (\Exception $e) {
            error_log('Erro ao salvar insights: ' . $e->getMessage());
        }
    }

    public function refreshToken(string $email)
    {
        $tokenData = $this->tokenRepo->getByEmail($email);
        if (!$tokenData) {
            throw new \Exception('Token não encontrado');
        }

        try {
            $response = $this->fb->get(
                '/oauth/access_token?grant_type=ig_refresh_token&access_token=' . $tokenData['access_token']
            );
            
            $newToken = $response->getDecodedBody();
            $expiresAt = date('Y-m-d H:i:s', strtotime('+60 days'));
            
            $this->tokenRepo->save(
                $email,
                $newToken['access_token'],
                $expiresAt,
                $tokenData['instagram_user_id'],
                $tokenData['instagram_username']
            );

            return $newToken;
        } catch (FacebookSDKException $e) {
            throw new \Exception('Erro ao renovar token: ' . $e->getMessage());
        }
    }
}
