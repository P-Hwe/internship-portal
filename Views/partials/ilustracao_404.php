<?php /** Ilustração e textos da página 404. Espera $modo ('publico'|'app') e $voltar (['rótulo', 'url'] do painel, ou null). */ ?>
<style>
.e404{--e-blob:#e6f2e9;--e-ground:#b9dcc1;--e-num:#2c6e3c;--e-num-ln:#14301c;--e-leaf1:#1f7a35;--e-leaf2:#4cc263;--e-leaf3:#0f5a27;--e-pot:#fff;--e-pot-sh:#c9d6cc;--e-skin:#e0a97f;--e-hair:#1a201c;--e-shirt:#6cc63b;--e-shirt2:#d40000;--e-pants:#1a201c;--e-wood:#7a5a3a;--e-dot:#f2b24c;
  max-width:920px;margin:0 auto;padding:24px 16px 40px;text-align:center;color:var(--text,#1a201c)}
[data-theme="dark"] .e404{--e-blob:#10241a;--e-ground:#1d3a28;--e-num:#3d9a52;--e-num-ln:#a7e1b3;--e-leaf1:#4cc263;--e-leaf2:#72d683;--e-leaf3:#2b8a45;--e-pot:#262e29;--e-pot-sh:#161b18;--e-skin:#c98f66;--e-hair:#0c0c0c;--e-shirt:#4cc263;--e-pants:#0c0c0c;--e-wood:#a07d57}
.e404 svg{display:block;width:100%;height:auto;max-width:860px;margin:0 auto}
.e404 p.e404-oops{margin:0;font-size:clamp(26px,5vw,40px);line-height:1.1;font-weight:800;letter-spacing:.04em;color:var(--e-num-ln)}
.e404 h1.e404-t{margin:10px 0 0;line-height:1.2;font-size:clamp(22px,3.4vw,30px);font-weight:800;color:var(--e-num-ln)}
.e404-t::after{content:"";display:block;width:84px;height:4px;border-radius:3px;margin:10px auto 0;background:var(--e-leaf1)}
.e404 p.e404-p{max-width:560px;margin:14px auto 0;font-size:16px;line-height:1.55;color:var(--muted,#59655d)}
.e404-acoes{display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin-top:22px}
.e404-link{display:inline-flex;align-items:center;gap:6px;padding:12px 22px;border-radius:9px;font-weight:700;text-decoration:none;border:2px solid var(--e-leaf1);color:var(--e-leaf1);background:transparent;cursor:pointer;font-size:15px}
.e404-link.is-main{background:var(--e-leaf1);color:#fff}
.e404-link:hover,.e404-link:focus-visible{outline:3px solid var(--e-dot);outline-offset:2px}
.e404 .sway{transform-box:fill-box;transform-origin:50% 100%}
@media (prefers-reduced-motion:no-preference){
  .e404 .sway{animation:e404-sway 4.5s ease-in-out infinite}
  .e404 .sway.b{animation-delay:-2s}
  .e404 .wave{transform-box:fill-box;transform-origin:0% 50%;animation:e404-wave 2.6s ease-in-out infinite}
  .e404 .tipa{animation:e404-tip 1.3s steps(2,end) infinite}
}
@keyframes e404-sway{0%,100%{transform:rotate(-2.2deg)}50%{transform:rotate(2.2deg)}}
@keyframes e404-wave{0%,100%{transform:rotate(-6deg)}50%{transform:rotate(7deg)}}
@keyframes e404-tip{50%{opacity:.35}}
</style>
<section class="e404" aria-labelledby="e404-titulo">
    <p class="e404-oops">OPS...</p>
    <svg viewBox="0 0 900 480" role="img" aria-label="Ilustração: o número 404 com duas pessoas, uma em uma escada e outra sentada com um notebook">
        <path transform="translate(450 0) scale(.94 1) translate(-450 0)" fill="var(--e-blob)" d="M70 250C20 170 60 52 190 38c96-10 150 36 250 24C540 50 600 4 700 22c90 16 140 84 176 130 40 52 30 118-4 168-30 44-22 92-74 118-70 40-150 6-232 22-90 18-150 66-250 40C196 520 130 470 92 400 62 346 96 304 70 250Z"/>
        <ellipse cx="450" cy="392" rx="372" ry="26" fill="var(--e-ground)"/>
        <g fill="none" stroke="var(--e-num-ln)" stroke-width="3" stroke-linejoin="round">   <!-- contorno deslocado, como na arte de referência -->
            <path transform="translate(-9,-9)" fill-rule="evenodd" d="M235 90h65v160h45v55h-45v65h-65v-65H105v-50ZM235 160l-75 95h75Z"/>
            <path transform="translate(-9,-9)" fill-rule="evenodd" d="M450 85c-58 0-100 62-100 145s42 145 100 145 100-62 100-145S508 85 450 85ZM450 135c-27 0-48 40-48 95s21 95 48 95 48-40 48-95-21-95-48-95Z"/>
            <path transform="translate(441,-9)" fill-rule="evenodd" d="M235 90h65v160h45v55h-45v65h-65v-65H105v-50ZM235 160l-75 95h75Z"/>
        </g>
        <g fill="var(--e-num)" fill-rule="evenodd">
            <path d="M235 90h65v160h45v55h-45v65h-65v-65H105v-50ZM235 160l-75 95h75Z"/>
            <path d="M450 85c-58 0-100 62-100 145s42 145 100 145 100-62 100-145S508 85 450 85ZM450 135c-27 0-48 40-48 95s21 95 48 95 48-40 48-95-21-95-48-95Z"/>
            <path transform="translate(450,0)" d="M235 90h65v160h45v55h-45v65h-65v-65H105v-50ZM235 160l-75 95h75Z"/>
        </g>

        <!-- escada + pessoa à esquerda -->
        <g>
            <path d="M82 392 126 232M158 392 188 236" stroke="var(--e-wood)" stroke-width="7" stroke-linecap="round"/>
            <path d="M92 360h66M99 332h66M106 304h66M113 276h62M120 248h58" stroke="var(--e-wood)" stroke-width="6" stroke-linecap="round"/>
            <g class="wave"><path d="M146 150 205 160" stroke="var(--e-shirt)" stroke-width="15" stroke-linecap="round"/><circle cx="212" cy="162" r="8" fill="var(--e-skin)"/></g>
            <path d="M118 236 114 190M156 238 150 190" stroke="var(--e-pants)" stroke-width="20" stroke-linecap="round"/>
            <path d="M104 238 108 180 156 180 160 240Z" fill="var(--e-pants)"/>
            <path d="M100 252h24v10h-30zM150 254h26v10h-32z" fill="var(--e-shirt2)"/>
            <rect x="106" y="118" width="54" height="76" rx="20" fill="var(--e-shirt)"/>
            <rect x="106" y="150" width="54" height="10" fill="var(--e-shirt2)" opacity=".9"/>
            <circle cx="133" cy="98" r="19" fill="var(--e-skin)"/>
            <path d="M113 94c-2-18 14-28 28-22 12 4 14 14 11 24-6-10-18-12-39-2Z" fill="var(--e-hair)"/>
        </g>

        <!-- pessoa sentada com notebook à direita -->
        <g>
            <path d="M792 255h-72" stroke="var(--e-pants)" stroke-width="22" stroke-linecap="round"/>
            <path d="M722 258v46" stroke="var(--e-pants)" stroke-width="20" stroke-linecap="round"/>
            <path d="M708 306h26" stroke="var(--e-shirt2)" stroke-width="9" stroke-linecap="round"/>
            <rect x="762" y="186" width="46" height="72" rx="18" fill="var(--e-shirt)"/>
            <path d="M790 214 742 238" stroke="var(--e-shirt)" stroke-width="13" stroke-linecap="round"/>
            <path d="M742 240l24-44 6 3-22 42Z" fill="#2b2f2c"/><rect x="722" y="236" width="38" height="5" rx="2" fill="#555d57"/>
            <circle cx="788" cy="168" r="18" fill="var(--e-skin)"/>
            <path d="M770 166c-3-20 14-30 30-22 10 5 10 16 6 24-5-9-14-10-36-2Z" fill="var(--e-hair)"/>
            <circle class="tipa" cx="738" cy="214" r="3" fill="var(--e-dot)"/>
        </g>

        <!-- plantas -->
        <g class="sway"><ellipse cx="62" cy="388" rx="22" ry="6" fill="var(--e-pot-sh)"/><path d="M44 360h36l-5 28H49Z" fill="var(--e-pot)" stroke="var(--e-pot-sh)" stroke-width="2"/>
            <path d="M62 360C34 340 30 300 40 276c20 14 28 46 22 84Z" fill="var(--e-leaf1)"/><path d="M62 360c4-30 18-58 40-76 6 30-10 62-40 76Z" fill="var(--e-leaf2)"/><path d="M62 360C58 318 66 290 80 266c8 36 0 70-18 94Z" fill="var(--e-leaf3)"/></g>
        <g class="sway b"><ellipse cx="846" cy="388" rx="22" ry="6" fill="var(--e-pot-sh)"/><path d="M828 360h36l-5 28h-26Z" fill="var(--e-pot)" stroke="var(--e-pot-sh)" stroke-width="2"/>
            <path d="M846 360c28-20 32-60 22-84-20 14-28 46-22 84Z" fill="var(--e-leaf1)"/><path d="M846 360c-4-30-18-58-40-76-6 30 10 62 40 76Z" fill="var(--e-leaf2)"/><path d="M846 360c4-42-4-70-18-94-8 36 0 70 18 94Z" fill="var(--e-leaf3)"/></g>

        <!-- detalhes: pontinhos e sinais de + -->
        <g fill="var(--e-dot)"><circle cx="300" cy="52" r="5"/><circle cx="640" cy="60" r="4"/><circle cx="820" cy="140" r="5"/><circle cx="84" cy="130" r="4"/></g>
        <g stroke="var(--e-leaf1)" stroke-width="3" stroke-linecap="round"><path d="M560 44v14M553 51h14"/><path d="M230 70v12M224 76h12"/><path d="M850 226v12M844 232h12"/></g>
    </svg>
    <h1 class="e404-t" id="e404-titulo">Página não encontrada</h1>
    <p class="e404-p">O endereço que você tentou abrir não existe, mudou de lugar ou a vaga já saiu do ar. Que tal voltar e continuar procurando?</p>
    <div class="e404-acoes">
        <?php if (!empty($voltar)): ?>
            <a class="e404-link is-main" href="<?= e($voltar[1]) ?>"><?= e($voltar[0]) ?></a>
            <a class="e404-link" href="javascript:history.back()">Voltar</a>
        <?php else: ?>
            <a class="e404-link is-main" href="<?= e(url()) ?>">Ver vagas abertas</a>
            <a class="e404-link" href="<?= e(url('empresa-login')) ?>">Sou empresa</a>
        <?php endif; ?>
    </div>
</section>
