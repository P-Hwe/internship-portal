<?php
$abas = ['tudo' => 'Tudo', 'semana' => '1 semana', 'mes' => '1 mês', 'ano' => '1 ano'];
$q = fn(array $x) => url('admin-atividade', $x);
?>
<div class="ui-row-between">
    <div class="ui-actions"><a class="ui-btn ui-btn-sm" href="<?= url('admin') ?>"><?= icon('arrow-left', 15) ?>
            Início</a></div>
    <nav class="ui-period-tabs" aria-label="Período"><?php foreach ($abas as $k => $r): ?><a
            href="<?= e($q(['filtro' => $k])) ?>"
            class="<?= $k === $filtro ? 'is-active' : '' ?>"><?= e($r) ?></a><?php endforeach; ?></nav>
</div>
<form method="get" action="<?= e(BASE_URL) ?>" class="ui-actions" style="margin:12px 0;align-items:end">
    <?= campo_rota('admin-atividade') ?><input type="hidden" name="filtro" value="dia">
    <label class="ui-label" for="desde" style="margin:0">A partir de um dia específico, até hoje
        <input class="ui-input" type="date" id="desde" name="desde" value="<?= e($desde) ?>" max="<?= e($hoje) ?>"
            required></label>
    <button class="ui-btn ui-btn-primary" type="submit">Filtrar</button>
</form>
<p class="ui-sub"><?= (int) $total ?>
    registro(s)<?= $ini ? ' desde ' . e(data_br($ini)) . ' até hoje' : ' no histórico completo' ?>.</p>
<section class="ui-card">
    <?php if (!$lista): echo Ui::vazio('Nada neste período', 'Tente um período maior.');
    else: ?>
    <ul class="ui-feed">
        <?php foreach ($lista as $h): [$quem, $verbo, $item] = Historico::frase($h);
                $link = $h['entidade'] === 'vaga' ? url('admin-vaga', ['id' => $h['entidade_id']]) : ($h['entidade'] === 'empresa' ? url('admin-empresa', ['id' => $h['entidade_id']]) : null); ?>
        <li><strong><?= e($quem) ?></strong> <?= e($verbo) ?>
            <?= $link && $item ? '<a href="' . e($link) . '">' . e($item) . '</a>' : e($item) ?>
            <small><?= e(date('d/m/Y H:i', strtotime($h['criado_em']))) ?> ·
                <?= e(tempo_atras($h['criado_em'])) ?></small><?php if (in_array($h['acao'], ['rejeitada', 'reprovada', 'bloqueada'], true) && $h['detalhe']): ?><small
                class="why">Motivo: <?= e($h['detalhe']) ?></small><?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul><?php endif; ?>
</section>
<?php if ($paginas > 1): ?><nav class="ui-pager" aria-label="Páginas"><?php for ($i = 1; $i <= $paginas; $i++): ?><a
        class="ui-btn ui-btn-sm <?= $i === $pg ? 'ui-btn-primary' : '' ?>"
        href="<?= e($q(['filtro' => $filtro, 'desde' => $desde, 'pg' => $i])) ?>"><?= $i ?></a> <?php endfor; ?></nav>
<?php endif; ?>