<?php

namespace App\Repository;

use DataBase\Connection\database;
use PDO;

class CalendarRepository
{
    private function getConnection(): PDO
    {
        return database::getConnection();
    }

    public function getPostsByMonth(string $email, int $year, int $month): array
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
        $stmt->execute([':email' => $email, ':year' => $year, ':month' => $month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStoriesByMonth(string $email, int $year, int $month): array
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
        $stmt->execute([':email' => $email, ':year' => $year, ':month' => $month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getScheduledPostsByMonth(string $email, int $year, int $month): array
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
        $stmt->execute([':email' => $email, ':year' => $year, ':month' => $month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllContentByMonth(string $email, int $year, int $month): array
    {
        $allContent = array_merge(
            $this->getPostsByMonth($email, $year, $month),
            $this->getStoriesByMonth($email, $year, $month),
            $this->getScheduledPostsByMonth($email, $year, $month)
        );

        $grouped = [];
        foreach ($allContent as $item) {
            $date = (string) $item['date'];
            $grouped[$date] ??= [];
            $grouped[$date][] = $item;
        }

        ksort($grouped);
        return $grouped;
    }

    public function schedulePost(string $email, string $contentType, ?string $caption, ?string $mediaUrl, string $scheduledDate): bool
    {
        $stmt = $this->getConnection()->prepare(
            'INSERT INTO scheduled_posts (email, content_type, caption, media_url, scheduled_date)
             VALUES (:email, :content_type, :caption, :media_url, :scheduled_date)'
        );

        return $stmt->execute([
            ':email' => $email,
            ':content_type' => $contentType,
            ':caption' => $caption,
            ':media_url' => $mediaUrl,
            ':scheduled_date' => $scheduledDate,
        ]);
    }
}
