<?php declare(strict_types=1); ?>
<header class="site-header<?= (($fullBleed ?? false) === true) ? ' site-header--transparent' : '' ?>">
  <div class="site-container site-header__inner">
    <a class="site-brand site-brand__mark" href="/">
      <span class="site-brand__dot" aria-hidden="true"></span>
      <span>Akan Best Ambalaj</span>
    </a>
    <button class="site-nav__toggle" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="Menüyü aç">
      <span class="site-nav__toggle-bars" aria-hidden="true">
        <span></span><span></span><span></span>
      </span>
    </button>
    <nav class="site-nav" id="site-nav" aria-label="Ana menü">
      <ul class="site-nav__list">
        <li><a class="site-nav__link" href="/"<?= (($_SERVER['REQUEST_URI'] ?? '/') === '/' ? ' aria-current="page"' : '') ?>>Ana Sayfa</a></li>
        <li><a class="site-nav__link" href="/#urunler">Ürünler</a></li>
        <li><a class="site-nav__link" href="/#kurumsal">Kurumsal</a></li>
        <li><a class="site-nav__link" href="/#teklif">İletişim</a></li>
      </ul>
    </nav>
    <div class="site-header__cta">
      <a class="btn btn--accent btn--sm" href="/#teklif">Teklif Al</a>
    </div>
  </div>
</header>
