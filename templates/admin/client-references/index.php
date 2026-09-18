<?php declare(strict_types=1); ?>
<?php ob_start(); ?>
<h1>Referanslar</h1>
<p><a href="/admin/client-references/create">Yeni Referans</a></p>
<table>
<thead><tr><th>Firma</th><th>Logo</th><th>Web</th><th>Sıra</th><th>Durum</th><th>İşlemler</th></tr></thead>
<tbody>
<?php foreach (($rows ?? []) as $row): ?>
<tr>
<td><?= e((string) $row['company_name']) ?></td>
<td><?= e((string) ($row['logo_path'] ?? '—')) ?></td>
<td><?= e((string) ($row['website_url'] ?? '—')) ?></td>
<td><?= e((string) $row['sort_order']) ?></td>
<td><span class="badge <?= ((int) $row['is_active'] === 1) ? 'badge-on' : 'badge-off' ?>"><?= ((int) $row['is_active'] === 1) ? 'Aktif' : 'Pasif' ?></span></td>
<td>
<a href="/admin/client-references/<?= (int) $row['id'] ?>/edit">Düzenle</a>
<form method="post" action="/admin/client-references/<?= (int) $row['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Silinsin mi?')">
<?= App\Core\Csrf::field() ?>
<button type="submit">Sil</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php $content = (string) ob_get_clean(); ?>
<?php $title = 'Referanslar'; ?>
<?php include BASE_PATH . '/templates/admin/layout.php'; ?>
