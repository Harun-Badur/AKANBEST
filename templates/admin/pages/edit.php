<?php declare(strict_types=1); ?>
<?php ob_start(); ?>
<h1>Sayfa Düzenle</h1>
<?php foreach (($errors ?? []) as $field => $fieldErrors): ?>
<p class="flash flash-error">Hata (<?= e((string) $field) ?>): <?= e(implode(', ', (array) $fieldErrors)) ?></p>
<?php endforeach; ?>
<?php $r = $row ?? []; ?>
<form method="post" action="/admin/pages/<?= (int) ($r['id'] ?? 0) ?>">
<?= App\Core\Csrf::field() ?>
<label>Başlık <input type="text" name="title" value="<?= e((string) ($old['title'] ?? $r['title'] ?? '')) ?>" required maxlength="255"></label>
<label>İçerik (düz metin) <textarea name="content" rows="12"><?= e((string) ($old['content'] ?? $r['content'] ?? '')) ?></textarea></label>
<label>SEO Başlık <input type="text" name="seo_title" value="<?= e((string) ($old['seo_title'] ?? $r['seo_title'] ?? '')) ?>" maxlength="255"></label>
<label>SEO Açıklama <textarea name="seo_description" rows="3" maxlength="500"><?= e((string) ($old['seo_description'] ?? $r['seo_description'] ?? '')) ?></textarea></label>
<button type="submit">Güncelle</button>
</form>
<?php $content = (string) ob_get_clean(); ?>
<?php $title = 'Sayfa Düzenle'; ?>
<?php include BASE_PATH . '/templates/admin/layout.php'; ?>
