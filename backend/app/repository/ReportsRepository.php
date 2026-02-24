<?php

namespace App\Repository;

use PDO;
use DataBase\Connection\database;

class ReportsRepository
{
    private function getConnection(): PDO
    {
        return database::getConnection();
    }

    public function getFollowersEvolution(string $email, int $days = 30)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT 
                DATE(collected_at) as date,
                followers_count,
                collected_at
             FROM instagram_metrics 
             WHERE email = :email 
             AND collected_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
             ORDER BY collected_at ASC'
        );
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReachEvolution(string $email, int $days = 30)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT 
                DATE(collected_at) as date,
                reach,
                collected_at
             FROM instagram_metrics 
             WHERE email = :email 
             AND collected_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
             ORDER BY collected_at ASC'
        );
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLast7DaysSummary(string $email)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT 
                DATE(collected_at) as date,
                followers_count,
                reach,
                engagement_rate,
                LAG(followers_count) OVER (ORDER BY collected_at) as prev_followers
             FROM instagram_metrics 
             WHERE email = :email 
             AND collected_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             ORDER BY collected_at DESC'
        );
        $stmt->execute([':email' => $email]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calcular tendência
        foreach ($results as &$row) {
            if ($row['prev_followers'] !== null) {
                $diff = $row['followers_count'] - $row['prev_followers'];
                $row['trend'] = $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'stable');
                $row['trend_value'] = $diff;
            } else {
                $row['trend'] = 'stable';
                $row['trend_value'] = 0;
            }
        }

        return $results;
    }

    public function getOverallSummary(string $email)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT 
                (SELECT followers_count FROM instagram_metrics WHERE email = :email ORDER BY collected_at DESC LIMIT 1) as current_followers,
                (SELECT followers_count FROM instagram_metrics WHERE email = :email ORDER BY collected_at ASC LIMIT 1) as initial_followers,
                (SELECT AVG(reach) FROM instagram_metrics WHERE email = :email) as avg_reach,
                (SELECT AVG(engagement_rate) FROM instagram_metrics WHERE email = :email) as avg_engagement,
                (SELECT COUNT(*) FROM instagram_posts WHERE email = :email) as total_posts
             FROM instagram_metrics 
             WHERE email = :email 
             LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && $result['initial_followers'] > 0) {
            $growth = (($result['current_followers'] - $result['initial_followers']) / $result['initial_followers']) * 100;
            $result['growth_percentage'] = round($growth, 2);
        } else {
            $result['growth_percentage'] = 0;
        }

        return $result;
    }
}
