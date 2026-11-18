/* Akan Best Ambalaj — public site JS (M9). Vanilla, framework yok.
   Kapsam: mobil hamburger menü + klavye/erişilebilirlik. */
(function () {
  'use strict';

  // Kaydırınca header'a gölge/düz arka plan (is-scrolled)
  var header = document.querySelector('.site-header');
  if (header) {
    var onScroll = function () {
      header.classList.toggle('is-scrolled', window.scrollY > 8);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

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
  // Ana sayfada kaydırırken aktif bölümü menüde vurgula (scroll tabanlı, rAF ile throttle).
  // Kural: header + 80px çizgisini geçmiş SON bölüm aktif; eşleşme yoksa önceki durum korunur.
  // Ana Sayfa yalnızca hero'dayken; sayfa sonunda/footer'da İletişim. Alt sayfalarda hiçbiri.
  function initScrollSpy() {
    var links = {};
    nav.querySelectorAll('a[data-section]').forEach(function (a) {
      links[a.getAttribute('data-section')] = a;
    });
    var sections = ['urunler', 'kurumsal', 'teklif']
      .map(function (id) { return document.getElementById(id); })
      .filter(Boolean);
    var hero = document.querySelector('.hp-hero');
    if (!sections.length || !hero) {
      return;
    }

    var current = null;
    var locked = null;     // menü tıklamasıyla sabitlenen hedef
    var lockTimer = 0;
    var ticking = false;

    function setActive(key) {
      if (key === current) { return; }
      current = key;
      Object.keys(links).forEach(function (k) {
        if (k === key) {
          links[k].setAttribute('aria-current', 'page');
        } else {
          links[k].removeAttribute('aria-current');
        }
      });
    }

    function compute() {
      ticking = false;
      if (locked) {
        setActive(locked);
        return;
      }
      var line = (header ? header.getBoundingClientRect().height : 64) + 80;
      var doc = document.documentElement;
      if (window.scrollY + window.innerHeight >= doc.scrollHeight - 2) {
        setActive('teklif');
        return;
      }
      var found = null;
      sections.forEach(function (sec) {
        if (sec.getBoundingClientRect().top <= line) { found = sec.id; }
      });
      if (found) {
        setActive(found);
      } else if (hero.getBoundingClientRect().bottom > line) {
        setActive('top');
      }
      // aksi halde önceki aktif durum korunur
    }

    function schedule() {
      if (!ticking) {
        ticking = true;
        // rAF durdurulan (gizli/arka plan) sekmelerde takılmasın diye zaman aşımı yedeği
        var done = false;
        var run = function () {
          if (!done) { done = true; compute(); }
        };
        window.requestAnimationFrame(run);
        window.setTimeout(run, 100);
      }
    }

    function release() {
      locked = null;
      schedule();
    }

    // Kaydırma durunca (150ms sessizlik) kilidi bırak; kullanıcı müdahale ederse hemen bırak
    window.addEventListener('scroll', function () {
      if (locked) {
        window.clearTimeout(lockTimer);
        lockTimer = window.setTimeout(release, 150);
      }
      schedule();
    }, { passive: true });
    ['wheel', 'touchstart', 'keydown'].forEach(function (name) {
      window.addEventListener(name, function () {
        if (locked) { window.clearTimeout(lockTimer); release(); }
      }, { passive: true });
    });
    window.addEventListener('resize', schedule);
    window.addEventListener('load', schedule);

    // Menü tıklaması: hedef bölümü hemen sabitle, kaydırma bitince normal moda dön
    nav.addEventListener('click', function (event) {
      var link = event.target.closest && event.target.closest('a[data-section]');
      var key = link && link.getAttribute('data-section');
      if (!key || key === 'top' || !links[key]) { return; }
      locked = key;
      setActive(key);
      window.clearTimeout(lockTimer);
      lockTimer = window.setTimeout(release, 1200); // scroll olayı hiç gelmezse (zaten hedefteyse)
    });
    var cta = document.querySelector('.site-header__cta a[href="/#teklif"]');
    if (cta) {
      cta.addEventListener('click', function () {
        locked = 'teklif';
        setActive('teklif');
        window.clearTimeout(lockTimer);
        lockTimer = window.setTimeout(release, 1200);
      });
    }

    compute();
  }
})();
