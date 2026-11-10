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
            <?= e(Company::segmentoTxt($e)) ?><?= $e['porte'] ? ' · ' . e($e['porte']) : '' ?><?= $e['cidade'] ? ' · ' . e($e['cidade'] . '/' . $e['uf']) : '' ?>
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
<?php require __DIR__ . '/../partials/performance-item.php'; ?>
<?php if ($e['status'] === Status::E_ENCERRADA): ?>
<div class="ui-alert ui-alert-info"><?= icon('shield', 22) ?><div>
    <strong>Parceria encerrada pela empresa<?= $e['encerrada_em'] ? ' em ' . e(date('d/m/Y', strtotime($e['encerrada_em']))) : '' ?></strong>
    <p>A empresa pediu o encerramento pela área da empresa. CNPJ, contatos, usuários e arquivos foram apagados do portal (LGPD) e as vagas saíram do mural. Fica só este registro e o histórico abaixo. O convênio assinado segue arquivado no sistema oficial do IFSP.</p>
</div></div>
<?php else: ?>
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
<section class="ui-card ag-admin" style="margin-top:22px" id="convenio">
    <h2>Convênio de Concessão de Estágio <?= $e['convenio_arquivo'] ? Ui::badge('Assinado por todas as partes', 'ok') : Ui::badge('Falta a via final', 'warn') ?></h2>
    <dl class="ui-kv">
        <dt>Via da empresa</dt>
        <dd><?= $e['termo_arquivo'] ? '<a href="' . e(url('admin-termo', ['id' => $e['id']])) . '" target="_blank" rel="noopener">Baixar a via assinada pela empresa</a>' : 'Não enviada' ?></dd>
        <dt>Via final</dt>
        <dd><?= $e['convenio_arquivo'] ? '<a href="' . e(url('admin-convenio', ['id' => $e['id']])) . '" target="_blank" rel="noopener">Baixar o convênio assinado por todas as partes</a> · anexado em ' . e(date('d/m/Y', strtotime($e['convenio_assinado_em']))) : 'Ainda não anexada' ?></dd>
    </dl>
    <form method="post" action="<?= url('admin-convenio', ['id' => $e['id']]) ?>" enctype="multipart/form-data" class="ag-upload">
        <?= csrf_field() ?>
        <p class="ui-hint" style="margin:12px 0 8px">Baixe a via da empresa, colha as assinaturas do(a) Diretor(a)-Geral e do setor de estágio no sistema oficial do IFSP e anexe aqui o PDF final. A empresa recebe um e-mail e baixa o arquivo na aba Convênio.</p>
        <div class="ui-actions">
            <input type="file" name="convenio" accept="application/pdf,.pdf" required aria-label="Convênio assinado por todas as partes (PDF)">
            <button class="ui-btn ui-btn-sm ui-btn-primary" type="submit"><?= icon('file', 15) ?> <?= $e['convenio_arquivo'] ? 'Substituir via final' : 'Anexar via final' ?></button>
        </div>
    </form>
</section>
<section class="ui-section" style="margin-top:22px">
    <h2>Usuários da empresa <span class="ui-hint" style="font-weight:400">· <?= count($membros) ?></span></h2>
    <div class="ui-table-wrap"><table class="ui-table">
        <thead><tr><th>Nome</th><th>Cargo</th><th>Situação</th><th>Último acesso</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($membros as $m): $f = ['tipo' => 'membro', 'id' => $m['id'], 'ret' => 'admin-empresa', 'ret_id' => $e['id']]; $principal = $m['papel'] === CompanyMember::PRINCIPAL; ?>
            <tr>
                <td><strong><?= e($m['nome']) ?></strong><br><small class="ui-sub"><?= e($m['email']) ?></small></td>
                <td><?= Ui::badge(CompanyMember::roleLabel($m['papel']), ['principal' => 'info', 'gestor' => 'ok'][$m['papel']] ?? 'mute') ?></td>
                <td><?= $m['ativo'] ? (!empty($m['trocar_senha']) ? Ui::badge('Aguardando 1º acesso', 'warn') : Ui::badge('Ativo', 'ok')) : Ui::badge($m['desativado_por'] === 'admin' ? 'Desativado pela Coordenação' : 'Desativado pela empresa', 'mute') ?></td>
                <td><?= $m['ultimo_acesso'] ? e(date('d/m/Y H:i', strtotime($m['ultimo_acesso']))) : 'nunca' ?></td>
                <td style="text-align:right;white-space:nowrap">
                    <?php if ($principal): ?><span class="ui-hint" style="margin:0">Use as ações da empresa</span>
                    <?php else: ?>
                    <div class="ui-actions" style="justify-content:flex-end">
                        <?= $m['ativo']
                            ? Ui::formConfirmar(url('admin-acao'), $f + ['acao' => 'desativar'], 'Desativar', 'Desativar este usuário?', $m['nome'] . ' não conseguirá mais entrar. A empresa não pode reativar um acesso desativado pela Coordenação.', 'danger', 'ui-btn ui-btn-sm')
                            : Ui::formConfirmar(url('admin-acao'), $f + ['acao' => 'ativar'], 'Reativar', 'Reativar este usuário?', $m['nome'] . ' volta a entrar na área da empresa.', 'primary', 'ui-btn ui-btn-sm') ?>
                        <?= Ui::formConfirmar(url('admin-acao'), $f + ['acao' => 'excluir'], 'Excluir', 'Excluir este usuário?', $m['nome'] . ' some das listas e perde o acesso. Os dados continuam guardados no banco.', 'danger', 'ui-btn ui-btn-sm ui-btn-danger-o') ?>
                    </div>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</section>
<?php endif; ?>
<section class="ui-section" style="margin-top:22px">
    <h2>Vagas da empresa</h2>
    <?php if (!$vagas): echo Ui::vazio('Esta empresa ainda não enviou vagas'); else: ?><div class="ui-grid">
        <?php foreach ($vagas as $v) { $ctx = 'admin'; $ret = 'admin-empresa'; require __DIR__ . '/../partials/job-card-panel.php'; } ?>
    </div><?php endif; ?>
</section>
<div class="ui-card">
    <h2>Histórico da empresa</h2><?php require __DIR__ . '/../partials/timeline.php'; ?>
</div>