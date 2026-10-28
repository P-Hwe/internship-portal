<?php require __DIR__ . '/header.php';
$ativa = 'empresas';
require __DIR__ . '/partials/nav.php';
$ext = Empresa::linkExterno($e); ?>
<nav class="vg-crumbs" aria-label="Você está aqui"><a href="<?= url('empresas') ?>">Empresas parceiras</a> <span
        aria-hidden="true">›</span> <span><?= e($e['nome']) ?></span></nav>
<article class="vg-detail">
    <div class="vg-detail-banner vg-comp-banner"><?= empresa_banner($e) ?></div>
    <div class="vg-detail-head vg-comp-page"><?= Ui::logo($e, 84) ?><div>
            <div class="vg-badges"><?php if ($e['segmento']): ?><span
                    class="vg-badge vg-badge-soft"><?= e($e['segmento']) ?></span><?php endif; ?><?php if ($e['porte']): ?><span
                    class="vg-badge vg-badge-soft"><?= e($e['porte']) ?></span><?php endif; ?></div>
            <h1><?= e($e['nome']) ?></h1>
            <p class="vg-detail-company"><?= icon('pin', 18) ?>
                <?= e($e['cidade'] ? $e['cidade'] . '/' . $e['uf'] : 'Localização não informada') ?></p>
        </div>
    </div>
    <?php if ($e['descricao']): ?><section class="vg-block">
        <h2>Sobre a empresa</h2>
        <p><?= nl2br(e($e['descricao'])) ?></p>
    </section><?php endif; ?>
    <dl class="vg-facts" style="margin-top:18px">
        <div>
            <dt>Vagas abertas</dt>
            <dd><?= (int) $e['vagas_abertas'] ?></dd>
        </div>
        <div>
            <dt>Vagas já publicadas</dt>
            <dd><?= (int) $e['total_vagas'] ?></dd>
        </div>
        <div>
            <dt>Contato</dt>
            <dd><?= e($e['email_contato'] ?: '-') ?></dd>
        </div>
    </dl>
    <section class="vg-apply">
        <div>
            <h2>Conheça a empresa</h2>
            <p>Os links abaixo levam para fora do sistema.</p>
        </div>
        <div class="vg-apply-actions">
            <?php if ($ext): ?><a class="vg-btn"
                href="<?= url('externo', ['id' => $e['id']]) ?>"><?= icon('external', 16) ?>
                <?= $e['link_principal'] === 'linkedin' && $e['linkedin'] ? 'LinkedIn da empresa' : 'Site da empresa' ?></a><?php else: ?><span
                class="vg-hint">A empresa ainda não informou site ou LinkedIn.</span><?php endif; ?></div>
    </section>
</article>
<section aria-labelledby="vg-ev">
    <div class="vg-list-head">
        <h2 id="vg-ev">Vagas abertas desta empresa</h2>
    </div>
    <?php if (!$vagas): ?><p class="vg-empty">Nenhuma vaga aberta no momento.</p><?php else: ?><div class="vg-grid">
        <?php foreach ($vagas as $v) {
                                                                                        $trilho = false;
                                                                                        $chave = null;
                                                                                        require __DIR__ . '/partials/card_vaga.php';
                                                                                    } ?>
    </div><?php endif; ?>
</section>
<p><a class="vg-back" href="<?= url('empresas') ?>"><?= icon('arrow-left', 16) ?> Voltar para empresas parceiras</a></p>
</div>
<?php require __DIR__ . '/footer.php'; ?>