<?php

declare(strict_types=1);

use App\Core\Env;

return [
    'app' => [
        'name' => 'Akan Best Ambalaj',
        'env' => Env::get('APP_ENV', 'production'),
        'debug' => filter_var(Env::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN),
        'url' => Env::get('APP_URL', 'http://localhost:8000'),
    ],
    // M0: placeholders only; no database connection is created.
    'db' => [
        'host' => Env::get('DB_HOST', '127.0.0.1'),
        'port' => (int) Env::get('DB_PORT', '3306'),
        'database' => Env::get('DB_DATABASE', 'akanbest_local'),
        'username' => Env::get('DB_USERNAME', 'your_db_username'),
        'password' => Env::get('DB_PASSWORD', 'your_db_password'),
    ],
];
