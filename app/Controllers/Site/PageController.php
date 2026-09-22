<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Page;

/**
 * Public corporate page renderer. Read-only: pages.slug lookup via the
 * inherited, prepared-statement Model::findBySlug(). Unknown slug => 404 with
 * the existing errors/404.php template (Router semantics untouched).
 */
final class PageController
{
    public static function show(Request $request, array $params): Response
    {
        $slug = trim((string) ($params['slug'] ?? ''));
        if ($slug === '') {
            return Response::html(View::render('errors/404'), 404);
        }

        $page = Page::findBySlug($slug);
        if ($page === []) {
            return Response::html(View::render('errors/404'), 404);
        }

        $title = (string) ($page['title'] ?? '');
        $seoTitle = isset($page['seo_title']) && is_string($page['seo_title']) && $page['seo_title'] !== ''
            ? $page['seo_title']
            : null;

        return Response::html(View::render('public/kurumsal', [
            'title' => $title,
            'seoTitle' => $seoTitle,
            'metaDescription' => isset($page['seo_description']) && is_string($page['seo_description'])
                ? $page['seo_description']
                : null,
            'content' => (string) ($page['content'] ?? ''),
        ]));
    }
}
