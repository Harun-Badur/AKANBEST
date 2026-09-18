<?php declare(strict_types=1); ?>
<?php ob_start(); ?>
<h1>Yeni Sayfa</h1>
<?php foreach (($errors ?? []) as $field => $fieldErrors): ?>
<p class="flash flash-error">Hata (<?= e((string) $field) ?>): <?= e(implode(', ', (array) $fieldErrors)) ?></p>
<?php endforeach; ?>
<form method="post" action="/admin/pages">
<?= App\Core\Csrf::field() ?>
<label>Başlık <input type="text" name="title" value="<?= e((string) ($old['title'] ?? '')) ?>" required maxlength="255"></label>
<label>İçerik (düz metin) <textarea name="content" rows="12"><?= e((string) ($old['content'] ?? '')) ?></textarea></label>
<label>SEO Başlık <input type="text" name="seo_title" value="<?= e((string) ($old['seo_title'] ?? '')) ?>" maxlength="255"></label>
<label>SEO Açıklama <textarea name="seo_description" rows="3" maxlength="500"><?= e((string) ($old['seo_description'] ?? '')) ?></textarea></label>
<button type="submit">Kaydet</button>
</form>
<?php $content = (string) ob_get_clean(); ?>
<?php $title = 'Yeni Sayfa'; ?>
<?php include BASE_PATH . '/templates/admin/layout.php'; ?>
