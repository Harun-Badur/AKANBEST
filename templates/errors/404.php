<?php declare(strict_types=1); ?>
<?php
$noindex = true;
$errorPage = true;
$title = 'Sayfa bulunamadı';
$seoTitle = 'Sayfa Bulunamadı | Akan Best Ambalaj';
$metaDescription = 'İstenen sayfa mevcut değil. Ana sayfaya dönebilir veya ürünler sayfasından koli bandı, streç film ve ambalaj bantlarını inceleyebilirsiniz.';
ob_start();
?>
<div class="site-page-head">
  <h1>Sayfa bulunamadı</h1>
  <p>İstenen sayfa mevcut değil.</p>
  <p><a href="/">Ana sayfa</a> · <a href="/#urunler">Ürünler</a></p>
</div>
<?php $content = (string) ob_get_clean(); ?>
<?php include BASE_PATH . '/templates/public/layout.php'; ?>
