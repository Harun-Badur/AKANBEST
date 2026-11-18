<?php declare(strict_types=1); ?>
<?php
$productPage = true;
$catalogPage = true;
include BASE_PATH . '/templates/public/partials/product-content.php';
$gallery = $images ?? [];
usort($gallery, static fn (array $a, array $b): int => (int) $b['is_primary'] <=> (int) $a['is_primary']);
$primary = $gallery[0] ?? null;
$wa = \App\Core\Contact::whatsappDigits();
ob_start();
?>
<article class="product-detail">
  <nav class="site-breadcrumb" aria-label="Sayfa yolu">
    <a href="/">Ana Sayfa</a><span aria-hidden="true">/</span>
    <a href="/#urunler">Ürünler</a><span aria-hidden="true">/</span>
    <?php if (($categoryName ?? null) !== null): ?>
    <span><?= e((string) $categoryName) ?></span><span aria-hidden="true">/</span>
    <?php endif; ?>
    <span aria-current="page"><?= e((string) $product['name']) ?></span>
  </nav>
  <div class="product-detail__grid">
    <div class="product-detail__gallery">
      <div class="product-detail__image">
        <?php if ($primary !== null): ?>
        <img id="product-main-image" src="/<?= e((string) $primary['image_path']) ?>" alt="<?= e((string) ($primary['alt_text'] ?: $product['name'])) ?>"<?= img_dimensions_attr((string) $primary['image_path']) ?> decoding="async" fetchpriority="high">
        <?php endif; ?>
      </div>
      <?php if ($gallery !== []): ?>
      <div class="product-detail__thumbs" aria-label="Ürün görselleri">
        <?php foreach ($gallery as $index => $img): ?>
        <button type="button" class="product-detail__thumb" aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>" aria-label="<?= e((string) $product['name']) ?> — Görsel <?= $index + 1 ?>" data-image="/<?= e((string) $img['image_path']) ?>" data-alt="<?= e((string) ($img['alt_text'] ?: $product['name'])) ?>">
          <img src="/<?= e((string) $img['image_path']) ?>" alt=""<?= img_dimensions_attr((string) $img['image_path']) ?> loading="lazy" decoding="async">
        </button>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <div class="product-detail__summary">
      <h1><?= e((string) $product['name']) ?></h1>
      <?php if (($categoryName ?? null) !== null): ?><p class="product-detail__category"><?= e((string) $categoryName) ?></p><?php endif; ?>
      <?php if (!empty($product['code'])): ?><p class="site-note"><?= e((string) $product['code']) ?></p><?php endif; ?>
      <?php if ($shortText !== ''): ?><p class="product-detail__lead"><?= e($shortText) ?></p><?php endif; ?>
      <?php if ($featureTexts !== []): ?>
      <ul class="product-detail__features">
        <?php foreach ($featureTexts as $feature): ?><li><span aria-hidden="true">✓</span><?= e($feature) ?></li><?php endforeach; ?>
      </ul>
      <?php endif; ?>
      <div class="product-detail__actions">
        <a class="btn btn--accent" href="/#teklif">Teklif Al <span aria-hidden="true">↗</span></a>
        <?php if ($wa !== ''): ?>
        <a class="btn product-detail__whatsapp" href="https://wa.me/<?= e($wa) ?>?text=<?= rawurlencode((string) $product['name'] . ' hakkında bilgi almak istiyorum.') ?>" target="_blank" rel="noopener noreferrer">WhatsApp ile İletişim</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php if ($featureTexts !== []): ?>
  <ul class="product-detail__advantages">
    <?php foreach ($featureTexts as $feature): ?>
    <li><svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 5 6v6c0 4 3 7 7 9 4-2 7-5 7-9V6z"/><path d="m9 12 2 2 4-4"/></svg><span><?= e($feature) ?></span></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
  <div class="product-detail__content">
    <div class="product-detail__tabs" role="tablist" aria-label="Ürün bilgileri">
      <?php foreach (['description' => 'Ürün Açıklaması', 'technical' => 'Teknik Özellikler', 'usage' => 'Kullanım Alanları'] as $key => $label): ?>
      <button type="button" role="tab" id="tab-<?= $key ?>" aria-controls="panel-<?= $key ?>" aria-selected="<?= $key === 'description' ? 'true' : 'false' ?>" tabindex="<?= $key === 'description' ? '0' : '-1' ?>"><?= e($label) ?></button>
      <?php endforeach; ?>
    </div>
    <?php foreach ($sections as $key => $paragraphs): ?>
    <section class="product-detail__panel" role="tabpanel" id="panel-<?= $key ?>" aria-labelledby="tab-<?= $key ?>" tabindex="0"<?= $key !== 'description' ? ' hidden' : '' ?>>
      <?php foreach ($paragraphs as $paragraph): ?><p><?= e($paragraph) ?></p><?php endforeach; ?>
      <?php if ($key === 'technical'): ?>
      <?php foreach ([['Teknik Özellikler', $specs ?? []], ['Varyantlar', $variants ?? []]] as [$heading, $pairs]): ?>
      <?php if ($pairs !== []): ?>
      <h2><?= e($heading) ?></h2><table class="site-specs"><tbody>
      <?php foreach ($pairs as $pair): ?><tr><th><?= e($pair['label']) ?></th><td><?= e($pair['value']) ?></td></tr><?php endforeach; ?>
      </tbody></table>
      <?php endif; ?>
      <?php endforeach; ?>
      <?php endif; ?>
    </section>
    <?php endforeach; ?>
  </div>
  <?php if (($related ?? []) !== []): ?>
  <section class="product-related" aria-labelledby="related-heading">
    <h2 id="related-heading">Diğer Ürünlerimiz</h2>
    <ul>
      <?php foreach ($related as $item): ?><li><a href="/urun/<?= e($item['slug']) ?>"><?= e($item['name']) ?></a></li><?php endforeach; ?>
    </ul>
    <p><a href="/#urunler">Tüm ürünleri görüntüle <span aria-hidden="true">→</span></a></p>
  </section>
  <?php endif; ?>
</article>
<?php $content = (string) ob_get_clean(); ?>
<?php include BASE_PATH . '/templates/public/layout.php'; ?>