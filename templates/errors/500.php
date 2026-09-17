<?php

declare(strict_types=1);
?>
<!doctype html>
<html lang="tr">
<head><meta charset="utf-8"><title>500 — Sunucu hatası</title></head>
<body>
<h1>500 — Sunucu hatası</h1>
<p>İstek işlenirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.</p>
<?php if ($local ?? false): ?>
<h2>Local hata ayrıntıları</h2>
<p><?= e($message ?? '') ?></p>
<pre><?= e($trace ?? '') ?></pre>
<?php endif; ?>
</body>
</html>
