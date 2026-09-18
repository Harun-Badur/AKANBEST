<?php declare(strict_types=1); ?>
<?php ob_start(); ?>
<h1>Ayarlar</h1>
<?php foreach (($errors ?? []) as $field => $fieldErrors): ?>
<p class="flash flash-error">Hata (<?= e((string) $field) ?>): <?= e(implode(', ', (array) $fieldErrors)) ?></p>
<?php endforeach; ?>
<?php if (is_string($success ?? null) && $success !== ''): ?><p class="flash flash-success"><?= e($success) ?></p><?php endif; ?>
<table>
<thead><tr><th>Anahtar</th><th>Değer</th><th>Kaydet</th></tr></thead>
<tbody>
<?php $existing = []; ?>
<?php foreach (($rows ?? []) as $row): ?>
<?php $existing[(string) $row['key']] = true; ?>
<tr>
<td><code><?= e((string) $row['key']) ?></code></td>
<td>
<form method="post" action="/admin/settings/update">
<?= App\Core\Csrf::field() ?>
<input type="hidden" name="key" value="<?= e((string) $row['key']) ?>">
<textarea name="value" rows="2" maxlength="1000"><?= e((string) ($row['value'] ?? '')) ?></textarea>
<button type="submit">Kaydet</button>
</form>
</td>
</tr>
<?php endforeach; ?>
<?php foreach (App\Models\Setting::WHITELIST_KEYS as $wk): ?>
<?php if (isset($existing[$wk])) continue; ?>
<tr>
<td><code><?= e($wk) ?></code></td>
<td colspan="2"><em>Tanımlı değil (phpMyAdmin ile INSERT gerekir — silme yok)</em></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php $content = (string) ob_get_clean(); ?>
<?php $title = 'Ayarlar'; ?>
<?php include BASE_PATH . '/templates/admin/layout.php'; ?>
