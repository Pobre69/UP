<?php

require_once __DIR__ . "/../routes/Acess.php";
require_once __DIR__ . "/../routes/Route.php";
require_once __DIR__ . "/../routes/PageNotFound.php";

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
    $route->get('indexController@index')->name([''])->parametros([]);
});

$route->group('/auth', function (Route $route) {
    $route->post('LoginController@authenticate')->name(['login']);
});

$route->group('/api', function (Route $route) {
    $route->post('SignUpController@register')->name(['']);
    
    // Dashboard
    $route->get('DashboardController@getDashboardData')->name(['dashboard']);
    
    // Engagement
    $route->get('EngagementController@getEngagementData')->name(['engagement']);
    
    // Calendar
    $route->get('CalendarController@getCalendarData')->name(['calendar']);
    $route->post('CalendarController@schedulePost')->name(['calendar/schedule']);
    
    // Ads
    $route->get('AdsController@getAdsData')->name(['ads']);
    $route->post('AdsController@createCampaign')->name(['ads/create']);
    $route->post('AdsController@updateCampaign')->name(['ads/update']);
    
    // Reports
    $route->get('ReportsController@getReportsData')->name(['reports']);
    $route->get('ReportsController@exportReport')->name(['reports/export']);
    
    // Requests
    $route->get('RequestsController@getRequests')->name(['requests']);
    $route->post('RequestsController@createRequest')->name(['requests/create']);
    
    // Service Status
    $route->get('ServiceStatusController@getServiceStatus')->name(['service-status']);
    $route->post('ServiceStatusController@createContent')->name(['service-status/create']);
    $route->post('ServiceStatusController@updateContentStatus')->name(['service-status/update']);
    
    // Plan
    $route->get('PlanController@getPlanData')->name(['plan']);
    
    // Instagram API Routes
    $route->post('InstagramController@connectAccount')->name(['instagram/connect']);
    $route->get('InstagramController@getMetrics')->name(['instagram/metrics']);
    $route->get('InstagramController@getPosts')->name(['instagram/posts']);
    $route->get('InstagramController@getMetricsHistory')->name(['instagram/metrics/history']);
    $route->post('InstagramController@refreshToken')->name(['instagram/refresh']);
});

$route->execute();

/*
agora eu gostaria de pedir algo que vai possuir muita demanda, logo tome cuidado. quero que vai na pasta frontend e abra todas as paginas do tipo /app e uma por uma (antes de fazer o proximo me pergunte se pode continuar) e verifique as informações que são necessarias puxar do banco de dados e quero que, verifique se essas informações estão salvas dentro do banco de dados, verifique se existe uma controller para gerenciar e enviar essas informações de forma correta para o front, e se a segurança esta ok */