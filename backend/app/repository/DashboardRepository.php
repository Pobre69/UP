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
            'SELECT followers_count, DATE(collected_at) as date
             FROM instagram_metrics 
             WHERE email = :email 
             AND collected_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
             GROUP BY DATE(collected_at)
             ORDER BY collected_at ASC'
        );
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReachSeries(string $email, int $days = 30)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT reach, DATE(collected_at) as date
             FROM instagram_metrics 
             WHERE email = :email 
             AND collected_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
             GROUP BY DATE(collected_at)
             ORDER BY collected_at ASC'
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
                SUM(like_count + comments_count) as total_engagement,
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
        
        if ($result && $result['initial_followers'] > 0) {
            $growth = (($result['current_followers'] - $result['initial_followers']) / $result['initial_followers']) * 100;
            return round($growth, 2);
        }
        return 0;
    }

    public function getStatsDelta(string $email)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT 
                m1.followers_count as current_followers,
                m1.profile_views as current_views,
                m1.reach as current_reach,
                m1.impressions as current_impressions,
                m1.engagement_rate as current_engagement,
                m2.followers_count as prev_followers,
                m2.profile_views as prev_views,
                m2.reach as prev_reach,
                m2.impressions as prev_impressions,
                m2.engagement_rate as prev_engagement
             FROM instagram_metrics m1
             LEFT JOIN instagram_metrics m2 ON m2.email = m1.email 
                AND m2.collected_at < m1.collected_at
             WHERE m1.email = :email
             ORDER BY m1.collected_at DESC
             LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
