<?php
$m = $modal ?? null; $abre1 = $m && ($m['passo'] ?? 1) === 1; $abre2 = $m && ($m['passo'] ?? 1) === 2;
$com = !empty($m['token']);
?>
<dialog class="ui-modal" id="modal-esqueci" <?= $abre1 ? 'data-abrir' : '' ?> aria-labelledby="t-esqueci">
    <form method="post" action="<?= url('senha-esqueci') ?>" data-form novalidate>
        <?= csrf_field() ?><input type="hidden" name="perfil" value="<?= e($perfil) ?>">
        <div class="ui-modal-head"><h2 id="t-esqueci">Esqueceu a senha?</h2><button type="button" class="app-icon-btn" data-fechar-modal aria-label="Fechar"><?= icon('x', 18) ?></button></div>
        <p class="ui-hint">Informe o e-mail da conta. Vamos enviar um link e um código para você criar uma nova senha.</p>
        <?php if ($abre1 && !empty($m['erro'])): ?><div class="ui-alert ui-alert-danger" role="alert"><div><?= e($m['erro']) ?></div></div><?php endif; ?>
        <?= Ui::campo(['name' => 'email', 'id' => 'esqueci-email', 'label' => 'E-mail da conta', 'type' => 'email', 'req' => true, 'attrs' => ['autocomplete' => 'username']], ['email' => $m['email'] ?? '']) ?>
        <div class="ui-modal-foot"><button type="button" class="ui-btn" data-fechar-modal>Cancelar</button><button class="ui-btn ui-btn-primary" type="submit">Enviar link e código</button></div>
        <p class="ui-hint" style="margin-top:12px">Já tem um código? <a href="#" data-trocar-modal="modal-redefinir">Digitar o código</a></p>
    </form>
</dialog>
<dialog class="ui-modal" id="modal-redefinir" <?= $abre2 ? 'data-abrir' : '' ?> aria-labelledby="t-redef">
    <form method="post" action="<?= url('senha-redefinir') ?>" data-form novalidate>
        <?= csrf_field() ?><input type="hidden" name="perfil" value="<?= e($perfil) ?>"><input type="hidden" name="token" value="<?= e($m['token'] ?? '') ?>">
        <div class="ui-modal-head"><h2 id="t-redef">Criar nova senha</h2><button type="button" class="app-icon-btn" data-fechar-modal aria-label="Fechar"><?= icon('x', 18) ?></button></div>
        <?php if ($abre2 && !empty($m['aviso'])): ?><div class="ui-alert ui-alert-info" role="status"><div><?= e($m['aviso']) ?></div></div><?php endif; ?>
        <?php if ($abre2 && !empty($m['erro'])): ?><div class="ui-alert ui-alert-danger" role="alert"><div><?= e($m['erro']) ?></div></div><?php endif; ?>
        <?php if ($com): ?><p class="ui-hint">Link confirmado para <strong><?= e($m['email']) ?></strong>. Escolha a nova senha.</p>
        <?php else: ?>
        <?= Ui::campo(['name' => 'email', 'id' => 'redef-email', 'label' => 'E-mail da conta', 'type' => 'email', 'req' => true, 'attrs' => ['autocomplete' => 'username']], ['email' => $m['email'] ?? '']) ?>
        <?= Ui::campo(['name' => 'codigo', 'id' => 'redef-codigo', 'label' => 'Código de 6 dígitos (do e-mail)', 'type' => 'text', 'req' => true, 'attrs' => ['inputmode' => 'numeric', 'maxlength' => '6', 'pattern' => '[0-9]{6}', 'autocomplete' => 'one-time-code']]) ?>
        <?php endif; ?>
        <?= Ui::campo(['name' => 'nova', 'id' => 'redef-nova', 'label' => 'Nova senha', 'type' => 'password', 'req' => true, 'hint' => '8+ caracteres, com letras e números.', 'attrs' => ['autocomplete' => 'new-password']]) ?>
        <?= Ui::campo(['name' => 'confirma', 'id' => 'redef-conf', 'label' => 'Repita a nova senha', 'type' => 'password', 'req' => true, 'attrs' => ['autocomplete' => 'new-password']]) ?>
        <div class="ui-modal-foot"><button type="button" class="ui-btn" data-fechar-modal>Cancelar</button><button class="ui-btn ui-btn-primary" type="submit">Salvar nova senha</button></div>
        <?php if (!$com): ?><p class="ui-hint" style="margin-top:12px">Não recebeu? <a href="#" data-trocar-modal="modal-esqueci">Pedir um novo código</a></p><?php endif; ?>
    </form>
</dialog>