<?php declare(strict_types=1); ?>
<?php ob_start(); ?>
<article class="site-page">
<nav class="site-breadcrumb"><a href="/">Ana Sayfa</a> / <a href="/urunler">Ürünler</a></nav>
<h1><?= e((string) (($product['name'] ?? ''))) ?></h1>
<?php if (($categoryName ?? null) !== null): ?>
<p class="site-meta">Kategori: <?= e((string) $categoryName) ?>
<?php if (!empty($product['code'])): ?> · Kod: <?= e((string) $product['code']) ?><?php endif; ?>
</p>
<?php endif; ?>
<?php if (($images ?? []) !== []): ?>
<div class="site-gallery">
<?php foreach ($images as $img): ?>
<figure class="site-gallery-item">
<img src="/<?= e((string) $img['image_path']) ?>" alt="<?= e((string) ($img['alt_text'] ?? ($product['name'] ?? ''))) ?>" loading="lazy">
<?php if ((int) $img['is_primary'] === 1): ?>
<figcaption class="badge badge-on">Birincil</figcaption>
<?php endif; ?>
</figure>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php if (!empty($product['short_description'])): ?>
<p class="site-lead"><?= nl2br(e((string) $product['short_description'])) ?></p>
<?php endif; ?>
<?php if (!empty($product['long_description'])): ?>
<div class="site-content">
<?= nl2br(e((string) $product['long_description'])) ?>
</div>
<?php endif; ?>
<?php foreach ([['Teknik Özellikler', ($specs ?? [])], ['Varyantlar', ($variants ?? [])]] as [$heading, $rows]): ?>
<?php if ($rows !== []): ?>
<h2><?= e($heading) ?></h2>
<table class="site-specs">
<tbody>
<?php foreach ($rows as $row): ?>
<tr>
<th><?= e((string) ($row['label'] ?? '')) ?></th>
<td><?= e((string) ($row['value'] ?? '')) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
<?php endforeach; ?>
</article>
<?php $content = (string) ob_get_clean(); ?>
<?php include BASE_PATH . '/templates/public/layout.php'; ?>
