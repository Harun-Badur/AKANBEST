<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$config = require BASE_PATH . '/config/config.php';

error_reporting(E_ALL);
ini_set('display_errors', $config['app']['env'] === 'local' ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', STORAGE_PATH . '/logs/app.log');

header('X-Content-Type-Options: nosniff');

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if ($path === '/health' || $path === '/healthz') {
    http_response_code(200);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'ok',
        'app' => $config['app']['name'],
        'environment' => $config['app']['env'],
        'php_version' => PHP_VERSION,
        'milestone' => 'M0',
        'timestamp' => gmdate('c'),
    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo "Not Found\n";
