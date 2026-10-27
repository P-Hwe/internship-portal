<?php $larga = false; ?>
<div class="auth-card">
    <span class="auth-env"><?= icon('building', 14) ?> Ambiente da empresa</span>
    <h1>Entrar</h1>
    <p class="lead">Acesse a área da sua empresa para gerenciar o perfil e as vagas.</p>
    <form method="post" action="<?= url('empresa-login') ?>" data-form novalidate>
        <?= csrf_field() ?><input type="hidden" name="next" value="<?= e($next) ?>">
        <?php if ($erro): ?><div class="ui-alert ui-alert-danger" role="alert">
            <div><?= e($erro) ?></div>
        </div><?php endif; ?>
        <?= Ui::campo(['name' => 'email', 'label' => 'E-mail de acesso', 'type' => 'email', 'req' => true, 'attrs' => ['autocomplete' => 'username']], ['email' => $email]) ?>
        <?= Ui::campo(['name' => 'senha', 'label' => 'Senha', 'type' => 'password', 'req' => true, 'attrs' => ['autocomplete' => 'current-password']]) ?>
        <button class="ui-btn ui-btn-primary ui-btn-lg ui-btn-block" type="submit">Entrar</button>
    </form>
    <div class="auth-links">
        <span><a href="<?= url('empresa-login', ['esqueci' => 1]) ?>" data-abrir-modal="modal-esqueci">Esqueceu a senha?</a></span>
        <span>Ainda não é parceira? <a href="<?= url('empresa-cadastro') ?>">Cadastrar empresa</a></span>
    </div>
</div>
<?php $perfil = 'empresa'; require __DIR__ . '/partials/modal_senha.php'; ?>