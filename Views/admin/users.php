<?php $c = fn(array $o) => Ui::campo($o, $old, $erros); $ehRaiz = Admin::ehRaiz($adm); ?>
<div class="ui-grid-2" style="grid-template-columns:minmax(0,1fr) 340px;align-items:start">
    <section>
        <div style="display:flex;align-items:baseline;justify-content:space-between;gap:12px;margin-bottom:12px">
            <h2 style="font-size:17px;margin:0">Administradores</h2>
            <span class="ui-hint" style="margin:0"><?= (int) $total ?> no total<?= $paginas > 1 ? ' · página ' . (int) $pg . ' de ' . (int) $paginas : '' ?></span>
        </div>
        <div class="ui-grid" style="grid-template-columns:repeat(auto-fill,minmax(260px,1fr))">
            <?php foreach ($admins as $a): $self = (int) $a['id'] === (int) $adm['id']; ?>
            <article class="ui-job">
                <div class="ui-job-top">
                    <strong><?= e($a['nome']) ?></strong><?= $a['papel'] === 'raiz' ? Ui::badge('Principal', 'info') : '' ?><?= $a['ativo'] ? Ui::badge('Ativo', 'ok') : Ui::badge('Desativado', 'mute') ?><?= !empty($a['trocar_senha']) ? Ui::badge('Aguardando 1º acesso', 'warn') : '' ?>
                </div>
                <ul class="ui-job-meta">
                    <li><?= icon('mail', 15) ?> <?= e($a['email']) ?></li>
                    <li><?= icon('clock', 15) ?> Último acesso:
                        <?= $a['ultimo_acesso'] ? e(date('d/m/Y H:i', strtotime($a['ultimo_acesso']))) : 'nunca' ?></li>
                    <li><?= icon('user', 15) ?> Criado em <?= data_br(substr($a['criado_em'], 0, 10)) ?></li>
                </ul>
                <?php if (!$self && $ehRaiz && $a['papel'] !== 'raiz'): $f = ['tipo' => 'admin', 'id' => $a['id'], 'ret' => 'admin-usuarios']; ?>
                <div class="ui-job-foot" style="gap:8px">
                    <?= $a['ativo']
                        ? Ui::formConfirmar(url('admin-acao'), $f + ['acao' => 'desativar'], 'Desativar', 'Desativar este administrador?', $a['nome'] . ' não conseguirá mais entrar e receberá um e-mail avisando. Você pode reativar depois.', 'danger', 'ui-btn ui-btn-sm')
                        : Ui::formConfirmar(url('admin-acao'), $f + ['acao' => 'ativar'], 'Reativar', 'Reativar este administrador?', $a['nome'] . ' volta a acessar a administração e recebe um e-mail avisando.', 'primary', 'ui-btn ui-btn-sm') ?>
                    <?= Ui::formConfirmar(url('admin-acao'), $f + ['acao' => 'excluir'], 'Excluir', 'Excluir este administrador?', $a['nome'] . ' sai da lista e perde o acesso de vez. O histórico de ações continua guardado.', 'danger', 'ui-btn ui-btn-sm ui-btn-danger-o') ?>
                </div>
                <?php elseif ($self): ?><div class="ui-job-foot"><span class="ui-hint" style="margin:0">Esta é a sua conta.</span></div><?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
        <?= Ui::paginacao($pg, $paginas, 'admin-usuarios') ?>
    </section>
    <?php if ($ehRaiz): ?><aside class="ui-card">
        <h2>Novo administrador</h2>
        <form method="post" action="<?= url('admin-acao') ?>" data-form novalidate><?= csrf_field() ?><input
                type="hidden" name="tipo" value="admin"><input type="hidden" name="acao" value="criar"><input
                type="hidden" name="ret" value="admin-usuarios">
            <?= $c(['name' => 'nome', 'label' => 'Nome', 'req' => true, 'attrs' => ['maxlength' => 100]]) ?>
            <?= $c(['name' => 'email', 'label' => 'E-mail institucional', 'type' => 'email', 'req' => true]) ?>
            <p class="ui-hint">O portal gera uma senha provisória e envia por e-mail, junto com um link para a pessoa criar a própria senha. Só ela vê essa senha.</p>
            <button class="ui-btn ui-btn-primary ui-btn-block" type="submit">Criar administrador</button>
        </form>
    </aside><?php else: ?><aside class="ui-card"><h2>Novo administrador</h2><p class="ui-hint">Somente o administrador principal (coordenador) cria, desativa e exclui administradores.</p></aside><?php endif; ?>
</div>
