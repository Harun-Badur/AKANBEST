<?php declare(strict_types=1); ?>
<nav>
<a href="/admin">Dashboard</a>
<a href="/admin/categories">Kategoriler</a>
<a href="/admin/client-references">Referanslar</a>
<a href="/admin/pages">Sayfalar</a>
<a href="/admin/settings">Ayarlar</a>
<form method="post" action="/admin/logout" style="display:inline">
<?= App\Core\Csrf::field() ?>
<button type="submit">Çıkış</button>
</form>
</nav>
