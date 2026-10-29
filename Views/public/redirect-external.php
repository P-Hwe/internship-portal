<?php require __DIR__ . '/../header.php'; $ativa = 'empresas'; require __DIR__ . '/../partials/nav.php'; ?>
<div class="vg-notice">
    <h1>Você está saindo do sistema</h1>
    <p>Você será levado para o endereço informado pela empresa <strong><?= e($e['nome']) ?></strong>:</p>
    <p><span class="vg-code" style="word-break:break-all"><?= e($destino) ?></span></p>
    <p>O IFSP não controla o conteúdo de sites externos. Confira o endereço antes de informar qualquer dado pessoal.</p>
    <div class="vg-apply-actions" style="justify-content:center"><a class="vg-btn vg-btn-lg" href="<?= e($destino) ?>"
            target="_blank" rel="noopener noreferrer nofollow"><?= icon('external', 18) ?> Continuar para
            <?= e(Link::dominio($destino)) ?></a>
        <a class="vg-btn vg-btn-ghost vg-btn-lg" href="<?= url('empresas') ?>">Voltar</a>
    </div>
</div>
</div>
<?php require __DIR__ . '/../footer.php'; ?>