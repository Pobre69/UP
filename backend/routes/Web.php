<?php

require_once __DIR__ . "/Acess.php";
require_once __DIR__ . "/Route.php";
require_once __DIR__ . "/PageNotFound.php";

use Routes\Acess;
use Routes\Route;
use App\Middleware\Security;

$acess = new Acess();
$acess->GetAll();
$security = new Security();
$security->startSession();

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
    $route->post('indexController@index')->name(['']);
});

$route->group('/api', function (Route $route) {
    $route->post('SignUpController@register')->name(['']);
});

$route->execute();