<?php $member = $emp['usuario']; ?>
<div class="auth-card">
    <span class="auth-env"><?= icon('building', 14) ?> <?= e($emp['nome']) ?></span>
    <h1>Crie a sua senha</h1>
    <p class="lead">Olá, <?= e(primeiro_nome($member['nome'])) ?>! A senha que chegou no seu e-mail é só para o primeiro acesso. Crie uma senha sua para continuar.</p>
    <form method="post" action="<?= url('empresa-senha') ?>" data-form novalidate>
        <?= csrf_field() ?>
        <?php if ($erro): ?><div class="ui-alert ui-alert-danger" role="alert"><div><?= e($erro) ?></div></div><?php endif; ?>
        <?= Ui::campo(['name' => 'atual', 'label' => 'Senha provisória (a do e-mail)', 'type' => 'password', 'req' => true, 'attrs' => ['autocomplete' => 'current-password']]) ?>
        <?= Ui::campo(['name' => 'nova', 'label' => 'Nova senha', 'type' => 'password', 'req' => true, 'hint' => '8+ caracteres, com letras e números.', 'attrs' => ['autocomplete' => 'new-password', 'data-pwd' => '']]) ?>
        <?= Ui::campo(['name' => 'confirma', 'label' => 'Repita a nova senha', 'type' => 'password', 'req' => true, 'attrs' => ['autocomplete' => 'new-password']]) ?>
        <button class="ui-btn ui-btn-primary ui-btn-lg ui-btn-block" type="submit">Salvar e entrar</button>
    </form>
    <form method="post" action="<?= url('sair') ?>" style="margin-top:12px;text-align:center"><?= csrf_field() ?><input type="hidden" name="perfil" value="empresa"><button class="ui-btn ui-btn-sm" type="submit">Sair</button></form>
</div>
