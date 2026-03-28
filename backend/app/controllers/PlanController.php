<?php

namespace Controllers;

use App\Repository\PlanRepository;
use App\Middleware\Security;

class PlanController
{
    private PlanRepository $planRepo;

    public function __construct()
    {
        $this->planRepo = new PlanRepository();
    }

    public function getPlanData()
    {
        header('Content-Type: application/json');
        
        Security::checkAuth();
        $user = Security::getAuthUser();
        $email = $user['email'];

        try {
            $currentPlan = $this->planRepo->getUserCurrentPlan($email);
            $allPlans = $this->planRepo->getAllPlans();
            $history = $this->planRepo->getUserPlanHistory($email);

            // Definir recursos por plano (pode vir de outra tabela futuramente)
            $planFeatures = [
                'Básico' => [
                    '4 posts/mês',
                    'Stories básicos',
                    'Relatório mensal',
                    '1 rede social'
                ],
                'Profissional' => [
                    '8 posts/mês',
                    'Stories + Reels',
                    'Relatório semanal',
                    '2 redes sociais',
                    'Gestão de tráfego básica'
                ],
                'Premium' => [
                    '12 posts/mês',
                    'Stories + Reels + Carrosséis',
                    'Relatório diário',
                    '3 redes sociais',
                    'Gestão de tráfego avançada',
                    'Planejamento estratégico'
                ]
            ];

            // Adicionar recursos aos planos
            foreach ($allPlans as &$plan) {
                $plan['features'] = $planFeatures[$plan['nome']] ?? [];
                $plan['active'] = $currentPlan && $plan['nome'] === $currentPlan['plano_nome'];
            }

            $response = [
                'success' => true,
                'data' => [
                    'currentPlan' => $currentPlan ? [
                        'name' => $currentPlan['plano_nome'],
                        'value' => $currentPlan['valor'],
                        'startDate' => $currentPlan['data_inicial'],
                        'endDate' => $currentPlan['data_final'],
                        'status' => $currentPlan['status']
                    ] : null,
                    'availablePlans' => $allPlans,
                    'history' => $history
                ]
            ];

            http_response_code(200);
            echo json_encode($response);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => 'Erro ao carregar plano']);
        }
    }
}
