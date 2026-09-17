<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Config;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
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

$loginView = static function (Request $request, array $params): Response {
    Session::start();

    return Response::html(View::render('admin/login', [
        'errors' => Session::getFlash('login_errors', []),
        'old' => Session::getFlash('_old', []),
        'notice' => Session::getFlash('guard_redirect') === 'login_required' ? 'Bu sayfa için giriş yapmalısınız.' : '',
    ]));
};

$routes = [
    ['GET', '/health', $health],
    ['GET', '/healthz', $health],
    ['GET', '/', static fn (Request $request, array $params): Response =>
        Response::html(View::render('public/placeholder', ['app' => Config::get('app.name')]))],

    ['GET', '/admin/login', $loginView],
    ['POST', '/admin/login', static function (Request $request, array $params): Response {
        Session::start();

        if (!Csrf::verify($request)) {
            return new Response("CSRF token mismatch\n", 419, ['Content-Type' => 'text/plain; charset=utf-8']);
        }

        $data = ['email' => (string) $request->post('email', ''), 'password' => (string) $request->post('password', '')];
        $errors = Validator::make($data, [
            'email' => 'required|email|max:255',
            'password' => 'required|min:6|max:255',
        ]);

        if ($errors !== []) {
            Validator::old($data);
            Session::flash('login_errors', $errors);

            return Response::redirect('/admin/login', 302);
        }

        if (Auth::attempt($data['email'], $data['password'])) {
            return Response::redirect('/admin/guard-test', 302);
        }

        Session::flash('login_errors', ['email' => ['credentials']]);
        Session::flash('_old', ['email' => $data['email']]);

        return Response::redirect('/admin/login', 302);
    }],
    ['POST', '/admin/logout', static function (Request $request, array $params): Response {
        Auth::logout();

        return Response::redirect('/admin/login', 302);
    }],
    ['GET', '/admin/guard-test', static function (Request $request, array $params): Response {
        $redirect = Auth::requireAdmin();
        if ($redirect !== null) {
            return $redirect;
        }

        return Response::json(['protected' => true, 'admin' => Auth::user()['admin_name']]);
    }],
];

// Temporary kernel routes: remove in M8. The literal fail route must precede {token}.
if (Config::get('app.env') === 'local') {
    $routes[] = ['GET', '/kernel-test/fail', static function (Request $request, array $params): Response {
        throw new RuntimeException('M1 handler test');
    }];

    // Server-side session seed for guard proof (M8 debt family). Fixed dummy
    // values only; no user input is read. Local environments exclusively.
    $routes[] = ['GET', '/kernel-test/session-seed', static function (Request $request, array $params): Response {
        Session::start();
        Session::set('admin_id', 1);
        Session::set('admin_name', 'Test Admin');

        return Response::json(['seeded' => true]);
    }];
}

$routes[] = ['GET', '/kernel-test/{token}', static fn (Request $request, array $params): Response =>
    Response::json(['token' => $params['token']])];

return $routes;
