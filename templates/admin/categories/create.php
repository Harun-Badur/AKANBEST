<?php declare(strict_types=1); ?>
<?php ob_start(); ?>
<h1>Yeni Kategori</h1>
<?php foreach (($errors ?? []) as $field => $fieldErrors): ?>
<p class="flash flash-error">Hata (<?= e((string) $field) ?>): <?= e(implode(', ', (array) $fieldErrors)) ?></p>
<?php endforeach; ?>
<form method="post" action="/admin/categories">
<?= App\Core\Csrf::field() ?>
<label>Ad <input type="text" name="name" value="<?= e((string) ($old['name'] ?? '')) ?>" required maxlength="191"></label>
<label>Üst Kategori
<select name="parent_id">
<option value="">— Yok —</option>
<?php $selected = (string) ($old['parent_id'] ?? ''); ?>
<?php foreach (($tree ?? []) as $root): ?>
<option value="<?= (int) $root['id'] ?>"<?= $selected === (string) (int) $root['id'] ? ' selected' : '' ?>><?= e((string) $root['name']) ?></option>
<?php foreach (($root['children'] ?? []) as $child): ?>
<option value="<?= (int) $child['id'] ?>"<?= $selected === (string) (int) $child['id'] ? ' selected' : '' ?>>&nbsp;&nbsp;— <?= e((string) $child['name']) ?></option>
<?php endforeach; ?>
<?php endforeach; ?>
</select>
</label>
<label>Açıklama <textarea name="description" maxlength="1000"><?= e((string) ($old['description'] ?? '')) ?></textarea></label>
<label>Sıra <input type="number" name="sort_order" value="<?= e((string) ($old['sort_order'] ?? '0')) ?>"></label>
<label><input type="checkbox" name="is_active" value="1"<?= ((int) ($old['is_active'] ?? 1) === 1) ? ' checked' : '' ?>> Aktif</label>
<button type="submit">Kaydet</button>
</form>
<?php $content = (string) ob_get_clean(); ?>
<?php $title = 'Yeni Kategori'; ?>
<?php include BASE_PATH . '/templates/admin/layout.php'; ?>
