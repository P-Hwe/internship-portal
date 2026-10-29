<?php $c = fn(array $o, $v = null) => Ui::campo($o, $old, $erros); $ehRaiz = Admin::ehRaiz($adm); ?>
<div class="ui-grid-2" style="grid-template-columns:minmax(0,1fr) 340px;align-items:start">
    <section>
        <h2 style="font-size:17px;margin-bottom:12px">Administradores</h2>
        <div class="ui-grid" style="grid-template-columns:repeat(auto-fill,minmax(260px,1fr))">
            <?php foreach ($admins as $a): ?>
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
                <?php if ((int) $a['id'] !== (int) $adm['id'] && $ehRaiz && $a['papel'] !== 'raiz'): ?><div class="ui-job-foot">
                    <?php
          echo $a['ativo'] ? Ui::formConfirmar(url('admin-acao'), ['tipo' => 'admin', 'acao' => 'desativar', 'id' => $a['id'], 'ret' => 'admin-usuarios'], 'Desativar', 'Desativar este administrador?', $a['nome'] . ' não conseguirá mais entrar. Você pode reativar depois.', 'danger', 'ui-btn ui-btn-sm ui-btn-danger-o')
                        : Ui::formConfirmar(url('admin-acao'), ['tipo' => 'admin', 'acao' => 'ativar', 'id' => $a['id'], 'ret' => 'admin-usuarios'], 'Reativar', 'Reativar este administrador?', $a['nome'] . ' volta a acessar a administração.', 'primary', 'ui-btn ui-btn-sm'); ?>
                </div>
                <?php elseif ((int) $a['id'] === (int) $adm['id']): ?><div class="ui-job-foot"><span class="ui-hint" style="margin:0">Esta é a sua conta.</span>
                </div><?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php if ($ehRaiz): ?><aside class="ui-card">
        <h2>Novo administrador</h2>
        <form method="post" action="<?= url('admin-acao') ?>" data-form novalidate><?= csrf_field() ?><input
                type="hidden" name="tipo" value="admin"><input type="hidden" name="acao" value="criar"><input
                type="hidden" name="ret" value="admin-usuarios">
            <?= $c(['name' => 'nome', 'label' => 'Nome', 'req' => true, 'attrs' => ['maxlength' => 100]]) ?>
            <?= $c(['name' => 'email', 'label' => 'E-mail institucional', 'type' => 'email', 'req' => true]) ?>
            <?= Ui::campo(['name' => 'senha', 'label' => 'Senha inicial', 'type' => 'password', 'req' => true, 'hint' => '8+ caracteres, com letras e números.', 'attrs' => ['autocomplete' => 'new-password', 'data-pwd' => '']], [], $erros) ?>
            <p class="ui-hint">A pessoa recebe um e-mail com o link de acesso e troca esta senha inicial no primeiro login. Combine a senha inicial com ela por outro canal.</p>
            <button class="ui-btn ui-btn-primary ui-btn-block" type="submit">Criar administrador</button>
        </form>
    </aside><?php else: ?><aside class="ui-card"><h2>Novo administrador</h2><p class="ui-hint">Somente o administrador principal (coordenador) cria e desativa administradores.</p></aside><?php endif; ?>
</div>