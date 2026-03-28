<?php

use App\Config\AppConfig;

$frontendUrl = null;
try {
    require_once __DIR__ . '/../app/config/AppConfig.php';
    $frontendUrl = AppConfig::get('frontendUrl', 'http://localhost:5173');
} catch (\Throwable $e) {
    $frontendUrl = 'http://localhost:5173';
}

$allowedOrigins = array_values(array_filter(array_unique([
    $frontendUrl,
    'http://localhost:5173',
    'http://127.0.0.1:5173'
])));

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '' && in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: {$origin}");
    header('Vary: Origin');
} elseif ($frontendUrl) {
    header("Access-Control-Allow-Origin: {$frontendUrl}");
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Webhook-Signature');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}
