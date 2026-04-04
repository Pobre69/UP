<?php

namespace App\Services;

use App\Config\StorageConfig;
use App\Repository\InstagramMetricsRepository;
use App\Repository\InstagramPostRepository;
use App\Repository\InstagramTokenRepository;

class InstagramService
{
    private InstagramTokenRepository $tokenRepo;
    private InstagramMetricsRepository $metricsRepo;
    private InstagramPostRepository $postRepo;
    private string $storagePath;

    public function __construct()
    {
        $this->storagePath = StorageConfig::getInstagramPath();
        StorageConfig::ensureDirectoriesExist();

        $this->tokenRepo = new InstagramTokenRepository();
        $this->metricsRepo = new InstagramMetricsRepository();
        $this->postRepo = new InstagramPostRepository();
    }

    private function saveToStorage(string $filename, array $data): void
    {
        file_put_contents($this->storagePath . $filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function requestJson(string $url): array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 20,
                'ignore_errors' => true,
                'header' => "Accept: application/json\r\n",
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            throw new \Exception('Não foi possível conectar ao Instagram.');
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            throw new \Exception('Resposta inválida do Instagram.');
        }

        if (isset($data['error'])) {
            $this->handleApiError($data['error']);
        }

        return $data;
    }

    private function requestJsonOrNull(string $url): ?array
    {
        try {
            return $this->requestJson($url);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function buildUrl(string $base, array $query): string
    {
        return $base . (str_contains($base, '?') ? '&' : '?') . http_build_query($query);
    }

    public function saveToken(string $email, string $accessToken): bool
    {
        $user = $this->requestJson('https://graph.instagram.com/me?fields=id,username,account_type,media_count&access_token=' . urlencode($accessToken));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+60 days'));

        $tokenData = [
            'email' => $email,
            'access_token' => $accessToken,
            'expires_at' => $expiresAt,
            'instagram_user_id' => $user['id'] ?? null,
            'instagram_username' => $user['username'] ?? 'unknown',
        ];

        $this->saveToStorage($email . '_token.json', $tokenData);

        return $this->tokenRepo->save(
            $email,
            $accessToken,
            $expiresAt,
            (string)($user['id'] ?? ''),
            (string)($user['username'] ?? 'unknown')
        );
    }

    private function getProfilePayload(array $tokenData): array
    {
        $accessToken = (string)$tokenData['access_token'];
        $instagramUserId = (string)$tokenData['instagram_user_id'];

        $profile = $this->requestJsonOrNull($this->buildUrl(
            'https://graph.facebook.com/v23.0/' . rawurlencode($instagramUserId),
            [
                'fields' => 'id,username,account_type,media_count,followers_count,follows_count',
                'access_token' => $accessToken,
            ]
        ));

        if (is_array($profile) && !empty($profile)) {
            return $profile;
        }

        return $this->requestJson($this->buildUrl(
            'https://graph.instagram.com/me',
            [
                'fields' => 'id,username,account_type,media_count',
                'access_token' => $accessToken,
            ]
        ));
    }

    public function getAccountMetrics(string $email): array
    {
        $tokenData = $this->tokenRepo->getByEmail($email);
        if (!$tokenData || empty($tokenData['access_token']) || empty($tokenData['instagram_user_id'])) {
            throw new \Exception('Instagram não conectado.\n\nConecte sua conta do Instagram para visualizar as métricas.');
        }

        $latestMetrics = $this->metricsRepo->getLatestByEmail($email) ?: [];
        $profile = $this->getProfilePayload($tokenData);
        $recentPosts = $this->getRecentPosts($email, 12, false);

        $followersCount = isset($profile['followers_count']) ? (int)$profile['followers_count'] : (int)($latestMetrics['followers_count'] ?? 0);
        $followsCount = isset($profile['follows_count']) ? (int)$profile['follows_count'] : (int)($latestMetrics['following_count'] ?? 0);
        $mediaCount = (int)($profile['media_count'] ?? 0);
        $profileViews = (int)($latestMetrics['profile_views'] ?? 0);
        $reach = (int)($latestMetrics['reach'] ?? 0);
        $impressions = (int)($latestMetrics['impressions'] ?? 0);

        $engagementTotal = 0;
        foreach ($recentPosts as $post) {
            $engagementTotal += (int)($post['like_count'] ?? 0);
            $engagementTotal += (int)($post['comments_count'] ?? 0);
            $engagementTotal += (int)($post['shares_count'] ?? 0);
            $engagementTotal += (int)($post['saved_count'] ?? 0);
        }
        $engagementRate = $followersCount > 0 && count($recentPosts) > 0
            ? round(($engagementTotal / max(count($recentPosts), 1)) / $followersCount * 100, 2)
            : (float)($latestMetrics['engagement_rate'] ?? 0);

        $metrics = [
            'id' => $profile['id'] ?? null,
            'username' => $profile['username'] ?? null,
            'account_type' => $profile['account_type'] ?? 'PERSONAL',
            'media_count' => $mediaCount,
            'followers_count' => $followersCount,
            'follows_count' => $followsCount,
            'profile_views' => $profileViews,
            'reach' => $reach,
            'impressions' => $impressions,
            'engagement_rate' => $engagementRate,
        ];

        $this->metricsRepo->save(
            $email,
            $metrics['followers_count'],
            $metrics['follows_count'],
            $metrics['media_count'],
            $metrics['engagement_rate'],
            $metrics['profile_views'],
            $metrics['reach'],
            $metrics['impressions']
        );

        $this->saveToStorage($email . '_metrics_' . date('Y-m-d') . '.json', $metrics);
        return $metrics;
    }

    public function getRecentPosts(string $email, int $limit = 20, bool $persist = true): array
    {
        $tokenData = $this->tokenRepo->getByEmail($email);
        if (!$tokenData || empty($tokenData['access_token']) || empty($tokenData['instagram_user_id'])) {
            throw new \Exception('Instagram não conectado.\n\nConecte sua conta do Instagram para visualizar seus posts.');
        }

        $accessToken = (string)$tokenData['access_token'];
        $instagramUserId = (string)$tokenData['instagram_user_id'];

        $result = $this->requestJsonOrNull($this->buildUrl(
            'https://graph.facebook.com/v23.0/' . rawurlencode($instagramUserId) . '/media',
            [
                'fields' => 'id,caption,media_type,media_url,permalink,timestamp,thumbnail_url,like_count,comments_count',
                'limit' => max(1, $limit),
                'access_token' => $accessToken,
            ]
        ));

        if (!$result || !isset($result['data'])) {
            $result = $this->requestJson(
                'https://graph.instagram.com/' . urlencode($instagramUserId) .
                '/media?fields=id,caption,media_type,media_url,permalink,timestamp&limit=' . max(1, $limit) .
                '&access_token=' . urlencode($accessToken)
            );
        }

        $posts = array_slice($result['data'] ?? [], 0, max(1, $limit));
        $normalized = [];

        foreach ($posts as $post) {
            if (!isset($post['id'], $post['media_type'])) {
                continue;
            }

            $postId = (string)$post['id'];
            $likeCount = (int)($post['like_count'] ?? 0);
            $commentsCount = (int)($post['comments_count'] ?? 0);
            $sharesCount = 0;
            $savedCount = 0;
            $reach = 0;
            $impressions = 0;

            $insights = $this->requestJsonOrNull($this->buildUrl(
                'https://graph.facebook.com/v23.0/' . rawurlencode($postId) . '/insights',
                [
                    'metric' => 'reach,impressions,saved,shares',
                    'access_token' => $accessToken,
                ]
            ));

            if (is_array($insights['data'] ?? null)) {
                foreach ($insights['data'] as $metric) {
                    $name = (string)($metric['name'] ?? '');
                    $value = (int)($metric['values'][0]['value'] ?? $metric['value'] ?? 0);
                    if ($name === 'reach') {
                        $reach = $value;
                    } elseif ($name === 'impressions') {
                        $impressions = $value;
                    } elseif ($name === 'saved') {
                        $savedCount = $value;
                    } elseif ($name === 'shares') {
                        $sharesCount = $value;
                    }
                }
            }

            if ($persist) {
                $this->postRepo->save(
                    $postId,
                    $email,
                    isset($post['caption']) ? (string)$post['caption'] : null,
                    (string)$post['media_type'],
                    isset($post['media_url']) ? (string)$post['media_url'] : (isset($post['thumbnail_url']) ? (string)$post['thumbnail_url'] : null),
                    isset($post['permalink']) ? (string)$post['permalink'] : null,
                    isset($post['timestamp']) ? date('Y-m-d H:i:s', strtotime((string)$post['timestamp'])) : null,
                    $likeCount,
                    $commentsCount,
                    $sharesCount,
                    $savedCount
                );

                $this->postRepo->saveInsights($postId, $reach, $impressions, $savedCount);
            }

            $normalized[] = [
                'id' => $postId,
                'caption' => $post['caption'] ?? null,
                'media_type' => $post['media_type'] ?? null,
                'media_url' => $post['media_url'] ?? ($post['thumbnail_url'] ?? null),
                'permalink' => $post['permalink'] ?? null,
                'timestamp' => $post['timestamp'] ?? null,
                'like_count' => $likeCount,
                'comments_count' => $commentsCount,
                'shares_count' => $sharesCount,
                'saved_count' => $savedCount,
                'reach' => $reach,
                'impressions' => $impressions,
            ];
        }

        if ($persist) {
            $this->saveToStorage($email . '_posts_' . date('Y-m-d') . '.json', $normalized);
        }
        return $normalized;
    }

    public function refreshToken(string $email): array
    {
        $tokenData = $this->tokenRepo->getByEmail($email);
        if (!$tokenData || empty($tokenData['access_token'])) {
            throw new \Exception('Instagram não conectado.');
        }

        $data = $this->requestJson(
            'https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token=' . urlencode((string)$tokenData['access_token'])
        );

        $newToken = (string)($data['access_token'] ?? $tokenData['access_token']);
        $expiresIn = (int)($data['expires_in'] ?? 60 * 24 * 60 * 60);
        $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn);

        $this->tokenRepo->save(
            $email,
            $newToken,
            $expiresAt,
            (string)($tokenData['instagram_user_id'] ?? ''),
            (string)($tokenData['instagram_username'] ?? 'unknown')
        );

        return [
            'access_token' => $newToken,
            'expires_in' => $expiresIn,
            'expires_at' => $expiresAt,
        ];
    }

    public function savePostInsights(string $postId, int $reach, int $impressions, int $saved): bool
    {
        return $this->postRepo->saveInsights($postId, $reach, $impressions, $saved);
    }

    private function handleApiError(array $error): void
    {
        $message = (string)($error['message'] ?? 'Erro desconhecido');
        if (stripos($message, 'invalid') !== false || stripos($message, 'token') !== false) {
            throw new \Exception('Token inválido ou expirado.\n\nPor favor, gere um novo token de acesso e tente novamente.');
        }
        if (stripos($message, 'permission') !== false) {
            throw new \Exception('Permissões insuficientes.\n\nO token precisa ter as permissões necessárias do Instagram Basic Display.');
        }

        throw new \Exception('Erro de autenticação: ' . $message);
    }
}
