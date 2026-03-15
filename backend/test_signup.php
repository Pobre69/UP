<?php
// Teste simples para verificar se o endpoint de cadastro está funcionando

$url = 'http://localhost/Sites/UP/backend/public/index.php/api';

$data = [
    'fullName' => 'Teste Usuario',
    'company' => 'Empresa Teste',
    'email' => 'teste@teste.com',
    'password' => '123456',
    'instagram' => '@teste',
    'segment' => 'tecnologia-saas',
    'city' => 'São Paulo - SP',
    'mainGoal' => 'mais-clientes-vendas',
    'competitors' => 'Concorrente 1, Concorrente 2',
    'driveLink' => 'https://drive.google.com/test',
    'attendant' => 'arthur-valentim'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

echo "Enviando requisição para: $url\n";
echo "Dados: " . json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($error) {
    echo "Erro cURL: $error\n";
}
echo "Resposta: $response\n";
