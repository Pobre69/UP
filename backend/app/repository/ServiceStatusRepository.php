<?php

namespace App\Repository;

use PDO;
use DataBase\Connection\database;

class ServiceStatusRepository
{
    private function getConnection(): PDO
    {
        return database::getConnection();
    }

    public function getStatusSummary(string $email)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT 
                status,
                COUNT(*) as count
             FROM content_pipeline 
             WHERE email = :email
             GROUP BY status'
        );
        $stmt->execute([':email' => $email]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $summary = [
            'review' => 0,
            'planned' => 0,
            'scheduled' => 0,
            'published' => 0
        ];

        foreach ($results as $row) {
            if (isset($summary[$row['status']])) {
                $summary[$row['status']] = $row['count'];
            }
        }

        return $summary;
    }

    public function getAllContent(string $email)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT 
                id,
                title,
                content_type,
                description,
                status,
                scheduled_date,
                published_date,
                created_at
             FROM content_pipeline 
             WHERE email = :email
             ORDER BY 
                CASE status
                    WHEN "review" THEN 1
                    WHEN "planned" THEN 2
                    WHEN "scheduled" THEN 3
                    WHEN "published" THEN 4
                END,
                created_at DESC'
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createContent(string $email, string $title, string $contentType, ?string $description = null, string $status = 'planned')
    {
        $stmt = $this->getConnection()->prepare(
            'INSERT INTO content_pipeline (email, title, content_type, description, status) 
             VALUES (:email, :title, :content_type, :description, :status)'
        );
        return $stmt->execute([
            ':email' => $email,
            ':title' => $title,
            ':content_type' => $contentType,
            ':description' => $description,
            ':status' => $status
        ]);
    }

    public function updateContentStatus(int $id, string $email, string $status, ?string $scheduledDate = null, ?string $publishedDate = null)
    {
        $fields = ['status = :status'];
        $params = [
            ':id' => $id,
            ':email' => $email,
            ':status' => $status
        ];

        if ($scheduledDate !== null) {
            $fields[] = 'scheduled_date = :scheduled_date';
            $params[':scheduled_date'] = $scheduledDate;
        }

        if ($publishedDate !== null) {
            $fields[] = 'published_date = :published_date';
            $params[':published_date'] = $publishedDate;
        }

        $sql = 'UPDATE content_pipeline SET ' . implode(', ', $fields) . ' WHERE id = :id AND email = :email';
        $stmt = $this->getConnection()->prepare($sql);
        return $stmt->execute($params);
    }
}
