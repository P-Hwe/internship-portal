<?php require __DIR__ . '/header.php'; $ativa = 'home'; require __DIR__ . '/partials/nav.php'; ?>
<div class="vg-hero">
    <h1>Vagas encerradas</h1>
    <p>Vagas que já saíram do ar nos últimos 120 dias. Elas não recebem mais inscrições e ficam aqui só para consulta.
    </p>
</div>
<?php if (!$vagas): ?><p class="vg-empty">Nenhuma vaga encerrada recentemente.</p><?php else: ?>
<div class="vg-grid"><?php foreach ($vagas as $v): ?>
    <article class="vg-card">
        <div class="vg-card-media"><?= vaga_imagem($v) ?><ul class="vg-tags-print">
                <li class="vg-tag vg-tag-tipo"><?= e($v['tipo']) ?></li>
                <li class="vg-tag vg-tag-warn"><?= $v['situacao'] === 'encerrada' ? 'Encerrada' : 'Prazo encerrado' ?>
                </li>
            </ul>
        </div>
        <div class="vg-card-body">
            <div class="vg-card-emp"><span class="vg-avatar"
                    aria-hidden="true"><?= e(iniciais($v['empresa']['nome'])) ?></span><span
                    class="vg-card-company"><?= e($v['empresa']['nome']) ?></span></div>
            <h3 class="vg-card-title"><a class="vg-stretched"
                    href="<?= e(url_vaga((int) $v['id'])) ?>"><?= e($v['titulo']) ?></a></h3>
            <ul class="vg-meta">
                <li><?= icon('pin', 15) ?> <?= e($v['local']) ?> · <?= e($v['modalidade']) ?></li>
                <li><?= icon('clock', 15) ?> Inscrições até <?= data_br($v['data_limite']) ?></li>
            </ul>
            <p class="vg-card-area"><?= e($v['area']) ?></p>
        </div>
    </article>
    <?php endforeach; ?>
</div><?php endif; ?>
<p><a class="vg-back" href="<?= url() ?>"><?= icon('arrow-left', 16) ?> Voltar para as vagas abertas</a></p>
</div>
<?php require __DIR__ . '/footer.php'; ?>