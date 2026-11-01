<?php
$erro   = $erro ?? null;
$perfil = 'admin';
require __DIR__ . '/../partials/modal-password.php';
?>
<div class="auth-card">
    <span class="auth-env"><?= icon('shield', 14) ?> Acesso restrito à Coordenação</span>
    <h1>Entrar no painel</h1>
    <p class="lead">Use o e-mail cadastrado pela Coordenação de estágios do câmpus.</p>
    <form method="post" action="<?= url('admin-login') ?>" data-form novalidate>
        <?= csrf_field() ?>
        <?php if ($erro): ?><div class="ui-alert ui-alert-danger" role="alert"><div><?= e($erro) ?></div></div><?php endif; ?>
        <?= Ui::campo(['name' => 'email', 'label' => 'E-mail', 'type' => 'email', 'req' => true, 'attrs' => ['autocomplete' => 'username', 'autofocus' => true, 'placeholder' => 'nome@ifsp.edu.br']], ['email' => $email ?? '']) ?>
        <?= Ui::campo(['name' => 'senha', 'label' => 'Senha', 'type' => 'password', 'req' => true, 'attrs' => ['autocomplete' => 'current-password']]) ?>
        <button class="ui-btn ui-btn-primary ui-btn-lg ui-btn-block" type="submit">Entrar</button>
    </form>
    <div class="auth-links">
        <span>Esqueceu a senha? <a href="<?= url('admin-login', ['esqueci' => 1]) ?>" data-abrir-modal="modal-esqueci">Redefinir por e-mail</a></span>
        <span>É uma empresa parceira? <a href="<?= url('empresa-login') ?>">Entrar na área da empresa</a></span>
    </div>
</div>
