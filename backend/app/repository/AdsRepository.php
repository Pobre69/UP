<?php

namespace App\Repository;

use PDO;
use DataBase\Connection\database;

class AdsRepository
{
    private function getConnection(): PDO
    {
        return database::getConnection();
    }

    public function getAdsSummary(string $email)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT 
                SUM(budget) as total_budget,
                SUM(spent) as total_spent,
                SUM(clicks) as total_clicks,
                SUM(reach) as total_reach,
                COUNT(*) as total_campaigns
             FROM ads_campaigns 
             WHERE email = :email'
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllCampaigns(string $email)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT 
                id,
                campaign_name,
                status,
                budget,
                spent,
                clicks,
                impressions,
                reach,
                start_date,
                end_date
             FROM ads_campaigns 
             WHERE email = :email
             ORDER BY created_at DESC'
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createCampaign(string $email, string $name, float $budget, string $startDate, ?string $endDate = null)
    {
        $stmt = $this->getConnection()->prepare(
            'INSERT INTO ads_campaigns (email, campaign_name, budget, start_date, end_date) 
             VALUES (:email, :campaign_name, :budget, :start_date, :end_date)'
        );
        return $stmt->execute([
            ':email' => $email,
            ':campaign_name' => $name,
            ':budget' => $budget,
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
    }

    public function updateCampaign(int $id, string $email, array $data)
    {
        $fields = [];
        $params = [':id' => $id, ':email' => $email];

        if (isset($data['spent'])) {
            $fields[] = 'spent = :spent';
            $params[':spent'] = $data['spent'];
        }
        if (isset($data['clicks'])) {
            $fields[] = 'clicks = :clicks';
            $params[':clicks'] = $data['clicks'];
        }
        if (isset($data['impressions'])) {
            $fields[] = 'impressions = :impressions';
            $params[':impressions'] = $data['impressions'];
        }
        if (isset($data['reach'])) {
            $fields[] = 'reach = :reach';
            $params[':reach'] = $data['reach'];
        }
        if (isset($data['status'])) {
            $fields[] = 'status = :status';
            $params[':status'] = $data['status'];
        }

        if (empty($fields)) {
            return false;
        }

        $sql = 'UPDATE ads_campaigns SET ' . implode(', ', $fields) . ' WHERE id = :id AND email = :email';
        $stmt = $this->getConnection()->prepare($sql);
        return $stmt->execute($params);
    }
}
