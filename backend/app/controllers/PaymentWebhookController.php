<?php

namespace Controllers;

use App\Repository\UsuarioRepository;

class PaymentWebhookController
{
    public function handleWebhook() {
        header('Content-Type: application/json');
        
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        
        error_log('[Webhook] Dados recebidos: ' . $rawInput);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Dados inválidos']);
            return;
        }
        
        $status = $data['status'] ?? '';
        $customerEmail = $data['customer']['email'] ?? '';
        
        if (empty($customerEmail)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Email não fornecido']);
            return;
        }
        
        if ($status === 'paid' || $status === 'approved') {
            try {
                $usuarioRepo = new UsuarioRepository();
                $usuario = $usuarioRepo->getByEmail($customerEmail);
                
                if (!$usuario) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'mensagem' => 'Usuário não encontrado']);
                    return;
                }
                
                $usuarioRepo->ativarConta($customerEmail);
                
                error_log('[Webhook] Conta ativada para: ' . $customerEmail);
                
                http_response_code(200);
                echo json_encode(['success' => true, 'mensagem' => 'Conta ativada com sucesso']);
            } catch (\Exception $e) {
                error_log('[Webhook] Erro: ' . $e->getMessage());
                http_response_code(500);
                echo json_encode(['success' => false, 'mensagem' => 'Erro ao ativar conta']);
            }
        } else {
            http_response_code(200);
            echo json_encode(['success' => true, 'mensagem' => 'Status não requer ação']);
        }
    }
    
    public function verificarPagamento() {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $email = $_SESSION['user_email'] ?? '';
        
        if (empty($email)) {
            http_response_code(401);
            echo json_encode(['success' => false, 'mensagem' => 'Não autenticado']);
            return;
        }
        
        try {
            $usuarioRepo = new UsuarioRepository();
            $usuario = $usuarioRepo->getByEmail($email);
            
            if (!$usuario) {
                http_response_code(404);
                echo json_encode(['success' => false, 'mensagem' => 'Usuário não encontrado']);
                return;
            }
            
            $ativo = $usuario['ativo'] ?? false;
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'ativo' => $ativo,
                'planoSelecionado' => $usuario['plano_selecionado'] ?? null
            ]);
        } catch (\Exception $e) {
            error_log('[Verificar Pagamento] Erro: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => 'Erro ao verificar status']);
        }
    }
}
