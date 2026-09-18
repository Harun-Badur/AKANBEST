<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Models\Category;
use RuntimeException;

/**
 * Admin category CRUD. All actions require an authenticated admin.
 * Form shapes are normalised here; Validator rules stay within the M3 set.
 */
final class CategoryController
{
    public static function dashboard(Request $request, array $params): Response
    {
        $redirect = Auth::requireAdmin();
        if ($redirect !== null) {
            return $redirect;
        }

        return Response::html(View::render('admin/dashboard', [
            'adminName' => Auth::user()['admin_name'],
        ]));
    }

    public static function index(Request $request, array $params): Response
    {
        $redirect = Auth::requireAdmin();
        if ($redirect !== null) {
            return $redirect;
        }

        return Response::html(View::render('admin/categories/index', [
            'rows' => Category::all(),
            'tree' => Category::tree(Category::all()),
        ]));
    }

    public static function create(Request $request, array $params): Response
    {
        $redirect = Auth::requireAdmin();
        if ($redirect !== null) {
            return $redirect;
        }
        Session::start();

        return Response::html(View::render('admin/categories/create', [
            'tree' => Category::tree(Category::all()),
            'errors' => Session::getFlash('category_errors', []),
            'old' => Session::getFlash('_old', []),
        ]));
    }

    /** @return array{parent_id:?int, sort_order:int, is_active:int} */
    private static function normalise(Request $request): array
    {
        $parentRaw = trim((string) $request->post('parent_id', ''));
        $parentId = null;
        if ($parentRaw !== '') {
            $parentId = filter_var($parentRaw, FILTER_VALIDATE_INT);
            $parentId = $parentId === false ? -1 : $parentId;
        }

        $sortRaw = trim((string) $request->post('sort_order', ''));
        $sortOrder = $sortRaw === '' ? 0 : (int) $sortRaw;

        return [
            'parent_id' => $parentId,
            'sort_order' => $sortOrder,
            'is_active' => $request->post('is_active') !== null ? 1 : 0,
        ];
    }

    public static function store(Request $request, array $params): Response
    {
        $redirect = Auth::requireAdmin();
        if ($redirect !== null) {
            return $redirect;
        }
        Session::start();

        if (!Csrf::verify($request)) {
            return new Response("CSRF token mismatch\n", 419, ['Content-Type' => 'text/plain; charset=utf-8']);
        }

        $norm = self::normalise($request);
        $data = [
            'name' => trim((string) $request->post('name', '')),
            'description' => trim((string) $request->post('description', '')),
        ];
        $errors = Validator::make($data, [
            'name' => 'required|max:191',
            'description' => 'max:1000',
        ]);

        if ($norm['parent_id'] === -1) {
            $errors['parent_id'][] = 'in';
        }

        if ($errors !== []) {
            Session::flash('category_errors', $errors);
            Session::flash('_old', [
                'name' => $data['name'],
                'description' => $data['description'],
                'parent_id' => (string) $request->post('parent_id', ''),
                'sort_order' => (string) $request->post('sort_order', ''),
                'is_active' => $norm['is_active'],
            ]);

            return Response::redirect('/admin/categories/create', 302);
        }

        try {
            Category::create($data + $norm);
        } catch (RuntimeException $exception) {
            Session::flash('category_errors', ['name' => [$exception->getMessage()]]);
            Session::flash('_old', ['name' => $data['name'], 'description' => $data['description']]);

            return Response::redirect('/admin/categories/create', 302);
        }

        Session::flash('success', 'Kategori oluşturuldu.');

        return Response::redirect('/admin/categories', 302);
    }

    public static function edit(Request $request, array $params): Response
    {
        $redirect = Auth::requireAdmin();
        if ($redirect !== null) {
            return $redirect;
        }
        Session::start();

        $id = filter_var($params['id'] ?? '', FILTER_VALIDATE_INT);
        if ($id === false) {
            return Response::html(View::render('errors/404'), 404);
        }

        $category = Category::find($id);
        if ($category === []) {
            return Response::html(View::render('errors/404'), 404);
        }

        return Response::html(View::render('admin/categories/edit', [
            'category' => $category,
            'tree' => Category::tree(Category::all()),
            'errors' => Session::getFlash('category_errors', []),
            'old' => Session::getFlash('_old', []),
        ]));
    }
    public static function update(Request $request, array $params): Response
    {
        $redirect = Auth::requireAdmin();
        if ($redirect !== null) {
            return $redirect;
        }
        Session::start();

        if (!Csrf::verify($request)) {
            return new Response("CSRF token mismatch\n", 419, ['Content-Type' => 'text/plain; charset=utf-8']);
        }

        $id = filter_var($params['id'] ?? '', FILTER_VALIDATE_INT);
        if ($id === false || Category::find($id) === []) {
            return Response::html(View::render('errors/404'), 404);
        }

        $norm = self::normalise($request);
        $data = [
            'name' => trim((string) $request->post('name', '')),
            'description' => trim((string) $request->post('description', '')),
        ];
        $errors = Validator::make($data, [
            'name' => 'required|max:191',
            'description' => 'max:1000',
        ]);

        if ($norm['parent_id'] === -1) {
            $errors['parent_id'][] = 'in';
        }

        if ($errors !== []) {
            Session::flash('category_errors', $errors);
            Session::flash('_old', ['name' => $data['name'], 'description' => $data['description']]);

            return Response::redirect('/admin/categories/' . $id . '/edit', 302);
        }

        try {
            Category::update($id, $data + $norm);
        } catch (RuntimeException $exception) {
            Session::flash('category_errors', ['name' => [$exception->getMessage()]]);

            return Response::redirect('/admin/categories/' . $id . '/edit', 302);
        }

        Session::flash('success', 'Kategori güncellendi.');

        return Response::redirect('/admin/categories', 302);
    }

    public static function delete(Request $request, array $params): Response
    {
        $redirect = Auth::requireAdmin();
        if ($redirect !== null) {
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
        if ($id === false || Category::find($id) === []) {
            return Response::html(View::render('errors/404'), 404);
        }

        $result = Category::delete($id);
        if ($result === false) {
            Session::flash('error', 'İlişkili ürünler var, silinemez.');
        } else {
            Session::flash('success', 'Kategori silindi.');
        }

        return Response::redirect('/admin/categories', 302);
    }

    public static function reorder(Request $request, array $params): Response
    {
        $redirect = Auth::requireAdmin();
        if ($redirect !== null) {
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

        $raw = file_get_contents('php://input');
        $items = is_string($raw) ? json_decode($raw, true) : null;
        if (!is_array($items)) {
            Session::flash('error', 'Geçersiz sıralama verisi.');

            return Response::redirect('/admin/categories', 302);
        }

        $pairs = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                Session::flash('error', 'Geçersiz sıralama verisi.');

                return Response::redirect('/admin/categories', 302);
            }
            $id = filter_var($item['id'] ?? null, FILTER_VALIDATE_INT);
            $sort = filter_var($item['sort_order'] ?? null, FILTER_VALIDATE_INT);
            if ($id === false || $sort === false) {
                Session::flash('error', 'Geçersiz sıralama verisi.');

                return Response::redirect('/admin/categories', 302);
            }
            $pairs[] = ['id' => $id, 'sort_order' => $sort];
        }

        Database::transaction(static function ($pdo) use ($pairs): void {
            $statement = $pdo->prepare('UPDATE `categories` SET `sort_order` = ? WHERE `id` = ?');
            foreach ($pairs as $pair) {
                $statement->execute([$pair['sort_order'], $pair['id']]);
            }
        });

        Session::flash('success', 'Sıralama güncellendi.');

        return Response::redirect('/admin/categories', 302);
    }
}
