<?php

namespace App\Repository;

use PDO;
use DataBase\Connection\database;

class DashboardRepository
{
    private function getConnection(): PDO
    {
        return database::getConnection();
    }

    public function getDashboardStats(string $email)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT 
                followers_count,
                profile_views,
                reach,
                impressions,
                engagement_rate
             FROM instagram_metrics 
             WHERE email = :email 
             ORDER BY collected_at DESC 
             LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getFollowersGrowth(string $email, int $days = 30)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT DATE(collected_at) as date, MAX(followers_count) as followers_count
             FROM instagram_metrics 
             WHERE email = :email 
             AND collected_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
             GROUP BY DATE(collected_at)
             ORDER BY DATE(collected_at) ASC'
        );
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReachSeries(string $email, int $days = 30)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT DATE(collected_at) as date, MAX(reach) as reach
             FROM instagram_metrics 
             WHERE email = :email 
             AND collected_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
             GROUP BY DATE(collected_at)
             ORDER BY DATE(collected_at) ASC'
        );
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEngagementSummary(string $email)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT 
                AVG(like_count) as avg_likes,
                AVG(comments_count) as avg_comments,
                AVG(shares_count) as avg_shares,
                SUM(like_count + comments_count + shares_count) as total_engagement,
                COUNT(*) as total_posts
             FROM instagram_posts 
             WHERE email = :email'
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getGrowthPercentile(string $email)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT 
                (SELECT followers_count FROM instagram_metrics WHERE email = :email ORDER BY collected_at DESC LIMIT 1) as current_followers,
                (SELECT followers_count FROM instagram_metrics WHERE email = :email ORDER BY collected_at ASC LIMIT 1) as initial_followers'
        );
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && (int)$result['initial_followers'] > 0) {
            $growth = (((int)$result['current_followers'] - (int)$result['initial_followers']) / (int)$result['initial_followers']) * 100;
            return round($growth, 2);
        }
        return 0;
    }

    public function getStatsDelta(string $email)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT followers_count, profile_views, reach, impressions, engagement_rate
             FROM instagram_metrics
             WHERE email = :email
             ORDER BY collected_at DESC
             LIMIT 2'
        );
        $stmt->execute([':email' => $email]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $current = $rows[0] ?? [];
        $previous = $rows[1] ?? [];

        return [
            'current_followers' => $current['followers_count'] ?? 0,
            'current_views' => $current['profile_views'] ?? 0,
            'current_reach' => $current['reach'] ?? 0,
            'current_impressions' => $current['impressions'] ?? 0,
            'current_engagement' => $current['engagement_rate'] ?? 0,
            'prev_followers' => $previous['followers_count'] ?? 0,
            'prev_views' => $previous['profile_views'] ?? 0,
            'prev_reach' => $previous['reach'] ?? 0,
            'prev_impressions' => $previous['impressions'] ?? 0,
            'prev_engagement' => $previous['engagement_rate'] ?? 0,
        ];
    }
}
