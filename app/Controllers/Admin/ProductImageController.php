<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageUploader;
use RuntimeException;

final class ProductImageController
{
    /** Step 1: auth only — session check, never touches DB. */
    private static function guardAuth(): ?Response
    {
        $redirect = Auth::requireAdmin();
        if ($redirect !== null) {
            return $redirect;
        }
        Session::start();

        return null;
    }

    /** Step 2: POST-only methods. */
    private static function guardMethod(Request $request): ?Response
    {
        if ($request->method() !== 'POST') {
            return new Response("Method Not Allowed\n", 405, [
                'Content-Type' => 'text/plain; charset=utf-8',
                'Allow' => 'POST',
            ]);
        }

        return null;
    }

    /** Step 3: CSRF token (419) — still no DB. */
    private static function guardCsrf(Request $request): ?Response
    {
        if (!Csrf::verify($request)) {
            return new Response("CSRF token mismatch\n", 419, ['Content-Type' => 'text/plain; charset=utf-8']);
        }

        return null;
    }

    /** Step 4: Product existence (404) — first DB touch. */
    private static function resolveProduct(mixed $id): Response|int
    {
        $productId = filter_var($id, FILTER_VALIDATE_INT);
        if ($productId === false || $productId < 1 || Product::find($productId) === []) {
            return Response::html(View::render('errors/404'), 404);
        }

        return $productId;
    }

    public static function index(Request $request, array $params): Response
    {
        if (($response = self::guardAuth()) !== null) {
            return $response;
        }
        $resolved = self::resolveProduct($params['id'] ?? 0);
        if ($resolved instanceof Response) {
            return $resolved;
        }

        return Response::html(View::render('admin/products/images', [
            'product' => Product::find($resolved),
            'images' => ProductImage::allForProduct($resolved),
            'errors' => Session::getFlash('image_errors', []),
            'success' => Session::getFlash('success'),
        ]));
    }

    /** @return array<int, array{tmp_name: string, size: int}> */
    private static function uploadQueue(): array
    {
        $files = $_FILES['images'] ?? null;
        if (!is_array($files) || !isset($files['name']) || !is_array($files['name'])) {
            return [];
        }
        $queue = [];
        foreach ($files['name'] as $i => $name) {
            $tmp = $files['tmp_name'][$i] ?? '';
            $error = (int) ($files['error'][$i] ?? UPLOAD_ERR_NO_FILE);
            if ($error !== UPLOAD_ERR_OK || !is_string($tmp) || $tmp === '') {
                continue;
            }
            $queue[] = ['tmp_name' => $tmp, 'size' => (int) ($files['size'][$i] ?? 0)];
        }

        return $queue;
    }

    public static function store(Request $request, array $params): Response
    {
        if (($response = self::guardAuth()) !== null) {
            return $response;
        }
        if (($response = self::guardMethod($request)) !== null) {
            return $response;
        }
        if (($response = self::guardCsrf($request)) !== null) {
            return $response;
        }
        $resolved = self::resolveProduct($params['id'] ?? 0);
        if ($resolved instanceof Response) {
            return $resolved;
        }
        $productId = $resolved;

        $queue = self::uploadQueue();
        if ($queue === []) {
            Session::flash('image_errors', ['images' => ['Yüklenecek dosya bulunamadı.']]);

            return Response::redirect('/admin/products/' . $productId . '/images', 302);
        }

        $saved = 0;
        $rejected = [];
        foreach ($queue as $file) {
            [$ok, $reason] = ImageUploader::validatePath($file['tmp_name'], $file['size']);
            if (!$ok) {
                $rejected[] = $reason;
                continue;
            }
            if (!is_uploaded_file($file['tmp_name'])) {
                $rejected[] = 'Yükleme güvenliği reddedildi.';
                continue;
            }
            try {
                $path = ImageUploader::store($file['tmp_name'], $productId);
            } catch (RuntimeException $exception) {
                $rejected[] = $exception->getMessage();
                continue;
            }
            $hasPrimary = ProductImage::countForProduct($productId) > 0 ? 1 : 0;
            ProductImage::create([
                'product_id' => $productId,
                'image_path' => $path,
                'alt_text' => null,
                'is_primary' => $saved === 0 && $hasPrimary === 0 ? 1 : 0,
                'sort_order' => ProductImage::countForProduct($productId),
            ]);
            $saved++;
        }

        if ($saved === 0) {
            Session::flash('image_errors', ['images' => $rejected === [] ? ['Hiçbir dosya kaydedilemedi.'] : array_values(array_unique($rejected))]);

            return Response::redirect('/admin/products/' . $productId . '/images', 302);
        }

        Session::flash('success', $saved . ' görsel yüklendi.' . ($rejected === [] ? '' : ' Reddedilen: ' . count($rejected) . '.'));

        return Response::redirect('/admin/products/' . $productId . '/images', 302);
    }
    public static function update(Request $request, array $params): Response
    {
        if (($response = self::guardAuth()) !== null) {
            return $response;
        }
        if (($response = self::guardMethod($request)) !== null) {
            return $response;
        }
        if (($response = self::guardCsrf($request)) !== null) {
            return $response;
        }
        $resolved = self::resolveProduct($params['id'] ?? 0);
        if ($resolved instanceof Response) {
            return $resolved;
        }
        $productId = $resolved;

        $alts = $request->post('alts', []);
        $sorts = $request->post('sorts', []);
        if (!is_array($alts) || !is_array($sorts)) {
            Session::flash('image_errors', ['images' => ['Gecersiz form verisi.']]);

            return Response::redirect('/admin/products/' . $productId . '/images', 302);
        }

        $rows = [];
        foreach ($alts as $imageId => $alt) {
            $id = filter_var($imageId, FILTER_VALIDATE_INT);
            $sort = filter_var($sorts[$imageId] ?? null, FILTER_VALIDATE_INT);
            if ($id === false || $id < 1 || $sort === false) {
                Session::flash('image_errors', ['images' => ['Gecersiz kayit verisi.']]);

                return Response::redirect('/admin/products/' . $productId . '/images', 302);
            }
            $rows[] = [
                'imageId' => $id,
                'alt' => is_string($alt) ? $alt : '',
                'sort' => $sort,
            ];
        }

        try {
            ProductImage::updateMany($productId, $rows);
        } catch (RuntimeException $exception) {
            Session::flash('image_errors', ['images' => [$exception->getMessage()]]);

            return Response::redirect('/admin/products/' . $productId . '/images', 302);
        }

        Session::flash('success', 'Gorsel bilgileri guncellendi.');

        return Response::redirect('/admin/products/' . $productId . '/images', 302);
    }

    public static function primary(Request $request, array $params): Response
    {
        if (($response = self::guardAuth()) !== null) {
            return $response;
        }
        if (($response = self::guardMethod($request)) !== null) {
            return $response;
        }
        if (($response = self::guardCsrf($request)) !== null) {
            return $response;
        }
        $resolved = self::resolveProduct($params['id'] ?? 0);
        if ($resolved instanceof Response) {
            return $resolved;
        }
        $productId = $resolved;

        $imageId = filter_var($params['imageId'] ?? '', FILTER_VALIDATE_INT);
        if ($imageId === false || $imageId < 1) {
            return Response::html(View::render('errors/404'), 404);
        }

        try {
            ProductImage::setPrimary($imageId, $productId);
        } catch (RuntimeException $exception) {
            Session::flash('image_errors', ['images' => [$exception->getMessage()]]);

            return Response::redirect('/admin/products/' . $productId . '/images', 302);
        }

        Session::flash('success', 'Birincil gorsel atandi.');

        return Response::redirect('/admin/products/' . $productId . '/images', 302);
    }

    public static function delete(Request $request, array $params): Response
    {
        if (($response = self::guardAuth()) !== null) {
            return $response;
        }
        if (($response = self::guardMethod($request)) !== null) {
            return $response;
        }
        if (($response = self::guardCsrf($request)) !== null) {
            return $response;
        }
        $resolved = self::resolveProduct($params['id'] ?? 0);
        if ($resolved instanceof Response) {
            return $resolved;
        }
        $productId = $resolved;

        $imageId = filter_var($params['imageId'] ?? '', FILTER_VALIDATE_INT);
        if ($imageId === false || $imageId < 1) {
            return Response::html(View::render('errors/404'), 404);
        }

        $removed = ProductImage::deleteWithFile($imageId, $productId);
        if ($removed === 0) {
            return Response::html(View::render('errors/404'), 404);
        }

        Session::flash('success', 'Gorsel silindi.');

        return Response::redirect('/admin/products/' . $productId . '/images', 302);
    }
}