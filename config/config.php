<?php

declare(strict_types=1);

use App\Core\Env;

$smtpPort = trim((string) Env::get('SMTP_PORT', ''));

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
    'mail' => [
        'to' => trim((string) Env::get('QUOTE_MAIL_TO', '')),
        'from' => trim((string) Env::get('QUOTE_MAIL_FROM', '')),
        'smtp_host' => trim((string) Env::get('SMTP_HOST', '')),
        'smtp_port' => $smtpPort === '' ? 465 : (int) $smtpPort,
        'smtp_user' => trim((string) Env::get('SMTP_USER', '')),
        'smtp_pass' => trim((string) Env::get('SMTP_PASS', '')),
    ],
    'contact' => [
        'whatsapp' => trim((string) Env::get('WHATSAPP_NUMBER', '')),
        'phone' => trim((string) Env::get('CONTACT_PHONE', '')),
        'email' => trim((string) Env::get('CONTACT_EMAIL', '')),
        'address' => trim((string) Env::get('CONTACT_ADDRESS', '')),
    ],
];
