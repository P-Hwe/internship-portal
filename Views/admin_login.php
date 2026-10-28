<div class="auth-card">
    <span class="auth-env"><?= icon('lock', 14) ?> Ambiente administrativo</span>
    <h1>Entrar na administração</h1>
    <p class="lead">Acesso restrito à Coordenação e à Secretaria.</p>
    <form method="post" action="<?= url('admin-login') ?>" data-form novalidate>
        <?= csrf_field() ?>
        <?php if ($erro): ?><div class="ui-alert ui-alert-danger" role="alert">
            <div><?= e($erro) ?></div>
        </div><?php endif; ?>
        <?= Ui::campo(['name' => 'email', 'label' => 'E-mail institucional', 'type' => 'email', 'req' => true, 'attrs' => ['autocomplete' => 'username']], ['email' => $email]) ?>
        <?= Ui::campo(['name' => 'senha', 'label' => 'Senha', 'type' => 'password', 'req' => true, 'attrs' => ['autocomplete' => 'current-password']]) ?>
        <button class="ui-btn ui-btn-primary ui-btn-lg ui-btn-block" type="submit">Entrar</button>
    </form>
    <div class="auth-links"><span><a href="<?= url('admin-login', ['esqueci' => 1]) ?>"
                data-abrir-modal="modal-esqueci">Esqueceu a senha?</a></span></div>
</div>
<?php $perfil = 'admin';
require __DIR__ . '/partials/modal_senha.php'; ?>