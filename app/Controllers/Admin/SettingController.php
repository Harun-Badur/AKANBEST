<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Setting;
use RuntimeException;

final class SettingController
{
    public static function index(Request $request, array $params): Response
    {
        if (($redirect = Auth::requireAdmin()) !== null) {
            return $redirect;
        }
        Session::start();

        return Response::html(View::render('admin/settings/index', [
            'rows' => Setting::all(),
            'errors' => Session::getFlash('setting_errors', []),
            'success' => Session::getFlash('success'),
        ]));
    }

    public static function update(Request $request, array $params): Response
    {
        if (($redirect = Auth::requireAdmin()) !== null) {
            return $redirect;
        }
        Session::start();

        if (!Csrf::verify($request)) {
            return new Response("CSRF token mismatch\n", 419, ['Content-Type' => 'text/plain; charset=utf-8']);
        }

        $key = (string) $request->post('key', '');
        $value = (string) $request->post('value', '');
        $errors = Validator::make(['value' => $value], ['value' => 'max:1000']);
        if ($errors !== []) {
            Session::flash('setting_errors', ['value' => ['max']]);

            return Response::redirect('/admin/settings', 302);
        }

        try {
            Setting::set($key, $value === '' ? null : $value);
        } catch (RuntimeException $exception) {
            Session::flash('setting_errors', ['key' => [$exception->getMessage()]]);

            return Response::redirect('/admin/settings', 302);
        }

        Session::flash('success', 'Ayar güncellendi.');

        return Response::redirect('/admin/settings', 302);
    }
}
