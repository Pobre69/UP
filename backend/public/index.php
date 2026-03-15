<?php

require_once __DIR__ . '/../config/cors.php';

$configPath = __DIR__ . '/../config/config.json';
$config = json_decode(file_get_contents($configPath), true);
$dbConfig = $config['database']['UP'];

require_once __DIR__ . '/../config/database/database.php';
use DataBase\Connection\database;

$db = new database();
$db->setConnection(
    $dbConfig['host'],
    $dbConfig['username'],
    $dbConfig['password'],
    $dbConfig['database']
);

require_once __DIR__ . '/../routes/Web.php';
