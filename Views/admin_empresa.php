<?php $ret = 'admin-empresa'; ?>
<div class="ui-row-between">
    <div class="ui-actions"><a class="ui-btn ui-btn-sm"
            href="<?= url('admin-empresas') ?>"><?= icon('arrow-left', 15) ?> Empresas</a>
        <?= Ui::statusEmpresa($e['status']) ?></div><?= Ui::acoesEmpresaAdmin($e, 'admin-empresa', (int) $e['id']) ?>
</div>
<div class="ui-card ui-cover-card" style="margin-bottom:16px">
    <div class="ui-cover"><?= empresa_banner($e) ?></div>
    <div class="ui-cover-body"><span class="ui-cover-logo"><?= Ui::logo($e, 72) ?></span>
        <h2 style="font-size:22px;margin:0 0 2px"><?= e($e['nome']) ?></h2>
        <p class="ui-sub" style="margin:0">
            <?= e(Empresa::segmentoTxt($e)) ?><?= $e['porte'] ? ' · ' . e($e['porte']) : '' ?><?= $e['cidade'] ? ' · ' . e($e['cidade'] . '/' . $e['uf']) : '' ?>
        </p>
        <?php if ($e['descricao']): ?><p style="margin:14px 0 0"><?= nl2br(e($e['descricao'])) ?></p><?php endif; ?>
    </div>
</div>
<?php if ($e['status'] === 'reprovada' || $e['status'] === 'bloqueada'): ?><div class="ui-alert ui-alert-danger">
    <div><strong><?= $e['status'] === 'bloqueada' ? 'Empresa bloqueada' : 'Cadastro reprovado' ?></strong>
        <p>Motivo: <?= nl2br(e((string) $e['motivo'])) ?></p>
    </div>
</div><?php endif; ?>
<div class="ui-metrics">
    <?= Ui::metrica('Vagas cadastradas', $e['total_vagas'], 'briefcase') ?><?= Ui::metrica('Vagas no ar', $e['vagas_abertas'], 'check') ?><?= Ui::metrica('Último acesso do recrutador', $e['ultimo_acesso'] ? date('d/m/Y', strtotime($e['ultimo_acesso'])) : 'Nunca', 'clock', null, 'mute') ?>
</div>
<?php require __DIR__ . '/partials/desempenho_item.php'; ?>
<div class="ui-grid-2">
    <div class="ui-card">
        <h2>Dados cadastrais</h2>
        <dl class="ui-kv">
            <dt>Razão social</dt>
            <dd><?= e($e['razao_social'] ?: '-') ?></dd>
            <dt>CNPJ</dt>
            <dd><span class="ui-code"><?= e(Cnpj::formatar($e['cnpj'])) ?></span></dd>
            <dt>Endereço</dt>
            <dd><?= e($e['endereco']) ?></dd>
            <dt>Site</dt>
            <dd><?= $e['site'] ? '<a href="' . e($e['site']) . '" target="_blank" rel="noopener noreferrer nofollow">' . e(Link::dominio($e['site'])) . '</a>' : '-' ?>
            </dd>
            <dt>LinkedIn</dt>
            <dd><?= $e['linkedin'] ? '<a href="' . e($e['linkedin']) . '" target="_blank" rel="noopener noreferrer nofollow">' . e(Link::dominio($e['linkedin'])) . '</a>' : '-' ?>
            </dd>
            <dt>Link principal</dt>
            <dd><?= $e['link_principal'] === 'linkedin' ? 'LinkedIn' : 'Site oficial' ?></dd>
            <dt>Cadastro</dt>
            <dd><?= e(date('d/m/Y H:i', strtotime($e['criado_em']))) ?></dd>
            <dt>Termos aceitos</dt>
            <dd><?= $e['termos_aceitos_em'] ? e(date('d/m/Y H:i', strtotime($e['termos_aceitos_em']))) : '-' ?></dd>
            <dt>Termo anexado</dt>
            <dd><?= $e['termo_arquivo'] ? '<a href="' . e(url('admin-termo', ['id' => $e['id']])) . '" target="_blank" rel="noopener">Abrir termo de cooperação</a>' : '-' ?>
            </dd>
        </dl>
    </div>
    <div class="ui-card">
        <h2>Contato</h2>
        <dl class="ui-kv">
            <dt>Responsável</dt>
            <dd><?= e($e['responsavel']) ?><?= $e['cargo'] ? ' (' . e($e['cargo']) . ')' : '' ?></dd>
            <dt>Telefone</dt>
            <dd><?= e(fone_fmt($e['telefone'])) ?></dd>
            <dt>Celular</dt>
            <dd><?= $e['celular'] ? e(fone_fmt($e['celular'])) : '-' ?></dd>
            <dt>E-mail de acesso</dt>
            <dd><?= e($e['email']) ?></dd>
            <dt>E-mail de contato</dt>
            <dd><?= e($e['email_contato'] ?: '-') ?></dd>
        </dl>
    </div>
</div>
<section class="ui-section" style="margin-top:22px">
    <h2>Vagas da empresa</h2>
    <?php if (!$vagas): echo Ui::vazio('Esta empresa ainda não enviou vagas');
    else: ?><div class="ui-grid">
        <?php foreach ($vagas as $v) {
                $ctx = 'admin';
                $ret = 'admin-empresa';
                require __DIR__ . '/partials/vaga_card_app.php';
            } ?>
    </div><?php endif; ?>
</section>
<div class="ui-card">
    <h2>Histórico da empresa</h2><?php require __DIR__ . '/partials/timeline.php'; ?>
</div>