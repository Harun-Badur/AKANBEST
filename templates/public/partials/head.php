<?php declare(strict_types=1);

use App\Core\Config;
use App\Core\Contact;

$headTitle = (isset($seoTitle) && is_string($seoTitle) && $seoTitle !== '')
    ? $seoTitle
    : ((isset($title) && is_string($title) && $title !== '') ? $title : 'Akan Best Ambalaj');
$headDescription = (isset($metaDescription) && is_string($metaDescription)) ? trim($metaDescription) : '';

$appUrl = rtrim((string) Config::get('app.url', ''), '/');
$requestPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$path = is_string($requestPath) && $requestPath !== '' ? $requestPath : '/';
$canonical = $appUrl . $path;
$seoCategory = (isset($seoCategory) && is_string($seoCategory)) ? $seoCategory : '';
if ($path === '/urunler' && $seoCategory !== '') {
    $canonical .= '?kategori=' . rawurlencode($seoCategory);
}

$defaultOgPath = '/assets/images/logo/og-image.png';
$ogImagePath = $defaultOgPath;
if (($productPage ?? false) === true && isset($primary) && is_array($primary) && trim((string) ($primary['image_path'] ?? '')) !== '') {
    $ogImagePath = '/' . ltrim((string) $primary['image_path'], '/');
}
$ogImage = $appUrl . $ogImagePath;
$ogType = (($productPage ?? false) === true) ? 'product' : 'website';

$organization = [
    '@type' => 'Organization',
    'name' => (string) Config::get('app.name', 'Akan Best Ambalaj'),
    'url' => $appUrl . '/',
    'logo' => $appUrl . '/assets/images/logo/favicon-512.png',
];
$phone = Contact::phone();
if ($phone !== '') {
    $organization['telephone'] = $phone;
}
$email = Contact::email();
if ($email !== '') {
    $organization['email'] = $email;
}
$address = Contact::address();
if ($address !== '') {
    $organization['address'] = [
        '@type' => 'PostalAddress',
        'streetAddress' => $address,
    ];
}
$graph = [$organization];

$productName = (isset($product) && is_array($product)) ? trim((string) ($product['name'] ?? '')) : '';
if (($productPage ?? false) === true && $productName !== '') {
    $productNode = [
        '@type' => 'Product',
        'name' => $productName,
    ];
    if ($headDescription !== '') {
        $productNode['description'] = $headDescription;
    }
    if ($ogImagePath !== $defaultOgPath) {
        $productNode['image'] = $ogImage;
    }
    $graph[] = $productNode;

    $crumbs = [
        ['name' => 'Ana Sayfa', 'item' => $appUrl . '/'],
        ['name' => 'Ürünler', 'item' => $appUrl . '/#urunler'],
    ];
    $categoryName = (isset($categoryName) && is_string($categoryName)) ? trim($categoryName) : '';
    $categorySlug = (isset($categorySlug) && is_string($categorySlug)) ? trim($categorySlug) : '';
    if ($categoryName !== '' && $categorySlug !== '') {
        $crumbs[] = [
            'name' => $categoryName,
            'item' => $appUrl . '/urunler?kategori=' . rawurlencode($categorySlug),
        ];
    }
    $crumbs[] = ['name' => $productName, 'item' => $canonical];
    $items = [];
    foreach ($crumbs as $crumb) {
        if ($crumb['name'] === '' || $crumb['item'] === '') {
            continue;
        }
        $items[] = [
            '@type' => 'ListItem',
            'position' => count($items) + 1,
            'name' => $crumb['name'],
            'item' => $crumb['item'],
        ];
    }
    $graph[] = [
        '@type' => 'BreadcrumbList',
        'itemListElement' => $items,
    ];
}

$jsonLd = json_encode(
    ['@context' => 'https://schema.org', '@graph' => $graph],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS
);
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/images/logo/favicon-32.png">
<link rel="apple-touch-icon" sizes="180x180" href="/assets/images/logo/favicon-180.png">
<?php if (($noindex ?? false) === true): ?>
<meta name="robots" content="noindex">
<?php endif; ?>
<title><?= e($headTitle) ?></title>
<?php if ($headDescription !== ''): ?>
<meta name="description" content="<?= e($headDescription) ?>">
<?php endif; ?>
<link rel="canonical" href="<?= e($canonical) ?>">
<meta property="og:type" content="<?= e($ogType) ?>">
<meta property="og:title" content="<?= e($headTitle) ?>">
<?php if ($headDescription !== ''): ?>
<meta property="og:description" content="<?= e($headDescription) ?>">
<?php endif; ?>
<meta property="og:image" content="<?= e($ogImage) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:locale" content="tr_TR">
<meta property="og:site_name" content="Akan Best Ambalaj">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($headTitle) ?>">
<?php if ($headDescription !== ''): ?>
<meta name="twitter:description" content="<?= e($headDescription) ?>">
<?php endif; ?>
<meta name="twitter:image" content="<?= e($ogImage) ?>">
<?php if (is_string($jsonLd) && $jsonLd !== ''): ?>
<script type="application/ld+json"><?= $jsonLd ?></script>
<?php endif; ?>
<link rel="stylesheet" href="/assets/css/site.css">
<?php if (($homePage ?? false) === true): ?>
<link rel="stylesheet" href="/assets/css/home.css">
<?php
$quoteCss = BASE_PATH . '/public/assets/css/quote.css';
$quoteCssVersion = file_exists($quoteCss) ? (int) filemtime($quoteCss) : 1;
?>
<link rel="stylesheet" href="/assets/css/quote.css?v=<?= $quoteCssVersion ?>">
<?php endif; ?>
<script defer src="/assets/js/site.js"></script>
<?php if (($catalogPage ?? false) === true): ?>
<link rel="stylesheet" href="/assets/css/catalog.css">
<?php endif; ?>
<?php if (($productPage ?? false) === true): ?>
<link rel="stylesheet" href="/assets/css/product.css">
<script defer src="/assets/js/product.js"></script>
<?php endif; ?>
