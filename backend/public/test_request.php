<?php
// Teste simples para verificar a criação de solicitação

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/database/database.php';

use DataBase\Connection\database;

try {
    // Configurar conexão
    $db = new database();
    $db->setConnection('localhost', 'root', '', 'UP');
    
    // Dados de teste
    $email = 'test@test.com';
    $titulo = 'Teste de Solicitação';
    $tipo = 'Feedback';
    $texto = 'Texto de teste';
    
    // Tentar inserir
    $conn = database::getConnection();
    $stmt = $conn->prepare(
        'INSERT INTO feedBack (usuario_email, titulo, tipo, texto) 
         VALUES (:email, :titulo, :tipo, :texto)'
    );
    
    $result = $stmt->execute([
        ':email' => $email,
        ':titulo' => $titulo,
        ':tipo' => $tipo,
        ':texto' => $texto
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'mensagem' => 'Teste bem-sucedido!',
            'id' => $conn->lastInsertId()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'mensagem' => 'Falha ao inserir'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'mensagem' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
