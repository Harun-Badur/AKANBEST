<?php declare(strict_types=1); ?>
<?php if (!empty($productCard)): ?>
<?php $card = $productCard; ?>
<?php $cardCat = is_string($cardCat ?? null) ? $cardCat : null; ?>
<article class="site-card">
<a class="site-card-link" href="/urun/<?= e((string) $card['slug']) ?>">
<?php if (!empty($cardThumb)): ?>
<img class="site-card-image" src="/<?= e((string) $cardThumb) ?>" alt="<?= e((string) $card['name']) ?>" loading="lazy">
<?php endif; ?>
<span class="site-card-code"><?= e((string) ($card['code'] ?? '')) ?></span>
<h3 class="site-card-title"><?= e((string) $card['name']) ?></h3>
<?php if ($cardCat !== null): ?>
<p class="site-card-category"><?= e($cardCat) ?></p>
<?php endif; ?>
</a>
</article>
<?php endif; ?>
