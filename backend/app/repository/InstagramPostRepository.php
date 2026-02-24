<?php

namespace App\Repository;

use PDO;
use DataBase\Connection\database;

class InstagramPostRepository
{
    private function getConnection(): PDO
    {
        return database::getConnection();
    }

    public function save(string $id, string $email, ?string $caption, string $mediaType, ?string $mediaUrl, ?string $permalink, ?string $timestamp, int $likeCount, int $commentsCount, int $sharesCount = 0, int $savedCount = 0)
    {
        $stmt = $this->getConnection()->prepare(
            'INSERT INTO instagram_posts (id, email, caption, media_type, media_url, permalink, timestamp, like_count, comments_count, shares_count, saved_count) 
             VALUES (:id, :email, :caption, :media_type, :media_url, :permalink, :timestamp, :like_count, :comments_count, :shares_count, :saved_count)
             ON DUPLICATE KEY UPDATE 
             like_count = :like_count, 
             comments_count = :comments_count,
             shares_count = :shares_count,
             saved_count = :saved_count'
        );
        return $stmt->execute([
            ':id' => $id,
            ':email' => $email,
            ':caption' => $caption,
            ':media_type' => $mediaType,
            ':media_url' => $mediaUrl,
            ':permalink' => $permalink,
            ':timestamp' => $timestamp,
            ':like_count' => $likeCount,
            ':comments_count' => $commentsCount,
            ':shares_count' => $sharesCount,
            ':saved_count' => $savedCount
        ]);
    }

    public function getByEmail(string $email, int $limit = 20)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT * FROM instagram_posts WHERE email = :email ORDER BY timestamp DESC LIMIT :limit'
        );
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
