<?php declare(strict_types=1); ?>
<nav>
<a href="/admin">Dashboard</a>
<a href="/admin/categories">Kategoriler</a>
<form method="post" action="/admin/logout" style="display:inline">
<?= App\Core\Csrf::field() ?>
<button type="submit">Çıkış</button>
</form>
</nav>
