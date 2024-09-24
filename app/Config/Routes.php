<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
  $routes->get('invalid-access', 'Auth\AuthController::accessDenied');

  // Auth Routes
  $routes->group('auth', function ($routes) {
    $routes->post('register', 'Auth\AuthController::register');
    $routes->post('login', 'Auth\AuthController::login');
    $routes->get('logout', 'Auth\AuthController::logout', ['filter' => ['tokens', 'api-auth']]);
  });

  // Routes yang memerlukan tokens
  $routes->group('', ['filter' => 'tokens', 'api-auth'], function ($routes) {
    $routes->get('profile', 'UserController::profile');

    $routes->group('projects', function ($routes) {
      $routes->get('', 'ProjectController::list');
      $routes->post('store', 'ProjectController::store');
      $routes->delete('(:num)', 'ProjectController::destroy/$1');
    });
  });
});
