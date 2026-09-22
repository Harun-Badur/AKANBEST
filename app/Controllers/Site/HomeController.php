<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;

/**
 * Public home page skeleton. Deliberately performs NO database queries:
 * corporate page links are static placeholders until M5-C import provides
 * the real slug list from the `pages` table (M8 refresh).
 */
final class HomeController
{
    /**
     * Static link placeholders. Keys are slugs (pages.slug), values are labels.
     * NOT derived from the database in M7 by design.
     *
     * @var array<string, string>
     */
    private const CORPORATE_LINKS = [
        'hakkimizda' => 'Hakkımızda',
        'vizyon-misyon' => 'Vizyon ve Misyon',
        'kalite-politikasi' => 'Kalite Politikası',
    ];

    public static function index(Request $request, array $params): Response
    {
        return Response::html(View::render('public/home', [
            'title' => 'Ana Sayfa',
            'seoTitle' => 'Akan Best Ambalaj',
            'metaDescription' => 'Akan Best Ambalaj kurumsal web sitesi: ürünlerimiz, kurumsal bilgiler ve teklif talebi.',
            'corporateLinks' => self::CORPORATE_LINKS,
        ]));
    }
}
