(function () {
  "use strict";
  var $ = function (s, r) {
    return (r || document).querySelector(s);
  };
  var $$ = function (s, r) {
    return Array.prototype.slice.call((r || document).querySelectorAll(s));
  };
  var reduz =
    window.matchMedia &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  /* ---------- modo noturno (tokens: só troca data-theme) ---------- */
  function tema(t) {
    document.documentElement.setAttribute("data-theme", t);
    try {
      localStorage.setItem("vg-theme", t);
    } catch (e) {}
    $$("[data-theme-toggle]").forEach(function (b) {
      b.setAttribute("aria-pressed", t === "dark");
      b.setAttribute(
        "aria-label",
        t === "dark" ? "Ativar modo claro" : "Ativar modo noturno",
      );
    });
  }
  $$("[data-theme-toggle]").forEach(function (b) {
    b.addEventListener("click", function () {
      tema(
        document.documentElement.getAttribute("data-theme") === "dark"
          ? "light"
          : "dark",
      );
    });
  });
  tema(document.documentElement.getAttribute("data-theme") || "light");

  /* ---------- menu lateral (mobile) ---------- */
  var side = $(".app-side"),
    scrim = $(".app-scrim");
  function menu(a) {
    if (!side) return;
    side.classList.toggle("is-open", a);
    scrim.classList.toggle("is-open", a);
  }
  $$("[data-menu-open]").forEach(function (b) {
    b.addEventListener("click", function () {
      menu(true);
    });
  });
  if (scrim)
    scrim.addEventListener("click", function () {
      menu(false);
    });
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") menu(false);
  });

  /* ---------- modais de confirmação (no lugar do alert/confirm do navegador) ---------- */
  var dlg = $("#ui-dialog");
  function dialogo(o) {
    return new Promise(function (resolve) {
      if (!dlg || !dlg.showModal) {
        // navegador sem <dialog>: cai no confirm/prompt nativo
        if (o.motivo) {
          var m = window.prompt(
            o.titulo + "\n\nInforme o motivo (mínimo 5 caracteres):",
          );
          resolve({
            ok: !!m && m.trim().length >= 5,
            motivo: (m || "").trim(),
          });
        } else
          resolve({
            ok: window.confirm(o.titulo + "\n\n" + (o.texto || "")),
            motivo: "",
          });
        return;
      }
      $("#ui-dlg-title").textContent = o.titulo;
      $("#ui-dlg-text").textContent = o.texto || "";
      var ta = $("#ui-dlg-motivo"),
        er = $("#ui-dlg-err"),
        ok = $("#ui-dlg-ok");
      ta.hidden = !o.motivo;
      ta.value = "";
      er.textContent = "";
      ta.classList.remove("is-invalid");
      ok.textContent = o.ok || "Confirmar";
      ok.className =
        "ui-btn " + (o.tom === "danger" ? "ui-btn-danger" : "ui-btn-primary");
      var fim = function (v) {
        dlg.removeEventListener("close", aoFechar);
        ok.onclick = null;
        dlg.close();
        resolve(v);
      };
      var aoFechar = function () {
        resolve({ ok: false, motivo: "" });
      };
      dlg.addEventListener("close", aoFechar, { once: true });
      ok.onclick = function () {
        if (o.motivo && ta.value.trim().length < 5) {
          er.textContent = "Informe o motivo (mínimo de 5 caracteres).";
          ta.classList.add("is-invalid");
          ta.focus();
          return;
        }
        dlg.removeEventListener("close", aoFechar);
        dlg.close();
        resolve({ ok: true, motivo: ta.value.trim() });
      };
      $("#ui-dlg-cancel").onclick = function () {
        fim({ ok: false, motivo: "" });
      };
      dlg.showModal();
      (o.motivo ? ta : ok).focus();
    });
  }
  document.addEventListener(
    "submit",
    function (ev) {
      var f = ev.target;
      if (
        !f.matches ||
        !(f.matches("form[data-confirm]") || f.matches("form[data-motivo]")) ||
        f.dataset.ok === "1"
      )
        return;
      ev.preventDefault();
      var motivo = f.hasAttribute("data-motivo");
      dialogo({
        titulo: f.dataset.confirmTitle || f.dataset.motivo || "Confirmar ação",
        texto:
          f.dataset.confirm || "O motivo será enviado à empresa por e-mail.",
        ok: f.dataset.confirmOk,
        tom: f.dataset.confirmTone || (motivo ? "danger" : "primary"),
        motivo: motivo,
      }).then(function (r) {
        if (!r.ok) return;
        if (motivo) {
          var h = f.querySelector("input[name=motivo]");
          if (h) h.value = r.motivo;
        }
        f.dataset.ok = "1";
        f.submit();
      });
    },
    true,
  );

  /* ---------- fechar popovers ao clicar fora ---------- */
  document.addEventListener("click", function (e) {
    $$("details.ui-bell[open]").forEach(function (d) {
      if (!d.contains(e.target)) d.open = false;
    });
  });

  /* ---------- copiar ---------- */
  $$("[data-copy]").forEach(function (b) {
    b.addEventListener("click", function () {
      var t = b.dataset.copy,
        o = b.innerHTML,
        ok = function () {
          b.textContent = "Copiado";
          setTimeout(function () {
            b.innerHTML = o;
          }, 1500);
        };
      if (navigator.clipboard && navigator.clipboard.writeText)
        navigator.clipboard.writeText(t).then(ok);
    });
  });

  /* ---------- máscaras ---------- */
  function maskCnpj(v) {
    // CNPJ alfanumérico (IN RFB 2.229/2024): 12 primeiros letras/números, 2 últimos dígitos
    v = v.replace(/[^0-9A-Za-z]/g, "").toUpperCase();
    var base = "";
    for (var k = 0; k < v.length && base.length < 14; k++) {
      if (base.length >= 12 && !/\d/.test(v[k])) continue;
      base += v[k];
    }
    var o = "";
    for (var i = 0; i < base.length; i++) {
      if (i === 2 || i === 5) o += ".";
      if (i === 8) o += "/";
      if (i === 12) o += "-";
      o += base[i];
    }
    return o;
  }
  function maskTel(v) {
    var d = v.replace(/\D/g, "").slice(0, 11),
      cut = d.length > 10 ? 5 : 4,
      r = d.slice(2);
    return (
      d.slice(0, 2) +
      (r ? " " + r.slice(0, cut) : "") +
      (r.length > cut ? "-" + r.slice(cut) : "")
    );
  }
  function maskMoney(v) {
    v = v.replace(/[^\d,]/g, "");
    var p = v.split(",");
    return p.length > 1 ? p[0] + "," + p.slice(1).join("").slice(0, 2) : p[0];
  }
  function maskCep(v) {
    var d = v.replace(/\D/g, "").slice(0, 8);
    return d.length > 5 ? d.slice(0, 5) + "-" + d.slice(5) : d;
  }
  function maskInt(v) {
    return v.replace(/\D/g, "").slice(0, 2);
  }
  var MASC = {
    cnpj: maskCnpj,
    tel: maskTel,
    money: maskMoney,
    cep: maskCep,
    int: maskInt,
  };
  $$("[data-mask]").forEach(function (i) {
    var f = MASC[i.dataset.mask];
    i.addEventListener("input", function () {
      i.value = f(i.value);
    });
    if (i.value) i.value = f(i.value);
  });
  function cnpjValido(v) {
    var c = v.replace(/[^0-9A-Za-z]/g, "").toUpperCase();
    if (!/^[A-Z0-9]{12}[0-9]{2}$/.test(c) || /^(.)\1{13}$/.test(c))
      return false;
    function dv(b) {
      var s = 0,
        p = 2;
      for (var i = b.length - 1; i >= 0; i--) {
        s += (b.charCodeAt(i) - 48) * p;
        p = p === 9 ? 2 : p + 1;
      }
      var r = s % 11;
      return r < 2 ? 0 : 11 - r;
    }
    var d1 = dv(c.slice(0, 12));
    return d1 === +c[12] && dv(c.slice(0, 12) + d1) === +c[13];
  }
  function urlOk(v, host) {
    v = v.trim();
    if (!v) return true;
    if (!/^[a-z][a-z0-9+.-]*:/i.test(v)) v = "https://" + v;
    try {
      var u = new URL(v);
      if (
        !/^https?:$/.test(u.protocol) ||
        !/^[a-z0-9.-]+\.[a-z]{2,}$/i.test(u.hostname) ||
        u.username
      )
        return false;
      if (
        host &&
        !new RegExp("(^|\\.)" + host.replace(".", "\\.") + "$", "i").test(
          u.hostname,
        )
      )
        return false;
      return true;
    } catch (e) {
      return false;
    }
  }
  var senhaForte = function (s) {
    return s.length >= 8 && /[A-Za-z]/.test(s) && /\d/.test(s);
  };

  /* ---------- validação ---------- */
  function wrapErr(c) {
    var b = c.closest(".ui-field") || c.parentNode;
    return { box: b, err: b && b.querySelector("[data-err]") };
  }
  function erro(c, msg) {
    var w = wrapErr(c),
      alvo = c.matches("textarea[data-chips-required]")
        ? $(".ui-chips", w.box)
        : c;
    if (alvo) alvo.classList.toggle("is-invalid", !!msg);
    c.setAttribute("aria-invalid", msg ? "true" : "false");
    if (w.err) w.err.textContent = msg || "";
    return !msg;
  }
  function valida(c) {
    if (c.type === "hidden" || c.disabled || c.closest("[hidden]")) return true;
    var v = (c.value || "").trim();
    if (c.type === "checkbox" && c.hasAttribute("data-need-one")) return true;
    if (c.type === "checkbox")
      return erro(
        c,
        c.required && !c.checked ? "Confirme para continuar." : "",
      );
    if (c.type === "radio") return true;
    if (c.type === "file") {
      var f = c.files[0];
      if (!f) return erro(c, c.required ? "Anexe um arquivo." : "");
      var ok = c.hasAttribute("data-img-only")
        ? ["image/jpeg", "image/png"]
        : ["application/pdf", "image/jpeg", "image/png"];
      if (ok.indexOf(f.type) < 0) return erro(c, "Formato não permitido.");
      if (f.size > (+c.dataset.max || 5) * 1048576)
        return erro(c, "Arquivo maior que " + (c.dataset.max || 5) + " MB.");
      return erro(c, "");
    }
    if (c.required && !v)
      return erro(
        c,
        c.tagName === "SELECT"
          ? "Selecione uma opção."
          : "Preencha este campo.",
      );
    if (v && c.minLength > 0 && v.length < c.minLength && c.name !== "senha")
      return erro(c, "Mínimo de " + c.minLength + " caracteres.");
    if (c.dataset.mask === "cnpj" && v && !cnpjValido(v))
      return erro(c, "CNPJ inválido. Confira os 14 caracteres.");
    if (c.dataset.mask === "tel" && v && v.replace(/\D/g, "").length < 10)
      return erro(c, "Telefone incompleto.");
    if (c.dataset.mask === "cep" && v && v.replace(/\D/g, "").length !== 8)
      return erro(c, "CEP incompleto. Informe os 8 números.");
    if (c.type === "email" && v && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(v))
      return erro(c, "E-mail inválido.");
    if (c.hasAttribute("data-url") && v) {
      // domínio exigido: fixo (data-host) ou pela plataforma escolhida (data-host-from)
      var plat = c.dataset.hostFrom
        ? ($("[name=" + c.dataset.hostFrom + "]", c.form) || {}).value
        : "";
      var host =
        c.dataset.host ||
        { gupy: "gupy.io", linkedin: "linkedin.com" }[plat] ||
        "";
      if (!urlOk(v, host))
        return erro(
          c,
          host
            ? "Use um endereço de " + host + "."
            : "Endereço inválido. Exemplo: https://www.empresa.com.br",
        );
      if (c.dataset.hostFrom && !plat)
        return erro(c, "Escolha onde o processo seletivo acontece.");
    }
    if (c.hasAttribute("data-pwd") && v && !senhaForte(v))
      return erro(c, "Use 8 ou mais caracteres, com letras e números.");
    if (c.name === "senha2" && v !== $("[name=senha]", c.form).value)
      return erro(c, "As senhas não conferem.");
    if (c.dataset.after) {
      var o = $("[name=" + c.dataset.after + "]", c.form);
      if (o && o.value && v && v <= o.value)
        return erro(c, "A data final deve ser depois da inicial.");
    }
    if (c.dataset.minDate && v && v < c.dataset.minDate)
      return erro(c, "Escolha uma data de hoje em diante.");
    if (
      c.dataset.max &&
      c.type === "text" &&
      c.dataset.mask === "int" &&
      v &&
      (+v < +c.dataset.min || +v > +c.dataset.max)
    )
      return erro(
        c,
        "Informe de " + c.dataset.min + " a " + c.dataset.max + ".",
      );
    if (c.hasAttribute("data-chips-required") && !v)
      return erro(c, "Adicione ao menos um item.");
    if (c.dataset.moneyReq !== undefined) {
      var tipo = $("[name=remuneracao_tipo]:checked", c.form);
      if (
        tipo &&
        tipo.value === "valor" &&
        (!v || parseFloat(v.replace(",", ".")) <= 0)
      )
        return erro(c, 'Informe o valor mensal ou escolha "A combinar".');
    }
    return erro(c, "");
  }
  function validaGrupos(raiz) {
    var ok = true;
    $$("[data-need-one]", raiz).forEach(function (g) {
      if (g.closest("[hidden]")) return;
      var tem = $$("input[type=checkbox]", g).some(function (i) {
          return i.checked;
        }),
        e = g.parentNode.querySelector("[data-err]");
      if (e)
        e.textContent = tem
          ? ""
          : g.dataset.needOne || "Marque ao menos uma opção.";
      if (!tem) ok = false;
    });
    return ok;
  }
  function foca(c) {
    var w = c.closest && c.closest("[data-chips]");
    var t =
      c.matches && c.matches("textarea") && w ? $(".ui-chips input", w) : c;
    if (t && t.focus) t.focus();
  }
  function camposDe(raiz) {
    return $$(
      "input:not([type=hidden]):not([type=submit]),select,textarea",
      raiz,
    );
  }
  $$("[data-form]").forEach(function (form) {
    camposDe(form).forEach(function (c) {
      c.addEventListener("blur", function () {
        valida(c);
      });
      c.addEventListener("input", function () {
        if (
          c.classList.contains("is-invalid") ||
          ((c.closest(".ui-field") || {}).querySelector &&
            c.closest(".ui-field").querySelector(".is-invalid"))
        )
          valida(c);
      }); // erro some enquanto digita (sem mudar o layout no clique)
      c.addEventListener("change", function () {
        if (
          c.classList.contains("is-invalid") ||
          (c.parentNode.querySelector("[data-err]") || {}).textContent
        )
          valida(c);
      });
    });
    form.addEventListener("submit", function (ev) {
      if (form.dataset.ok === "1") return;
      var primeiro = null;
      camposDe(form).forEach(function (c) {
        if (!valida(c) && !primeiro) primeiro = c;
      });
      if (!validaGrupos(form) && !primeiro)
        primeiro = $("[data-need-one] input", form);
      if (primeiro) {
        ev.preventDefault();
        if (form._wizGo) form._wizGo(primeiro);
        else foca(primeiro);
        return;
      }
      var b = $("button[type=submit]:not([hidden])", form);
      if (b) {
        b.disabled = true;
        b.textContent = "Enviando...";
      }
    });
  });
/* ---------- ViaCEP ---------- */
var cep = $("[data-viacep]");
if (cep) {
  var st = $("[data-cep-status]"),
    box = cep.closest(".ui-field"),
    ult = "",
    rid = 0;
  var msg = function (t, c) {
    st.textContent = t;
    st.className = "ui-hint" + (c ? " " + c : "");
  };
  var busca = function () {
    var d = cep.value.replace(/\D/g, "");
    if (d.length !== 8) {
      ult = "";
      return;
    }
    if (d === ult) return;
    ult = d;
    var meu = ++rid,
      ctl = window.AbortController ? new AbortController() : null,
      t = setTimeout(function () {
        if (ctl) ctl.abort();
      }, 7000);
    msg("Buscando endereço...");
    fetch(
      "https://viacep.com.br/ws/" + d + "/json/",
      ctl ? { signal: ctl.signal } : undefined,
    )
      .then(function (r) {
        if (!r.ok) throw new Error("http");
        return r.json();
      })
      .then(function (j) {
        if (meu !== rid) return;
        clearTimeout(t);
        if (j.erro) {
          msg(
            "CEP não encontrado. Confira o número ou preencha o endereço manualmente.",
            "is-warn",
          );
          return;
        }
        var m = {
          logradouro: j.logradouro,
          bairro: j.bairro,
          cidade: j.localidade,
          uf: j.uf,
        };
        Object.keys(m).forEach(function (k) {
          var el = $("[data-cep-fill=" + k + "]");
          if (el && m[k]) {
            el.value = m[k];
            el.dispatchEvent(new Event("change", { bubbles: true }));
          }
        });
        msg("Endereço encontrado. Confira e informe o número.", "is-ok");
        var a = document.activeElement,
          n = $("#numero");
        if (n && (a === cep || a === document.body)) n.focus();
      })
      .catch(function () {
        if (meu !== rid) return;
        clearTimeout(t);
        msg(
          "Não foi possível buscar o CEP agora. Preencha o endereço manualmente.",
          "is-warn",
        );
      });
  };
  cep.addEventListener("input", busca);
  cep.addEventListener("blur", busca);
}

/* ---------- assistente em etapas (cadastro de empresa e de vaga) ---------- */
var wiz = $("[data-wiz]");
if (wiz) {
  wiz.classList.add("ui-wiz-on");
  var panels = $$(".ui-step", wiz),
    ult2 = panels.length - 1,
    atual = -1,
    alcance = 0;
  var ol = document.createElement("ol");
  ol.className = "ui-stepper" + (panels.length > 5 ? " is-many" : "");
  ol.setAttribute("aria-label", "Etapas");
  panels.forEach(function (p, i) {
    var li = document.createElement("li"),
      b = document.createElement("button");
    b.type = "button";
    b.innerHTML =
      '<span class="n">' + (i + 1) + '</span><span class="t"></span>';
    $(".t", b).textContent = p.dataset.title;
    b.setAttribute(
      "aria-label",
      "Etapa " + (i + 1) + " de " + panels.length + ": " + p.dataset.title,
    );
    b.addEventListener("click", function () {
      if (i <= alcance) ir(i, true);
    });
    li.appendChild(b);
    ol.appendChild(li);
  });
  var barra = document.createElement("div");
  barra.className = "ui-progress";
  barra.innerHTML = "<i></i>";
  var alvo = $("[data-stepper]", wiz) || wiz.firstElementChild;
  alvo.parentNode.insertBefore(ol, alvo);
  alvo.parentNode.insertBefore(barra, alvo);
  var lis = $$("li", ol),
    bVolta = $("[data-prev]", wiz),
    bProx = $("[data-next]", wiz),
    bEnvia = $("[data-submit]", wiz);
  var live = document.createElement("p");
  live.className = "ui-sr";
  live.setAttribute("aria-live", "polite");
  wiz.appendChild(live);
  function rotulo(c) {
    if (c.tagName === "SELECT")
      return c.selectedIndex > 0 || c.value
        ? c.options[c.selectedIndex].text
        : "";
    if (c.type === "date")
      return c.value ? c.value.split("-").reverse().join("/") : "";
    if (c.type === "file") return c.files[0] ? c.files[0].name : "";
    if (c.tagName === "TEXTAREA")
      return c.value.trim().split(/\n/).filter(Boolean).join(" • ");
    if (c.type === "password") return c.value ? "••••••••" : "";
    return c.value.trim();
  }
  function resumo() {
    var box = $("[data-review]", wiz);
    if (!box) return;
    var dl = $("dl", box);
    dl.innerHTML = "";
    var linha = function (k, v) {
      if (!v) return;
      var d = document.createElement("div"),
        dt = document.createElement("dt"),
        dd = document.createElement("dd");
      dt.textContent = k;
      dd.textContent = v;
      d.appendChild(dt);
      d.appendChild(dd);
      dl.appendChild(d);
    };
    $$("[data-rev]", wiz).forEach(function (c) {
      if (c.closest("[hidden]")) return;
      if (c.type === "radio") {
        if (c.checked)
          linha(c.dataset.rev, (c.closest("label") || c).textContent.trim());
        return;
      }
      if (c.hasAttribute("data-rev-group")) {
        linha(
          c.dataset.rev,
          $$("input:checked", c)
            .map(function (i) {
              return i.closest("label").textContent.trim();
            })
            .join(", "),
        );
        return;
      }
      var v = rotulo(c);
      if (c.dataset.prefix && v) v = c.dataset.prefix + v;
      if (c.dataset.suffix && v) v += c.dataset.suffix;
      linha(c.dataset.rev, v);
    });
    box.hidden = false;
  }
  function erros() {
    lis.forEach(function (li, i) {
      li.classList.toggle(
        "has-error",
        !!$(".is-invalid", panels[i]) ||
          !!$$("[data-err]", panels[i]).some(function (e) {
            return e.textContent;
          }),
      );
    });
  }
  function ir(i, foco) {
    i = Math.max(0, Math.min(ult2, i));
    var mudou = i !== atual;
    atual = i;
    alcance = Math.max(alcance, i);
    panels.forEach(function (p, k) {
      p.classList.toggle("is-active", k === i);
    });
    lis.forEach(function (li, k) {
      li.classList.toggle("is-current", k === i);
      li.classList.toggle("is-done", k !== i && k < alcance);
      var b = $("button", li);
      b.disabled = k > alcance;
      if (k === i) b.setAttribute("aria-current", "step");
      else b.removeAttribute("aria-current");
    });
    $("i", barra).style.width = ((i + 1) / panels.length) * 100 + "%";
    bVolta.hidden = i === 0;
    bProx.hidden = i === ult2;
    bEnvia.hidden = i !== ult2;
    if (i === ult2) resumo();
    live.textContent =
      "Etapa " +
      (i + 1) +
      " de " +
      panels.length +
      ": " +
      panels[i].dataset.title;
    erros();
    if (foco && mudou) {
      ol.scrollIntoView({
        behavior: reduz ? "auto" : "smooth",
        block: "start",
      });
      var p = camposDe(panels[i])[0];
      if (p) p.focus({ preventScroll: true });
    }
  }
  function painelOk(i) {
    var primeiro = null;
    camposDe(panels[i]).forEach(function (c) {
      if (!valida(c) && !primeiro) primeiro = c;
    });
    if (!validaGrupos(panels[i]) && !primeiro)
      primeiro = $("[data-need-one] input", panels[i]);
    erros();
    if (primeiro) foca(primeiro);
    return !primeiro;
  }
  function proximo() {
    if (painelOk(atual)) ir(atual + 1, true);
  }
  bProx.addEventListener("click", proximo);
  bVolta.addEventListener("click", function () {
    ir(atual - 1, true);
  });
  wiz.addEventListener("keydown", function (e) {
    if (
      e.key === "Enter" &&
      e.target.tagName === "INPUT" &&
      e.target.type !== "file" &&
      e.target.type !== "checkbox" &&
      !e.target.closest(".ui-chips") &&
      atual < ult2
    ) {
      e.preventDefault();
      proximo();
    }
  });
  wiz._wizGo = function (campo) {
    var p = campo.closest(".ui-step");
    alcance = ult2;
    ir(+p.dataset.step, false);
    erros();
    foca(campo);
  };
  if (wiz.hasAttribute("data-free") || $(".ui-alert-danger", wiz))
    alcance = ult2;
  ir(+wiz.dataset.start || 0, false);
}
})();
