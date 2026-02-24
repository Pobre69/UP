<?php

namespace App\Repository;

use PDO;
use DataBase\Connection\database;

class InstagramMetricsRepository
{
    private function getConnection(): PDO
    {
        return database::getConnection();
    }

    public function save(string $email, int $followersCount, int $followingCount, int $mediaCount, float $engagementRate, int $profileViews = 0, int $reach = 0, int $impressions = 0)
    {
        $stmt = $this->getConnection()->prepare(
            'INSERT INTO instagram_metrics (email, followers_count, following_count, media_count, engagement_rate, profile_views, reach, impressions) 
             VALUES (:email, :followers_count, :following_count, :media_count, :engagement_rate, :profile_views, :reach, :impressions)'
        );
        return $stmt->execute([
            ':email' => $email,
            ':followers_count' => $followersCount,
            ':following_count' => $followingCount,
            ':media_count' => $mediaCount,
            ':engagement_rate' => $engagementRate,
            ':profile_views' => $profileViews,
            ':reach' => $reach,
            ':impressions' => $impressions
        ]);
    }

    public function getLatestByEmail(string $email)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT * FROM instagram_metrics WHERE email = :email ORDER BY collected_at DESC LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getHistoryByEmail(string $email, int $limit = 30)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT * FROM instagram_metrics WHERE email = :email ORDER BY collected_at DESC LIMIT :limit'
        );
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
