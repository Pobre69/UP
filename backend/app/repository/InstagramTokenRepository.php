<?php

namespace App\Repository;

use PDO;
use DataBase\Connection\database;

class InstagramTokenRepository
{
    private function getConnection(): PDO
    {
        return database::getConnection();
    }

    public function save(string $email, string $accessToken, ?string $expiresAt = null, ?string $instagramUserId = null, ?string $instagramUsername = null)
    {
        $stmt = $this->getConnection()->prepare(
            'INSERT INTO instagram_tokens (email, access_token, expires_at, instagram_user_id, instagram_username) 
             VALUES (:email, :access_token, :expires_at, :instagram_user_id, :instagram_username)
             ON DUPLICATE KEY UPDATE 
             access_token = :access_token, 
             expires_at = :expires_at,
             instagram_user_id = :instagram_user_id,
             instagram_username = :instagram_username'
        );
        return $stmt->execute([
            ':email' => $email,
            ':access_token' => $accessToken,
            ':expires_at' => $expiresAt,
            ':instagram_user_id' => $instagramUserId,
            ':instagram_username' => $instagramUsername
        ]);
    }

    public function getByEmail(string $email)
    {
        $stmt = $this->getConnection()->prepare('SELECT * FROM instagram_tokens WHERE email = :email');
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function delete(string $email)
    {
        $stmt = $this->getConnection()->prepare('DELETE FROM instagram_tokens WHERE email = :email');
        return $stmt->execute([':email' => $email]);
    }
}
