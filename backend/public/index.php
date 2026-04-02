<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../app/config/AppConfig.php';

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (is_file($autoloadPath)) {
    require_once $autoloadPath;
}

use App\Config\AppConfig;
use DataBase\Connection\database;

try {
    if (!extension_loaded('pdo_mysql')) {
        throw new RuntimeException('A extensão pdo_mysql do PHP não está habilitada.');
    }

    $dbConfig = AppConfig::get('database.UP');
    if (!is_array($dbConfig)) {
        throw new RuntimeException('Configuração de banco ausente.');
    }

    require_once __DIR__ . '/../config/database/database.php';

    $db = new database();
    $db->setConnection(
        (string)($dbConfig['host'] ?? 'localhost'),
        (string)($dbConfig['username'] ?? 'root'),
        (string)($dbConfig['password'] ?? ''),
        (string)($dbConfig['database'] ?? 'UP')
    );

    require_once __DIR__ . '/../routes/Web.php';
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'mensagem' => 'Falha ao iniciar o backend.',
        'codigo' => 'BOOTSTRAP_ERROR'
    ]);
    error_log('[Bootstrap] ' . $e->getMessage());
}
