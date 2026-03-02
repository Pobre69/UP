<?php

namespace Controllers;

use App\Repository\UsuarioRepository;
use App\Repository\UsuarioDetalhesRepository;
use App\Repository\ConcorrenteRepository;
use App\Middleware\Security;

class SignUpController
{
    public function register() {
        error_log("[SignUp] Iniciando registro");
        header('Content-Type: application/json');
        
        $rawInput = file_get_contents('php://input');
        error_log("[SignUp] Input recebido: " . $rawInput);
        
        $data = json_decode($rawInput, true);
        
        if (!$data) {
            error_log("[SignUp] ERRO: JSON inválido");
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Dados inválidos']);
            return;
        }
        
        error_log("[SignUp] Dados parseados: " . json_encode($data));
        
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
            error_log("[SignUp] ERRO: Campos obrigatórios vazios");
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Campos obrigatórios não preenchidos']);
            return;
        }
        
        if (strlen($password) < 6) {
            error_log("[SignUp] ERRO: Senha muito curta");
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Senha deve ter no mínimo 6 caracteres']);
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            error_log("[SignUp] ERRO: Email inválido: " . $email);
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'E-mail inválido']);
            return;
        }
        
        try {
            error_log("[SignUp] Iniciando transação no banco");
            
            $usuarioRepo = new UsuarioRepository();
            $detalhesRepo = new UsuarioDetalhesRepository();
            $concorrenteRepo = new ConcorrenteRepository();
            
            $companyValue = !empty($company) ? $company : 'Não informado';
            $localizacaoJson = json_encode(['cidade' => $city]);
            $instagramValue = !empty($instagram) ? $instagram : null;
            $driveLinkValue = !empty($driveLink) ? $driveLink : null;
            $attendantValue = !empty($attendant) ? $attendant : null;
            
            error_log("[SignUp] Adicionando usuário: " . $email);
            $resultUsuario = $usuarioRepo->add($email, $fullName, $hashedPassword, $companyValue);
            error_log("[SignUp] Resultado usuário: " . json_encode($resultUsuario));
            
            if (isset($resultUsuario[0]['RESULTADO']) && strpos($resultUsuario[0]['RESULTADO'], 'sucesso') === false) {
                error_log("[SignUp] ERRO: Falha ao adicionar usuário");
                http_response_code(400);
                echo json_encode(['success' => false, 'mensagem' => $resultUsuario[0]['RESULTADO']]);
                return;
            }
            
            error_log("[SignUp] Adicionando detalhes");
            $detalhesRepo->add($email, $mainGoal, $driveLinkValue, $segment, $instagramValue, $attendantValue, $localizacaoJson);
            
            if (!empty($competitors)) {
                error_log("[SignUp] Adicionando concorrentes");
                $concorrenteRepo->add($email, $competitors);
            }
            
            // Criar sessão automaticamente
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $fullName;
            $_SESSION['logged_in'] = true;
            
            error_log("[SignUp] Cadastro concluído com sucesso e sessão criada");
            http_response_code(201);
            echo json_encode([
                'success' => true, 
                'mensagem' => 'Cadastro realizado com sucesso!',
                'redirect' => '/app'
            ]);
        } catch (\Exception $e) {
            error_log("[SignUp] EXCEÇÃO: " . $e->getMessage());
            error_log("[SignUp] Stack: " . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => 'Erro ao cadastrar: ' . $e->getMessage()]);
        }
    }
}
