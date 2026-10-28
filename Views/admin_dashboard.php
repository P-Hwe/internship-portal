<?php
$j = $resumo['janela'];
$at = $resumo['atual'];
$an = $resumo['anterior'];
[$rotPeriodo, $txtAtual, $txtAnt] = Visitas::PERIODOS[$periodo];
$delta = function (int $a, int $b) {
    [$t, $p] = Visitas::variacao($a, $b);
    if ($t === 'novo') return '<span class="ui-delta is-up">novo no período</span>';
    if ($t === 'igual') return '<span class="ui-delta">igual ao período anterior</span>';
    return '<span class="ui-delta ' . ($t === 'sobe' ? 'is-up' : 'is-down') . '">' . ($p > 0 ? '+' : '') . $p . '% sobre o período anterior</span>';
};
$m = fn(string $r, int $a, int $b) => '<div class="ui-metric"><span class="ui-metric-n">' . $a . '</span><span class="ui-metric-l">' . e($r) . '</span>' . $delta($a, $b) . '</div>';
?>
<?php if ($pend): ?><p style="margin:-10px 0 24px"><a class="ui-btn ui-btn-primary"
        href="<?= url('admin-aprovacoes') ?>">Abrir aprovações</a></p><?php endif; ?>

<section class="ui-section">
    <div class="ui-row-between">
        <h2 style="margin:0">Quem visitou o site de vagas</h2>
        <nav class="ui-period-tabs" aria-label="Período"><?php foreach (Visitas::PERIODOS as $k => $d): ?><a
                href="<?= e(url('admin', ['periodo' => $k])) ?>"
                class="<?= $k === $periodo ? 'is-active' : '' ?>"><?= e($d[0]) ?></a><?php endforeach; ?></nav>
    </div>
    <p class="ui-sub">Mostra os <?= e($txtAtual) ?> comparados com os <?= e($txtAnt) ?>. Conta estudantes que entram sem
        login, de qualquer aparelho. Administradores, empresas logadas e robôs ficam de fora.</p>
    <div class="ui-metrics">
        <?= $m('visualizações de página', $at['visualizacoes'], $an['visualizacoes']) ?>
        <?= $m('visitantes diferentes', $at['visitantes'], $an['visitantes']) ?>
        <?= $m('cliques para o site da empresa', $at['saidas'], $an['saidas']) ?>
        <?= $m('pessoas que fizeram login', $at['logins'], $an['logins']) ?>
    </div>
    <div class="ui-grid-2">
        <div class="ui-card">
            <h2>Visualizações por <?= $j['bucket'] === 'mes' ? 'mês' : 'dia' ?></h2>
            <?= Ui::comparar($resumo['linhas'], 'Visualizações ' . $txtAtual . ' e ' . $txtAnt) ?>
        </div>
        <div class="ui-card">
            <h2>Por página do site</h2>
            <table class="ui-table" style="border:0">
                <thead>
                    <tr>
                        <th>Página</th>
                        <th class="n">Agora</th>
                        <th class="n">Antes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resumo['paginas'] as $p): ?><tr>
                        <td><?= e($p['rot']) ?></td>
                        <td class="n"><?= (int) $p['atual'] ?></td>
                        <td class="n"><?= (int) $p['anterior'] ?></td>
                    </tr><?php endforeach; ?></tbody>
            </table>
            <p class="ui-sub" style="margin:12px 0 0"><a
                    href="<?= e(url('admin-desempenho', ['periodo' => $periodo])) ?>">Ver quais vagas e empresas mais
                    chamam atenção</a></p>
        </div>
    </div>
</section>

<div class="ui-grid-2" style="margin-bottom:30px">
    <section class="ui-card">
        <div class="ui-row-between">
            <h2>O que aconteceu por aqui</h2><a class="ui-btn ui-btn-sm" href="<?= e(url('admin-atividade')) ?>">Ver
                mais</a>
        </div>
        <?php if (!$atividade): echo Ui::vazio('Nada por enquanto', 'As decisões da Coordenação e os envios das empresas aparecem aqui.');
        else: ?>
        <ul class="ui-feed">
            <?php foreach ($atividade as $h): [$quem, $verbo, $item] = Historico::frase($h);
                    $link = $h['entidade'] === 'vaga' ? url('admin-vaga', ['id' => $h['entidade_id']]) : ($h['entidade'] === 'empresa' ? url('admin-empresa', ['id' => $h['entidade_id']]) : null); ?>
            <li><strong><?= e($quem) ?></strong> <?= e($verbo) ?>
                <?= $link && $item ? '<a href="' . e($link) . '">' . e($item) . '</a>' : e($item) ?>
                <small><?= e(tempo_atras($h['criado_em'])) ?></small><?php if (in_array($h['acao'], ['rejeitada', 'reprovada', 'bloqueada'], true) && $h['detalhe']): ?><small
                    class="why">Motivo: <?= e($h['detalhe']) ?></small><?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul><?php endif; ?>
    </section>
    <section class="ui-card">
        <h2>Empresas e vagas em números</h2>
        <ul class="ui-ledger">
            <li><a href="<?= url('admin-empresas', ['aba' => 'todas']) ?>"><?= (int) ($emp['total'] ?? 0) ?>
                    empresas</a><span><?= (int) ($emp['aprovada'] ?? 0) ?> aprovadas · <a
                        href="<?= url('admin-empresas', ['aba' => 'pendentes']) ?>"><?= (int) ($emp['pendente'] ?? 0) ?>
                        aguardando</a> · <?= (int) ($emp['reprovada'] ?? 0) ?> reprovadas ·
                    <?= (int) ($emp['bloqueada'] ?? 0) ?> bloqueadas</span></li>
            <li><a href="<?= url('admin-vagas') ?>"><?= (int) ($vag['todas'] ?? 0) ?> vagas</a><span><a
                        href="<?= url('admin-vagas', ['aba' => 'publicada']) ?>"><?= (int) ($vag['publicada'] ?? 0) ?>
                        no ar</a> · <a
                        href="<?= url('admin-vagas', ['aba' => 'aguardando']) ?>"><?= (int) ($vag['aguardando'] ?? 0) ?>
                        em análise</a> · <?= (int) ($vag['agendada'] ?? 0) ?> agendadas</span></li>
            <li><span style="color:var(--text)">Já saíram do ar</span><span><?= (int) ($vag['expirada'] ?? 0) ?>
                    expiradas · <?= (int) ($vag['encerrada'] ?? 0) ?> encerradas · <?= (int) ($vag['rejeitada'] ?? 0) ?>
                    rejeitadas</span></li>
        </ul>
        <h2 style="margin:20px 0 8px;font-size:15px">Empresas que chegaram há pouco</h2>
        <?php if (!$empresasRecentes): echo '<p class="ui-sub">Nenhuma empresa cadastrada ainda.</p>';
        else: ?><ul class="ui-ledger"><?php foreach ($empresasRecentes as $r): ?>
            <li><a href="<?= url('admin-empresa', ['id' => $r['id']]) ?>"><?= e($r['nome']) ?></a><span><?= Ui::statusEmpresa($r['status']) ?>
                    <?= e(data_br(substr($r['criado_em'], 0, 10))) ?></span></li><?php endforeach; ?>
        </ul><?php endif; ?>
    </section>
</div>