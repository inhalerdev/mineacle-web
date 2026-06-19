(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn, { once: true });
      return;
    }
    fn();
  }

  function findBanTarget() {
    return document.querySelector('.bans-section, .bans-v3-results, .ban-list-wrap, #bansModule, #banRecordsModule');
  }

  ready(function () {
    var heroCopy = document.querySelector('.ban-hero-copy');
    if (!heroCopy || heroCopy.querySelector('.ban-hero-scroll-btn')) {
      return;
    }

    var target = findBanTarget();
    if (target && !target.id) {
      target.id = 'banRecordsModule';
    }

    var actions = document.createElement('div');
    actions.className = 'ban-hero-actions';

    var button = document.createElement('button');
    button.className = 'ban-hero-scroll-btn';
    button.type = 'button';
    button.textContent = 'Click To See Bans';
    button.setAttribute('aria-label', 'Scroll to active ban records');

    button.addEventListener('click', function () {
      var scrollTarget = findBanTarget();
      if (!scrollTarget) {
        return;
      }
      if (!scrollTarget.id) {
        scrollTarget.id = 'banRecordsModule';
      }
      scrollTarget.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    actions.appendChild(button);
    heroCopy.appendChild(actions);
  });
})();
