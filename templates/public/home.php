<?php declare(strict_types=1); ?>
<?php ob_start(); ?>
<section class="site-hero">
<h1><?= e((string) ($seoTitle ?? 'Akan Best Ambalaj')) ?></h1>
<p>Kurumsal ambalaj çözümleri. Bu bölüm M8 tasarım aşamasında tamamlanacaktır.</p>
</section>

<section id="kurumsal-sayfalar" class="site-section">
<h2>Kurumsal Sayfalar</h2>
<ul class="site-link-list">
<?php foreach (($corporateLinks ?? []) as $slug => $label): ?>
<li><a href="/kurumsal/<?= e((string) $slug) ?>"><?= e((string) $label) ?></a></li>
<?php endforeach; ?>
</ul>
<p class="site-note">İskelet notu: bağlantı hedefleri M7'de statiktir; M5-C içe aktarımı sonrası
gerçek slug listesi <code>pages</code> tablosundan üretilecektir (M8).</p>
</section>
<?php $content = (string) ob_get_clean(); ?>
<?php include BASE_PATH . '/templates/public/layout.php'; ?>
