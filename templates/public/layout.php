<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="tr">
<head>
<?php include BASE_PATH . '/templates/public/partials/head.php'; ?>
</head>
<?php
$bodyClass = '';
if (($homePage ?? false) === true) {
    $bodyClass = 'home-page';
} elseif (($productPage ?? false) === true) {
    $bodyClass = 'catalog-page product-page';
} elseif (($catalogPage ?? false) === true) {
    $bodyClass = 'catalog-page';
} elseif (($errorPage ?? false) === true) {
    $bodyClass = 'error-page';
}
?>
<body<?= $bodyClass !== '' ? ' class="' . e($bodyClass) . '"' : '' ?>>
<a class="site-skip-link" href="#site-main">İçeriğe geç</a>
<?php include BASE_PATH . '/templates/public/partials/header.php'; ?>
<main class="site-main<?= (($fullBleed ?? false) === true) ? ' site-main--flush' : '' ?>" id="site-main">
<?php if (($fullBleed ?? false) === true): ?>
<?= $content ?? '' ?>
<?php else: ?>
<div class="site-container">
<?= $content ?? '' ?>
</div>
<?php endif; ?>
</main>
<?php include BASE_PATH . '/templates/public/partials/footer.php'; ?>
</body>
</html>
