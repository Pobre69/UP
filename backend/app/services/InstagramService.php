<?php

namespace App\Services;

use Facebook\Facebook;
use Facebook\Exceptions\FacebookSDKException;
use App\Repository\InstagramTokenRepository;
use App\Repository\InstagramMetricsRepository;
use App\Repository\InstagramPostRepository;
use App\Config\StorageConfig;

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
        $this->storagePath = StorageConfig::getInstagramPath();
        
        StorageConfig::ensureDirectoriesExist();
        
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
            // Usar API do Instagram Basic Display diretamente
            $url = 'https://graph.instagram.com/me?fields=id,username,account_type,media_count&access_token=' . urlencode($accessToken);
            $response = file_get_contents($url);
            
            if ($response === false) {
                throw new \Exception('Não foi possível conectar ao Instagram.');
            }
            
            $user = json_decode($response, true);
            
            if (isset($user['error'])) {
                $errorMsg = $user['error']['message'] ?? 'Erro desconhecido';
                
                if (strpos($errorMsg, 'Invalid') !== false || strpos($errorMsg, 'token') !== false) {
                    throw new \Exception('Token inválido ou expirado.\n\nPor favor, gere um novo token de acesso e tente novamente.');
                } elseif (strpos($errorMsg, 'permissions') !== false) {
                    throw new \Exception('Permissões insuficientes.\n\nO token precisa ter as permissões necessárias do Instagram Basic Display.');
                } else {
                    throw new \Exception('Erro de autenticação: ' . $errorMsg);
                }
            }
            
            $expiresAt = date('Y-m-d H:i:s', strtotime('+60 days'));
            
            $tokenData = [
                'email' => $email,
                'access_token' => $accessToken,
                'expires_at' => $expiresAt,
                'instagram_user_id' => $user['id'],
                'instagram_username' => $user['username'] ?? 'unknown'
            ];
            
            $this->saveToStorage($email . '_token.json', $tokenData);
            
            return $this->tokenRepo->save(
                $email,
                $accessToken,
                $expiresAt,
                $user['id'],
                $user['username'] ?? 'unknown'
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getAccountMetrics(string $email)
    {
        $tokenData = $this->tokenRepo->getByEmail($email);
        if (!$tokenData) {
            throw new \Exception('Instagram não conectado.\n\nConecte sua conta do Instagram para visualizar as métricas.');
        }

        try {
            // Buscar dados básicos da conta usando Instagram Basic Display API
            $url = 'https://graph.instagram.com/' . $tokenData['instagram_user_id'] . '?fields=id,username,account_type,media_count&access_token=' . urlencode($tokenData['access_token']);
            $response = file_get_contents($url);
            
            if ($response === false) {
                throw new \Exception('Não foi possível conectar ao Instagram.');
            }
            
            $data = json_decode($response, true);
            
            if (isset($data['error'])) {
                $this->handleApiError($data['error']);
            }
            
            // Instagram Basic Display não fornece followers_count, usar valores padrão
            $metrics = [
                'id' => $data['id'],
                'username' => $data['username'],
                'account_type' => $data['account_type'] ?? 'PERSONAL',
                'media_count' => $data['media_count'] ?? 0,
                'followers_count' => 0,
                'follows_count' => 0,
                'profile_views' => 0,
                'reach' => 0,
                'impressions' => 0,
                'engagement_rate' => 0
            ];
            
            // Salvar métricas no banco de dados
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
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getRecentPosts(string $email, int $limit = 20)
    {
        $tokenData = $this->tokenRepo->getByEmail($email);
        if (!$tokenData) {
            throw new \Exception('Instagram não conectado.\n\nConecte sua conta do Instagram para visualizar seus posts.');
        }

        try {
            $url = 'https://graph.instagram.com/' . $tokenData['instagram_user_id'] . '/media?fields=id,caption,media_type,media_url,permalink,timestamp&access_token=' . urlencode($tokenData['access_token']);
            $response = file_get_contents($url);
            
            if ($response === false) {
                throw new \Exception('Não foi possível conectar ao Instagram.');
            }
            
            $result = json_decode($response, true);
            
            if (isset($result['error'])) {
                $this->handleApiError($result['error']);
            }
            
            $posts = $result['data'] ?? [];
            
            // Salvar posts no banco de dados
            foreach ($posts as $post) {
                $this->postRepo->save(
                    $post['id'],
                    $email,
                    $post['caption'] ?? null,
                    $post['media_type'],
                    $post['media_url'] ?? null,
                    $post['permalink'] ?? null,
                    $post['timestamp'] ?? null,
                    0, // like_count não disponível no Basic Display
                    0, // comments_count não disponível no Basic Display
                    0, // shares_count
                    0  // saved_count
                );
            }

            $this->saveToStorage($email . '_posts_' . date('Y-m-d') . '.json', $posts);

            return $posts;
        } catch (\Exception $e) {
            throw $e;
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
            throw new \Exception('Instagram não conectado.\n\nConecte sua conta do Instagram primeiro.');
        }

        try {
            $url = 'https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token=' . urlencode($tokenData['access_token']);
            $response = file_get_contents($url);
            
            if ($response === false) {
                throw new \Exception('Não foi possível renovar o token.');
            }
            
            $newToken = json_decode($response, true);
            
            if (isset($newToken['error'])) {
                throw new \Exception('Não foi possível renovar o token automaticamente.\n\nPor favor, reconecte sua conta do Instagram manualmente.');
            }
            
            $expiresAt = date('Y-m-d H:i:s', strtotime('+60 days'));
            
            $this->tokenRepo->save(
                $email,
                $newToken['access_token'],
                $expiresAt,
                $tokenData['instagram_user_id'],
                $tokenData['instagram_username']
            );

            return $newToken;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    private function handleApiError(array $error)
    {
        $errorMsg = $error['message'] ?? 'Erro desconhecido';
        $errorCode = $error['code'] ?? 0;
        
        if (strpos($errorMsg, 'Invalid') !== false || strpos($errorMsg, 'token') !== false || $errorCode == 190) {
            throw new \Exception('Sua sessão expirou.\n\nPor favor, reconecte sua conta do Instagram.');
        } elseif (strpos($errorMsg, 'permissions') !== false || $errorCode == 10) {
            throw new \Exception('Sem permissão para acessar este recurso.\n\nVerifique as permissões do token.');
        } elseif (strpos($errorMsg, 'rate limit') !== false || $errorCode == 4) {
            throw new \Exception('Muitas requisições.\n\nAguarde alguns minutos antes de tentar novamente.');
        } else {
            throw new \Exception('Erro ao acessar Instagram: ' . $errorMsg);
        }
    }
}age'] ?? 'Erro desconhecido';
        $errorCode = $error['code'] ?? 0;
        
        if (strpos($errorMsg, 'Invalid') !== false || strpos($errorMsg, 'token') !== false || $errorCode == 190) {
            throw new \Exception('Sua sessão expirou.\n\nPor favor, reconecte sua conta do Instagram.');
        } elseif (strpos($errorMsg, 'permissions') !== false || $errorCode == 10) {
            throw new \Exception('Sem permissão para acessar este recurso.\n\nVerifique as permissões do token.');
        } elseif (strpos($errorMsg, 'rate limit') !== false || $errorCode == 4) {
            throw new \Exception('Muitas requisições.\n\nAguarde alguns minutos antes de tentar novamente.');
        } else {
            throw new \Exception('Erro ao acessar Instagram: ' . $errorMsg);
        }
    }
}
