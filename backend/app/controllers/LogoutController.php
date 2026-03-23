<?php
namespace Controllers;

class LogoutController
{
    public function logout() {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Limpar todas as variáveis de sessão
        $_SESSION = [];
        
        // Destruir o cookie da sessão
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        // Destruir a sessão
        session_destroy();
        
        // Limpar headers de cache para evitar que a página logada seja cacheada
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'mensagem' => 'Logout realizado com sucesso'
        ]);
    }
}
