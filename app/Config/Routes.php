<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// --- Маршруты для WEB-интерфейса ---
$routes->group('', ['namespace' => 'App\Controllers\Web'], function($routes) {
    $routes->get('/', 'Dashboard::index'); // Главная
    $routes->get('dashboard', 'Dashboard::index'); 
        $routes->get('beneficiaries', 'Beneficiaries::index');
    $routes->get('import', 'Import::index');
});

$routes->group('api', ['namespace' => 'App\Controllers\Api'], function($routes) {
    $routes->get('services', 'Services::index');
    $routes->post('services', 'Services::create'); // Для добавления (ТЗ п.5)
    $routes->delete('services/(:num)', 'Services::delete/$1'); // Для удаления (ТЗ п.6)
});
