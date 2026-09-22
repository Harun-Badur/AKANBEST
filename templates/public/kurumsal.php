<?php declare(strict_types=1); ?>
<?php ob_start(); ?>
<article class="site-page">
<h1><?= e((string) ($title ?? '')) ?></h1>
<div class="site-content">
<?= nl2br(e((string) ($content ?? ''))) ?>
</div>
</article>
<?php $content = (string) ob_get_clean(); ?>
<?php include BASE_PATH . '/templates/public/layout.php'; ?>
