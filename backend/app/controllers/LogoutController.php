<?php

namespace Controllers;

use App\Middleware\Security;

class LogoutController
{
    public function logout(): void
    {
        header('Content-Type: application/json');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

        Security::destroySession();

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'mensagem' => 'Logout realizado com sucesso'
        ]);
    }
}
