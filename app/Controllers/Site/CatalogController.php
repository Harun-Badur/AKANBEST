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
        $seoCategory = '';
        $seoTitle = 'Koli Bandı ve Streç Film Kataloğu | Akan Best Ambalaj';
        $metaDescription = 'Koli bandı, streç film ve ambalaj bantları kataloğunu inceleyin. Ürün detayına bakın, toptan tedarik için Akan Best Ambalaj’a teklif bırakın.';

        if ($filterSlug !== '') {
            $category = Category::findBySlug($filterSlug);
            if ($category !== []) {
                $categoryId = (int) $category['id'];
                $categoryName = (string) $category['name'];
                $seoCategory = (string) $category['slug'];
                $seoTitle = seo_title($categoryName);
                $metaDescription = self::categoryDescription($categoryName);
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

        $rows = $filterSlug !== '' && $categoryId === null ? [] : Product::activeList($categoryId);

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
            'seoTitle' => $seoTitle,
            'metaDescription' => $metaDescription,
            'seoCategory' => $seoCategory,
            'rows' => $rows,
            'filterLinks' => $filterLinks,
            'activeSlug' => $filterSlug,
            'filterName' => $categoryName,
            'categories' => $names,
            'thumbs' => $thumbs,
        ]));
    }

    private static function categoryDescription(string $name): string
    {
        $text = $name . ' ürünlerini koli bandı, streç film ve ambalaj bantları kataloğunda inceleyin. Toptan tedarik için Akan Best Ambalaj’a teklif bırakın.';
        if (mb_strlen($text) <= 160) {
            return $text;
        }

        $shorter = $name . ' için koli bandı, streç film ve ambalaj bantlarını inceleyin. Toptan tedarik için Akan Best Ambalaj’a teklif bırakın.';
        if (mb_strlen($shorter) <= 160 && mb_strlen($shorter) >= 120) {
            return $shorter;
        }

        $cut = mb_substr($text, 0, 157);
        $space = mb_strrpos($cut, ' ');
        if ($space !== false && $space >= 120) {
            $cut = mb_substr($cut, 0, $space);
        }

        return rtrim($cut, " \t.,;:") . '.';
    }
}
