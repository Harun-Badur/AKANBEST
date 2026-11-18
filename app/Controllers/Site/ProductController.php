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
 * Public product detail. Read-only: active product lookup by slug; inactive
 * or unknown slug => secure 404. specs/variants JSON is decoded, validated
 * and rendered escaped (never raw HTML).
 */
final class ProductController
{
    /** Eski slug => güncel slug (akanbest_work_pre_seo.sql ile akanbest_live.sql karşılaştırması). */
    private const LEGACY_SLUGS = [
        'cift-tarafl-bantlar' => 'cift-tarafli-bantlar',
        'kat-bant' => 'kagit-bant',
        'dokunmam-bant' => 'dokunmamis-bant',
        'baskl-bant' => 'baskili-bant',
        'cop-poeti' => 'cop-poseti',
    ];

    /**
     * Decodes a stored JSON pairs column into label/value rows.
     * Stored data is trusted only after shape validation.
     *
     * @return array<int, array{label: string, value: string}>
     */
    /** @return array<int, array{label: string, value: string}> */
    private static function pairs(mixed $json): array
    {
        return Product::decodePairs($json);
    }

    public static function show(Request $request, array $params): Response
    {
        $slug = trim((string) ($params['slug'] ?? ''));
        if ($slug === '') {
            return Response::html(View::render('errors/404'), 404);
        }

        $product = Product::findActiveBySlug($slug);
        if ($product === [] && isset(self::LEGACY_SLUGS[$slug])) {
            // Eski (Türkçe karakteri düşmüş) slug'lar: güncel adrese kalıcı yönlendirme.
            return Response::redirect('/urun/' . self::LEGACY_SLUGS[$slug], 301);
        }
        if ($product === []) {
            return Response::html(View::render('errors/404'), 404);
        }

        $category = Category::find((int) $product['category_id']);
        $name = trim((string) ($product['name'] ?? ''));
        $short = trim((string) ($product['short_description'] ?? ''));

        $related = [];
        foreach (Product::activeList() as $row) {
            if ((int) $row['id'] === (int) $product['id']) {
                continue;
            }
            $related[] = ['name' => (string) $row['name'], 'slug' => (string) $row['slug']];
        }

        return Response::html(View::render('public/product', [
            'related' => $related,
            'title' => $name,
            'seoTitle' => seo_title($name),
            'metaDescription' => $short !== '' ? $short : product_meta_description($name),
            'product' => $product,
            'categoryName' => is_string($category['name'] ?? null) ? (string) $category['name'] : null,
            'categorySlug' => is_string($category['slug'] ?? null) ? (string) $category['slug'] : null,
            'specs' => self::pairs($product['specs_json'] ?? null),
            'variants' => self::pairs($product['variants_json'] ?? null),
            'images' => ProductImage::allForProduct((int) $product['id']),
        ]));
    }
}
