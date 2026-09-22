<?php declare(strict_types=1); ?>
<?php ob_start(); ?>
<h1>Ürün Görselleri — <?= e((string) (($product['name'] ?? 'Ürün'))) ?></h1>
<p><a href="/admin/products">Ürünlere Dön</a></p>
<?php if (is_string($success ?? null) && $success !== ''): ?><p class="flash flash-success"><?= e($success) ?></p><?php endif; ?>
<?php foreach (($errors ?? []) as $field => $fieldErrors): ?>
<p class="flash flash-error">Hata (<?= e((string) $field) ?>): <?= e(implode(', ', (array) $fieldErrors)) ?></p>
<?php endforeach; ?>

<h2>Yeni Yükleme</h2>
<form method="post" action="/admin/products/<?= (int) (($product['id'] ?? 0)) ?>/images" enctype="multipart/form-data">
<?= App\Core\Csrf::field() ?>
<label>Dosyalar (JPEG/PNG/WebP, max 5 MB)
<input type="file" name="images[]" multiple accept=".jpg,.jpeg,.png,.webp">
</label>
<button type="submit">Yükle</button>
</form>

<h2>Mevcut Görseller</h2>
<form method="post" action="/admin/products/<?= (int) (($product['id'] ?? 0)) ?>/images/update">
<?= App\Core\Csrf::field() ?>
<table>
<thead><tr><th>Önizleme</th><th>Alt Metin</th><th>Sıra</th><th>Birincil</th><th>Sil</th></tr></thead>
<tbody>
<?php foreach (($images ?? []) as $img): ?>
<tr>
<td><img src="/<?= e((string) $img['image_path']) ?>" alt="" width="120" loading="lazy"></td>
<td><input type="text" name="alts[<?= (int) $img['id'] ?>]" value="<?= e((string) ($img['alt_text'] ?? '')) ?>" maxlength="255"></td>
<td><input type="number" name="sorts[<?= (int) $img['id'] ?>]" value="<?= e((string) $img['sort_order']) ?>"></td>
<td>
<?php if ((int) $img['is_primary'] === 1): ?>
<span class="badge badge-on">Birincil</span>
<?php endif; ?>
</td>
<td>
<button type="submit" form="primary-<?= (int) $img['id'] ?>">Birincil Yap</button>
<button type="submit" form="delete-<?= (int) $img['id'] ?>" onclick="return confirm('Silinsin mi?')">Sil</button>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<button type="submit">Alt Metin ve Sıralamayı Kaydet</button>
</form>

<?php foreach (($images ?? []) as $img): ?>
<form id="primary-<?= (int) $img['id'] ?>" method="post" action="/admin/products/<?= (int) (($product['id'] ?? 0)) ?>/images/<?= (int) $img['id'] ?>/primary">
<?= App\Core\Csrf::field() ?>
</form>
<form id="delete-<?= (int) $img['id'] ?>" method="post" action="/admin/products/<?= (int) (($product['id'] ?? 0)) ?>/images/<?= (int) $img['id'] ?>/delete">
<?= App\Core\Csrf::field() ?>
</form>
<?php endforeach; ?>

<?php $content = (string) ob_get_clean(); ?>
<?php $title = 'Ürün Görselleri'; ?>
<?php include BASE_PATH . '/templates/admin/layout.php'; ?>
