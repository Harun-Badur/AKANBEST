/* Akan Best Ambalaj — public site JS (M9). Vanilla, framework yok.
   Kapsam: mobil hamburger menü + klavye/erişilebilirlik. */
(function () {
  'use strict';

  var toggle = document.querySelector('.site-nav__toggle');
  var nav = document.querySelector('.site-nav');

  if (!toggle || !nav) {
    return;
  }

  function isOpen() {
    return nav.classList.contains('is-open');
  }

  function setOpen(open) {
    nav.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    toggle.setAttribute('aria-label', open ? 'Menüyü kapat' : 'Menüyü aç');
  }

  toggle.addEventListener('click', function (event) {
    event.preventDefault();
    setOpen(!isOpen());
  });

  // ESC ile kapat
  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && isOpen()) {
      setOpen(false);
      toggle.focus();
    }
  });

  // Menü dışına tıklayınca kapat
  document.addEventListener('click', function (event) {
    if (isOpen() && !nav.contains(event.target) && !toggle.contains(event.target)) {
      setOpen(false);
    }
  });

  // Yeniden boyutlandırdaki masaüstünde menüyü sıfırla
  var desktop = window.matchMedia('(min-width: 769px)');
  function resetOnDesktop(query) {
    if (query.matches) {
      setOpen(false);
    }
  }
  if (typeof desktop.addEventListener === 'function') {
    desktop.addEventListener('change', resetOnDesktop);
  } else if (typeof desktop.addListener === 'function') {
    desktop.addListener(resetOnDesktop);
  }
})();
