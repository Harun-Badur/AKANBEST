<?php declare(strict_types=1); ?>
<?php
$homePage = true;
$categories = $categories ?? [];
$products = $products ?? [];
$settings = $settings ?? [];
$categoryTotal = (int) ($categoryTotal ?? 0);
ob_start();
?>
<section class="hp-hero">
  <div class="hp-hero__media">
    <img class="hp-hero__img" src="/assets/images/hero/hero-banner.jpg?v=2" alt="Akan Best Ambalaj deposunda koli bandı, streç film ve ambalaj kolileri" width="1024" height="576" fetchpriority="high" decoding="async">
  </div>
  <div class="hp-hero__inner">
    <div class="hp-hero__text">
      <span class="hp-eyebrow">Koli bandı <span aria-hidden="true">·</span> Streç film <span aria-hidden="true">·</span> Ambalaj çözümleri</span>
      <h1 class="hp-hero__title">İşinizin her aşamasında <span class="hp-hero__titleAccent">doğru ambalaj.</span></h1>
      <p class="hp-hero__lead">Bant ve ambalaj ihtiyaçlarınızı tek noktadan keşfedin. İşletmenize uygun ürünler için Akan Best Ambalaj ile iletişime geçin.</p>
      <div class="hp-hero__actions">
        <a class="btn btn--accent" href="/#urunler">Ürünleri İncele <span aria-hidden="true">→</span></a>
        <a class="btn btn--onDark" href="/#teklif"><svg class="btn__icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3.5h8l4 4v13H6z"/><path d="M14 3.5v4h4"/><path d="M9 12.5h6M9 16h6"/></svg> Teklif Al</a>
      </div>
    </div>
  </div>
<div class="hp-hero__trust" role="region" aria-label="Değer önerilerimiz">
    <div class="site-container">
      <ul class="hp-trust">
        <li class="hp-trust__item">
          <span class="hp-trust__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8.4 12 3.4 3 8.4v7.2l9 5 9-5z"/><path d="m3 8.4 9 5 9-5"/><path d="M12 13.4v7.2"/></svg>
          </span>
          <span class="hp-trust__body">
            <strong>Geniş Ürün Yelpazesi</strong>
            <span><?php if ($categoryTotal > 0): ?><?= e((string) $categoryTotal) ?> Kategori<?php else: ?>Ürün Yelpazesi<?php endif; ?></span>
          </span>
        </li>
        <li class="hp-trust__item">
          <span class="hp-trust__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3.2 5 6.1v5.6c0 4 2.9 6.7 7 9.1 4.1-2.4 7-5.1 7-9.1V6.1z"/><path d="m9.1 12.1 2.2 2.2 4.1-4.4"/></svg>
          </span>
          <span class="hp-trust__body">
            <strong>Güvenilir Çözümler</strong>
            <span>İşletmenize Özel Destek</span>
          </span>
        </li>
        <li class="hp-trust__item">
          <span class="hp-trust__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6.6h11v9.8H3z"/><path d="M14 10.2h3.9l3.1 3.1v3.1H14z"/><circle cx="7.1" cy="18.4" r="1.9"/><circle cx="17.4" cy="18.4" r="1.9"/></svg>
          </span>
          <span class="hp-trust__body">
            <strong>Tedarik İletişimi</strong>
            <span>İhtiyacınızı Birlikte Planlayalım</span>
          </span>
        </li>
        <li class="hp-trust__item">
          <span class="hp-trust__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3.3"/><path d="M12 3.2v2.4M12 18.4v2.4M20.8 12h-2.4M5.6 12H3.2M18.2 5.8l-1.7 1.7M7.5 16.5l-1.7 1.7M18.2 18.2l-1.7-1.7M7.5 7.5 5.8 5.8"/></svg>
          </span>
          <span class="hp-trust__body">
            <strong>Kurumsal Yaklaşım</strong>
            <span>Uzun Vadeli İş Ortaklığı</span>
          </span>
        </li>
      </ul>
    </div>
  </div>
</section>


<?php if ($products !== []): ?>
<section class="hp-section hp-section--alt" id="urunler">
  <div class="site-container">
    <div class="hp-section__head">
      <span class="hp-kicker">ÜRÜN PORTFÖYÜ</span><h2>Koli Bandı, Streç Film ve Ambalaj Ürünlerimiz</h2>
      <p>Ambalajlama operasyonlarınızda kullanılan bant, streç film ve ambalaj ürünleri.</p>
    </div>
    <ul class="hp-prod-grid">
      <?php foreach ($products as $product): ?>
      <li>
        <a class="hp-prod" href="/urun/<?= e((string) $product['slug']) ?>">
          <span class="hp-prod__media">
            <?php if ($product['image'] !== null): ?>
            <img src="/<?= e((string) $product['image']) ?>" alt="<?= e((string) $product['name']) ?>"<?= img_dimensions_attr((string) $product['image']) ?> loading="lazy" decoding="async">
            <?php endif; ?>
          </span>
          <span class="hp-prod__body">
            <?php if ($product['category'] !== null): ?>
            <span class="hp-prod__cat"><?= e((string) $product['category']) ?></span>
            <?php endif; ?>
            <span class="hp-prod__name"><?= e((string) $product['name']) ?></span>
            <span class="hp-prod__cta">Ürünü İncele <span aria-hidden="true">→</span></span>
          </span>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>


<section class="hp-section" id="kurumsal">
  <div class="site-container">
    <div class="hp-section__head">
      <span class="hp-kicker">KURUMSAL</span>
      <h2>Akan Best Ambalaj Hakkında</h2>
    </div>
    <div class="site-content">
      <?php $aboutText = trim(strip_tags((string) ($aboutPage['content'] ?? ''))); ?>
      <?php if ($aboutText !== ''): ?>
      <p><?= nl2br(e($aboutText)) ?></p>
      <?php else: ?>
      <p>Akan Best Ambalaj’ın bant ve ambalaj ürünlerini keşfedin. Ürün özelliklerini inceleyin; işletmenizin ihtiyacını ve talep ettiğiniz miktarı bizimle paylaşarak teklif isteyin.</p>
      <?php endif; ?>
    </div>
  </div>
</section>
<section class="hp-section hp-approach" id="deger">
  <div class="site-container">
    <div class="hp-section__head">
      <span class="hp-kicker">KURUMSAL YAKLAŞIM</span>
      <h2>İhtiyacınızı anlayarak başlayalım.</h2>
      <p>Ürün seçiminden teklif talebine, ambalaj ihtiyacınızı birlikte değerlendirelim.</p>
    </div>
    <ol class="hp-value-grid">
      <li class="hp-value"><span class="hp-step">01 / KALİTE</span><h3>Doğru ürün seçimi</h3><p>Kullanım alanınıza uygun ürünün özelliklerini ve seçeneklerini inceleyin.</p></li>
      <li class="hp-value"><span class="hp-step">02 / ÜRETİM</span><h3>Operasyonunuza uygun</h3><p>Üretim ve paketleme süreçlerinizde ihtiyaç duyduğunuz ambalajı belirleyin.</p></li>
      <li class="hp-value"><span class="hp-step">03 / TEDARİK</span><h3>Net bir ihtiyaç planı</h3><p>Ürün, miktar ve teslimat beklentinizi teklif talebinizde paylaşın.</p></li>
      <li class="hp-value"><span class="hp-step">04 / İŞ ORTAKLIĞI</span><h3>Doğrudan iletişim</h3><p>Tek seferlik ya da düzenli ihtiyaçlarınız için bizimle iletişime geçin.</p></li>
    </ol>
  </div>
</section>
<?php include BASE_PATH . '/templates/public/partials/quote-section.php'; ?>
<?php $content = (string) ob_get_clean(); ?>
<?php include BASE_PATH . '/templates/public/layout.php'; ?>
