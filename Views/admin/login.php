<?php
/** View de login da administração */
$modalHtml = $modal ? view('partials/modal-password', ['perfil' => 'admin', 'modal' => $modal]) : '';

?>
<div class="auth-card">
    <div class="auth-logo-wrap">
        <img src="<?= BASE_URL ?>public/img/ifsp/if.png" alt="IFSP Guarulhos" width="120" height="120">
        <h1>Portal de Vagas de Estágio</h1>
        <p>Acesse sua conta de administrador</p>
    </div>

    <?php if ($error): ?>
    <div class="ui-alert ui-alert-danger" role="alert">
        <div><?= e($error) ?></div>
    </div>
    <?php endif; ?>

    <form method="post" action="<?= url('admin-login') ?>" data-form novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="next" value="<?= e($_GET['next'] ?? 'painel') ?>">

        <div class="ui-field">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" class="ui-input" value="<?= e($email) ?>" required autocomplete="username" autofocus>
        </div>

        <div class="ui-field">
            <label for="senha">Senha</label>
            <div class="ui-input-wrap">
                <input type="password" id="senha" name="senha" class="ui-input" required autocomplete="current-password">
                <button type="button" class="app-icon-btn" data-toggle-visibility="senha" aria-label="Mostrar senha"><i class="icon-eye"></i></button>
            </div>
        </div>

        <div class="ui-field">
            <a href="<?= url('senha-esqueci', ['esqueci' => '1']) ?>" class="ui-link">Esqueceu a senha?</a>
        </div>

        <button type="submit" class="ui-btn ui-btn-primary ui-btn-block">Entrar</button>
    </form>

    <?= $modalHtml ?>

    <div class="auth-footer">
        <p>&copy; <?= (string) date('Y') ?> IFSP Campus Guarulhos. Todos os direitos reservados.</p>
    </div>
</div>
