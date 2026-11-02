<?php
$c = fn(array $o) => Ui::campo($o, $old, $erros);
$me = $emp['usuario'];
$isPrincipal = CompanyMember::isPrincipal($me);
$roleTone = [CompanyMember::PRINCIPAL => 'info', CompanyMember::MANAGER => 'ok', CompanyMember::MEMBER => 'mute'];
$roles = $isPrincipal ? [CompanyMember::MEMBER => CompanyMember::ROLES[CompanyMember::MEMBER], CompanyMember::MANAGER => CompanyMember::ROLES[CompanyMember::MANAGER]] : [CompanyMember::MEMBER => CompanyMember::ROLES[CompanyMember::MEMBER]];
?>
<div class="ui-grid-2" style="grid-template-columns:minmax(0,1fr) 340px;align-items:start">
    <section>
        <div style="display:flex;align-items:baseline;justify-content:space-between;gap:12px;margin-bottom:12px">
            <h2 style="font-size:17px;margin:0">Pessoas com acesso</h2>
            <span class="ui-hint" style="margin:0"><?= (int) $total ?> no total<?= $paginas > 1 ? ' · página ' . (int) $pg . ' de ' . (int) $paginas : '' ?></span>
        </div>
        <div class="ui-grid" style="grid-template-columns:repeat(auto-fill,minmax(260px,1fr))">
            <?php foreach ($membros as $m):
                $self = (int) $m['id'] === (int) $me['id'];
                $principal = $m['papel'] === CompanyMember::PRINCIPAL;
                $byCoord = !$m['ativo'] && $m['desativado_por'] === 'admin';
                $f = ['id' => $m['id'], 'pg' => $pg]; ?>
            <article class="ui-job">
                <div class="ui-job-top">
                    <strong><?= e($m['nome']) ?></strong><?= Ui::badge(CompanyMember::roleLabel($m['papel']), $roleTone[$m['papel']] ?? 'mute') ?><?= $m['ativo'] ? '' : Ui::badge($byCoord ? 'Desativado pela Coordenação' : 'Desativado', $byCoord ? 'danger' : 'mute') ?><?= !empty($m['trocar_senha']) && $m['ativo'] ? Ui::badge('Aguardando 1º acesso', 'warn') : '' ?>
                </div>
                <ul class="ui-job-meta">
                    <li><?= icon('mail', 15) ?> <?= e($m['email']) ?></li>
                    <li><?= icon('clock', 15) ?> Último acesso: <?= $m['ultimo_acesso'] ? e(date('d/m/Y H:i', strtotime($m['ultimo_acesso']))) : 'nunca' ?></li>
                    <li><?= icon('user', 15) ?> Desde <?= data_br(substr($m['criado_em'], 0, 10)) ?></li>
                </ul>
                <?php if ($self): ?>
                <div class="ui-job-foot"><span class="ui-hint" style="margin:0">Esta é a sua conta.</span></div>
                <?php elseif ($principal): ?>
                <div class="ui-job-foot"><span class="ui-hint" style="margin:0">Responsável pelo cadastro da empresa. Não pode ser removido.</span></div>
                <?php else: ?>
                <div class="ui-job-foot" style="gap:8px;flex-wrap:wrap">
                    <?php if ($isPrincipal): $toManager = $m['papel'] === CompanyMember::MEMBER; ?>
                    <?= Ui::formConfirmar(url('empresa-usuarios'), $f + ['acao' => 'papel', 'papel' => $toManager ? CompanyMember::MANAGER : CompanyMember::MEMBER], $toManager ? 'Tornar administrador' : 'Tornar representante',
                        $toManager ? 'Dar permissão de administrador?' : 'Tirar a permissão de administrador?',
                        $toManager ? $m['nome'] . ' poderá ver o desempenho e adicionar, desativar e remover usuários (menos você).' : $m['nome'] . ' continua publicando vagas, mas deixa de ver usuários e desempenho.',
                        'primary', 'ui-btn ui-btn-sm') ?>
                    <?php endif; ?>
                    <?php if ($m['ativo']): ?>
                    <?= Ui::formConfirmar(url('empresa-usuarios'), $f + ['acao' => 'desativar'], 'Desativar', 'Desativar este acesso?', $m['nome'] . ' não conseguirá mais entrar e receberá um e-mail avisando. Dá para reativar depois.', 'danger', 'ui-btn ui-btn-sm') ?>
                    <?php elseif (!$byCoord): ?>
                    <?= Ui::formConfirmar(url('empresa-usuarios'), $f + ['acao' => 'ativar'], 'Reativar', 'Reativar este acesso?', $m['nome'] . ' volta a entrar na área da empresa.', 'primary', 'ui-btn ui-btn-sm') ?>
                    <?php endif; ?>
                    <?= Ui::formConfirmar(url('empresa-usuarios'), $f + ['acao' => 'excluir'], 'Remover', 'Remover este usuário?', $m['nome'] . ' perde o acesso de vez e recebe um e-mail avisando. As vagas publicadas continuam.', 'danger', 'ui-btn ui-btn-sm ui-btn-danger-o') ?>
                </div>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
        <?= Ui::paginacao($pg, $paginas, 'empresa-usuarios') ?>
    </section>
    <aside class="ui-card">
        <h2>Adicionar pessoa</h2>
        <form method="post" action="<?= url('empresa-usuarios') ?>" data-form novalidate><?= csrf_field() ?><input type="hidden" name="acao" value="criar">
            <?= $c(['name' => 'nome', 'label' => 'Nome completo', 'req' => true, 'attrs' => ['maxlength' => 100]]) ?>
            <?= $c(['name' => 'email', 'label' => 'E-mail', 'type' => 'email', 'req' => true, 'attrs' => ['maxlength' => 120]]) ?>
            <?= $c(['name' => 'papel', 'label' => 'Permissão', 'type' => 'select', 'req' => true, 'opcoes' => $roles, 'vazio' => 'Escolha']) ?>
            <ul class="ui-hint" style="padding-left:18px;margin:0 0 12px">
                <li><strong>Representante:</strong> publica, edita e encerra vagas.</li>
                <?php if ($isPrincipal): ?><li><strong>Administrador da empresa:</strong> também vê o desempenho e gerencia usuários.</li><?php endif; ?>
            </ul>
            <p class="ui-hint">A pessoa recebe por e-mail uma senha provisória e um link para criar a própria senha. Você não vê essa senha. A Coordenação de estágios também é avisada.</p>
            <button class="ui-btn ui-btn-primary ui-btn-block" type="submit">Adicionar</button>
        </form>
    </aside>
</div>
