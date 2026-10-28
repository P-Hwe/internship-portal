<?php $larga = true; ?>
<div class="auth-card">
    <span class="auth-env"><?= icon('check', 14) ?> Etapa final</span>
    <ol class="ui-stepper">
        <?php foreach (['Dados', 'Endereço', 'Contato', 'Acesso', 'Termos', 'Conclusão'] as $i => $t): ?><li
            class="is-done"><button type="button" disabled><span class="n"><?= $i + 1 ?></span><span
                    class="t"><?= e($t) ?></span></button></li><?php endforeach; ?>
    </ol>
    <h1>Cadastro enviado</h1>
    <p class="lead">Recebemos os dados da <strong><?= e($emp['nome']) ?></strong>. A Coordenação vai conferir as
        informações e o termo de cooperação.</p>
    <div class="ui-card">
        <ul class="ui-list">
            <li>Você receberá um e-mail em <strong><?= e($emp['email']) ?></strong> com o resultado da análise.</li>
            <li>Enquanto isso, já pode completar o perfil da empresa.</li>
            <li>Depois da aprovação, você poderá publicar vagas.</li>
        </ul>
    </div>
    <a class="ui-btn ui-btn-primary ui-btn-lg" href="<?= url('painel') ?>">Ir para a área da empresa</a>
</div>