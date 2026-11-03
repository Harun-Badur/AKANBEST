<?php

declare(strict_types=1);

// PHP's built-in server uses this file as the router. Existing public files
// (uploads, assets) must be returned as-is; Apache already skips them via .htaccess.
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if (is_string($path) && !str_contains($path, '..') && is_file(__DIR__ . $path)) {
        return false;
    }
}

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
