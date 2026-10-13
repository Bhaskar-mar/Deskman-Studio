/* Deskman Studio — site behaviour. No dependencies. Every module is a
   progressive enhancement: the page works with JS off (tabs show the first
   panel, FAQ is native <details>, forms post normally). */
(function () {
  'use strict';
  var d = document, root = d.documentElement;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var $ = function (s, c) { return (c || d).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || d).querySelectorAll(s)); };

  /* nav: scrolled shadow -------------------------------------------------- */
  var nav = $('#dmNav');
  if (nav) {
    var tick = false;
    var onScroll = function () {
      if (tick) { return; } tick = true;
      requestAnimationFrame(function () { nav.classList.toggle('is-scrolled', window.scrollY > 8); tick = false; });
    };
    window.addEventListener('scroll', onScroll, { passive: true }); onScroll();
  }

  /* mobile menu ------------------------------------------------------------ */
  var menu = $('#dmMnav'), opener = $('.nav .burger');
  if (menu && opener) {
    var close = function (focus) {
      menu.classList.remove('is-open'); root.classList.remove('menu-open');
      opener.setAttribute('aria-expanded', 'false');
      if (focus) { opener.focus(); }
    };
    opener.addEventListener('click', function () {
      menu.classList.add('is-open'); root.classList.add('menu-open');
      opener.setAttribute('aria-expanded', 'true');
      var first = $('a', menu); if (first) { first.focus(); }
    });
    $$('[data-close]', menu).forEach(function (b) { b.addEventListener('click', function () { close(true); }); });
    menu.addEventListener('click', function (e) { if (e.target.closest('a')) { close(false); } });
    d.addEventListener('keydown', function (e) {
      if (!menu.classList.contains('is-open')) { return; }
      if (e.key === 'Escape') { close(true); }
      if (e.key === 'Tab') { // keep focus inside the open menu
        var f = $$('a,button', menu), a = f[0], z = f[f.length - 1];
        if (e.shiftKey && d.activeElement === a) { e.preventDefault(); z.focus(); }
        else if (!e.shiftKey && d.activeElement === z) { e.preventDefault(); a.focus(); }
      }
    });
    window.matchMedia('(min-width: 1101px)').addEventListener('change', function (m) { if (m.matches) { close(false); } });
  }

  /* service tabs: click, arrow keys, gentle auto-advance while visible --------- */
  $$('[data-tabs]').forEach(function (box) {
    var tabs = $$('[role="tab"]', box), panels = $$('[role="tabpanel"]', box);
    var idx = 0, auto = !reduce, timer = null, seen = false;
    function show(i, focus) {
      idx = (i + tabs.length) % tabs.length;
      tabs.forEach(function (t, k) {
        var on = k === idx;
        t.classList.toggle('on', on); t.setAttribute('aria-selected', on ? 'true' : 'false'); t.tabIndex = on ? 0 : -1;
        // restart the progress bar animation
        var bar = $('.bar2 i', t); if (bar && on) { bar.style.animation = 'none'; void bar.offsetWidth; bar.style.animation = auto ? '' : 'none'; if (!auto) { bar.style.width = '100%'; } }
      });
      panels.forEach(function (p, k) { p.hidden = k !== idx; });
      if (focus) { tabs[idx].focus(); }
      schedule();
    }
    function schedule() {
      clearTimeout(timer);
      if (auto && seen) { timer = setTimeout(function () { show(idx + 1); }, 6000); }
    }
    function stop() { auto = false; clearTimeout(timer); tabs.forEach(function (t) { var b = $('.bar2 i', t); if (b) { b.style.animation = 'none'; b.style.width = '100%'; } }); }
    tabs.forEach(function (t, k) {
      t.addEventListener('click', function () { stop(); show(k); });
      t.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowDown' || e.key === 'ArrowRight') { e.preventDefault(); stop(); show(idx + 1, true); }
        if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') { e.preventDefault(); stop(); show(idx - 1, true); }
      });
    });
    box.addEventListener('mouseenter', function () { clearTimeout(timer); });
    box.addEventListener('mouseleave', schedule);
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (en) { seen = en[0].isIntersecting; if (seen) { schedule(); } else { clearTimeout(timer); } }, { threshold: .4 }).observe(box);
    }
    show(0);
  });

  /* packages: new / existing website --------------------------------------- */
  $$('.seg').forEach(function (seg) {
    var scope = seg.closest('section');
    $$('button[data-seg]', seg).forEach(function (b) {
      b.addEventListener('click', function () {
        $$('button', seg).forEach(function (x) { var on = x === b; x.classList.toggle('on', on); x.setAttribute('aria-selected', on ? 'true' : 'false'); });
        $$('.plans[data-set]', scope).forEach(function (p) { p.hidden = p.getAttribute('data-set') !== b.getAttribute('data-seg'); });
        $$('.plans:not([hidden]) .rv', scope).forEach(function (el) { el.classList.add('is-in'); });
      });
    });
  });

  /* work filters ------------------------------------------------------------ */
  $$('[data-filter]').forEach(function (bar) {
    var grid = $(bar.getAttribute('data-filter'));
    if (!grid) { return; }
    $$('button[data-f]', bar).forEach(function (b) {
      b.addEventListener('click', function () {
        var f = b.getAttribute('data-f');
        $$('button', bar).forEach(function (x) { var on = x === b; x.classList.toggle('on', on); x.setAttribute('aria-pressed', on ? 'true' : 'false'); });
        $$('.wk', grid).forEach(function (card) {
          card.hidden = !(f === '*' || (' ' + card.getAttribute('data-cats') + ' ').indexOf(' ' + f + ' ') > -1);
          if (!card.hidden) { card.classList.add('is-in'); }
        });
      });
    });
  });

  /* enquiry forms: JS flag (spam score), inline validation, no double submit -- */
  $$('form[data-enquiry], form.news').forEach(function (form) {
    var js = $('input[name="cf_js"]', form); if (js) { js.value = '1'; }
    form.addEventListener('submit', function (e) {
      var bad = null;
      $$('input[required], textarea[required]', form).forEach(function (el) {
        var wrap = el.closest('.fx');
        var ok = el.value.trim() !== '' && (el.type !== 'email' || /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(el.value.trim()));
        if (wrap) { wrap.classList.toggle('is-bad', !ok); }
        if (!ok && !bad) { bad = el; }
      });
      if (bad) { e.preventDefault(); bad.focus(); return; }
      var btn = $('button[type="submit"]', form);
      if (btn) { btn.disabled = true; btn.style.opacity = '.7'; }
    });
    $$('.fx input, .fx textarea', form).forEach(function (el) {
      el.addEventListener('input', function () { var w = el.closest('.fx'); if (w) { w.classList.remove('is-bad'); } });
    });
  });
  /* after a submit round-trip, bring the message into view */
  if (/[?&]deskman_contact=/.test(location.search)) {
    var target = $('.sent') || $('.notice--err');
    if (target) { setTimeout(function () { target.scrollIntoView({ block: 'center', behavior: reduce ? 'auto' : 'smooth' }); }, 60); }
  }

  /* reveal on scroll -------------------------------------------------------- */
  var rv = $$('.rv');
  if (reduce || !('IntersectionObserver' in window)) { rv.forEach(function (el) { el.classList.add('is-in'); }); }
  else {
    var io = new IntersectionObserver(function (en) {
      en.forEach(function (x) { if (x.isIntersecting) { x.target.classList.add('is-in'); io.unobserve(x.target); } });
    }, { rootMargin: '0px 0px -6% 0px', threshold: .06 });
    rv.forEach(function (el) { io.observe(el); });
  }
})();
