<?php
require_once __DIR__ . '/../routes/Acess.php';
require_once __DIR__ . '/../routes/Route.php';
require_once __DIR__ . '/../routes/PageNotFound.php';

use App\Middleware\Security;
use Routes\Acess;
use Routes\Route;

$acess = new Acess();
$acess->GetAll();
Security::startSession();

$route = new Route();

$route->notFound(
    function() use ($route) {
        renderPageNotFound($route->getPath(), $_SERVER['REQUEST_METHOD'] ?? '', Security::getBasePath());
    },
    function($controllerClass) {
        renderControllerNotFound($controllerClass, Security::getBasePath());
    },
    function($controllerClass, $methodName) {
        renderMethodNotFound($controllerClass, $methodName, Security::getBasePath());
    }
);

$route->group('/home', function (Route $route) {
    $route->get('indexController@index')->name(['']);
});

$route->group('/auth', function (Route $route) {
    $route->post('LoginController@authenticate')->name(['login']);
    $route->get('LoginController@validate')->name(['validate']);
    $route->post('LogoutController@logout')->name(['logout']);
    $route->post('SignUpController@register')->name(['signup']);
});

$route->group('/payment', function (Route $route) {
    $route->post('PaymentWebhookController@handleWebhook')->name(['webhook']);
    $route->get('PaymentWebhookController@verificarPagamento')->name(['verificar']);
});

$route->group('/api', function (Route $route) {
    $route->get('DashboardController@getDashboardData')->name(['dashboard']);
    $route->get('EngagementController@getEngagementData')->name(['engagement']);
    $route->get('CalendarController@getCalendarData')->name(['calendar']);
    $route->post('CalendarController@schedulePost')->name(['calendar', 'schedule']);
    $route->get('AdsController@getAdsData')->name(['ads']);
    $route->post('AdsController@createCampaign')->name(['ads', 'create']);
    $route->post('AdsController@updateCampaign')->name(['ads', 'update']);
    $route->get('ReportsController@getReportsData')->name(['reports']);
    $route->get('ReportsController@exportReport')->name(['reports', 'export']);
    $route->get('RequestsController@getRequests')->name(['requests']);
    $route->post('RequestsController@createRequest')->name(['requests', 'create']);
    $route->get('ServiceStatusController@getServiceStatus')->name(['service-status']);
    $route->post('ServiceStatusController@createContent')->name(['service-status', 'create']);
    $route->post('ServiceStatusController@updateContentStatus')->name(['service-status', 'update']);
    $route->get('PlanController@getPlanData')->name(['plan']);
    $route->post('InstagramController@connectAccount')->name(['instagram', 'connect']);
    $route->get('InstagramController@getMetrics')->name(['instagram', 'metrics']);
    $route->get('InstagramController@getPosts')->name(['instagram', 'posts']);
    $route->get('InstagramController@getMetricsHistory')->name(['instagram', 'metrics', 'history']);
    $route->post('InstagramController@refreshToken')->name(['instagram', 'refresh']);
    $route->post('InstagramController@disconnectAccount')->name(['instagram', 'disconnect']);
    $route->get('InstagramController@getConnectionStatus')->name(['instagram', 'status']);
});

$route->execute();
