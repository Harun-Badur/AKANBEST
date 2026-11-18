<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Config;
use App\Core\Request;
use App\Core\Response;
use App\Models\Product;

/** Public URL list. Active products only; lastmod comes from the stored timestamp. */
final class SitemapController
{
    public static function index(Request $request, array $params): Response
    {
        $base = rtrim((string) Config::get('app.url', ''), '/');
        $urls = [
            ['loc' => $base . '/', 'lastmod' => ''],
        ];

        foreach (Product::activeList() as $row) {
            $stamp = trim((string) ($row['updated_at'] ?? ''));
            if ($stamp === '') {
                $stamp = trim((string) ($row['created_at'] ?? ''));
            }
            $lastmod = '';
            if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $stamp, $match) === 1) {
                $lastmod = $match[1];
            }
            $urls[] = [
                'loc' => $base . '/urun/' . rawurlencode((string) $row['slug']),
                'lastmod' => $lastmod,
            ];
        }

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . self::x($url['loc']) . "</loc>\n";
            if ($url['lastmod'] !== '') {
                $xml .= '    <lastmod>' . self::x($url['lastmod']) . "</lastmod>\n";
            }
            $xml .= "  </url>\n";
        }
        $xml .= "</urlset>\n";

        return new Response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    public static function robots(Request $request, array $params): Response
    {
        $base = rtrim((string) Config::get('app.url', ''), '/');
        $body = "User-agent: *
Allow: /
Disallow: /admin
Disallow: /teklif

Sitemap: " . $base . "/sitemap.xml
";

        return new Response($body, 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    private static function x(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
