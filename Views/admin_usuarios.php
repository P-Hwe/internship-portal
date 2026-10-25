<?php

/**
 * @var array $admins
 * @var array $adm
 * @var array $old
 * @var array $erros
 */
?>
<?php $c = fn(array $o, $v = null) => Ui::campo($o, $old, $erros); ?>
<?php require __DIR__ . '/../../public/css/admin_usuarios.css'; ?>
<div class="ui-grid-2">
  <section>
    <h2>Administradores</h2>
    <div class="ui-grid"><?php foreach ($admins as $a): ?>
        <article class="ui-job">
          <div class="ui-job-top">
            <strong><?= e($a['nome']) ?></strong><?= $a['ativo'] ? Ui::badge('Ativo', 'ok') : Ui::badge('Desativado', 'mute') ?>
          </div>
          <ul class="ui-job-meta">
            <li><?= icon('mail', 15) ?> <?= e($a['email']) ?></li>
            <li><?= icon('clock', 15) ?> Último acesso:
              <?= $a['ultimo_acesso'] ? e(date('d/m/Y H:i', strtotime($a['ultimo_acesso']))) : 'nunca' ?></li>
            <li><?= icon('user', 15) ?> Criado em <?= data_br(substr($a['criado_em'], 0, 10)) ?></li>
          </ul>
          <?php if ((int) $a['id'] !== (int) $adm['id']): ?><div class="ui-job-foot">
              <?php
                              echo $a['ativo'] ? Ui::formConfirmar(url('admin-acao'), ['tipo' => 'admin', 'acao' => 'desativar', 'id' => $a['id'], 'ret' => 'admin-usuarios'], 'Desativar', 'Desativar este administrador?', $a['nome'] . ' não conseguirá mais entrar. Você pode reativar depois.', 'danger', 'ui-btn ui-btn-sm ui-btn-danger-o')
                                : Ui::formConfirmar(url('admin-acao'), ['tipo' => 'admin', 'acao' => 'ativar', 'id' => $a['id'], 'ret' => 'admin-usuarios'], 'Reativar', 'Reativar este administrador?', $a['nome'] . ' volta a acessar a administração.', 'primary', 'ui-btn ui-btn-sm'); ?>
            </div>
          <?php else: ?><div class="ui-job-foot"><span class="ui-hint">Esta é a sua conta.</span></div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
  <aside class="ui-card">
    <h2>Novo administrador</h2>
    <form method="post" action="<?= url('admin-acao') ?>" data-form novalidate><?= csrf_field() ?><input
        type="hidden" name="tipo" value="admin"><input type="hidden" name="acao" value="criar"><input
        type="hidden" name="ret" value="admin-usuarios">
      <?= $c(['name' => 'nome', 'label' => 'Nome', 'req' => true, 'attrs' => ['maxlength' => 100]]) ?>
      <?= $c(['name' => 'email', 'label' => 'E-mail institucional', 'type' => 'email', 'req' => true]) ?>
      <?= Ui::campo(['name' => 'senha', 'label' => 'Senha inicial', 'type' => 'password', 'req' => true, 'hint' => '8+ caracteres, com letras e números.', 'attrs' => ['autocomplete' => 'new-password', 'data-pwd' => '']], [], $erros) ?>
      <button class="ui-btn ui-btn-primary ui-btn-block" type="submit">Criar administrador</button>
    </form>
  </aside>
</div>
