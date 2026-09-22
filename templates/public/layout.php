<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="tr">
<head>
<?php include BASE_PATH . '/templates/public/partials/head.php'; ?>
</head>
<body>
<?php include BASE_PATH . '/templates/public/partials/header.php'; ?>
<main class="site-main">
<div class="site-container">
<?= $content ?? '' ?>
</div>
</main>
<?php include BASE_PATH . '/templates/public/partials/footer.php'; ?>
</body>
</html>
