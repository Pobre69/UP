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
        $password = $data['password'] ?? '';
        $instagram = trim($data['instagram'] ?? '');
        $segment = $data['segment'] ?? '';
        $city = trim($data['city'] ?? '');
        $mainGoal = $data['mainGoal'] ?? '';
        $competitors = trim($data['competitors'] ?? '');
        $driveLink = trim($data['driveLink'] ?? '');
        $attendant = $data['attendant'] ?? '';
        $planoSelecionado = $data['planoSelecionado'] ?? '';
        
        if (empty($fullName) || empty($email) || empty($password) || empty($segment) || empty($city) || empty($mainGoal) || empty($planoSelecionado)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Campos obrigatórios não preenchidos']);
            return;
        }
        
        if (strlen($password) < 6) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'A senha deve ter no mínimo 6 caracteres']);
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
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $usuarioRepo->add($email, $fullName, $hashedPassword, $companyValue, $planoSelecionado);
            $detalhesRepo->add($email, $mainGoal, $driveLink, $segment, $instagram, $attendant, $localizacaoJson);
            
            if (!empty($competitors)) {
                $concorrenteRepo->add($email, $competitors);
            }
            http_response_code(201);
            echo json_encode(['success' => true, 'mensagem' => 'Cadastro realizado com sucesso!']);
        } catch (\PDOException $e) {
            error_log('[SignUp] Erro PDO: ' . $e->getMessage());
            http_response_code(500);
            
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo json_encode(['success' => false, 'mensagem' => 'Este e-mail já está cadastrado']);
            } else {
                echo json_encode(['success' => false, 'mensagem' => 'Erro ao realizar cadastro. Tente novamente.']);
            }
        } catch (\Exception $e) {
            error_log('[SignUp] Erro: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => 'Erro ao realizar cadastro. Tente novamente.']);
        }
    }
}