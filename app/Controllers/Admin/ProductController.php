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
use App\Models\Category;
use App\Models\Product;
use RuntimeException;

final class ProductController
{
    public static function index(Request $request, array $params): Response
    {
        if (($redirect = Auth::requireAdmin()) !== null) {
            return $redirect;
        }
        Session::start();

        return Response::html(View::render('admin/products/index', [
            'rows' => Product::all(),
            'categories' => self::categoryMap(),
            'success' => Session::getFlash('success'),
        ]));
    }

    public static function create(Request $request, array $params): Response
    {
        if (($redirect = Auth::requireAdmin()) !== null) {
            return $redirect;
        }
        Session::start();

        return Response::html(View::render('admin/products/create', [
            'tree' => Category::tree(Category::all()),
            'errors' => Session::getFlash('product_errors', []),
            'old' => Session::getFlash('_old', []),
            'specs' => Session::getFlash('_old_specs', []),
            'variants' => Session::getFlash('_old_variants', []),
        ]));
    }

    /** @return array<int, string> */
    private static function categoryMap(): array
    {
        $map = [];
        foreach (Category::all() as $row) {
            $map[(int) $row['id']] = (string) $row['name'];
        }

        return $map;
    }

    /**
     * Builds the validated payload from request input.
     * Rejects raw JSON strings: specs/variants must be nested arrays.
     * Throws RuntimeException on invalid input (message intended for flash).
     *
     * @return array{data: array<string, mixed>, specs: array<int, array<string,string>>, variants: array<int, array<string,string>>}
     */
    private static function payload(Request $request): array
    {
        $categoryId = filter_var(trim((string) $request->post('category_id', '')), FILTER_VALIDATE_INT);
        if ($categoryId === false || $categoryId < 1 || Category::find($categoryId) === []) {
            throw new RuntimeException('Geçerli bir kategori seçin.');
        }

        $sortRaw = trim((string) $request->post('sort_order', ''));
        $specsJson = Product::encodePairs($request->post('specs'));
        $variantsJson = Product::encodePairs($request->post('variants'));

        return [
            'data' => [
                'name' => trim((string) $request->post('name', '')),
                'code' => trim((string) $request->post('code', '')),
                'category_id' => $categoryId,
                'short_description' => (string) $request->post('short_description', ''),
                'long_description' => (string) $request->post('long_description', ''),
                'specs_json' => $specsJson,
                'variants_json' => $variantsJson,
                'is_active' => $request->post('is_active') !== null ? 1 : 0,
                'is_featured' => $request->post('is_featured') !== null ? 1 : 0,
                'sort_order' => $sortRaw === '' ? 0 : (int) $sortRaw,
            ],
            'specs' => Product::decodePairs($specsJson),
            'variants' => Product::decodePairs($variantsJson),
        ];
    }

    /** @return array<int, array{label:string, value:string}> */
    private static function safePairs(mixed $input): array
    {
        try {
            return Product::decodePairs(Product::encodePairs($input));
        } catch (RuntimeException) {
            return [];
        }
    }

    private static function flashInvalid(Request $request, array $errors, string $target, array $payload = []): Response
    {
        Session::flash('product_errors', $errors);
        Session::flash('_old', $payload !== [] ? $payload : ['name' => (string) $request->post('name', '')]);
        Session::flash('_old_specs', self::safePairs($request->post('specs')));
        Session::flash('_old_variants', self::safePairs($request->post('variants')));

        return Response::redirect($target, 302);
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

        try {
            $payload = self::payload($request);
        } catch (RuntimeException $exception) {
            return self::flashInvalid($request, ['form' => [$exception->getMessage()]], '/admin/products/create');
        }

        $errors = Validator::make($payload['data'], [
            'name' => 'required|max:255',
            'code' => 'max:100',
            'short_description' => 'max:500',
        ]);
        if ($errors !== []) {
            return self::flashInvalid($request, $errors, '/admin/products/create', $payload['data']);
        }

        try {
            Product::create($payload['data']);
        } catch (RuntimeException $exception) {
            return self::flashInvalid($request, ['name' => [$exception->getMessage()]], '/admin/products/create', $payload['data']);
        }

        Session::flash('success', 'Ürün oluşturuldu.');

        return Response::redirect('/admin/products', 302);
    }

    public static function edit(Request $request, array $params): Response
    {
        if (($redirect = Auth::requireAdmin()) !== null) {
            return $redirect;
        }
        Session::start();

        $id = filter_var($params['id'] ?? '', FILTER_VALIDATE_INT);
        $row = $id === false ? [] : Product::find($id);
        if ($row === []) {
            return Response::html(View::render('errors/404'), 404);
        }

        return Response::html(View::render('admin/products/edit', [
            'row' => $row,
            'tree' => Category::tree(Category::all()),
            'errors' => Session::getFlash('product_errors', []),
            'old' => Session::getFlash('_old', []),
            'specs' => Session::getFlash('_old_specs', Product::decodePairs($row['specs_json'] ?? null)),
            'variants' => Session::getFlash('_old_variants', Product::decodePairs($row['variants_json'] ?? null)),
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
        if ($id === false || Product::find($id) === []) {
            return Response::html(View::render('errors/404'), 404);
        }
        $target = '/admin/products/' . $id . '/edit';

        try {
            $payload = self::payload($request);
        } catch (RuntimeException $exception) {
            return self::flashInvalid($request, ['form' => [$exception->getMessage()]], $target);
        }

        $errors = Validator::make($payload['data'], [
            'name' => 'required|max:255',
            'code' => 'max:100',
            'short_description' => 'max:500',
        ]);
        if ($errors !== []) {
            return self::flashInvalid($request, $errors, $target, $payload['data']);
        }

        try {
            Product::update($id, $payload['data']);
        } catch (RuntimeException $exception) {
            return self::flashInvalid($request, ['name' => [$exception->getMessage()]], $target, $payload['data']);
        }

        Session::flash('success', 'Ürün güncellendi.');

        return Response::redirect('/admin/products', 302);
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
        if ($id === false || Product::find($id) === []) {
            return Response::html(View::render('errors/404'), 404);
        }

        Product::delete($id);
        Session::flash('success', 'Ürün silindi. (Görsel dosya temizliği M6-D2 kapsamındadır.)');

        return Response::redirect('/admin/products', 302);
    }


}