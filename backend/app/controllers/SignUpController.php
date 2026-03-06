<?php

namespace Controllers;

use App\Repository\UsuarioRepository;
use App\Repository\UsuarioDetalhesRepository;
use App\Repository\ConcorrenteRepository;

class SignUpController
{
    public function register() {
        @ini_set('display_errors', '0');
        error_reporting(0);
        
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $rawInput = file_get_contents('php://input');
            $data = json_decode($rawInput, true);
            
            if (!$data) {
                http_response_code(400);
                die(json_encode(['success' => false, 'mensagem' => 'Dados inválidos']));
            }
            
            $fullName = trim($data['fullName'] ?? '');
            $company = trim($data['company'] ?? '');
            $email = trim($data['email'] ?? '');
            $password = trim($data['password'] ?? '');
            $instagram = trim($data['instagram'] ?? '');
            $segment = $data['segment'] ?? '';
            $city = trim($data['city'] ?? '');
            $mainGoal = $data['mainGoal'] ?? '';
            $competitors = trim($data['competitors'] ?? '');
            $driveLink = trim($data['driveLink'] ?? '');
            $attendant = $data['attendant'] ?? '';
            
            if (empty($fullName) || empty($email) || empty($password) || empty($segment) || empty($city) || empty($mainGoal)) {
                http_response_code(400);
                die(json_encode(['success' => false, 'mensagem' => 'Campos obrigatórios não preenchidos']));
            }
            
            if (strlen($password) < 6) {
                http_response_code(400);
                die(json_encode(['success' => false, 'mensagem' => 'Senha deve ter no mínimo 6 caracteres']));
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                die(json_encode(['success' => false, 'mensagem' => 'E-mail inválido']));
            }
            
            $usuarioRepo = new UsuarioRepository();
            $detalhesRepo = new UsuarioDetalhesRepository();
            $concorrenteRepo = new ConcorrenteRepository();
            
            $companyValue = !empty($company) ? $company : 'Não informado';
            $localizacaoJson = json_encode(['cidade' => $city]);
            $instagramValue = !empty($instagram) ? $instagram : null;
            $driveLinkValue = !empty($driveLink) ? $driveLink : null;
            $attendantValue = !empty($attendant) ? $attendant : null;
            
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            
            $resultUsuario = $usuarioRepo->add($email, $fullName, $hashedPassword, $companyValue);
            
            if (isset($resultUsuario[0]['RESULTADO']) && strpos($resultUsuario[0]['RESULTADO'], 'sucesso') === false) {
                http_response_code(400);
                die(json_encode(['success' => false, 'mensagem' => $resultUsuario[0]['RESULTADO']]));
            }
            
            $detalhesRepo->add($email, $mainGoal, $driveLinkValue, $segment, $instagramValue, $attendantValue, $localizacaoJson);
            
            if (!empty($competitors)) {
                $concorrenteRepo->add($email, $competitors);
            }
            
            @session_start();
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $fullName;
            $_SESSION['logged_in'] = true;
            
            http_response_code(201);
            die(json_encode([
                'success' => true, 
                'mensagem' => 'Cadastro realizado com sucesso!',
                'redirect' => '/app'
            ]));
        } catch (\Exception $e) {
            http_response_code(500);
            die(json_encode(['success' => false, 'mensagem' => 'Erro ao cadastrar: ' . $e->getMessage()]));
        }
    }
}
