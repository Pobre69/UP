<?php

namespace Controllers;

use App\Repository\AdsRepository;
use App\Middleware\Security;

class AdsController
{
    private AdsRepository $adsRepo;

    public function __construct()
    {
        $this->adsRepo = new AdsRepository();
    }

    public function getAdsData()
    {
        header('Content-Type: application/json');
        
        Security::checkAuth();
        $user = Security::getAuthUser();
        $email = $user['email'];

        try {
            $summary = $this->adsRepo->getAdsSummary($email);
            $campaigns = $this->adsRepo->getAllCampaigns($email);

            // Calcular CPC para cada campanha
            foreach ($campaigns as &$campaign) {
                $cpc = $campaign['clicks'] > 0 ? $campaign['spent'] / $campaign['clicks'] : 0;
                $campaign['cpc'] = round($cpc, 2);
            }

            $response = [
                'success' => true,
                'data' => [
                    'summary' => [
                        'totalBudget' => $summary['total_budget'] ?? 0,
                        'totalSpent' => $summary['total_spent'] ?? 0,
                        'totalClicks' => $summary['total_clicks'] ?? 0,
                        'totalReach' => $summary['total_reach'] ?? 0
                    ],
                    'campaigns' => $campaigns
                ]
            ];

            http_response_code(200);
            echo json_encode($response);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    public function createCampaign()
    {
        header('Content-Type: application/json');
        
        Security::checkAuth();
        $user = Security::getAuthUser();
        $email = $user['email'];

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['campaign_name']) || !isset($data['budget']) || !isset($data['start_date'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Dados obrigatórios faltando']);
            return;
        }

        try {
            $this->adsRepo->createCampaign(
                $email,
                $data['campaign_name'],
                $data['budget'],
                $data['start_date'],
                $data['end_date'] ?? null
            );

            http_response_code(201);
            echo json_encode(['success' => true, 'mensagem' => 'Campanha criada com sucesso']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    public function updateCampaign()
    {
        header('Content-Type: application/json');
        
        Security::checkAuth();
        $user = Security::getAuthUser();
        $email = $user['email'];

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'ID da campanha é obrigatório']);
            return;
        }

        try {
            $this->adsRepo->updateCampaign($data['id'], $email, $data);

            http_response_code(200);
            echo json_encode(['success' => true, 'mensagem' => 'Campanha atualizada com sucesso']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => $e->getMessage()]);
        }
    }
}
