<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('STORAGE_PATH', BASE_PATH . '/storage');

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});

require_once BASE_PATH . '/app/Core/Helpers.php';

error_reporting(E_ALL);
// The central handler owns all output, including local diagnostics.
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', STORAGE_PATH . '/logs/app.log');
ini_set('zend.exception_ignore_args', '1');

$handleFailure = static function (Throwable $exception): void {
    static $handling = false;
    if ($handling) {
        return;
    }
    $handling = true;

    // Keep each entry on one line, even when an exception message contains newlines.
    $writeLog = static function (Throwable $error): void {
        $entry = sprintf(
            '[%s] %s: %s in %s:%d | %s',
            gmdate('c'),
            $error::class,
            $error->getMessage(),
            $error->getFile(),
            $error->getLine(),
            $error->getTraceAsString(),
        );
        @error_log(str_replace(["\r", "\n"], ['\\r', '\\n'], $entry) . PHP_EOL, 3, STORAGE_PATH . '/logs/app.log');
    };

    $writeLog($exception);
    while (ob_get_level() > 0) {
        if (!@ob_end_clean()) {
            break;
        }
    }

    try {
        $data = ['local' => App\Core\Env::get('APP_ENV', 'production') === 'local'];
        if ($data['local']) {
            $data['message'] = $exception->getMessage();
            $data['trace'] = $exception->getTraceAsString();
        }
        $response = App\Core\Response::html(App\Core\View::render('errors/500', $data), 500);
        if (!headers_sent()) {
            header_remove('Location');
            $response->send();
        } else {
            echo $response->body;
        }
    } catch (Throwable $renderError) {
        $writeLog($renderError);
        if (!headers_sent()) {
            http_response_code(500);
            header_remove('Location');
            header('Content-Type: text/plain; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
        }
        echo "500 - Internal Server Error\n";
    }
};

set_exception_handler($handleFailure);
set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});
register_shutdown_function(static function () use ($handleFailure): void {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        $handleFailure(new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
    }
});

App\Core\Env::load(BASE_PATH . '/.env');

// Session wiring: cookie parameters are fixed before any session_start().
// Session::start() itself is idempotent and called lazily at access points.
// Env is used directly because Config::load() runs later in the front controller.
if (PHP_SAPI !== 'cli') {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => App\Core\Env::get('APP_ENV', 'production') !== 'local',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_name('akanbest_session');
}
