<?php

declare(strict_types=1);

// Admin paneli devre dışı. Giriş ve panel rotaları kayıtlı değil; /admin 404 döner.
// Controller, şablon ve Auth dosyaları yerinde duruyor.

$routes = [
    ['GET', '/', [App\Controllers\Site\HomeController::class, 'index']],
    ['GET', '/kurumsal', [App\Controllers\Site\HomeController::class, 'redirectCorporate']],
    ['POST', '/teklif', [App\Controllers\Site\QuoteController::class, 'store']],
    ['GET', '/urunler', [App\Controllers\Site\HomeController::class, 'redirectCatalog']],
    ['GET', '/urun/{slug}', [App\Controllers\Site\ProductController::class, 'show']],
    ['GET', '/sitemap.xml', [App\Controllers\Site\SitemapController::class, 'index']],
];

return $routes;
