<?php declare(strict_types=1); ?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php
$headTitle = (isset($seoTitle) && is_string($seoTitle) && $seoTitle !== '')
    ? $seoTitle
    : ((isset($title) && is_string($title) && $title !== '') ? $title : 'Akan Best Ambalaj');
?>
<title><?= e($headTitle) ?></title>
<?php if (isset($metaDescription) && is_string($metaDescription) && $metaDescription !== ''): ?>
<meta name="description" content="<?= e($metaDescription) ?>">
<?php endif; ?>
<link rel="stylesheet" href="/assets/css/site.css">
