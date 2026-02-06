<?php

namespace Controllers;

use App\Repository\UsuarioRepository;
use App\Repository\UsuarioDetalhesRepository;
use App\Repository\ConcorrenteRepository;
use DataBase\Connection\database;

class SignUpController
{
    private function getConfig(): array
    {
        $configPath = __DIR__ . '/../../config/config.json';
        return json_decode(file_get_contents($configPath), true);
    }

    private function getConnection(): \PDO
    {
        $config = $this->getConfig();
        $db = new database();
        return $db->setConnection(
            $config['database']['UP']['host'],
            $config['database']['UP']['username'],
            $config['database']['UP']['password'],
            $config['database']['UP']['database'],
            'UP'
        );
    }

    public function register()
    {
        header('Content-Type: application/json');
        
        $config = $this->getConfig();
        $allowedOrigin = $config['frontendUrl'] ?? 'http://localhost:5173';
        header("Access-Control-Allow-Origin: $allowedOrigin");
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $email = $input['email'] ?? '';
        $fullName = $input['fullName'] ?? '';
        $company = $input['company'] ?? '';
        $instagram = $input['instagram'] ?? null;
        $segment = $input['segment'] ?? '';
        $city = $input['city'] ?? '';
        $mainGoal = $input['mainGoal'] ?? '';
        $competitors = $input['competitors'] ?? null;
        $driveLink = $input['driveLink'] ?? null;
        $attendant = $input['attendant'] ?? null;

        if (empty($email) || empty($fullName) || empty($segment) || empty($city) || empty($mainGoal)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Campos obrigatórios faltando']);
            exit;
        }

        try {
            $conn = $this->getConnection();
            
            $usuarioRepo = new UsuarioRepository($conn);
            $result = $usuarioRepo->add($email, $fullName, null, $company);

            if (isset($result[0]['RESULTADO']) && strpos($result[0]['RESULTADO'], 'sucesso') === false) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => $result[0]['RESULTADO']]);
                exit;
            }

            $localizacao = json_encode([
                'cidade' => $city,
                'cep' => null,
                'bairro' => null,
                'estado' => null,
                'pais' => null
            ]);

            $detalhesRepo = new UsuarioDetalhesRepository($conn);
            $detalhesRepo->add($email, $mainGoal, $driveLink, $segment, $instagram, $attendant, $localizacao);

            if (!empty($competitors)) {
                $concorrenteRepo = new ConcorrenteRepository($conn);
                $concorrenteRepo->add($email, $competitors);
            }

            echo json_encode(['success' => true, 'message' => 'Cadastro realizado com sucesso']);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao processar cadastro: ' . $e->getMessage()]);
        }
    }
}
