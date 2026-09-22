<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;

/**
 * Public catalog. Read-only: active products grid with optional category
 * filter via the ?kategori={slug} query parameter. Unknown filter slug
 * yields an empty result (never an error page).
 */
final class CatalogController
{
    public static function index(Request $request, array $params): Response
    {
        $filterSlug = trim((string) $request->query('kategori', ''));
        $categoryId = null;
        $categoryName = null;

        if ($filterSlug !== '') {
            $category = Category::findBySlug($filterSlug);
            if ($category !== []) {
                $categoryId = (int) $category['id'];
                $categoryName = (string) $category['name'];
            }
        }

        $names = [];
        $filterLinks = [];
        foreach (Category::all() as $row) {
            if ((int) $row['is_active'] === 1) {
                $filterLinks[(string) $row['slug']] = (string) $row['name'];
                $names[(int) $row['id']] = (string) $row['name'];
            }
        }

        $rows = Product::activeList($categoryId);

        $thumbs = [];
        foreach ($rows as $row) {
            $images = ProductImage::allForProduct((int) $row['id']);
            foreach ($images as $img) {
                if ((int) $img['is_primary'] === 1) {
                    $thumbs[(int) $row['id']] = (string) $img['image_path'];
                    break;
                }
            }
            if (!isset($thumbs[(int) $row['id']]) && isset($images[0])) {
                $thumbs[(int) $row['id']] = (string) $images[0]['image_path'];
            }
        }

        return Response::html(View::render('public/catalog', [
            'title' => 'Ürünlerimiz',
            'seoTitle' => 'Ürünlerimiz — Akan Best Ambalaj',
            'metaDescription' => 'Akan Best Ambalaj ürün kataloğu: kategorilere göre filtreleyin, ürün detaylarını inceleyin.',
            'rows' => $rows,
            'filterLinks' => $filterLinks,
            'activeSlug' => $filterSlug === '' || $categoryName === null ? '' : $filterSlug,
            'filterName' => $categoryName,
            'categories' => $names,
            'thumbs' => $thumbs,
        ]));
    }
}
