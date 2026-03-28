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

    public function getAccountMetrics(string $email): array
    {
        $tokenData = $this->tokenRepo->getByEmail($email);
        if (!$tokenData || empty($tokenData['access_token']) || empty($tokenData['instagram_user_id'])) {
            throw new \Exception('Instagram não conectado.\n\nConecte sua conta do Instagram para visualizar as métricas.');
        }

        $data = $this->requestJson(
            'https://graph.instagram.com/' . urlencode((string)$tokenData['instagram_user_id']) .
            '?fields=id,username,account_type,media_count&access_token=' . urlencode((string)$tokenData['access_token'])
        );

        $metrics = [
            'id' => $data['id'] ?? null,
            'username' => $data['username'] ?? null,
            'account_type' => $data['account_type'] ?? 'PERSONAL',
            'media_count' => (int)($data['media_count'] ?? 0),
            'followers_count' => 0,
            'follows_count' => 0,
            'profile_views' => 0,
            'reach' => 0,
            'impressions' => 0,
            'engagement_rate' => 0,
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

    public function getRecentPosts(string $email, int $limit = 20): array
    {
        $tokenData = $this->tokenRepo->getByEmail($email);
        if (!$tokenData || empty($tokenData['access_token']) || empty($tokenData['instagram_user_id'])) {
            throw new \Exception('Instagram não conectado.\n\nConecte sua conta do Instagram para visualizar seus posts.');
        }

        $result = $this->requestJson(
            'https://graph.instagram.com/' . urlencode((string)$tokenData['instagram_user_id']) .
            '/media?fields=id,caption,media_type,media_url,permalink,timestamp&limit=' . max(1, $limit) .
            '&access_token=' . urlencode((string)$tokenData['access_token'])
        );

        $posts = array_slice($result['data'] ?? [], 0, max(1, $limit));
        foreach ($posts as $post) {
            if (!isset($post['id'], $post['media_type'])) {
                continue;
            }
            $this->postRepo->save(
                (string)$post['id'],
                $email,
                isset($post['caption']) ? (string)$post['caption'] : null,
                (string)$post['media_type'],
                isset($post['media_url']) ? (string)$post['media_url'] : null,
                isset($post['permalink']) ? (string)$post['permalink'] : null,
                isset($post['timestamp']) ? date('Y-m-d H:i:s', strtotime((string)$post['timestamp'])) : null,
                0,
                0,
                0,
                0
            );
        }

        $this->saveToStorage($email . '_posts_' . date('Y-m-d') . '.json', $posts);
        return $posts;
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
