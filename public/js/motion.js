/* Camada de movimento das áreas logadas: entrada escalonada, contadores, gráficos que se desenham,
   holofote que segue o cursor e botões de download com estado. Sem JS, tudo aparece normalmente. */
(function () {
  'use strict';
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var reduz = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var main = document.querySelector('.app-content');

  /* ---------- downloads com estado (funciona mesmo com movimento reduzido) ---------- */
  $$('form[data-dl-card]').forEach(function (card) {
    var btn = card.querySelector('[data-dl]');
    card.addEventListener('submit', function (e) {
      if (!btn || !window.fetch || !window.URL || !URL.createObjectURL) return;
      e.preventDefault();
      if (btn.classList.contains('is-loading')) return;
      var url = new URL(card.getAttribute('action'), location.href);
      new FormData(card).forEach(function (v, k) { url.searchParams.set(k, v); });
      btn.classList.remove('is-done'); btn.classList.add('is-loading'); btn.setAttribute('aria-busy', 'true');
      var live = card.querySelector('[data-dl-live]');
      if (live) live.textContent = 'Gerando o arquivo…';
      fetch(url, { credentials: 'same-origin' }).then(function (r) {
        if (!r.ok || (r.headers.get('content-type') || '').indexOf('text/csv') !== 0) throw new Error('falhou');
        var nome = /filename="([^"]+)"/.exec(r.headers.get('content-disposition') || '');
        return r.blob().then(function (b) { return [b, nome ? nome[1] : 'relatorio.csv']; });
      }).then(function (res) {
        var a = document.createElement('a'), href = URL.createObjectURL(res[0]);
        a.href = href; a.download = res[1]; document.body.appendChild(a); a.click(); a.remove();
        setTimeout(function () { URL.revokeObjectURL(href); }, 4000);
        btn.classList.remove('is-loading'); btn.classList.add('is-done'); btn.removeAttribute('aria-busy');
        if (live) live.textContent = 'Pronto: ' + res[1] + ' foi baixado.';
        setTimeout(function () { btn.classList.remove('is-done'); }, 2600);
      }).catch(function () {
        btn.classList.remove('is-loading'); btn.removeAttribute('aria-busy');
        location.href = url.toString();
      });
    });
  });

  if (!main || reduz || !('IntersectionObserver' in window)) return;
  document.documentElement.classList.add('motion-on');

  /* ---------- entrada escalonada ---------- */
  var alvos = '.dash-hero, .dash-kpi, .dash-grid > *, .ui-metric, .ui-vcard, .dl-card, .app-content > .ui-card, .ui-grid-2 > *, .ui-section > .ui-card, .ui-alert, .ui-row-between, .ui-toolbar, .ui-table-wrap';
  var vistos = [];
  $$(alvos, main).forEach(function (el) {
    if (vistos.some(function (v) { return v !== el && v.contains(el); })) return;
    vistos.push(el);
  });
  var porPai = new Map();
  vistos.forEach(function (el) {
    var i = porPai.get(el.parentNode) || 0; porPai.set(el.parentNode, i + 1);
    el.setAttribute('data-reveal', '');
    el.style.setProperty('--d', Math.min(i, 8) * 70 + 'ms');
  });

  /* ---------- números que contam até o valor ---------- */
  function conta(el) {
    var no = Array.prototype.find.call(el.childNodes, function (n) { return n.nodeType === 3 && /^\s*\d{1,7}\s*$/.test(n.nodeValue); });
    if (!no) return;
    var fim = parseInt(no.nodeValue, 10); if (fim < 2) return;
    var t0 = null, dur = Math.min(1400, 500 + fim * 12);
    no.nodeValue = '0';
    function passo(t) {
      if (!t0) t0 = t; var p = Math.min(1, (t - t0) / dur), e = 1 - Math.pow(1 - p, 3);
      no.nodeValue = String(Math.round(fim * e));
      if (p < 1) requestAnimationFrame(passo);
    }
    requestAnimationFrame(passo);
  }

  var io = new IntersectionObserver(function (ents) {
    ents.forEach(function (en) {
      if (!en.isIntersecting) return;
      var el = en.target; io.unobserve(el);
      el.classList.add('is-in');
      if (el.querySelector('.ui-linechart, .ui-hbars')) el.classList.add('is-drawn');
      $$('.dash-kpi strong, .ui-metric-n, .dl-count, .dash-mini-stats strong', el).forEach(conta);
    });
  }, { rootMargin: '0px 0px -6% 0px', threshold: 0.08 });
  vistos.forEach(function (el) { io.observe(el); });
  $$('.ui-hbars', main).forEach(function (ol) { $$('.ui-hbar', ol).forEach(function (li, i) { li.style.setProperty('--i', i); }); });

  /* ---------- holofote que segue o cursor ---------- */
  if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
    $$('.ui-card, .dash-kpi, .ui-vcard, .dl-card, .ui-metric', main).forEach(function (el) {
      el.classList.add('has-spot');
      var pend = false, ev;
      el.addEventListener('pointermove', function (e) {
        ev = e; if (pend) return; pend = true;
        requestAnimationFrame(function () {
          var r = el.getBoundingClientRect();
          el.style.setProperty('--mx', (ev.clientX - r.left) + 'px');
          el.style.setProperty('--my', (ev.clientY - r.top) + 'px');
          pend = false;
        });
      });
    });
  }
})();
