(function () {
  'use strict';

  document.querySelectorAll('.iacce-card').forEach(function (card) {
    var tabs = card.querySelectorAll('.iacce-lang-tab');
    var panels = card.querySelectorAll('.iacce-lang-panel');
    if (!tabs.length) {
      return;
    }
    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        var lang = tab.getAttribute('data-lang');
        tabs.forEach(function (t) {
          t.classList.toggle('iacce-lang-tab--active', t === tab);
        });
        panels.forEach(function (p) {
          p.classList.toggle('iacce-lang-panel--active', p.getAttribute('data-lang') === lang);
        });
      });
    });
  });

  document.querySelectorAll('.iacce-restore-one').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var card = btn.closest('.iacce-card');
      if (!card) {
        return;
      }
      var lang = btn.getAttribute('data-lang');
      var input = card.querySelector('.iacce-input[data-lang="' + lang + '"]');
      var orig = card.getAttribute('data-orig-' + lang);
      if (input && orig !== null) {
        input.value = orig;
        input.dispatchEvent(new Event('input', { bubbles: true }));
      }
    });
  });

  document.querySelectorAll('.iacce-restore-link').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var card = btn.closest('.iacce-card--link');
      var input = card && card.querySelector('.iacce-link-input');
      var orig = card && card.getAttribute('data-orig-href');
      if (input && orig) {
        input.value = orig;
        input.dispatchEvent(new Event('input', { bubbles: true }));
      }
    });
  });

  var dirtyHint = document.querySelector('.iacce-dirty-hint');
  var anyDirty = false;

  function markCardDirty(card) {
    var changed = false;
    card.querySelectorAll('.iacce-input').forEach(function (input) {
      var lang = input.getAttribute('data-lang');
      var orig = card.getAttribute('data-orig-' + lang);
      if (orig !== null && input.value !== orig) {
        changed = true;
      }
    });
    card.classList.toggle('iacce-card--dirty', changed);
    card.classList.toggle('iacce-card--modified', changed);
    return changed;
  }

  function updateDirtyHint() {
    anyDirty = false;
    document.querySelectorAll('.iacce-card--dirty').forEach(function () {
      anyDirty = true;
    });
    if (dirtyHint) {
      dirtyHint.hidden = !anyDirty;
    }
  }

  var textForm = document.getElementById('iacce-text-form');
  if (textForm) {
    textForm.addEventListener('input', function (e) {
      var card = e.target.closest('.iacce-card');
      if (card) {
        markCardDirty(card);
        updateDirtyHint();
      }
    });
    textForm.querySelectorAll('.iacce-card').forEach(markCardDirty);
    updateDirtyHint();
    window.addEventListener('beforeunload', function (e) {
      if (!anyDirty) {
        return;
      }
      e.preventDefault();
      e.returnValue = '';
    });
    textForm.addEventListener('submit', function () {
      anyDirty = false;
    });
  }

  var linksForm = document.getElementById('iacce-links-form');
  if (linksForm) {
    linksForm.addEventListener('input', function (e) {
      var card = e.target.closest('.iacce-card--link');
      if (!card) {
        return;
      }
      var input = card.querySelector('.iacce-link-input');
      var orig = card.getAttribute('data-orig-href');
      var changed = input && orig && input.value !== orig;
      card.classList.toggle('iacce-card--dirty', !!changed);
    });
  }
})();
