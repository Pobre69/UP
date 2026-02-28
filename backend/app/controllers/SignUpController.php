<?php

namespace Controllers;

use App\Repository\UsuarioRepository;
use App\Repository\UsuarioDetalhesRepository;
use App\Repository\ConcorrenteRepository;

class SignUpController
{
    public function register() {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Dados inválidos']);
            return;
        }
        
        $fullName = trim($data['fullName'] ?? '');
        $company = trim($data['company'] ?? '');
        $email = trim($data['email'] ?? '');
        $instagram = trim($data['instagram'] ?? '');
        $segment = $data['segment'] ?? '';
        $city = trim($data['city'] ?? '');
        $mainGoal = $data['mainGoal'] ?? '';
        $competitors = trim($data['competitors'] ?? '');
        $driveLink = trim($data['driveLink'] ?? '');
        $attendant = $data['attendant'] ?? '';
        
        if (empty($fullName) || empty($email) || empty($segment) || empty($city) || empty($mainGoal)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Campos obrigatórios não preenchidos']);
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'E-mail inválido']);
            return;
        }
        
        try {
            $usuarioRepo = new UsuarioRepository();
            $detalhesRepo = new UsuarioDetalhesRepository();
            $concorrenteRepo = new ConcorrenteRepository();
            
            $companyValue = !empty($company) ? $company : 'Não informado';
            $localizacaoJson = json_encode(['cidade' => $city]);
            $instagramValue = !empty($instagram) ? $instagram : null;
            $driveLinkValue = !empty($driveLink) ? $driveLink : null;
            $attendantValue = !empty($attendant) ? $attendant : null;
            
            $resultUsuario = $usuarioRepo->add($email, $fullName, null, $companyValue);
            
            if (isset($resultUsuario[0]['RESULTADO']) && strpos($resultUsuario[0]['RESULTADO'], 'sucesso') === false) {
                http_response_code(400);
                echo json_encode(['success' => false, 'mensagem' => $resultUsuario[0]['RESULTADO']]);
                return;
            }
            
            $detalhesRepo->add($email, $mainGoal, $driveLinkValue, $segment, $instagramValue, $attendantValue, $localizacaoJson);
            
            if (!empty($competitors)) {
                $concorrenteRepo->add($email, $competitors);
            }
            
            http_response_code(201);
            echo json_encode(['success' => true, 'mensagem' => 'Cadastro realizado com sucesso!']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => 'Erro ao cadastrar: ' . $e->getMessage()]);
        }
    }
}
