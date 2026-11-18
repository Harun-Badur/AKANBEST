/* Akan Best Ambalaj — public site JS (M9). Vanilla, framework yok.
   Kapsam: mobil hamburger menü + klavye/erişilebilirlik. */
(function () {
  'use strict';

  var toggle = document.querySelector('.site-nav__toggle');
  var nav = document.querySelector('.site-nav');

  if (!toggle || !nav) {
    return;
  }

  initScrollSpy();

  function isOpen() {
    return nav.classList.contains('is-open');
  }

  function setOpen(open) {
    nav.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    toggle.setAttribute('aria-label', open ? 'Menüyü kapat' : 'Menüyü aç');
  }

  // Menü bağlantısına tıklayınca mobil menüyü kapat
  nav.addEventListener('click', function (event) {
    if (event.target.closest && event.target.closest('a') && isOpen()) {
      setOpen(false);
    }
  });

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
  // Ana sayfada kaydırırken aktif bölümü menüde vurgula
  function initScrollSpy() {
    if (!('IntersectionObserver' in window)) {
      return;
    }
    var links = {};
    nav.querySelectorAll('a[data-section]').forEach(function (a) {
      links[a.getAttribute('data-section')] = a;
    });
    var ids = ['urunler', 'kurumsal', 'teklif'];
    var sections = ids.map(function (id) { return document.getElementById(id); })
      .filter(Boolean);
    if (!sections.length || !document.querySelector('.hp-hero, .home-page')) {
      return;
    }
    var visible = {};
    function mark() {
      var current = 'top';
      sections.forEach(function (sec) {
        if (visible[sec.id]) { current = sec.id; }
      });
      Object.keys(links).forEach(function (key) {
        if (key === current) {
          links[key].setAttribute('aria-current', 'page');
        } else {
          links[key].removeAttribute('aria-current');
        }
      });
    }
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        visible[entry.target.id] = entry.isIntersecting;
      });
      mark();
    }, { rootMargin: '-35% 0px -55% 0px', threshold: 0 });
    sections.forEach(function (sec) { observer.observe(sec); });
  }
})();
