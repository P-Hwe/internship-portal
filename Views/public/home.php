<?php require __DIR__ . '/../header.php'; $ativa = 'home'; require __DIR__ . '/../partials/nav.php'; ?>

<div class="vg-hero">
    <h1>Vagas de Estágio e Primeiro Emprego</h1>
    <p>Oportunidades divulgadas por empresas parceiras do IFSP Campus Guarulhos. Escolha uma vaga, veja os detalhes e
        fale direto com a empresa.</p>
</div>

<?php if ($destaques): ?>
<section class="vg-carousel" data-carousel aria-roledescription="carrossel" aria-label="Vagas em destaque">
    <div class="vg-slides">
        <?php foreach ($destaques as $i => $v): ?>
        <a class="vg-slide <?= $i === 0 ? 'is-active' : '' ?>" href="<?= url('vaga', ['id' => $v['id']]) ?>"
            aria-label="<?= ($i + 1) ?> de <?= count($destaques) ?>: <?= e($v['titulo']) ?> - <?= e($v['empresa']['nome']) ?>">
            <?= vaga_imagem($v) ?>
            <span class="vg-slide-caption">
                <span class="vg-slide-tag"><?= e($v['tipo']) ?> · Destaque</span>
                <strong><?= e($v['titulo']) ?></strong>
                <span><?= e($v['empresa']['nome']) ?> · <?= e($v['modalidade']) ?> · <?= e($v['bolsa_txt']) ?></span>
            </span>
        </a>
        <?php endforeach; ?>
    </div>
    <?php if (count($destaques) > 1): ?>
    <button type="button" class="vg-car-btn vg-prev" data-prev
        aria-label="Vaga anterior"><?= icon('chevron-left', 22) ?></button>
    <button type="button" class="vg-car-btn vg-next" data-next
        aria-label="Próxima vaga"><?= icon('chevron-right', 22) ?></button>
    <div class="vg-dots" role="tablist" aria-label="Escolher slide">
        <?php foreach ($destaques as $i => $v): ?><button type="button" class="<?= $i === 0 ? 'is-active' : '' ?>"
            data-dot="<?= $i ?>" aria-label="Ir para a vaga <?= $i + 1 ?>"></button><?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php foreach ($trilhos as $t): ?>
<section class="vg-rail" data-rail aria-labelledby="rail-<?= e($t['id']) ?>">
    <div class="vg-rail-head">
        <div>
            <h2 id="rail-<?= e($t['id']) ?>"><?= icon($t['icone'], 20) ?> <?= e($t['titulo']) ?></h2>
            <?php if (!empty($t['sub'])): ?><p><?= e($t['sub']) ?></p><?php endif; ?>
        </div>
        <div class="vg-rail-ctrl">
            <button type="button" class="vg-rail-btn" data-rail-prev
                aria-label="Voltar em <?= e($t['titulo']) ?>"><?= icon('chevron-left', 20) ?></button>
            <button type="button" class="vg-rail-btn" data-rail-next
                aria-label="Avançar em <?= e($t['titulo']) ?>"><?= icon('chevron-right', 20) ?></button>
        </div>
    </div>
    <div class="vg-rail-track" data-rail-track tabindex="0">
        <?php foreach ($t['itens'] as $v) { $chave = $t['chave']; $trilho = true; require __DIR__ . '/../partials/job-card.php'; } ?>
    </div>
</section>
<?php endforeach; ?>

<section id="todas" aria-labelledby="vg-lista-titulo">
    <div class="vg-list-head">
        <h2 id="vg-lista-titulo">Todas as vagas abertas <span class="vg-count" data-count><?= count($vagas) ?></span></h2>
    </div>

    <?php if ($tipos): ?>
    <div class="vg-chips" role="group" aria-label="Filtrar por tipo de vaga">
        <button type="button" class="vg-chip is-active" data-chip="">Todas</button>
        <?php foreach ($tipos as $t): ?><button type="button" class="vg-chip" data-chip="<?= e($t) ?>"><?= e($t) ?></button><?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form class="vg-filters" data-filters role="search" method="get" action="<?= url('home') ?>">
        <?= campo_rota('home') ?>
        <label class="vg-sr" for="f-busca">Buscar vaga</label>
        <input id="f-busca" type="search" name="busca" value="<?= e($filtros['busca'] ?? '') ?>" placeholder="Buscar por cargo, empresa ou palavra-chave" data-f-busca>
        <label class="vg-sr" for="f-area">Área</label>
        <select id="f-area" name="area" data-f-area>
            <option value="">Todas as áreas</option>
            <?php foreach ($areas as $a): ?><option <?= ($filtros['area'] ?? '') === $a ? 'selected' : '' ?>><?= e($a) ?></option><?php endforeach; ?>
        </select>
        <label class="vg-sr" for="f-mod">Modalidade</label>
        <select id="f-mod" name="modalidade" data-f-mod>
            <option value="">Toda modalidade</option>
            <?php foreach ($modalidades as $m): ?><option <?= ($filtros['modalidade'] ?? '') === $m ? 'selected' : '' ?>><?= e($m) ?></option><?php endforeach; ?>
        </select>
        <label class="vg-sr" for="f-ord">Ordenar</label>
        <select id="f-ord" name="ord">
            <option value="" <?= empty($filtros['ord'] ?? '') ? 'selected' : '' ?>>Mais recentes</option>
            <option value="salario" <?= ($filtros['ord'] ?? '') === 'salario' ? 'selected' : '' ?>>Maior salário</option>
            <option value="prazo" <?= ($filtros['ord'] ?? '') === 'prazo' ? 'selected' : '' ?>>Encerram em breve</option>
        </select>
    </form>

    <div class="vg-grid" data-grid>
        <?php foreach ($vagas as $v) { $trilho = false; $chave = null; require __DIR__ . '/../partials/job-card.php'; } ?>
    </div>

    <?php if ($totalPaginas > 1): ?>
    <nav class="vg-pagination" aria-label="Paginação de vagas">
        <div class="vg-pagination-btns">
            <?php if ($pagina > 1): ?>
                <a class="vg-p-btn" href="<?= e(url('home', array_merge($filtros, ['pg' => $pagina - 1]))) ?>#todas"><?= icon('chevron-left', 16) ?> Anterior</a>
            <?php endif; ?>
            <?php
            $start = max(1, $pagina - 2);
            $end   = min($totalPaginas, $pagina + 2);
            if ($start > 1): ?><a class="vg-p-btn" href="<?= e(url('home', array_merge($filtros, ['pg' => 1]))) ?>#todas">1</a><?php if ($start > 2): ?><span class="vg-p-ellipsis">…</span><?php endif; endif;
            for ($i = $start; $i <= $end; $i++): ?>
                <a class="vg-p-btn <?= $i === $pagina ? 'is-active' : '' ?>" href="<?= e(url('home', array_merge($filtros, ['pg' => $i]))) ?>#todas" <?= $i === $pagina ? 'aria-current="page"' : '' ?>><?= $i ?></a>
            <?php endfor;
            if ($end < $totalPaginas): if ($end < $totalPaginas - 1): ?><span class="vg-p-ellipsis">…</span><?php endif; ?><a class="vg-p-btn" href="<?= e(url('home', array_merge($filtros, ['pg' => $totalPaginas]))) ?>#todas"><?= $totalPaginas ?></a><?php endif; ?>
            <?php if ($pagina < $totalPaginas): ?>
                <a class="vg-p-btn" href="<?= e(url('home', array_merge($filtros, ['pg' => $pagina + 1]))) ?>#todas">Próxima <?= icon('chevron-right', 16) ?></a>
            <?php endif; ?>
        </div>
        <p class="vg-pagination-info"><?= $totalVagas ?> vagas · página <?= $pagina ?> de <?= $totalPaginas ?></p>
    </nav>
    <?php endif; ?>

    <p class="vg-empty" data-empty <?= $vagas ? 'hidden' : '' ?>>
        <?= $vagas ? 'Nenhuma vaga encontrada. Tente limpar os filtros.' : 'Nenhuma vaga aberta no momento. Volte em breve!' ?>
    </p>
</section>

<?php if (!Auth::role()): ?>
<aside class="vg-cta-box">
    <div>
        <h2>É uma empresa parceira?</h2>
        <p>Cadastre sua empresa e, depois de aprovada pela Coordenação, publique suas vagas.</p>
    </div>
    <div class="vg-cta-actions">
        <a class="vg-btn vg-btn-lg" href="<?= url('empresa-cadastro') ?>"><?= icon('building', 18) ?> Cadastrar minha
            empresa</a>
        <a class="vg-btn-link" href="<?= url('empresa-login') ?>">Já sou parceira: entrar
            <?= icon('arrow-right', 16) ?></a>
    </div>
</aside>
<?php endif; ?>
</div>

<?php require __DIR__ . '/../footer.php'; ?>