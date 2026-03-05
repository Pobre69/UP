<?php

namespace Controllers;

class LogoutController
{
    public function logout() {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        
        http_response_code(200);
        echo json_encode(['success' => true, 'mensagem' => 'Logout realizado com sucesso']);
    }
}
