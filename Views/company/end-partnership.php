<?php
$live = (int) ($cont['publicada'] ?? 0) + (int) ($cont['agendada'] ?? 0) + (int) ($cont['aguardando'] ?? 0);
?>
<div class="ui-alert ui-alert-danger" role="note"><?= icon('info', 22) ?><div>
    <strong>Esta ação não pode ser desfeita</strong>
    <p>Ao encerrar a parceria, a <?= e($emp['nome']) ?> sai do portal e os dados de cadastro são apagados. Para voltar a divulgar vagas, será preciso fazer um novo cadastro e passar outra vez pela análise da Coordenação.</p>
</div></div>

<div class="ui-grid-2" style="align-items:start">
    <section class="ui-card">
        <h2><?= icon('x', 18) ?> O que sai do portal</h2>
        <ul class="ui-list end-list">
            <li><strong><?= $live ?> vaga(s)</strong> no ar, agendadas ou em análise saem do mural na hora.</li>
            <li>CNPJ, razão social, endereço, telefones, e-mails, site e LinkedIn.</li>
            <li><strong><?= (int) $membros ?> pessoa(s)</strong> com acesso, com nome, e-mail e senha.</li>
            <li>Logo, capa, imagens das vagas e o termo de cooperação enviado.</li>
            <li>E-mails e endereços de acesso guardados nos registros de entrada.</li>
        </ul>
    </section>
    <section class="ui-card">
        <h2><?= icon('shield', 18) ?> O que fica com o IFSP</h2>
        <ul class="ui-list end-list">
            <li>O nome da empresa, o segmento e a cidade, com as datas de cadastro e de encerramento.</li>
            <li>O histórico das vagas (título, área e período), sem contatos nem links.</li>
            <li>O registro de que a parceria foi encerrada por você.</li>
        </ul>
        <p class="ui-hint" style="margin:10px 0 0">O CNPJ fica livre para um novo cadastro no futuro. O convênio assinado continua arquivado no sistema oficial do IFSP.</p>
    </section>
</div>

<form class="ui-card end-form" method="post" action="<?= url('empresa-encerrar') ?>" data-form novalidate>
    <?= csrf_field() ?>
    <h2>Confirmar o encerramento</h2>
    <?php if ($erro): ?><div class="ui-alert ui-alert-danger" role="alert"><div><?= e($erro) ?></div></div><?php endif; ?>
    <div class="ui-grid-2">
        <?= Ui::campo(['name' => 'cnpj', 'label' => 'Digite o CNPJ da empresa', 'req' => true, 'attrs' => ['data-mask' => 'cnpj', 'autocomplete' => 'off', 'inputmode' => 'text']]) ?>
        <?= Ui::campo(['name' => 'senha', 'label' => 'Sua senha', 'type' => 'password', 'req' => true, 'attrs' => ['autocomplete' => 'current-password']]) ?>
    </div>
    <label class="ui-consent"><input type="checkbox" name="confirmo" value="1" required> <span>Entendo que as vagas saem do mural e que os dados da <?= e($emp['nome']) ?> serão apagados do portal.</span></label>
    <div class="ui-actions" style="margin-top:16px">
        <button class="ui-btn ui-btn-danger" type="submit"><?= icon('x', 16) ?> Encerrar parceria e apagar dados</button>
        <a class="ui-btn" href="<?= url('empresa-perfil') ?>">Cancelar</a>
    </div>
</form>
