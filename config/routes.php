<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

$health = static fn (Request $request, array $params): Response => Response::json([
    'status' => 'ok',
    'app' => Config::get('app.name'),
    'environment' => Config::get('app.env'),
    'php_version' => PHP_VERSION,
    'milestone' => 'M0',
    'timestamp' => gmdate('c'),
    'router' => 'M1',
]);

$routes = [
    ['GET', '/health', $health],
    ['GET', '/healthz', $health],
    ['GET', '/', static fn (Request $request, array $params): Response =>
        Response::html(View::render('public/placeholder', ['app' => Config::get('app.name')]))],
];

// Temporary kernel routes: remove in M8. The literal fail route must precede {token}.
if (Config::get('app.env') === 'local') {
    $routes[] = ['GET', '/kernel-test/fail', static function (Request $request, array $params): Response {
        throw new RuntimeException('M1 handler test');
    }];
}

$routes[] = ['GET', '/kernel-test/{token}', static fn (Request $request, array $params): Response =>
    Response::json(['token' => $params['token']])];

return $routes;
