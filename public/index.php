<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Request;
use App\Core\Router;

require_once dirname(__DIR__) . '/app/bootstrap.php';

Config::load(BASE_PATH . '/config/config.php');
$request = Request::capture();
$routes = require BASE_PATH . '/config/routes.php';
$router = new Router();

foreach ($routes as [$method, $pattern, $handler]) {
    match ($method) {
        'GET' => $router->get($pattern, $handler),
        'POST' => $router->post($pattern, $handler),
        default => throw new InvalidArgumentException('Unsupported route registration method.'),
    };
}

$router->dispatch($request)->send();
