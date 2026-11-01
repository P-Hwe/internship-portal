<?php $larga = false; ?>
<div class="auth-card">
    <span class="auth-env"><?= icon('building', 14) ?> Área da empresa</span>
    <h1>Entrar na sua conta</h1>
    <p class="lead">Gerencie o perfil da empresa e as vagas publicadas para os estudantes.</p>
    <form method="post" action="<?= url('empresa-login') ?>" data-form novalidate>
        <?= csrf_field() ?><input type="hidden" name="next" value="<?= e($next) ?>">
        <?php if ($erro): ?><div class="ui-alert ui-alert-danger" role="alert"><div><?= e($erro) ?></div></div><?php endif; ?>
        <?= Ui::campo(['name' => 'email', 'label' => 'E-mail de acesso', 'type' => 'email', 'req' => true, 'attrs' => ['autocomplete' => 'username', 'placeholder' => 'contato@suaempresa.com.br']], ['email' => $email]) ?>
        <?= Ui::campo(['name' => 'senha', 'label' => 'Senha', 'type' => 'password', 'req' => true, 'attrs' => ['autocomplete' => 'current-password']]) ?>
        <button class="ui-btn ui-btn-primary ui-btn-lg ui-btn-block" type="submit">Entrar</button>
    </form>
    <div class="auth-links">
        <span>Esqueceu a senha? <a href="<?= url('empresa-login', ['esqueci' => 1]) ?>" data-abrir-modal="modal-esqueci">Redefinir por e-mail</a></span>
        <span>Ainda não é parceira? <a href="<?= url('empresa-cadastro') ?>">Cadastrar empresa</a></span>
    </div>
</div>
<?php $perfil = 'empresa'; require __DIR__ . '/../partials/modal-password.php'; ?>
