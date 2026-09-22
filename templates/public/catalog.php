<?php declare(strict_types=1); ?>
<?php ob_start(); ?>
<h1>Ürünlerimiz</h1>
<?php if (($filterName ?? null) !== null): ?>
<p>Aktif filtre: <strong><?= e((string) $filterName) ?></strong>
<a href="/urunler">Temizle</a></p>
<?php endif; ?>
<nav class="site-filters" aria-label="Kategoriye göre">
<a href="/urunler" class="<?= (($activeSlug ?? '') === '' ? 'is-active' : '') ?>">Tümü</a>
<?php foreach (($filterLinks ?? []) as $slug => $name): ?>
<a href="/urunler?kategori=<?= e((string) $slug) ?>" class="<?= (($activeSlug ?? '') === (string) $slug ? 'is-active' : '') ?>"><?= e((string) $name) ?></a>
<?php endforeach; ?>
</nav>
<?php if (($rows ?? []) === []): ?>
<p>Bu filtrede gösterilecek ürün bulunamadı.</p>
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
