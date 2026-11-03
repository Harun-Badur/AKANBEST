(function () {
  'use strict';
  var detail = document.querySelector('.product-detail');
  if (!detail) return;
  var main = detail.querySelector('#product-main-image');
  detail.querySelectorAll('[data-image]').forEach(function (button) {
    button.addEventListener('click', function () {
      if (!main) return;
      main.src = button.dataset.image;
      main.alt = button.dataset.alt;
      detail.querySelectorAll('[data-image]').forEach(function (item) {
        item.setAttribute('aria-pressed', item === button ? 'true' : 'false');
      });
    });
  });
  var tabs = Array.from(detail.querySelectorAll('[role="tab"]'));
  function select(tab) {
    tabs.forEach(function (item) {
      var active = item === tab;
      item.setAttribute('aria-selected', active ? 'true' : 'false');
      item.tabIndex = active ? 0 : -1;
      document.getElementById(item.getAttribute('aria-controls')).hidden = !active;
    });
  }
  tabs.forEach(function (tab, index) {
    tab.addEventListener('click', function () { select(tab); });
    tab.addEventListener('keydown', function (event) {
      var next;
      if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
      else if (event.key === 'ArrowLeft') next = (index + tabs.length - 1) % tabs.length;
      else if (event.key === 'Home') next = 0;
      else if (event.key === 'End') next = tabs.length - 1;
      else return;
      event.preventDefault();
      select(tabs[next]);
      tabs[next].focus();
    });
  });
})();