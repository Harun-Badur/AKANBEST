<?php declare(strict_types=1); ?>
<?php ob_start(); ?>
<h1>Kategori Düzenle</h1>
<?php foreach (($errors ?? []) as $field => $fieldErrors): ?>
<p class="flash flash-error">Hata (<?= e((string) $field) ?>): <?= e(implode(', ', (array) $fieldErrors)) ?></p>
<?php endforeach; ?>
<?php $cat = $category ?? []; ?>
<?php $sel = (string) ($old['parent_id'] ?? (string) ($cat['parent_id'] ?? '')); ?>
<form method="post" action="/admin/categories/<?= (int) ($cat['id'] ?? 0) ?>">
<?= App\Core\Csrf::field() ?>
<label>Ad <input type="text" name="name" value="<?= e((string) ($old['name'] ?? $cat['name'] ?? '')) ?>" required maxlength="191"></label>
<label>Üst Kategori
<select name="parent_id">
<option value="">— Yok —</option>
<?php foreach (($tree ?? []) as $root): ?>
<?php if ((int) $root['id'] === (int) ($cat['id'] ?? 0)) continue; ?>
<option value="<?= (int) $root['id'] ?>"<?= $sel === (string) (int) $root['id'] ? ' selected' : '' ?>><?= e((string) $root['name']) ?></option>
<?php foreach (($root['children'] ?? []) as $child): ?>
<?php if ((int) $child['id'] === (int) ($cat['id'] ?? 0)) continue; ?>
<option value="<?= (int) $child['id'] ?>"<?= $sel === (string) (int) $child['id'] ? ' selected' : '' ?>>&nbsp;&nbsp;— <?= e((string) $child['name']) ?></option>
<?php endforeach; ?>
<?php endforeach; ?>
</select>
</label>
<label>Açıklama <textarea name="description" maxlength="1000"><?= e((string) ($old['description'] ?? $cat['description'] ?? '')) ?></textarea></label>
<label>Sıra <input type="number" name="sort_order" value="<?= e((string) ($old['sort_order'] ?? (string) ($cat['sort_order'] ?? '0'))) ?>"></label>
<label><input type="checkbox" name="is_active" value="1"<?= ((int) ($old['is_active'] ?? ($cat['is_active'] ?? 1)) === 1) ? ' checked' : '' ?>> Aktif</label>
<button type="submit">Güncelle</button>
</form>
<?php $content = (string) ob_get_clean(); ?>
<?php $title = 'Kategori Düzenle'; ?>
<?php include BASE_PATH . '/templates/admin/layout.php'; ?>
