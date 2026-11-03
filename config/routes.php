<?php

declare(strict_types=1);

// Admin paneli devre dışı. Giriş ve panel rotaları kayıtlı değil; /admin 404 döner.
// Controller, şablon ve Auth dosyaları yerinde duruyor.

$routes = [
    ['GET', '/', [App\Controllers\Site\HomeController::class, 'index']],
    ['POST', '/teklif', [App\Controllers\Site\QuoteController::class, 'store']],
    ['GET', '/urunler', [App\Controllers\Site\CatalogController::class, 'index']],
    ['GET', '/urun/{slug}', [App\Controllers\Site\ProductController::class, 'show']],
];

return $routes;
