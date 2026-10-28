<?php

/** Desempenho de uma vaga ou empresa (para decidir o que destacar). Espera $desemp = Visitas::detalhe(). */
$emp = $desemp['tipo'] === 'empresa';
$d30 = $desemp['d30'];
$d7 = $desemp['d7'];
?>
<section class="ui-card" style="margin-bottom:16px">
    <h2 style="margin:0 0 4px">Desempenho <?= $emp ? 'da empresa' : 'da vaga' ?></h2>
    <p class="ui-hint" style="margin-top:0">Quantas vezes
        <?= $emp ? 'a página da empresa e as vagas dela' : 'esta vaga' ?> foram abertas por estudantes. Use para
        escolher o que colocar em destaque.</p>
    <div class="ui-metrics" style="margin-bottom:12px">
        <?= Ui::metrica('Visualizações (7 dias)', $d7['views'], 'eye') ?>
        <?= Ui::metrica('Visualizações (30 dias)', $d30['views'], 'eye') ?>
        <?= Ui::metrica('Visitantes diferentes (30 dias)', $d30['unicos'], 'users') ?>
        <?= Ui::metrica('Total desde o início', $desemp['tudo']['views'], 'chart') ?>
        <?php if ($emp): ?><?= Ui::metrica('Cliques para o site/LinkedIn (30 dias)', (int) $desemp['saidas'], 'external') ?><?php endif; ?>
        <?= Ui::metrica('Posição (30 dias)', $desemp['posicao'] ? $desemp['posicao'] . 'º de ' . $desemp['de'] : 'Sem visitas', 'trending', null, $desemp['posicao'] && $desemp['posicao'] <= 3 ? 'ok' : 'mute') ?>
    </div>
    <?= Ui::barras($desemp['dias'], [['visitas', 'Visualizações', 'var(--chart-1)']], 'Visualizações por dia nos últimos 14 dias') ?>
    <?php if ($desemp['tudo']['ultima']): ?><p class="ui-hint">Última visualização:
        <?= e(date('d/m/Y H:i', strtotime($desemp['tudo']['ultima']))) ?>.</p><?php endif; ?>
</section>