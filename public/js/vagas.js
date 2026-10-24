(function () {
  "use strict";
  var $ = function (s, r) {
    return (r || document).querySelector(s);
  };
  var $$ = function (s, r) {
    return Array.prototype.slice.call((r || document).querySelectorAll(s));
  };
  var reduzMovimento =
    window.matchMedia &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  $$("[data-carousel]").forEach(function (car) {
    var slides = $$(".vg-slide", car),
      dots = $$("[data-dot]", car),
      i = 0,
      timer;
    if (slides.length < 2) return;
    function go(n) {
      i = (n + slides.length) % slides.length;
      slides.forEach(function (s, k) {
        s.classList.toggle("is-active", k === i);
      });
      dots.forEach(function (d, k) {
        d.classList.toggle("is-active", k === i);
      });
    }
    function stop() {
      clearInterval(timer);
    }
    function play() {
      if (!reduzMovimento) {
        stop();
        timer = setInterval(function () {
          go(i + 1);
        }, 6000);
      }
    }
    $("[data-prev]", car).addEventListener("click", function () {
      go(i - 1);
      play();
    });
    $("[data-next]", car).addEventListener("click", function () {
      go(i + 1);
      play();
    });
    dots.forEach(function (d) {
      d.addEventListener("click", function () {
        go(+d.dataset.dot);
        play();
      });
    });
    car.addEventListener("mouseenter", stop);
    car.addEventListener("mouseleave", play);
    car.addEventListener("focusin", stop);
    car.addEventListener("focusout", play);
    var x0 = null;
    car.addEventListener(
      "touchstart",
      function (e) {
        x0 = e.touches[0].clientX;
      },
      { passive: true },
    );
    car.addEventListener("touchend", function (e) {
      if (x0 === null) return;
      var dx = e.changedTouches[0].clientX - x0;
      if (Math.abs(dx) > 40) {
        go(dx < 0 ? i + 1 : i - 1);
        play();
      }
      x0 = null;
    });
    play();
  });

  $$("[data-rail]").forEach(function (rail) {
    var trilho = $("[data-rail-track]", rail),
      ant = $("[data-rail-prev]", rail),
      prox = $("[data-rail-next]", rail);
    function passo() {
      return Math.max(240, trilho.clientWidth * 0.85);
    }
    function estado() {
      ant.disabled = trilho.scrollLeft <= 4;
      prox.disabled =
        trilho.scrollLeft + trilho.clientWidth >= trilho.scrollWidth - 4;
    }
    ant.addEventListener("click", function () {
      trilho.scrollBy({
        left: -passo(),
        behavior: reduzMovimento ? "auto" : "smooth",
      });
    });
    prox.addEventListener("click", function () {
      trilho.scrollBy({
        left: passo(),
        behavior: reduzMovimento ? "auto" : "smooth",
      });
    });
    trilho.addEventListener(
      "scroll",
      function () {
        window.requestAnimationFrame(estado);
      },
      { passive: true },
    );
    trilho.addEventListener("keydown", function (e) {
      if (e.key === "ArrowRight") {
        e.preventDefault();
        prox.click();
      }
      if (e.key === "ArrowLeft") {
        e.preventDefault();
        ant.click();
      }
    });
    window.addEventListener("resize", estado);
    estado();
  });

  var filtros = $("[data-filters]");
  if (filtros) {
    var cards = $$("[data-grid] .vg-card"),
      busca = $("[data-f-busca]"),
      area = $("[data-f-area]"),
      mod = $("[data-f-mod]");
    var chips = $$("[data-chip]"),
      tipo = "",
      contador = $("[data-count]"),
      vazio = $("[data-empty]");
    var normaliza = function (t) {
      return t
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "");
    };
    var aplicar = function () {
      var q = normaliza(busca.value.trim()),
        n = 0;
      cards.forEach(function (c) {
        var ok =
          (!q || normaliza(c.dataset.busca).indexOf(q) > -1) &&
          (!tipo || c.dataset.tipo === tipo) &&
          (!area.value || c.dataset.area === area.value) &&
          (!mod.value || c.dataset.modalidade === mod.value);
        c.hidden = !ok;
        if (ok) n++;
      });
      contador.textContent = n;
      vazio.hidden = n > 0;
      vazio.textContent = "Nenhuma vaga encontrada. Tente limpar os filtros.";
    };
    [busca, area, mod].forEach(function (el) {
      el.addEventListener("input", aplicar);
    });
    chips.forEach(function (c) {
      c.addEventListener("click", function () {
        tipo = c.dataset.chip;
        chips.forEach(function (x) {
          x.classList.toggle("is-active", x === c);
        });
        aplicar();
      });
    });
  }

  $$("[data-copy]").forEach(function (b) {
    b.addEventListener("click", function () {
      var txt = b.dataset.copy,
        orig = b.innerHTML;
      var ok = function () {
        b.textContent = "Copiado!";
        setTimeout(function () {
          b.innerHTML = orig;
        }, 1600);
      };
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(txt).then(ok);
      } else {
        var t = document.createElement("textarea");
        t.value = txt;
        document.body.appendChild(t);
        t.select();
        document.execCommand("copy");
        t.remove();
        ok();
      }
    });
  });

  /* ---------- QR Code por vaga (Modo TV): fundo branco, quiet zone e contraste máximo ---------- */
  $$("[data-qr]").forEach(function (el) {
    try {
      var q = window.qrcode(0, "M");
      q.addData(el.dataset.qr);
      q.make();
      el.innerHTML = q.createSvgTag({ cellSize: 4, margin: 3, scalable: true });
      var s = el.firstElementChild;
      s.setAttribute("role", "img");
      s.setAttribute("aria-label", "QR Code que abre esta vaga no site");
      s.removeAttribute("width");
      s.removeAttribute("height");
    } catch (e) {
      el.hidden = true;
    }
  });

  var tv = $("[data-tv]");
  if (tv) {
    var telas = $$(".vg-tv-slide"),
      n = 0,
      cont = $("[data-tv-count]");
    var mostra = function (k) {
      n = (k + telas.length) % telas.length;
      telas.forEach(function (s, j) {
        s.classList.toggle("is-active", j === n);
      });
      cont.textContent = "Vaga " + (n + 1) + " de " + telas.length;
    };
    if (telas.length) {
      mostra(0);
      if (telas.length > 1)
        setInterval(function () {
          mostra(n + 1);
        }, 9000);
    }
    document.addEventListener("keydown", function (e) {
      if (e.key === "f" || e.key === "F") {
        if (document.fullscreenElement) document.exitFullscreen();
        else document.documentElement.requestFullscreen();
      }
      if (e.key === "ArrowRight") mostra(n + 1);
      if (e.key === "ArrowLeft") mostra(n - 1);
    });
    setTimeout(
      function () {
        location.reload();
      },
      30 * 60 * 1000,
    );
  }
})();

(function () {
  "use strict";
  document.addEventListener(
    "click",
    function (ev) {
      var t = ev.target.closest ? ev.target : ev.target.parentNode;
      var botao = t.closest(".mainmenu-toggle");
      if (botao) {
        ev.preventDefault();
        ev.stopPropagation();
        var sec = document.getElementById("navigation-section");
        if (sec)
          sec.style.display = sec.style.display === "block" ? "none" : "block";
        return;
      }
      var h2 = t.closest("#navigation-section nav h2");
      if (
        h2 &&
        window.getComputedStyle(
          document.querySelector(".mainmenu-toggle") || document.body,
        ).display !== "none"
      ) {
        var ul = h2.parentNode.querySelector("ul");
        if (!ul) return;
        ev.preventDefault();
        ev.stopPropagation();
        ul.style.display = ul.style.display === "block" ? "none" : "block";
      }
    },
    true,
  );
})();

(function () {
  "use strict";
  document.addEventListener("click", function (ev) {
    var a = ev.target.closest ? ev.target.closest('a[href^="#"]') : null;
    if (!a || a.classList.contains("mainmenu-toggle")) return;
    var id = a.getAttribute("href").slice(1);
    if (!id) return;
    if (id === "topo") {
      ev.preventDefault();
      window.scrollTo({ top: 0, behavior: "smooth" });
      return;
    }
    var alvo = document.getElementById(id);
    if (!alvo) return;
    ev.preventDefault();
    alvo.scrollIntoView({ behavior: "smooth", block: "start" });
    if (!alvo.hasAttribute("tabindex")) alvo.setAttribute("tabindex", "-1");
    alvo.focus({ preventScroll: true });
  });
})();
