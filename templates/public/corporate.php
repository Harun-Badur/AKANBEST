<?php declare(strict_types=1); ?>
<?php
ob_start();
$aboutText = trim(strip_tags((string) ($aboutPage['content'] ?? '')));
?>
<div class="site-page-head">
  <p class="site-kicker">KURUMSAL</p>
  <h1>Akan Best Ambalaj</h1>
</div>
<div class="site-content">
  <?php if ($aboutText !== ''): ?>
  <p><?= nl2br(e($aboutText)) ?></p>
  <?php else: ?>
  <p>Akan Best Ambalaj’ın bant ve ambalaj ürünlerini keşfedin. Ürün özelliklerini inceleyin; işletmenizin ihtiyacını ve talep ettiğiniz miktarı bizimle paylaşarak teklif isteyin.</p>
  <?php endif; ?>
  <h2>İhtiyacınızı anlayarak başlayalım.</h2>
  <p>Ürün seçiminden teklif talebine, ambalaj ihtiyacınızı birlikte değerlendirelim.</p>
  <h3>Doğru ürün seçimi</h3>
  <p>Kullanım alanınıza uygun ürünün özelliklerini ve seçeneklerini inceleyin.</p>
  <h3>Operasyonunuza uygun</h3>
  <p>Üretim ve paketleme süreçlerinizde ihtiyaç duyduğunuz ambalajı belirleyin.</p>
  <h3>Net bir ihtiyaç planı</h3>
  <p>Ürün, miktar ve teslimat beklentinizi teklif talebinizde paylaşın.</p>
  <h3>Doğrudan iletişim</h3>
  <p>Tek seferlik ya da düzenli ihtiyaçlarınız için bizimle iletişime geçin.</p>
</div>
<?php $content = (string) ob_get_clean(); ?>
<?php include BASE_PATH . '/templates/public/layout.php'; ?>
