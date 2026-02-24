<?php

namespace App\Repository;

use PDO;
use DataBase\Connection\database;

class EngagementRepository
{
    private function getConnection(): PDO
    {
        return database::getConnection();
    }

    public function getEngagementStats(string $email)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT 
                AVG(like_count) as avg_likes,
                AVG(comments_count) as avg_comments,
                AVG(shares_count) as avg_shares,
                AVG(saved_count) as avg_saved,
                COUNT(*) as total_posts,
                SUM(like_count) as total_likes,
                SUM(comments_count) as total_comments,
                SUM(shares_count) as total_shares
             FROM instagram_posts 
             WHERE email = :email'
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPostsPerformance(string $email, int $limit = 20)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT 
                p.id,
                p.caption,
                p.media_type,
                p.timestamp,
                p.like_count,
                p.comments_count,
                p.shares_count,
                p.saved_count,
                i.reach,
                i.impressions,
                (p.like_count + p.comments_count + p.shares_count) as total_engagement
             FROM instagram_posts p
             LEFT JOIN instagram_post_insights i ON p.id = i.post_id
             WHERE p.email = :email
             ORDER BY p.timestamp DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAverageReach(string $email)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT AVG(i.reach) as avg_reach
             FROM instagram_post_insights i
             INNER JOIN instagram_posts p ON i.post_id = p.id
             WHERE p.email = :email'
        );
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($result['avg_reach'] ?? 0);
    }

    public function getBestStory(string $email)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT MAX(impressions) as best_story_views
             FROM instagram_stories
             WHERE email = :email'
        );
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['best_story_views'] ?? 0;
    }

    public function getEngagementRate(string $email)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT engagement_rate
             FROM instagram_metrics
             WHERE email = :email
             ORDER BY collected_at DESC
             LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['engagement_rate'] ?? 0;
    }

    public function getEngagementDistribution(string $email)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT 
                SUM(like_count) as total_likes,
                SUM(comments_count) as total_comments,
                SUM(shares_count) as total_shares
             FROM instagram_posts
             WHERE email = :email'
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
