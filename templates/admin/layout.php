<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title><?= e($title ?? 'Admin') ?> — Akan Best Ambalaj</title>
<style>
body{font-family:Arial,Helvetica,sans-serif;margin:0;background:#f4f4f4;color:#222}
header{background:#1f2937;color:#fff;padding:12px 20px}
header nav a{color:#fff;margin-right:14px;text-decoration:none}
header nav form{display:inline}
main{padding:20px;max-width:960px;margin:0 auto;background:#fff;min-height:60vh}
table{width:100%;border-collapse:collapse}
th,td{border:1px solid #ddd;padding:8px;text-align:left}
.badge{display:inline-block;padding:2px 8px;border-radius:4px;font-size:12px}
.badge-on{background:#dcfce7}.badge-off{background:#fee2e2}
.flash{padding:10px;margin:0 0 12px;border-radius:4px}
.flash-success{background:#dcfce7}.flash-error{background:#fee2e2}
label{display:block;margin:8px 0}
input[type=text],input[type=email],input[type=number],textarea,select{width:100%;max-width:480px;padding:8px}
button{padding:8px 14px;cursor:pointer}
footer{padding:12px 20px;color:#666;font-size:12px}
</style>
</head>
<body>
<?php include BASE_PATH . '/templates/admin/partials/header.php'; ?>
<main>
<?php include BASE_PATH . '/templates/admin/partials/flash.php'; ?>
<?= $content ?? '' ?>
</main>
<?php include BASE_PATH . '/templates/admin/partials/footer.php'; ?>
</body>
</html>
