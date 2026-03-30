<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// --- Маршруты для WEB-интерфейса ---
$routes->group('', ['namespace' => 'App\Controllers\Web'], function($routes) {
    $routes->get('/', 'Dashboard::index'); // Главная
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('services', 'Dashboard::index'); 
    $routes->get('beneficiaries', 'Beneficiaries::index');
    $routes->get('import', 'Import::index');
});

$routes->group('api', ['namespace' => 'App\Controllers\Api'], function($routes) {
    $routes->get('service-types', 'ServiceTypes::index');

    $routes->get('services', 'Services::index');
    $routes->post('services', 'Services::create'); // Для добавления (ТЗ п.5)
    $routes->resource('services', ['controller' => 'Services']);
    
    $routes->get('export', 'Excel::index');
    $routes->post('import', 'Excel::upload');
    $routes->get('export/template', 'Excel::template')
    ;
    $routes->post('ask', 'AiController::aiSearch');
    $routes->post('bro', 'AiController::broChat');

    $routes->get('beneficiaries', 'Beneficiary::index');
    $routes->get('beneficiaries/search', 'Beneficiary::search');
    $routes->resource('beneficiaries', ['controller' => 'Beneficiary']);
    

    $routes->post('clientLog', 'LogController::clientLog');

});
