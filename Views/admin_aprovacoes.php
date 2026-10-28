<?php $ret = 'admin-aprovacoes'; $total = count($empPend) + count($vagasPend); ?>
<?php if (!$total): echo Ui::vazio('Tudo em dia', 'Não há empresas nem vagas aguardando análise.'); endif; ?>
<?php if ($empPend): ?><section class="ui-section">
    <h2>Empresas aguardando aprovação <?= Ui::badge((string) count($empPend), 'warn') ?></h2>
    <div class="ui-grid">
        <?php foreach ($empPend as $e) { $retCard = 'admin-aprovacoes'; $comTermo = true; $e += ['total_vagas' => 0, 'vagas_abertas' => 0]; require __DIR__ . '/partials/empresa_card_app.php'; } ?>
    </div>
</section><?php endif; ?>
<?php if ($vagasPend): ?><section class="ui-section">
    <h2>Vagas aguardando aprovação <?= Ui::badge((string) count($vagasPend), 'warn') ?></h2>
    <div class="ui-grid">
        <?php foreach ($vagasPend as $v) { $ctx = 'admin'; require __DIR__ . '/partials/vaga_card_app.php'; } ?></div>
</section><?php endif; ?>