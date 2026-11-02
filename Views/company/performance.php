<?php
[$periodLabel, $currentText, $previousText] = Visits::PERIODS[$periodo];
$current = $resumo['atual'];
$previous = $resumo['anterior'];
$overall = $resumo['geral'];
$trend = function (int $now, int $before): string {
    [$dir, $pct] = Visits::variation($now, $before);
    if ($dir === 'novo') return '<span class="ui-delta is-up">novo</span>';
    if ($dir === 'igual') return '<span class="ui-delta">estável</span>';
    return '<span class="ui-delta ' . ($dir === 'sobe' ? 'is-up' : 'is-down') . '">' . ($pct > 0 ? '+' : '') . $pct . '%</span>';
};
$spark = function (array $values): string {
    $values = array_values(array_map('intval', $values));
    if (count($values) < 2) return '';
    $max = max(1, ...$values);
    $step = 100 / (count($values) - 1);
    $points = [];
    foreach ($values as $i => $value) $points[] = round($i * $step, 2) . ',' . round(30 - $value / $max * 26, 2);
    return '<svg class="dash-spark" viewBox="0 0 100 32" preserveAspectRatio="none" aria-hidden="true"><polyline points="' . implode(' ', $points) . '"/></svg>';
};
$sources = [
    ['Página da empresa', $current['pagina'], 'var(--chart-1)'],
    ['Vagas', $current['vagas'], 'var(--chart-2)'],
    ['Cliques para o seu site', $current['saidas'], 'var(--chart-3)'],
];
$sourceTotal = max(1, array_sum(array_column($sources, 1)));
$clickRate = $current['uniq'] ? (int) round($current['saidas'] / $current['uniq'] * 100) : 0;
$jobs = array_slice($resumo['vagas'], 0, 10);
$jobMax = max(1, ...array_map(fn($j) => max($j['views'], $j['anterior']), $jobs ?: [['views' => 1, 'anterior' => 0]]));
$pct = fn(int $value, int $max): string => number_format(min(100, $value / $max * 100), 2, '.', '');
$periodKey = mb_strtoupper(mb_substr($currentText, 0, 1)) . mb_substr($currentText, 1);
?>
<div class="ui-row-between">
  <p class="ui-sub ui-sub-flush">Quantos estudantes viram a <?= e($emp['nome']) ?> e as suas vagas nos <?= e($currentText) ?>, comparado com os <?= e($previousText) ?>.</p>
  <nav class="ui-period-tabs" aria-label="Período"><?php foreach (Visits::PERIODS as $key => $period): ?><a href="<?= e(url('empresa-desempenho', ['periodo' => $key])) ?>" class="<?= $key === $periodo ? 'is-active' : '' ?>"<?= $key === $periodo ? ' aria-current="true"' : '' ?>><?= e($period[0]) ?></a><?php endforeach; ?></nav>
</div>

<div class="dash-kpis">
  <div class="dash-kpi">
    <span class="dash-kpi-icon tone-blue"><?= icon('eye', 20) ?></span>
    <span class="dash-kpi-label">Visualizações</span>
    <strong><?= (int) $current['views'] ?> <?= $trend((int) $current['views'], (int) $previous['views']) ?></strong>
    <?= $spark(array_column($resumo['linhas'], 'atual')) ?>
  </div>
  <div class="dash-kpi">
    <span class="dash-kpi-icon tone-violet"><?= icon('users', 20) ?></span>
    <span class="dash-kpi-label">Pessoas diferentes</span>
    <strong><?= (int) $current['uniq'] ?> <?= $trend((int) $current['uniq'], (int) $previous['uniq']) ?></strong>
    <?= $spark(array_column($resumo['linhasU'], 'atual')) ?>
  </div>
  <div class="dash-kpi">
    <span class="dash-kpi-icon tone-green"><?= icon('building', 20) ?></span>
    <span class="dash-kpi-label">Abriram a página da empresa</span>
    <strong><?= (int) $current['pagina'] ?> <?= $trend((int) $current['pagina'], (int) $previous['pagina']) ?></strong>
    <small><?= (int) $current['vagas'] ?> visualizações nas vagas</small>
  </div>
  <div class="dash-kpi">
    <span class="dash-kpi-icon tone-amber"><?= icon('external', 20) ?></span>
    <span class="dash-kpi-label">Cliques para o seu site</span>
    <strong><?= (int) $current['saidas'] ?> <?= $trend((int) $current['saidas'], (int) $previous['saidas']) ?></strong>
    <small><?= $clickRate ?>% de quem visitou</small>
  </div>
</div>

<div class="dash-grid">
  <section class="ui-card dash-span-2">
    <div class="ui-chart-head"><h2>Visualizações por <?= $resumo['janela']['bucket'] === 'mes' ? 'mês' : 'dia' ?></h2></div>
    <div class="dash-mini-stats">
      <div><span>Posição no portal (30 dias)</span><strong><?= $overall['posicao'] ? (int) $overall['posicao'] . 'º' : '-' ?></strong><span class="ui-delta">de <?= (int) $overall['de'] ?> empresas</span></div>
      <div><span>Últimos 30 dias</span><strong><?= (int) $overall['d30']['views'] ?></strong><span class="ui-delta"><?= (int) $overall['d30']['unicos'] ?> pessoas</span></div>
      <div><span>Desde o cadastro</span><strong><?= (int) $overall['tudo']['views'] ?></strong><span class="ui-delta"><?= $overall['tudo']['ultima'] ? 'última ' . e(tempo_atras($overall['tudo']['ultima'])) : 'sem visitas' ?></span></div>
    </div>
    <?= Ui::comparar($resumo['linhas'], 'Visualizações ' . $currentText . ' e ' . $previousText) ?>
  </section>

  <section class="ui-card">
    <h2>Onde os estudantes olharam</h2>
    <div class="dash-stackbar" role="img" aria-label="<?= e(implode(', ', array_map(fn($s) => $s[0] . ' ' . $s[1], $sources))) ?>">
      <?php foreach ($sources as [$label, $value, $color]): if ($value > 0): ?><span style="width:<?= round($value / $sourceTotal * 100, 2) ?>%;background:<?= $color ?>" title="<?= e($label . ': ' . $value) ?>"></span><?php endif; endforeach; ?>
    </div>
    <ul class="ui-key dash-key"><?php foreach ($sources as [$label, $value, $color]): ?><li><i style="background:<?= $color ?>"></i> <?= e($label) ?> <strong><?= (int) $value ?></strong></li><?php endforeach; ?></ul>
    <h3 class="dash-subhead">Caminho do estudante</h3>
    <ol class="ui-hbars ui-hbars-compact">
      <?php foreach ($sources as $i => [$label, $value, $color]): ?>
      <li class="ui-hbar" tabindex="0" data-tip="<?= e($label . ': ' . $value) ?>">
        <div class="ui-hbar-label"><span><?= ['Página', 'Vagas', 'Cliques'][$i] ?></span></div>
        <div class="ui-hbar-track"><span class="ui-hbar-fill" style="width:<?= $pct((int) $value, max(1, ...array_column($sources, 1))) ?>%;background:<?= $color ?>"></span></div>
        <span class="ui-hbar-value"><?= (int) $value ?></span>
      </li>
      <?php endforeach; ?>
    </ol>
  </section>

  <section class="ui-card dash-span-2">
    <div class="ui-chart-head">
      <h2>Suas vagas mais vistas</h2>
      <ul class="ui-key" aria-label="Legenda"><li><i class="ui-key-bar"></i> <?= e($periodKey) ?></li><li><i class="ui-key-tick"></i> Período anterior</li></ul>
    </div>
    <?php if (!$jobs): echo Ui::vazio('Nenhuma vaga ainda', 'Publique uma vaga para acompanhar quantas pessoas a abriram.'); else: ?>
    <ol class="ui-hbars">
      <?php foreach ($jobs as $job): ?>
      <li class="ui-hbar" tabindex="0" data-tip="<?= e($job['titulo'] . ': ' . $job['views'] . ' visualizações, ' . $job['uniq'] . ' pessoas, ' . $job['anterior'] . ' no período anterior') ?>">
        <div class="ui-hbar-label">
          <a href="<?= url('empresa-vaga', ['id' => $job['id']]) ?>"><?= e($job['titulo']) ?></a>
          <small><?= e(Status::rotuloSituacao($job['situacao'])) ?><?= (int) $job['destaque'] ? ' · em destaque' : '' ?> · <?= $job['uniq'] ?> pessoas</small>
        </div>
        <div class="ui-hbar-track">
          <span class="ui-hbar-fill" style="width:<?= $pct($job['views'], $jobMax) ?>%"></span>
          <?php if ($job['anterior'] > 0): ?><span class="ui-hbar-tick" style="left:<?= $pct($job['anterior'], $jobMax) ?>%"></span><?php endif; ?>
        </div>
        <span class="ui-hbar-value"><?= $job['views'] ?></span>
      </li>
      <?php endforeach; ?>
    </ol>
    <?php endif; ?>
  </section>

  <section class="ui-card">
    <h2>Como ler estes números</h2>
    <ul class="ui-list">
      <li><strong>Visualizações</strong> somam as aberturas da página da empresa e das suas vagas.</li>
      <li><strong>Pessoas diferentes</strong> conta cada navegador uma vez no período.</li>
      <li><strong>Cliques para o seu site</strong> são estudantes que saíram do portal para o seu site ou LinkedIn.</li>
      <li>Acessos de quem está logado na área da empresa ou da Coordenação não entram na conta.</li>
    </ul>
  </section>
</div>
