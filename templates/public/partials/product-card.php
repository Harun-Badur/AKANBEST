<?php declare(strict_types=1); ?>
<?php if (!empty($productCard)): ?>
<?php $card = $productCard; ?>
<?php $cardCat = is_string($cardCat ?? null) ? $cardCat : null; ?>
<?php $cardCode = trim((string) ($card['code'] ?? '')); ?>
<article class="site-card">
<a class="site-card-link" href="/urun/<?= e((string) $card['slug']) ?>">
<div class="site-card-media">
<?php if (!empty($cardThumb)): ?>
<img class="site-card-image" src="/<?= e((string) $cardThumb) ?>" alt="<?= e((string) $card['name']) ?>"<?= img_dimensions_attr((string) $cardThumb) ?> loading="lazy" decoding="async">
<?php endif; ?>
</div>
<div class="site-card-body">
<h2 class="site-card-title"><?= e((string) $card['name']) ?></h2>
<?php if ($cardCat !== null): ?>
<p class="site-card-category"><?= e($cardCat) ?></p>
<?php endif; ?>
<?php if ($cardCode !== ''): ?>
<span class="site-card-code"><?= e($cardCode) ?></span>
<?php endif; ?>
<span class="site-card-cta">İncele<span aria-hidden="true">→</span></span>
</div>
</a>
</article>
<?php endif; ?>
