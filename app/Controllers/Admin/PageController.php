<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Models\Page;
use RuntimeException;

final class PageController
{
    public static function index(Request $request, array $params): Response
    {
        if (($redirect = Auth::requireAdmin()) !== null) {
            return $redirect;
        }
        Session::start();

        return Response::html(View::render('admin/pages/index', [
            'rows' => Page::all(),
        ]));
    }

    public static function create(Request $request, array $params): Response
    {
        if (($redirect = Auth::requireAdmin()) !== null) {
            return $redirect;
        }
        Session::start();

        return Response::html(View::render('admin/pages/create', [
            'errors' => Session::getFlash('page_errors', []),
            'old' => Session::getFlash('_old', []),
        ]));
    }

    public static function store(Request $request, array $params): Response
    {
        if (($redirect = Auth::requireAdmin()) !== null) {
            return $redirect;
        }
        Session::start();

        if (!Csrf::verify($request)) {
            return new Response("CSRF token mismatch\n", 419, ['Content-Type' => 'text/plain; charset=utf-8']);
        }

        $data = [
            'title' => trim((string) $request->post('title', '')),
            'content' => (string) $request->post('content', ''),
            'seo_title' => trim((string) $request->post('seo_title', '')),
            'seo_description' => trim((string) $request->post('seo_description', '')),
        ];
        $errors = Validator::make($data, ['title' => 'required|max:255']);
        if ($data['seo_title'] === '') {
            $data['seo_title'] = null;
        }
        if ($data['seo_description'] === '') {
            $data['seo_description'] = null;
        }

        if ($errors !== []) {
            Session::flash('page_errors', $errors);
            Session::flash('_old', $data);

            return Response::redirect('/admin/pages/create', 302);
        }

        try {
            Page::create($data);
        } catch (RuntimeException $exception) {
            Session::flash('page_errors', ['title' => [$exception->getMessage()]]);

            return Response::redirect('/admin/pages/create', 302);
        }

        Session::flash('success', 'Sayfa oluşturuldu.');

        return Response::redirect('/admin/pages', 302);
    }

    public static function edit(Request $request, array $params): Response
    {
        if (($redirect = Auth::requireAdmin()) !== null) {
            return $redirect;
        }
        Session::start();

        $id = filter_var($params['id'] ?? '', FILTER_VALIDATE_INT);
        $row = $id === false ? [] : Page::find($id);
        if ($row === []) {
            return Response::html(View::render('errors/404'), 404);
        }

        return Response::html(View::render('admin/pages/edit', [
            'row' => $row,
            'errors' => Session::getFlash('page_errors', []),
            'old' => Session::getFlash('_old', []),
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

        $id = filter_var($params['id'] ?? '', FILTER_VALIDATE_INT);
        if ($id === false || Page::find($id) === []) {
            return Response::html(View::render('errors/404'), 404);
        }

        $data = [
            'title' => trim((string) $request->post('title', '')),
            'content' => (string) $request->post('content', ''),
            'seo_title' => trim((string) $request->post('seo_title', '')),
            'seo_description' => trim((string) $request->post('seo_description', '')),
        ];
        $errors = Validator::make($data, ['title' => 'required|max:255']);
        if ($data['seo_title'] === '') {
            $data['seo_title'] = null;
        }
        if ($data['seo_description'] === '') {
            $data['seo_description'] = null;
        }

        if ($errors !== []) {
            Session::flash('page_errors', $errors);
            Session::flash('_old', $data);

            return Response::redirect('/admin/pages/' . $id . '/edit', 302);
        }

        try {
            Page::update($id, $data);
        } catch (RuntimeException $exception) {
            Session::flash('page_errors', ['title' => [$exception->getMessage()]]);

            return Response::redirect('/admin/pages/' . $id . '/edit', 302);
        }

        Session::flash('success', 'Sayfa güncellendi.');

        return Response::redirect('/admin/pages', 302);
    }

    public static function delete(Request $request, array $params): Response
    {
        if (($redirect = Auth::requireAdmin()) !== null) {
            return $redirect;
        }
        Session::start();

        if ($request->method() !== 'POST') {
            return new Response("Method Not Allowed\n", 405, [
                'Content-Type' => 'text/plain; charset=utf-8',
                'Allow' => 'POST',
            ]);
        }
        if (!Csrf::verify($request)) {
            return new Response("CSRF token mismatch\n", 419, ['Content-Type' => 'text/plain; charset=utf-8']);
        }

        $id = filter_var($params['id'] ?? '', FILTER_VALIDATE_INT);
        if ($id === false || Page::find($id) === []) {
            return Response::html(View::render('errors/404'), 404);
        }

        Page::deleteById($id);
        Session::flash('success', 'Sayfa silindi.');

        return Response::redirect('/admin/pages', 302);
    }
}
