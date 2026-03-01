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
    $route->get('indexController@index')->name(['']);
});

$route->group('/auth', function (Route $route) {
    $route->get('LoginController@authenticate')->name(['']);
});

$route->group('/api', function (Route $route) {
    $route->post('SignUpController@register')->name(['']);
    
    
    // Dashboard
    $route->get('DashboardController@getDashboardData')->name(['dashboard']);
    
    // Engagement
    $route->get('EngagementController@getEngagementData')->name(['engagement']);
    
    // Calendar
    $route->get('CalendarController@getCalendarData')->name(['calendar']);
    $route->post('CalendarController@schedulePost')->name(['calendar', 'schedule']);
    
    // Ads
    $route->get('AdsController@getAdsData')->name(['ads']);
    $route->post('AdsController@createCampaign')->name(['ads', 'create']);
    $route->post('AdsController@updateCampaign')->name(['ads', 'update']);
    
    // Reports
    $route->get('ReportsController@getReportsData')->name(['reports']);
    $route->get('ReportsController@exportReport')->name(['reports', 'export']);
    
    // Requests
    $route->get('RequestsController@getRequests')->name(['requests']);
    $route->post('RequestsController@createRequest')->name(['requests', 'create']);
    
    // Service Status
    $route->get('ServiceStatusController@getServiceStatus')->name(['service-status']);
    $route->post('ServiceStatusController@createContent')->name(['service-status', 'create']);
    $route->post('ServiceStatusController@updateContentStatus')->name(['service-status', 'update']);
    
    // Plan
    $route->get('PlanController@getPlanData')->name(['plan']);
    
    // Instagram API Routes
    $route->post('InstagramController@connectAccount')->name(['instagram', 'connect']);
    $route->get('InstagramController@getMetrics')->name(['instagram', 'metrics']);
    $route->get('InstagramController@getPosts')->name(['instagram', 'posts']);
    $route->get('InstagramController@getMetricsHistory')->name(['instagram', 'metrics', 'history']);
    $route->post('InstagramController@refreshToken')->name(['instagram', 'refresh']);
});

$route->execute();