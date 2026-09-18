<?php declare(strict_types=1); ?>
<?php ob_start(); ?>
<h1>Kategoriler</h1>
<p><a href="/admin/categories/create">Yeni Kategori</a></p>
<table>
<thead><tr><th>Ad</th><th>Slug</th><th>Üst</th><th>Sıra</th><th>Durum</th><th>İşlemler</th></tr></thead>
<tbody>
<?php $parents = []; ?>
<?php foreach (($rows ?? []) as $r): ?><?php $parents[(int) $r['id']] = (string) $r['name']; ?><?php endforeach; ?>
<?php foreach (($rows ?? []) as $row): ?>
<tr>
<td><?= e((string) $row['name']) ?></td>
<td><?= e((string) $row['slug']) ?></td>
<td><?= e($row['parent_id'] === null ? '—' : ($parents[(int) $row['parent_id']] ?? '#'.(int) $row['parent_id'])) ?></td>
<td><?= e((string) $row['sort_order']) ?></td>
<td><span class="badge <?= ((int) $row['is_active'] === 1) ? 'badge-on' : 'badge-off' ?>"><?= ((int) $row['is_active'] === 1) ? 'Aktif' : 'Pasif' ?></span></td>
<td>
<a href="/admin/categories/<?= (int) $row['id'] ?>/edit">Düzenle</a>
<form method="post" action="/admin/categories/<?= (int) $row['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Silinsin mi?')">
<?= App\Core\Csrf::field() ?>
<button type="submit">Sil</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php $content = (string) ob_get_clean(); ?>
<?php $title = 'Kategoriler'; ?>
<?php include BASE_PATH . '/templates/admin/layout.php'; ?>
