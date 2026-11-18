<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Page;

/**
 * Read-only B2B homepage.
 *
 * Read-only. Categories and products come from the database. Contact links
 * come from configuration and stay hidden when empty.
 */
final class HomeController
{
    public static function index(Request $request, array $params): Response
    {
        $categoryRows = [];
        $categoryNames = [];
        foreach (Category::all() as $row) {
            if ((int) $row['is_active'] !== 1) {
                continue;
            }
            $id = (int) $row['id'];
            $categoryNames[$id] = (string) $row['name'];
            $categoryRows[] = [
                'name' => (string) $row['name'],
                'slug' => (string) $row['slug'],
                'description' => trim((string) ($row['description'] ?? '')),
            ];
        }

        $active = Product::activeList();
        $products = [];
        foreach ($active as $row) {
            $products[] = [
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
                'slug' => (string) $row['slug'],
                'image' => self::primaryImage((int) $row['id']),
                'category' => $categoryNames[(int) $row['category_id']] ?? null,
            ];
        }

        Session::start();
        return Response::html(View::render('public/home', [
            'quoteErrors' => Session::getFlash('quote_errors', []),
            'quoteOld' => Session::getFlash('quote_old', []),
            'quoteSuccess' => (string) Session::getFlash('quote_success', ''),
            'quoteWhatsapp' => (string) Session::getFlash('quote_whatsapp', ''),
            'title' => 'Ana Sayfa',
            'seoTitle' => 'Koli Bandı, Streç Film ve Bant | Akan Best Ambalaj',
            'metaDescription' => 'Koli bandı, streç film ve ambalaj bantlarını Akan Best Ambalaj kataloğunda inceleyin. İşletmenize uygun ürün için teklif talebinizi iletin.',
            'fullBleed' => true,
            'categories' => $categoryRows,
            'products' => $products,
            'categoryTotal' => count($categoryRows),
            'productTotal' => count($active),
            'aboutPage' => Page::findBySlug('hakkimizda'),
        ]));
    }

    public static function redirectCorporate(Request $request, array $params): Response
    {
        return Response::redirect('/#kurumsal', 301);
    }

    public static function redirectCatalog(Request $request, array $params): Response
    {
        return Response::redirect('/#urunler', 301);
    }

    public static function corporate(Request $request, array $params): Response
    {
        return Response::html(View::render('public/corporate', [
            'title' => 'Kurumsal',
            'seoTitle' => 'Kurumsal Koli Bandı ve Streç Film | Akan Best Ambalaj',
            'metaDescription' => 'Akan Best Ambalaj’ın bant ve ambalaj ürünlerini keşfedin. Ürün özelliklerini inceleyin; ihtiyacınızı paylaşarak teklif isteyin.',
            'aboutPage' => Page::findBySlug('hakkimizda'),
        ]));
    }

    /** Primary image path for a product, or the first image, or null. */
    private static function primaryImage(int $productId): ?string
    {
        $images = ProductImage::allForProduct($productId);
        foreach ($images as $image) {
            if ((int) $image['is_primary'] === 1) {
                return (string) $image['image_path'];
            }
        }

        return isset($images[0]) ? (string) $images[0]['image_path'] : null;
    }
}
