<?php

namespace App\Repository;

use PDO;
use DataBase\Connection\database;

class CalendarRepository
{
    public function getConnection(): PDO
    {
        return database::getConnection();
    }

    public function getPostsByMonth(string $email, int $year, int $month)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT 
                id,
                DATE(timestamp) as date,
                media_type as content_type,
                caption,
                like_count,
                comments_count,
                "published" as status
             FROM instagram_posts 
             WHERE email = :email 
             AND YEAR(timestamp) = :year 
             AND MONTH(timestamp) = :month
             ORDER BY timestamp ASC'
        );
        $stmt->execute([
            ':email' => $email,
            ':year' => $year,
            ':month' => $month
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStoriesByMonth(string $email, int $year, int $month)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT 
                id,
                DATE(timestamp) as date,
                "STORY" as content_type,
                impressions,
                "published" as status
             FROM instagram_stories 
             WHERE email = :email 
             AND YEAR(timestamp) = :year 
             AND MONTH(timestamp) = :month
             ORDER BY timestamp ASC'
        );
        $stmt->execute([
            ':email' => $email,
            ':year' => $year,
            ':month' => $month
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getScheduledPostsByMonth(string $email, int $year, int $month)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT 
                id,
                DATE(scheduled_date) as date,
                content_type,
                caption,
                status
             FROM scheduled_posts 
             WHERE email = :email 
             AND YEAR(scheduled_date) = :year 
             AND MONTH(scheduled_date) = :month
             AND status = "pending"
             ORDER BY scheduled_date ASC'
        );
        $stmt->execute([
            ':email' => $email,
            ':year' => $year,
            ':month' => $month
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllContentByMonth(string $email, int $year, int $month)
    {
        $posts = $this->getPostsByMonth($email, $year, $month);
        $stories = $this->getStoriesByMonth($email, $year, $month);
        $scheduled = $this->getScheduledPostsByMonth($email, $year, $month);

        $allContent = array_merge($posts, $stories, $scheduled);
        
        // Agrupar por data
        $grouped = [];
        foreach ($allContent as $item) {
            $date = $item['date'];
            if (!isset($grouped[$date])) {
                $grouped[$date] = [];
            }
            $grouped[$date][] = $item;
        }

        return $grouped;
    }
}
