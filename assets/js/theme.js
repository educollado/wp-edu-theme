(function () {
  'use strict';

  // ── Nav mobile toggle ──────────────────────────────────────────────────────

  var nav = document.getElementById('site-nav');
  var toggle = nav && nav.querySelector('.nav__toggle');

  if (nav && toggle) {
    toggle.addEventListener('click', function () {
      var isOpen = nav.classList.toggle('nav--open');
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    document.addEventListener('click', function (e) {
      if (!nav.contains(e.target)) {
        nav.classList.remove('nav--open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        nav.classList.remove('nav--open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // ── H2: primera palabra en negro, resto en terracota ───────────────────────

  document.querySelectorAll('h2').forEach(function (h) {
    var text = h.textContent.trim();
    if (!text) return;
    var spaceIdx = text.indexOf(' ');
    if (spaceIdx === -1) return; // una sola palabra, sin efecto
    var span = document.createElement('span');
    span.className = 'h2-first';
    span.textContent = text.slice(0, spaceIdx);
    h.textContent = '';
    h.appendChild(span);
    h.appendChild(document.createTextNode(text.slice(spaceIdx)));
    h.classList.add('h2--split');
  });

  // ── TOC dinámico ───────────────────────────────────────────────────────────

  var tocNav   = document.getElementById('toc-nav');
  var tocCard  = document.getElementById('toc-card');
  var prose    = document.querySelector('.prose');

  if (tocNav && prose) {
    var headings = prose.querySelectorAll('h2, h3');

    if (headings.length === 0) {
      if (tocCard) tocCard.style.display = 'none';
      return;
    }

    // Asignar IDs si no tienen
    headings.forEach(function (h, i) {
      if (!h.id) {
        h.id = 'heading-' + i;
      }
    });

    // Construir lista TOC
    var ul = document.createElement('ul');
    ul.className = 'toc';

    headings.forEach(function (h) {
      var li = document.createElement('li');
      var tag = h.tagName.toLowerCase();

      if (tag === 'h2') {
        // Detectar si es prose-section-title (h2 con clase especial)
        if (h.classList.contains('prose-section-title')) {
          li.className = 'toc__item toc__item--section';
        } else {
          li.className = 'toc__item toc__item--h2';
        }
      } else if (tag === 'h3') {
        li.className = 'toc__item toc__item--h3';
      } else {
        li.className = 'toc__item';
      }

      var a = document.createElement('a');
      a.href = '#' + h.id;
      a.textContent = h.textContent;

      li.appendChild(a);
      ul.appendChild(li);
    });

    tocNav.appendChild(ul);

    // Resaltar sección activa al scrollear
    var tocItems = ul.querySelectorAll('.toc__item');
    var tocLinks = ul.querySelectorAll('a');

    var ticking = false;
    function onScroll() {
      if ( ticking ) return;
      ticking = true;
      window.requestAnimationFrame( function () {
        var scrollY = window.scrollY + 100;
        var active  = null;

        headings.forEach(function (h) {
          if (h.offsetTop <= scrollY) {
            active = h.id;
          }
        });

        tocLinks.forEach(function (a) {
          var li = a.parentElement;
          if (a.getAttribute('href') === '#' + active) {
            li.classList.add('toc__item--active');
          } else {
            li.classList.remove('toc__item--active');
          }
        });
        ticking = false;
      });
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  } else if (tocCard && (!prose || prose.querySelectorAll('h2, h3').length === 0)) {
    if (tocCard) tocCard.style.display = 'none';
  }

}());
