<?php

namespace App\Controllers;

use App\Repository\UsuarioRepository;
use App\Repository\UsuarioDetalhesRepository;
use App\Repository\ConcorrenteRepository;

class SignUpController
{
    public function register() {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $fullName = $data['fullName'] ?? '';
        $company = $data['company'] ?? '';
        $email = $data['email'] ?? '';
        $instagram = $data['instagram'] ?? '';
        $segment = $data['segment'] ?? '';
        $city = $data['city'] ?? '';
        $mainGoal = $data['mainGoal'] ?? '';
        $competitors = $data['competitors'] ?? '';
        $driveLink = $data['driveLink'] ?? '';
        $attendant = $data['attendant'] ?? '';
        
        try {
            $usuarioRepo = new UsuarioRepository();
            $detalhesRepo = new UsuarioDetalhesRepository();
            $concorrenteRepo = new ConcorrenteRepository();
            
            $usuarioRepo->add($email, $fullName, null, $company);
            $detalhesRepo->add($email, $mainGoal, $driveLink, $segment, $instagram, $attendant, $city);
            
            if (!empty($competitors)) {
                $concorrenteRepo->add($email, $competitors);
            }
            
            echo json_encode([
                'success' => true,
                'mensagem' => 'Cadastro realizado com sucesso!'
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'mensagem' => 'Erro ao cadastrar: ' . $e->getMessage()
            ]);
        }
    }
}
