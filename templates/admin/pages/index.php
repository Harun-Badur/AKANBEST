<?php declare(strict_types=1); ?>
<?php ob_start(); ?>
<h1>Sayfalar</h1>
<p><a href="/admin/pages/create">Yeni Sayfa</a></p>
<table>
<thead><tr><th>Başlık</th><th>Slug</th><th>SEO Başlık</th><th>İşlemler</th></tr></thead>
<tbody>
<?php foreach (($rows ?? []) as $row): ?>
<tr>
<td><?= e((string) $row['title']) ?></td>
<td><?= e((string) $row['slug']) ?></td>
<td><?= e((string) ($row['seo_title'] ?? '—')) ?></td>
<td>
<a href="/admin/pages/<?= (int) $row['id'] ?>/edit">Düzenle</a>
<form method="post" action="/admin/pages/<?= (int) $row['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Silinsin mi?')">
<?= App\Core\Csrf::field() ?>
<button type="submit">Sil</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php $content = (string) ob_get_clean(); ?>
<?php $title = 'Sayfalar'; ?>
<?php include BASE_PATH . '/templates/admin/layout.php'; ?>
