<?php [$rotPeriodo, $txtAtual, $txtAnt] = Visitas::PERIODOS[$periodo];
$dif = function (int $a, int $b) {
    $d = $a - $b;
    return $d === 0 ? 'igual ao anterior' : ($d > 0 ? '+' : '') . $d . ' vs. período anterior';
}; ?>
<div class="ui-row-between">
    <p class="ui-sub">Veja o que os estudantes mais procuram nos <?= e($txtAtual) ?> e
        use isso para escolher quais vagas colocar em destaque e quais empresas procurar.</p>
    <nav class="ui-period-tabs" aria-label="Período"><?php foreach (Visitas::PERIODOS as $k => $d): ?><a
            href="<?= e(url('admin-desempenho', ['periodo' => $k])) ?>"
            class="<?= $k === $periodo ? 'is-active' : '' ?>"><?= e($d[0]) ?></a><?php endforeach; ?></nav>
</div>
<?php if (Visitas::ignorarLogados()): ?><p class="ui-hint">Visitas de administradores e empresas logados não entram na
    contagem: para testar, abra o site em uma janela anônima (sem login).</p><?php endif; ?>
<div class="ui-grid-2">
    <section class="ui-card">
        <h2>Vagas mais vistas</h2>
        <?php if (!$vagasTop): echo Ui::vazio('Ainda sem visualizações de vagas', 'Quando estudantes abrirem vagas, elas aparecem aqui.');
        else: ?>
        <ol class="ui-rank"><?php foreach ($vagasTop as $v): ?>
            <li>
                <div class="t"><a
                        href="<?= url('admin-vaga', ['id' => $v['id']]) ?>"><?= e($v['titulo']) ?></a><small><?= e($v['empresa']) ?>
                        ·
                        <?= e(Estado::rotuloSituacao($v['situacao'])) ?><?= (int) $v['destaque'] ? ' · em destaque' : ($v['situacao'] === 'publicada' ? ' · boa candidata a destaque' : '') ?></small>
                </div>
                <div class="v"><?= (int) $v['views'] ?> <small><?= (int) $v['uniq'] ?> pessoas ·
                        <?= e($dif($v['views'], $v['anterior'])) ?></small></div>
            </li><?php endforeach; ?>
        </ol><?php endif; ?>
    </section>
    <section class="ui-card">
        <h2>Empresas com mais acessos</h2>
        <?php if (!$empresasTop): echo Ui::vazio('Ainda sem visualizações', 'Contamos a página da empresa e as vagas dela.');
        else: ?>
        <ol class="ui-rank"><?php foreach ($empresasTop as $e2): ?>
            <li>
                <div class="t"><a
                        href="<?= url('admin-empresa', ['id' => $e2['id']]) ?>"><?= e($e2['nome']) ?></a><small><?= (int) $e2['pagina'] ?>
                        na página · <?= (int) $e2['vagas'] ?> nas vagas · <?= (int) $e2['saidas'] ?> cliques para o site
                        ·
                        <?= $e2['ultimo_acesso'] ? 'recrutador entrou ' . e(tempo_atras($e2['ultimo_acesso'])) : 'recrutador nunca entrou' ?></small>
                </div>
                <div class="v"><?= (int) $e2['views'] ?> <small><?= (int) $e2['uniq'] ?> pessoas ·
                        <?= e($dif($e2['views'], $e2['anterior'])) ?></small></div>
            </li><?php endforeach; ?>
        </ol><?php endif; ?>
    </section>
</div>
<div class="ui-card">
    <h2>Visitantes diferentes por <?= $resumo['janela']['bucket'] === 'mes' ? 'mês' : 'dia' ?></h2>
    <?= Ui::comparar($resumo['linhasU'], 'Visitantes diferentes ' . $txtAtual . ' e ' . $txtAnt) ?>
</div>