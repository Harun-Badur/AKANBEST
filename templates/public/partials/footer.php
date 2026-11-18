<?php declare(strict_types=1); ?>
<?php
$sitePhone = \App\Core\Contact::phone();
$siteEmail = \App\Core\Contact::email();
$siteAddress = \App\Core\Contact::address();
$siteWa = \App\Core\Contact::whatsappDigits();
$sitePhoneLink = preg_replace('/[^\d+]/', '', $sitePhone) ?? '';
?>
<footer class="site-footer">
  <div class="site-container">
    <div class="site-footer__grid">
      <div>
        <p class="site-footer__brand">
          <img class="site-footer__logo" src="/assets/images/logo/akanbest-logo-mark-white.svg" alt="" width="74" height="28">
          <span>Akan Best Ambalaj</span>
        </p>
        <p class="site-footer__tagline">Kurumsal ambalaj ve bant çözümleri.</p>
      </div>
      <nav aria-label="Alt menü">
        <ul class="site-footer__nav">
          <li><a href="/">Ana Sayfa</a></li>
          <li><a href="/#urunler">Ürünler</a></li>
          <li><a href="/#kurumsal">Kurumsal</a></li>
          <?php if (($homePage ?? false) === true): ?>
          <li><a href="/#teklif">Teklif Al</a></li>
          <?php endif; ?>
        </ul>
      </nav>
      <?php if ($sitePhoneLink !== '' || $siteEmail !== '' || $siteAddress !== '' || $siteWa !== ''): ?>
      <div class="site-footer__contact">
        <p class="site-footer__contactTitle">İletişim</p>
        <ul class="site-footer__contactList">
          <?php if ($sitePhoneLink !== ''): ?>
          <li><a href="tel:<?= e($sitePhoneLink) ?>"><?= e($sitePhone) ?></a></li>
          <?php endif; ?>
          <?php if ($siteEmail !== ''): ?>
          <li><a href="mailto:<?= e($siteEmail) ?>"><?= e($siteEmail) ?></a></li>
          <?php endif; ?>
          <?php if ($siteWa !== ''): ?>
          <li><a href="https://wa.me/<?= e($siteWa) ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a></li>
          <?php endif; ?>
          <?php if ($siteAddress !== ''): ?>
          <li><span><?= e($siteAddress) ?></span></li>
          <?php endif; ?>
        </ul>
      </div>
      <?php endif; ?>
    </div>
    <div class="site-footer__bottom">
      © <?= e(gmdate('Y')) ?> Akan Best Ambalaj. Tüm hakları saklıdır.
    </div>
  </div>
</footer>
