<?php
[$periodLabel, $currentText, $previousText] = Visits::PERIODS[$periodo];
$current = $resumo['atual'];
$previous = $resumo['anterior'];
$delta = function (int $now, int $before): string {
    [$trend, $pct] = Visits::variation($now, $before);
    if ($trend === 'novo') return '<span class="ui-delta is-up">novo no período</span>';
    if ($trend === 'igual') return '<span class="ui-delta">igual ao anterior</span>';
    return '<span class="ui-delta ' . ($trend === 'sobe' ? 'is-up' : 'is-down') . '">' . ($pct > 0 ? '+' : '') . $pct . '% vs. anterior</span>';
};
$kpis = [
    ['Visualizações de página', $current['visualizacoes'], $previous['visualizacoes']],
    ['Visitantes diferentes', $current['visitantes'], $previous['visitantes']],
    ['Cliques para empresas', $current['saidas'], $previous['saidas']],
    ['Logins no painel', $current['logins'], $previous['logins']],
];
usort($empresasTop, fn($a, $b) => ((int) $b['pagina'] + (int) $b['vagas'] + (int) $b['saidas']) <=> ((int) $a['pagina'] + (int) $a['vagas'] + (int) $a['saidas']));
$periodKey = mb_strtoupper(mb_substr($currentText, 0, 1)) . mb_substr($currentText, 1);
$jobMax = max(1, ...array_values(array_map(fn($j) => max((int) $j['views'], (int) $j['anterior']), $vagasTop ?: [['views' => 1, 'anterior' => 0]])));
$companyMax = max(1, ...array_values(array_map(fn($c) => (int) $c['pagina'] + (int) $c['vagas'] + (int) $c['saidas'], $empresasTop ?: [['pagina' => 1, 'vagas' => 0, 'saidas' => 0]])));
$pageMax = max(1, ...array_values(array_map(fn($p) => max((int) $p['atual'], (int) $p['anterior']), $resumo['paginas'] ?: [['atual' => 1, 'anterior' => 0]])));
$pct = fn(int $value, int $max): string => number_format(min(100, $value / $max * 100), 2, '.', '');
?>
<div class="ui-row-between">
  <p class="ui-sub ui-sub-flush">O que os estudantes mais procuraram nos <?= e($currentText) ?>, comparado com os <?= e($previousText) ?>.</p>
  <nav class="ui-period-tabs" aria-label="Período"><?php foreach (Visits::PERIODS as $key => $period): ?><a href="<?= e(url('admin-desempenho', ['periodo' => $key])) ?>" class="<?= $key === $periodo ? 'is-active' : '' ?>"<?= $key === $periodo ? ' aria-current="true"' : '' ?>><?= e($period[0]) ?></a><?php endforeach; ?></nav>
</div>
<?php if (Visits::ignoreLoggedIn()): ?><p class="ui-hint">Visitas de administradores e empresas logados não entram na contagem. Para testar, abra o site em uma janela anônima.</p><?php endif; ?>

<div class="ui-metrics">
  <?php foreach ($kpis as [$label, $now, $before]): ?>
    <div class="ui-metric"><span class="ui-metric-l"><?= e($label) ?></span><span class="ui-metric-n"><?= (int) $now ?></span><?= $delta((int) $now, (int) $before) ?></div>
  <?php endforeach; ?>
</div>

<div class="ui-grid-2 ui-stack">
  <section class="ui-card">
    <div class="ui-chart-head">
      <h2>Vagas mais vistas</h2>
      <ul class="ui-key" aria-label="Legenda"><li><i class="ui-key-bar"></i> <?= e($periodKey) ?></li><li><i class="ui-key-tick"></i> Período anterior</li></ul>
    </div>
    <?php if (!$vagasTop): echo Ui::vazio('Ainda sem visualizações de vagas', 'Quando estudantes abrirem vagas, elas aparecem aqui.'); else: ?>
    <ol class="ui-hbars">
      <?php foreach ($vagasTop as $job): ?>
      <li class="ui-hbar" tabindex="0" data-tip="<?= e($job['titulo'] . ': ' . (int) $job['views'] . ' visualizações, ' . (int) $job['uniq'] . ' pessoas, ' . (int) $job['anterior'] . ' no período anterior') ?>">
        <div class="ui-hbar-label">
          <a href="<?= url('admin-vaga', ['id' => $job['id']]) ?>"><?= e($job['titulo']) ?></a>
          <small><?= e($job['empresa']) ?><?= (int) $job['destaque'] ? ' · em destaque' : ($job['situacao'] === 'publicada' ? ' · boa candidata a destaque' : ' · ' . e(Status::rotuloSituacao($job['situacao']))) ?></small>
        </div>
        <div class="ui-hbar-track">
          <span class="ui-hbar-fill" style="width:<?= $pct((int) $job['views'], $jobMax) ?>%"></span>
          <?php if ((int) $job['anterior'] > 0): ?><span class="ui-hbar-tick" style="left:<?= $pct((int) $job['anterior'], $jobMax) ?>%"></span><?php endif; ?>
        </div>
        <span class="ui-hbar-value"><?= (int) $job['views'] ?></span>
      </li>
      <?php endforeach; ?>
    </ol>
    <?php endif; ?>
  </section>

  <section class="ui-card">
    <div class="ui-chart-head">
      <h2>Empresas com mais acessos</h2>
      <ul class="ui-key" aria-label="Legenda"><li><i class="ui-key-s1"></i> Página da empresa</li><li><i class="ui-key-s2"></i> Vagas</li><li><i class="ui-key-s3"></i> Cliques para o site</li></ul>
    </div>
    <?php if (!$empresasTop): echo Ui::vazio('Ainda sem visualizações', 'Contamos a página da empresa, as vagas dela e os cliques para o site.'); else: ?>
    <ol class="ui-hbars">
      <?php foreach ($empresasTop as $company): $parts = [(int) $company['pagina'], (int) $company['vagas'], (int) $company['saidas']]; ?>
      <li class="ui-hbar" tabindex="0" data-tip="<?= e($company['nome'] . ': ' . $parts[0] . ' na página, ' . $parts[1] . ' nas vagas, ' . $parts[2] . ' cliques para o site') ?>">
        <div class="ui-hbar-label">
          <a href="<?= url('admin-empresa', ['id' => $company['id']]) ?>"><?= e($company['nome']) ?></a>
          <small><?= $company['ultimo_acesso'] ? 'Recrutador entrou ' . e(tempo_atras($company['ultimo_acesso'])) : 'Recrutador nunca entrou' ?></small>
        </div>
        <div class="ui-hbar-track ui-hbar-stack">
          <?php foreach ($parts as $i => $value): if ($value > 0): ?><span class="ui-hbar-seg ui-hbar-s<?= $i + 1 ?>" style="width:<?= $pct($value, $companyMax) ?>%"></span><?php endif; endforeach; ?>
        </div>
        <span class="ui-hbar-value"><?= array_sum($parts) ?></span>
      </li>
      <?php endforeach; ?>
    </ol>
    <?php endif; ?>
  </section>
</div>

<div class="ui-grid-2 ui-stack">
  <section class="ui-card">
    <h2>Visitantes diferentes por <?= $resumo['janela']['bucket'] === 'mes' ? 'mês' : 'dia' ?></h2>
    <?= Ui::comparar($resumo['linhasU'], 'Visitantes diferentes ' . $currentText . ' e ' . $previousText) ?>
  </section>
  <section class="ui-card">
    <div class="ui-chart-head">
      <h2>Acessos por página do site</h2>
      <ul class="ui-key" aria-label="Legenda"><li><i class="ui-key-bar"></i> Atual</li><li><i class="ui-key-tick"></i> Anterior</li></ul>
    </div>
    <ol class="ui-hbars ui-hbars-compact">
      <?php foreach ($resumo['paginas'] as $page): ?>
      <li class="ui-hbar" tabindex="0" data-tip="<?= e($page['rot'] . ': ' . (int) $page['atual'] . ' agora, ' . (int) $page['anterior'] . ' antes') ?>">
        <div class="ui-hbar-label"><span><?= e($page['rot']) ?></span></div>
        <div class="ui-hbar-track">
          <span class="ui-hbar-fill" style="width:<?= $pct((int) $page['atual'], $pageMax) ?>%"></span>
          <?php if ((int) $page['anterior'] > 0): ?><span class="ui-hbar-tick" style="left:<?= $pct((int) $page['anterior'], $pageMax) ?>%"></span><?php endif; ?>
        </div>
        <span class="ui-hbar-value"><?= (int) $page['atual'] ?></span>
      </li>
      <?php endforeach; ?>
    </ol>
  </section>
</div>
