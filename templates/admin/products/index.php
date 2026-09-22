<?php declare(strict_types=1); ?>
<?php ob_start(); ?>
<h1>Ürünler</h1>
<p><a href="/admin/products/create">Yeni Ürün</a></p>
<?php if (is_string($success ?? null) && $success !== ''): ?><p class="flash flash-success"><?= e($success) ?></p><?php endif; ?>
<table>
<thead><tr><th>Ad</th><th>Kod</th><th>Kategori</th><th>Sıra</th><th>Durum</th><th>Öne Çıkan</th><th>İşlemler</th></tr></thead>
<tbody>
<?php $cats = $categories ?? []; ?>
<?php foreach (($rows ?? []) as $row): ?>
<tr>
<td><?= e((string) $row['name']) ?></td>
<td><?= e((string) ($row['code'] ?? '—')) ?></td>
<td><?= e($cats[(int) $row['category_id']] ?? ('#'.(int) $row['category_id'])) ?></td>
<td><?= e((string) $row['sort_order']) ?></td>
<td><span class="badge <?= ((int) $row['is_active'] === 1) ? 'badge-on' : 'badge-off' ?>"><?= ((int) $row['is_active'] === 1) ? 'Aktif' : 'Pasif' ?></span></td>
<td><span class="badge <?= ((int) $row['is_featured'] === 1) ? 'badge-on' : 'badge-off' ?>"><?= ((int) $row['is_featured'] === 1) ? 'Evet' : 'Hayır' ?></span></td>
<td>
<a href="/admin/products/<?= (int) $row['id'] ?>/edit">Düzenle</a>
<a href="/admin/products/<?= (int) $row['id'] ?>/images">Görseller</a>
<form method="post" action="/admin/products/<?= (int) $row['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Silinsin mi?')">
<?= App\Core\Csrf::field() ?>
<button type="submit">Sil</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php $content = (string) ob_get_clean(); ?>
<?php $title = 'Ürünler'; ?>
<?php include BASE_PATH . '/templates/admin/layout.php'; ?>
