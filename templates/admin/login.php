<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="tr">
<head><meta charset="utf-8"><title>Yönetim Girişi</title></head>
<body>
<h1>Yönetim Girişi</h1>
<?php if (($notice ?? '') !== ''): ?><p><?= e($notice) ?></p><?php endif; ?>
<?php foreach (($errors ?? []) as $field => $fieldErrors): ?>
<p>Hata (<?= e((string) $field) ?>): <?= e(implode(', ', (array) $fieldErrors)) ?></p>
<?php endforeach; ?>
<form method="post" action="/admin/login">
<?= App\Core\Csrf::field() ?>
<label>E-posta <input type="email" name="email" value="<?= e((string) ($old['email'] ?? '')) ?>" required></label>
<label>Parola <input type="password" name="password" required></label>
<button type="submit">Giriş</button>
</form>
</body>
</html>
