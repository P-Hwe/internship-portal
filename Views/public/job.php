<?php require __DIR__ . '/../header.php'; $ativa = 'home'; require __DIR__ . '/../partials/nav.php'; $emp = $vaga['empresa']; $selos = $aberta ? array_slice($vaga['selos'], 0, 4, true) : []; ?>

<nav class="vg-crumbs" aria-label="Você está aqui"><a href="<?= url() ?>">Vagas</a> <span aria-hidden="true">›</span>
    <span><?= e($vaga['titulo']) ?></span></nav>
<?php if (!$aberta): ?><div class="vg-flash vg-flash-info" role="status"><?= icon('info', 18) ?> <span><strong>Esta vaga
            não recebe mais inscrições.</strong>
        <?= $vaga['situacao'] === 'encerrada' ? 'Foi encerrada pela empresa.' : 'O prazo terminou em ' . data_br($vaga['data_limite']) . '.' ?>
        As informações abaixo ficam disponíveis como histórico.</span></div><?php endif; ?>

<article class="vg-detail">
    <div class="vg-detail-banner"><?= vaga_imagem($vaga) ?>
        <ul class="vg-tags-print"><?php foreach ($selos as [$txt, $cls]): ?><li class="vg-tag <?= e($cls) ?>">
                <?= e($txt) ?></li><?php endforeach; ?><?php if (!$aberta): ?><li class="vg-tag vg-tag-tipo">Encerrada
            </li><?php endif; ?></ul>
    </div>
    <div class="vg-detail-head">
        <h1><?= e($vaga['titulo']) ?></h1>
        <p class="vg-detail-company">
            <?= icon('building', 16) ?> <a href="<?= url('empresa', ['id' => $emp['id']]) ?>" style="color:inherit"><?= e($emp['nome']) ?></a>
            <span class="vg-detail-meta-sep" aria-hidden="true">·</span><span class="vg-detail-meta-tag"><?= e($vaga['tipo']) ?></span>
            <span class="vg-detail-meta-sep" aria-hidden="true">·</span><span class="vg-detail-meta-tag"><?= e($vaga['modalidade']) ?></span>
            <span class="vg-detail-meta-sep" aria-hidden="true">·</span><span class="vg-detail-meta-tag"><?= e($vaga['area']) ?></span>
        </p>
    </div>
    <dl class="vg-facts">
        <div>
            <dt><?= $vaga['tipo'] === 'Estágio' ? 'Bolsa-auxílio' : 'Salário' ?></dt>
            <dd><?= e($vaga['bolsa_txt']) ?></dd>
        </div>
        <div>
            <dt>Carga horária</dt>
            <dd><?= e($vaga['carga_txt'] ?: 'A combinar') ?></dd>
        </div>
        <div>
            <dt>Local</dt>
            <dd><?= e($vaga['local']) ?></dd>
        </div>
        <div>
            <dt>Publicada em</dt>
            <dd><?= data_br($vaga['data_publicacao']) ?></dd>
        </div>
        <div>
            <dt><?= $aberta ? 'Inscrições até' : 'Inscrições encerradas em' ?></dt>
            <dd><?= data_br($vaga['data_limite']) ?></dd>
        </div>
        <div>
            <dt>Código da vaga</dt>
            <dd><span class="vg-code"><?= e($vaga['codigo']) ?></span> <button type="button" class="vg-copy"
                    data-copy="<?= e($vaga['codigo']) ?>" aria-label="Copiar código da vaga"><?= icon('copy', 14) ?>
                    Copiar</button></dd>
        </div>
    </dl>
    <section class="vg-block">
        <h2>Sobre a vaga</h2>
        <p><?= nl2br(e($vaga['descricao'])) ?></p>
    </section>
    <div class="vg-two">
        <?php if ($vaga['atividades']): ?><section class="vg-block">
            <h2>Atividades</h2>
            <ul><?php foreach ($vaga['atividades'] as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul>
        </section><?php endif; ?>
        <?php if ($vaga['requisitos']): ?><section class="vg-block">
            <h2>Requisitos</h2>
            <ul><?php foreach ($vaga['requisitos'] as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul>
            <?php if ($vaga['requisitos_desejaveis']): ?><h2 style="margin-top:12px">Desejáveis</h2>
            <ul><?php foreach ($vaga['requisitos_desejaveis'] as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul>
            <?php endif; ?>
        </section><?php endif; ?>
    </div>
    <?php if ($vaga['beneficios']): ?><section class="vg-block">
        <h2>Benefícios</h2>
        <ul class="vg-tags"><?php foreach ($vaga['beneficios'] as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul>
    </section><?php endif; ?>
    <section class="vg-block vg-company">
        <h2>Sobre a empresa</h2>
        <p><strong><?= e($emp['nome']) ?></strong><?= $emp['segmento'] ? ' · ' . e($emp['segmento']) : '' ?><br>CNPJ:
            <?= e(Cnpj::formatar($emp['cnpj'])) ?><br><?= e($emp['endereco']) ?><br><a
                href="<?= url('empresa', ['id' => $emp['id']]) ?>">Ver página da empresa</a></p>
    </section>

    <?php if ($aberta): ?>
    <section class="vg-apply">
        <div>
            <h2>Como se candidatar</h2>
            <p>A candidatura é feita diretamente com a
                empresa<?= $vaga['link_candidatura'] ? ' (' . e(Link::PLATAFORMAS[$vaga['link_plataforma']] ?? 'processo externo') . ')' : '' ?>.
                Informe o código <strong><?= e($vaga['codigo']) ?></strong> no contato.</p>
        </div>
        <div class="vg-apply-actions">
            <?php if ($vaga['link_candidatura']): ?><a class="vg-btn" href="<?= e($vaga['link_candidatura']) ?>"
                target="_blank" rel="noopener noreferrer nofollow"><?= icon('external', 16) ?>
                Candidatar-se<?= !empty($vaga['link_plataforma']) && isset(Link::PLATAFORMAS[$vaga['link_plataforma']]) ? ' pela ' . e(Link::PLATAFORMAS[$vaga['link_plataforma']]) : '' ?></a><?php endif; ?>
            <?php if ($vaga['contato']): ?><a class="vg-btn <?= $vaga['link_candidatura'] ? 'vg-btn-ghost' : '' ?>"
                href="mailto:<?= e($vaga['contato']) ?>?subject=<?= rawurlencode('Candidatura ' . $vaga['codigo'] . ' - ' . $vaga['titulo']) ?>"><?= icon('mail', 16) ?>
                <?= e($vaga['contato']) ?></a><?php endif; ?>
            <?php if ($emp['telefone']): ?><a class="vg-btn vg-btn-ghost"
                href="tel:<?= e(preg_replace('/\D/', '', $emp['telefone'])) ?>"><?= icon('phone', 16) ?>
                <?= e(fone_fmt($emp['telefone'])) ?></a><?php endif; ?>
            <?php foreach (['link_site' => 'Site da empresa', 'link_linkedin' => 'LinkedIn'] as $k => $rot): if ($vaga[$k]): ?><a
                class="vg-btn vg-btn-ghost" href="<?= e($vaga[$k]) ?>" target="_blank"
                rel="noopener noreferrer nofollow"><?= icon('external', 16) ?>
                <?= $rot ?></a><?php endif; endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</article>

<?php if ($relacionadas): ?><section aria-labelledby="vg-rel">
    <div class="vg-list-head">
        <h2 id="vg-rel">Outras vagas</h2>
    </div>
    <div class="vg-grid">
        <?php foreach ($relacionadas as $v) { $trilho = false; $chave = null; require __DIR__ . '/../partials/job-card.php'; } ?>
    </div>
</section><?php endif; ?>
<p><a class="vg-back" href="<?= url() ?>"><?= icon('arrow-left', 16) ?> Voltar para todas as vagas</a></p>
</div>
<?php require __DIR__ . '/../footer.php'; ?>