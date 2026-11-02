<?php
[$periodLabel, $currentText, $previousText] = Visits::PERIODS[$periodo];
$current = $resumo['atual'];
$previous = $resumo['anterior'];
$lead = null;
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
$jobStates = [
    ['Publicadas', (int) ($vag['publicada'] ?? 0), 'var(--chart-1)'],
    ['Agendadas', (int) ($vag['agendada'] ?? 0), 'var(--chart-2)'],
    ['Em análise', (int) ($vag['aguardando'] ?? 0), 'var(--chart-3)'],
    ['Encerradas', (int) ($vag['expirada'] ?? 0) + (int) ($vag['encerrada'] ?? 0), 'var(--chart-4)'],
    ['Rejeitadas', (int) ($vag['rejeitada'] ?? 0), 'var(--chart-5)'],
];
$companyStates = [
    ['Aprovadas', (int) ($emp['aprovada'] ?? 0), 'var(--chart-1)'],
    ['Aguardando', (int) ($emp['pendente'] ?? 0), 'var(--chart-3)'],
    ['Reprovadas', (int) ($emp['reprovada'] ?? 0), 'var(--chart-4)'],
    ['Bloqueadas', (int) ($emp['bloqueada'] ?? 0), 'var(--chart-5)'],
];
$companyTotal = max(1, array_sum(array_column($companyStates, 1)));
$activityIcon = fn(string $action): array => match (true) {
    in_array($action, ['aprovada', 'desbloqueada'], true) => ['check-s', 'ok'],
    in_array($action, ['rejeitada', 'reprovada', 'bloqueada', 'encerrada'], true) => ['x', 'danger'],
    in_array($action, ['cadastro', 'enviada', 'reenviada'], true) => ['plus', 'info'],
    default => ['edit', 'mute'],
};
$visitsSeries = array_column($resumo['linhas'], 'atual');
?>
<section class="dash-hero">
  <div class="dash-hero-text">
    <span class="dash-hero-kicker">Mural de vagas · IFSP Guarulhos</span>
    <h2><?= (int) ($vag['publicada'] ?? 0) ?> vagas no ar de <?= (int) ($emp['aprovada'] ?? 0) ?> empresas parceiras</h2>
    <?php if ($pend): ?>
      <p><?= (int) ($emp['pendente'] ?? 0) ?> empresa(s) e <?= (int) ($vag['aguardando'] ?? 0) ?> vaga(s) aguardam a sua análise.</p>
      <a class="ui-btn ui-btn-primary" href="<?= url('admin-aprovacoes') ?>"><?= icon('check', 16) ?> Revisar aprovações</a>
    <?php else: ?>
      <p>Nenhuma empresa ou vaga aguardando análise.</p>
      <a class="ui-btn" href="<?= url('admin-vagas') ?>"><?= icon('briefcase', 16) ?> Ver vagas</a>
    <?php endif; ?>
  </div>
  <svg class="dash-hero-art" viewBox="0 0 220 150" aria-hidden="true" focusable="false">
    <?php foreach ([[0, 3], [1, 5], [2, 4], [3, 7], [4, 6], [5, 8]] as [$col, $height]): for ($row = 0; $row < $height; $row++): ?>
      <rect x="<?= 6 + $col * 36 ?>" y="<?= 132 - $row * 16 ?>" width="28" height="12" rx="3" class="<?= $row === $height - 1 ? 'is-top' : '' ?>"/>
    <?php endfor; endforeach; ?>
    <circle cx="200" cy="10" r="9" class="is-dot"/>
  </svg>
</section>

<div class="dash-kpis">
  <a class="dash-kpi" href="<?= url('admin-vagas', ['aba' => 'publicada']) ?>">
    <span class="dash-kpi-icon tone-green"><?= icon('briefcase', 20) ?></span>
    <span class="dash-kpi-label">Vagas no ar</span>
    <strong><?= (int) ($vag['publicada'] ?? 0) ?></strong>
    <small><?= (int) ($vag['destaque'] ?? 0) ?> em destaque</small>
  </a>
  <a class="dash-kpi" href="<?= url('admin-empresas', ['aba' => 'todas']) ?>">
    <span class="dash-kpi-icon tone-blue"><?= icon('building', 20) ?></span>
    <span class="dash-kpi-label">Empresas parceiras</span>
    <strong><?= (int) ($emp['aprovada'] ?? 0) ?></strong>
    <small><?= (int) ($emp['total'] ?? 0) ?> cadastradas</small>
  </a>
  <a class="dash-kpi" href="<?= url('admin-aprovacoes') ?>">
    <span class="dash-kpi-icon tone-amber"><?= icon('inbox', 20) ?></span>
    <span class="dash-kpi-label">Aguardando análise</span>
    <strong><?= (int) $pend ?></strong>
    <small><?= (int) ($emp['pendente'] ?? 0) ?> empresas · <?= (int) ($vag['aguardando'] ?? 0) ?> vagas</small>
  </a>
  <a class="dash-kpi" href="<?= url('admin-desempenho', ['periodo' => $periodo]) ?>">
    <span class="dash-kpi-icon tone-violet"><?= icon('users', 20) ?></span>
    <span class="dash-kpi-label">Visitantes</span>
    <strong><?= (int) $current['visitantes'] ?> <?= $trend((int) $current['visitantes'], (int) $previous['visitantes']) ?></strong>
    <?= $spark($visitsSeries) ?>
  </a>
</div>

<div class="dash-grid">
  <section class="ui-card dash-span-2">
    <div class="ui-chart-head">
      <h2>Visualizações do site</h2>
      <nav class="ui-period-tabs" aria-label="Período"><?php foreach (Visits::PERIODS as $key => $period): ?><a href="<?= e(url('admin', ['periodo' => $key])) ?>" class="<?= $key === $periodo ? 'is-active' : '' ?>"<?= $key === $periodo ? ' aria-current="true"' : '' ?>><?= e($period[0]) ?></a><?php endforeach; ?></nav>
    </div>
    <div class="dash-mini-stats">
      <div><span>Visualizações</span><strong><?= (int) $current['visualizacoes'] ?></strong><?= $trend((int) $current['visualizacoes'], (int) $previous['visualizacoes']) ?></div>
      <div><span>Cliques para empresas</span><strong><?= (int) $current['saidas'] ?></strong><?= $trend((int) $current['saidas'], (int) $previous['saidas']) ?></div>
      <div><span>Logins no painel</span><strong><?= (int) $current['logins'] ?></strong><?= $trend((int) $current['logins'], (int) $previous['logins']) ?></div>
    </div>
    <?= Ui::comparar($resumo['linhas'], 'Visualizações ' . $currentText . ' e ' . $previousText) ?>
  </section>

  <section class="ui-card">
    <h2>Vagas por situação</h2>
    <?= Ui::donut($jobStates, 'vagas') ?>
  </section>

  <section class="ui-card">
    <div class="ui-chart-head"><h2>Empresas</h2><a class="ui-link-sm" href="<?= url('admin-empresas', ['aba' => 'todas']) ?>">Ver todas</a></div>
    <div class="dash-stackbar" role="img" aria-label="<?= e(implode(', ', array_map(fn($s) => $s[0] . ' ' . $s[1], $companyStates))) ?>">
      <?php foreach ($companyStates as [$label, $value, $color]): if ($value > 0): ?><span style="width:<?= round($value / $companyTotal * 100, 2) ?>%;background:<?= $color ?>" title="<?= e($label . ': ' . $value) ?>"></span><?php endif; endforeach; ?>
    </div>
    <ul class="ui-key dash-key"><?php foreach ($companyStates as [$label, $value, $color]): ?><li><i style="background:<?= $color ?>"></i> <?= e($label) ?> <strong><?= $value ?></strong></li><?php endforeach; ?></ul>
    <h3 class="dash-subhead">Chegaram há pouco</h3>
    <?php if (!$empresasRecentes): ?><p class="ui-sub">Nenhuma empresa cadastrada ainda.</p><?php else: ?>
    <ul class="dash-people">
      <?php foreach ($empresasRecentes as $company): ?>
      <li><span class="app-avatar"><?= e(iniciais($company['nome'])) ?></span><a href="<?= url('admin-empresa', ['id' => $company['id']]) ?>"><?= e($company['nome']) ?></a><?= Ui::statusEmpresa($company['status']) ?></li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </section>

  <section class="ui-card dash-span-2">
    <div class="ui-chart-head"><h2>Atividade recente</h2><a class="ui-link-sm" href="<?= e(url('admin-atividade')) ?>">Ver histórico</a></div>
    <?php if (!$atividade): echo Ui::vazio('Nenhuma atividade ainda', 'Decisões da Coordenação e envios das empresas aparecem aqui.'); else: ?>
    <ol class="dash-feed">
      <?php foreach (array_slice($atividade, 0, 6) as $entry): [$who, $verb, $item] = AuditLog::frase($entry); [$feedIcon, $tone] = $activityIcon((string) $entry['acao']);
        $link = $entry['entidade'] === 'vaga' ? url('admin-vaga', ['id' => $entry['entidade_id']]) : ($entry['entidade'] === 'empresa' ? url('admin-empresa', ['id' => $entry['entidade_id']]) : null); ?>
      <li>
        <span class="dash-feed-icon tone-<?= $tone ?>"><?= icon($feedIcon, 14) ?></span>
        <p><strong><?= e($who) ?></strong> <?= e($verb) ?> <?= $link && $item ? '<a href="' . e($link) . '">' . e($item) . '</a>' : e($item) ?></p>
        <time datetime="<?= e($entry['criado_em']) ?>"><?= e(tempo_atras($entry['criado_em'])) ?></time>
      </li>
      <?php endforeach; ?>
    </ol>
    <?php endif; ?>
  </section>
</div>
