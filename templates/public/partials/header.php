<?php declare(strict_types=1); ?>
<header class="site-header<?= (($fullBleed ?? false) === true) ? ' site-header--transparent' : '' ?>">
  <div class="site-container site-header__inner">
    <a class="site-brand site-brand__mark" href="/">
      <img class="site-brand__logo" src="/assets/images/logo/akanbest-logo-mark-white.svg" alt="Akan Best Ambalaj" width="84" height="32">
      <span class="site-brand__name">Akan Best Ambalaj</span>
    </a>
    <button class="site-nav__toggle" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="Menüyü aç">
      <span class="site-nav__toggle-bars" aria-hidden="true">
        <span></span><span></span><span></span>
      </span>
    </button>
    <nav class="site-nav" id="site-nav" aria-label="Ana menü">
      <ul class="site-nav__list">
        <li><a class="site-nav__link" href="/" data-section="top"<?= (($_SERVER['REQUEST_URI'] ?? '/') === '/' ? ' aria-current="page"' : '') ?>>Ana Sayfa</a></li>
        <li><a class="site-nav__link" href="/#urunler" data-section="urunler">Ürünler</a></li>
        <li><a class="site-nav__link" href="/#kurumsal" data-section="kurumsal">Kurumsal</a></li>
        <li><a class="site-nav__link" href="/#teklif" data-section="teklif">İletişim</a></li>
      </ul>
    </nav>
    <div class="site-header__cta">
      <a class="btn btn--accent btn--sm" href="/#teklif">Teklif Al</a>
    </div>
  </div>
</header>
