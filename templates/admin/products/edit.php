<?php declare(strict_types=1); ?>
<?php ob_start(); ?>
<h1>Ürün Düzenle</h1>
<?php foreach (($errors ?? []) as $field => $fieldErrors): ?>
<p class="flash flash-error">Hata (<?= e((string) $field) ?>): <?= e(implode(', ', (array) $fieldErrors)) ?></p>
<?php endforeach; ?>
<?php $p = $row ?? []; $o = $old ?? []; ?>
<?php $val = static fn (string $k, string $d = ''): string => (string) ($o[$k] ?? $p[$k] ?? $d); ?>
<?php $currentCat = (int) ($o['category_id'] ?? $p['category_id'] ?? 0); ?>
<form method="post" action="/admin/products/<?= (int) ($p['id'] ?? 0) ?>">
<?= App\Core\Csrf::field() ?>
<p>Slug: <code><?= e((string) ($p['slug'] ?? '')) ?></code></p>
<label>Ad <input type="text" name="name" value="<?= e($val('name')) ?>" required maxlength="255"></label>
<label>Kod <input type="text" name="code" value="<?= e($val('code')) ?>" maxlength="100"></label>
<label>Kategori
<select name="category_id" required>
<option value="">— Seçin —</option>
<?php foreach (($tree ?? []) as $root): ?>
<option value="<?= (int) $root['id'] ?>"<?= $currentCat === (int) $root['id'] ? ' selected' : '' ?>><?= e((string) $root['name']) ?></option>
<?php foreach (($root['children'] ?? []) as $child): ?>
<option value="<?= (int) $child['id'] ?>"<?= $currentCat === (int) $child['id'] ? ' selected' : '' ?>>&nbsp;&nbsp;— <?= e((string) $child['name']) ?></option>
<?php endforeach; ?>
<?php endforeach; ?>
</select>
</label>
<label>Kısa Açıklama <textarea name="short_description" rows="3" maxlength="500"><?= e($val('short_description')) ?></textarea></label>
<label>Uzun Açıklama (düz metin) <textarea name="long_description" rows="10"><?= e($val('long_description')) ?></textarea></label>
<fieldset>
<legend>Teknik Özellikler (specs)</legend>
<div id="specs-rows">
<?php foreach (($specs ?? []) as $i => $row): ?>
<div class="pair-row">
<input type="text" name="specs[<?= (int) $i ?>][label]" value="<?= e((string) ($row['label'] ?? '')) ?>" placeholder="Etiket" maxlength="200">
<input type="text" name="specs[<?= (int) $i ?>][value]" value="<?= e((string) ($row['value'] ?? '')) ?>" placeholder="Değer" maxlength="200">
<button type="button" onclick="this.parentNode.remove()">Satırı Sil</button>
</div>
<?php endforeach; ?>
</div>
<button type="button" onclick="abAddPair('specs-rows','specs')">Satır Ekle</button>
</fieldset>
<fieldset>
<legend>Varyantlar (variants)</legend>
<div id="variants-rows">
<?php foreach (($variants ?? []) as $i => $row): ?>
<div class="pair-row">
<input type="text" name="variants[<?= (int) $i ?>][label]" value="<?= e((string) ($row['label'] ?? '')) ?>" placeholder="Etiket" maxlength="200">
<input type="text" name="variants[<?= (int) $i ?>][value]" value="<?= e((string) ($row['value'] ?? '')) ?>" placeholder="Değer" maxlength="200">
<button type="button" onclick="this.parentNode.remove()">Satırı Sil</button>
</div>
<?php endforeach; ?>
</div>
<button type="button" onclick="abAddPair('variants-rows','variants')">Satır Ekle</button>
</fieldset>
<label>Sıra <input type="number" name="sort_order" value="<?= e($val('sort_order', '0')) ?>"></label>
<label><input type="checkbox" name="is_active" value="1"<?= ((int) ($o['is_active'] ?? ($p['is_active'] ?? 1)) === 1) ? ' checked' : '' ?>> Aktif</label>
<label><input type="checkbox" name="is_featured" value="1"<?= ((int) ($o['is_featured'] ?? ($p['is_featured'] ?? 0)) === 1) ? ' checked' : '' ?>> Öne Çıkan</label>
<button type="submit">Güncelle</button>
</form>
<script>
function abAddPair(containerId, field) {
  var box = document.getElementById(containerId);
  var i = box.querySelectorAll('.pair-row').length;
  var div = document.createElement('div');
  div.className = 'pair-row';
  div.innerHTML = '<input type="text" name="' + field + '[' + i + '][label]" placeholder="Etiket" maxlength="200">'
    + '<input type="text" name="' + field + '[' + i + '][value]" placeholder="Değer" maxlength="200">'
    + '<button type="button">Satırı Sil</button>';
  div.querySelector('button').addEventListener('click', function () { div.remove(); });
  box.appendChild(div);
}
</script>
<?php $content = (string) ob_get_clean(); ?>
<?php $title = 'Ürün Düzenle'; ?>
<?php include BASE_PATH . '/templates/admin/layout.php'; ?>