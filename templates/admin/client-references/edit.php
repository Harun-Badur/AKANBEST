<?php declare(strict_types=1); ?>
<?php ob_start(); ?>
<h1>Referans Düzenle</h1>
<?php foreach (($errors ?? []) as $field => $fieldErrors): ?>
<p class="flash flash-error">Hata (<?= e((string) $field) ?>): <?= e(implode(', ', (array) $fieldErrors)) ?></p>
<?php endforeach; ?>
<?php $r = $row ?? []; ?>
<form method="post" action="/admin/client-references/<?= (int) ($r['id'] ?? 0) ?>" enctype="multipart/form-data">
<?= App\Core\Csrf::field() ?>
<label>Firma <input type="text" name="company_name" value="<?= e((string) ($old['company_name'] ?? $r['company_name'] ?? '')) ?>" required maxlength="255"></label>
<p>Mevcut logo: <?= e((string) ($r['logo_path'] ?? '—')) ?></p>
<label>Yeni Logo <input type="file" name="logo" accept=".jpg,.jpeg,.png,.webp,.gif"></label>
<label>Web Sitesi <input type="url" name="website_url" value="<?= e((string) ($old['website_url'] ?? $r['website_url'] ?? '')) ?>"></label>
<label>Sıra <input type="number" name="sort_order" value="<?= e((string) ($old['sort_order'] ?? (string) ($r['sort_order'] ?? '0'))) ?>"></label>
<label><input type="checkbox" name="is_active" value="1"<?= ((int) ($old['is_active'] ?? ($r['is_active'] ?? 1)) === 1) ? ' checked' : '' ?>> Aktif</label>
<button type="submit">Güncelle</button>
</form>
<?php $content = (string) ob_get_clean(); ?>
<?php $title = 'Referans Düzenle'; ?>
<?php include BASE_PATH . '/templates/admin/layout.php'; ?>
