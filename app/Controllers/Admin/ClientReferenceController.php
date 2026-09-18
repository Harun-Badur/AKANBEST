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
use App\Models\ClientReference;
use RuntimeException;

final class ClientReferenceController
{
    private const UPLOAD_DIR = BASE_PATH . '/public/uploads/references';
    private const ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    private const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public static function index(Request $request, array $params): Response
    {
        if (($redirect = Auth::requireAdmin()) !== null) {
            return $redirect;
        }
        Session::start();

        return Response::html(View::render('admin/client-references/index', [
            'rows' => ClientReference::all(),
        ]));
    }

    public static function create(Request $request, array $params): Response
    {
        if (($redirect = Auth::requireAdmin()) !== null) {
            return $redirect;
        }
        Session::start();

        return Response::html(View::render('admin/client-references/create', [
            'errors' => Session::getFlash('cr_errors', []),
            'old' => Session::getFlash('_old', []),
        ]));
    }

    private static function normalise(Request $request): array
    {
        $sortRaw = trim((string) $request->post('sort_order', ''));
        $website = trim((string) $request->post('website_url', ''));

        return [
            'company_name' => trim((string) $request->post('company_name', '')),
            'website_url' => $website === '' ? null : $website,
            'sort_order' => $sortRaw === '' ? 0 : (int) $sortRaw,
            'is_active' => $request->post('is_active') !== null ? 1 : 0,
        ];
    }

    private static function storeUpload(Request $request): ?string
    {
        $file = $_FILES['logo'] ?? null;
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($file['error'] ?? 1) !== UPLOAD_ERR_OK || !is_uploaded_file((string) ($file['tmp_name'] ?? ''))) {
            throw new RuntimeException('Yükleme hatası; dosya kabul edilmedi.');
        }

        $ext = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXT, true)) {
            throw new RuntimeException('Geçersiz dosya uzantısı.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file((string) $file['tmp_name']);
        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            throw new RuntimeException('Geçersiz dosya türü.');
        }

        if (!is_dir(self::UPLOAD_DIR) && !@mkdir(self::UPLOAD_DIR, 0775, true) && !is_dir(self::UPLOAD_DIR)) {
            throw new RuntimeException('Yükleme dizini oluşturulamadı.');
        }

        $storedName = gmdate('YmdHis') . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
        $target = self::UPLOAD_DIR . '/' . $storedName;
        if (!move_uploaded_file((string) $file['tmp_name'], $target)) {
            throw new RuntimeException('Dosya kaydedilemedi.');
        }

        return 'uploads/references/' . $storedName;
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

        $norm = self::normalise($request);
        $errors = Validator::make($norm, ['company_name' => 'required|max:255']);
        if ($norm['website_url'] !== null && !filter_var($norm['website_url'], FILTER_VALIDATE_URL)) {
            $errors['website_url'][] = 'url';
        }

        if ($errors !== []) {
            Session::flash('cr_errors', $errors);
            Session::flash('_old', $norm);

            return Response::redirect('/admin/client-references/create', 302);
        }

        try {
            $logoPath = self::storeUpload($request);
        } catch (RuntimeException $exception) {
            Session::flash('cr_errors', ['logo' => [$exception->getMessage()]]);

            return Response::redirect('/admin/client-references/create', 302);
        }

        ClientReference::create($norm + ['logo_path' => $logoPath]);
        Session::flash('success', 'Referans oluşturuldu.');

        return Response::redirect('/admin/client-references', 302);
    }

    public static function edit(Request $request, array $params): Response
    {
        if (($redirect = Auth::requireAdmin()) !== null) {
            return $redirect;
        }
        Session::start();

        $id = filter_var($params['id'] ?? '', FILTER_VALIDATE_INT);
        $row = $id === false ? [] : ClientReference::find($id);
        if ($row === []) {
            return Response::html(View::render('errors/404'), 404);
        }

        return Response::html(View::render('admin/client-references/edit', [
            'row' => $row,
            'errors' => Session::getFlash('cr_errors', []),
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
        if ($id === false || ClientReference::find($id) === []) {
            return Response::html(View::render('errors/404'), 404);
        }

        $norm = self::normalise($request);
        $errors = Validator::make($norm, ['company_name' => 'required|max:255']);
        if ($errors !== []) {
            Session::flash('cr_errors', $errors);
            Session::flash('_old', $norm);

            return Response::redirect('/admin/client-references/' . $id . '/edit', 302);
        }

        try {
            $logoPath = self::storeUpload($request);
        } catch (RuntimeException $exception) {
            Session::flash('cr_errors', ['logo' => [$exception->getMessage()]]);

            return Response::redirect('/admin/client-references/' . $id . '/edit', 302);
        }

        ClientReference::updateById($id, $norm + ($logoPath !== null ? ['logo_path' => $logoPath] : []));
        Session::flash('success', 'Referans güncellendi.');

        return Response::redirect('/admin/client-references', 302);
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
        if ($id === false || ClientReference::find($id) === []) {
            return Response::html(View::render('errors/404'), 404);
        }

        ClientReference::deleteById($id);
        Session::flash('success', 'Referans silindi.');

        return Response::redirect('/admin/client-references', 302);
    }
}
