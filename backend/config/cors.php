<?php

$configPath = __DIR__ . '/config.json';
$config = json_decode(file_get_contents($configPath), true);
$allowedOrigin = $config['frontendUrl'] ?? 'http://localhost:5173';

header("Access-Control-Allow-Origin: $allowedOrigin");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
