<?php declare(strict_types=1); ?>
<?php ob_start(); ?>
<h1>Admin Dashboard — M4 tamamlandı</h1>
<p>Hoş geldin, <?= e((string) ($adminName ?? '')) ?>.</p>
<p><a href="/admin/categories">Kategoriler</a></p>
<?php $content = (string) ob_get_clean(); ?>
<?php $title = 'Dashboard'; ?>
<?php include BASE_PATH . '/templates/admin/layout.php'; ?>
