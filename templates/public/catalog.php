<?php declare(strict_types=1); ?>
<?php $catalogPage = true; ob_start(); ?>
<div class="site-page-head">
<span class="catalog-kicker">AKAN BEST AMBALAJ / ÜRÜN KATALOĞU</span>
<h1>Ürünlerimiz</h1>
<p class="catalog-lead">Bant ve ambalaj ihtiyaçlarınıza uygun ürünleri keşfedin. Ürün detaylarını inceleyin, teklif talebinizi bizimle paylaşın.</p>
<?php if (($filterName ?? null) !== null): ?>
<div class="site-filter-bar">
<span>Aktif filtre: <strong><?= e((string) $filterName) ?></strong></span>
<a class="btn btn--ghost btn--sm" href="/urunler">Filtreyi temizle</a>
</div>
<?php endif; ?>
</div>
<?php if (($rows ?? []) === []): ?>
<p class="site-empty">Bu filtrede gösterilecek ürün bulunamadı.</p>
<?php endif; ?>
<div class="site-grid">
<?php foreach (($rows ?? []) as $row): ?>
<?php
$productCard = $row;
$cardCat = ($categories ?? [])[(int) $row['category_id']] ?? null;
$cardThumb = ($thumbs ?? [])[(int) $row['id']] ?? null;
include BASE_PATH . '/templates/public/partials/product-card.php';
?>
<?php endforeach; ?>
</div>
<?php $content = (string) ob_get_clean(); ?>
<?php include BASE_PATH . '/templates/public/layout.php'; ?>
