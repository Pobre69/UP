<?php

require_once __DIR__ . "/Acess.php";
require_once __DIR__ . "/Security.php";
require_once __DIR__ . "/Route.php";
require_once __DIR__ . "/PageNotFound.php";

use Routes\Acess;
use Routes\Route;
use Routes\Security;
use DataBase\Connection\DB_Connection;

$acess = new Acess();
$acess->GetAll();
$security = new Security();
$security->startSession();
$db_connection = new DB_Connection();
$db_connection->Start_DataBase($acess->sqlAcess());

$route = new Route();
$route->notFound(
    function() use ($route, $security) {
        renderPageNotFound(
            $route->getPath(),
            $_SERVER['REQUEST_METHOD'] ?? '',
            $security->getBasePath()
        );
    },
    function($controllerClass) use ($route, $security) {
        renderControllerNotFound(
            $controllerClass,
            $security->getBasePath()
        );
    },
    function($controllerClass, $methodName) use ($route, $security) {
        renderMethodNotFound(
            $controllerClass,
            $methodName,
            $security->getBasePath()
        );
    }
);

$route->group('/Home', function (Route $route) {
    $route->get('indexControler@index')->name([''])->parametros([]);
});

$route->execute();