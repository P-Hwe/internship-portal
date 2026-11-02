<?php
$me = $emp['usuario'] ?? null;
$approved = $emp['status'] === 'aprovada';
$live = (int) ($cont['publicada'] ?? 0);
$review = (int) ($cont['aguardando'] ?? 0);
$scheduled = (int) ($cont['agendada'] ?? 0);
$closed = (int) ($cont['expirada'] ?? 0) + (int) ($cont['encerrada'] ?? 0);
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
    ['No ar', $live, 'var(--chart-1)'],
    ['Agendadas', $scheduled, 'var(--chart-2)'],
    ['Em análise', $review, 'var(--chart-3)'],
    ['Encerradas', $closed, 'var(--chart-4)'],
    ['Rejeitadas', (int) ($cont['rejeitada'] ?? 0), 'var(--chart-5)'],
];
$topJob = $resumo ? ($resumo['vagas'][0] ?? null) : null;
if ($topJob && $topJob['views'] === 0) $topJob = null;
$tips = [
    ['star', 'Vagas com bolsa e benefícios claros recebem mais cliques.'],
    ['clock', 'Defina uma data final realista: a vaga sai do ar sozinha quando vence.'],
    ['flame', 'Um banner na vaga chama mais atenção no mural e no Modo TV.'],
    ['users', 'Descreva as atividades do dia a dia: o estudante quer saber o que vai fazer.'],
];
[$tipIcon, $tipText] = $tips[(int) date('z') % count($tips)];
if (!$approved) $heroText = $emp['status'] === 'pendente' ? 'Assim que a Coordenação aprovar o cadastro, você poderá publicar vagas.' : 'Corrija o cadastro e reenvie para análise para voltar a publicar vagas.';
elseif ($review) $heroText = $review . ' vaga(s) aguardando a análise da Coordenação.';
elseif ($live) $heroText = 'Tudo em dia. Suas vagas estão visíveis para os estudantes do IFSP Guarulhos.';
else $heroText = 'Publique a primeira vaga e ela aparece no mural depois da análise da Coordenação.';
?>
<?php if ($emp['status'] === 'pendente'): ?>
<div class="ui-alert ui-alert-warn"><?= icon('clock', 22) ?><div><strong>Cadastro em análise</strong>
        <p>A Coordenação está conferindo os dados e o termo de cooperação. Você receberá um e-mail com o resultado.</p>
    </div>
</div>
<?php elseif ($emp['status'] === 'reprovada'): ?>
<div class="ui-alert ui-alert-danger"><?= icon('info', 22) ?><div><strong>Cadastro não aprovado</strong>
        <p>Motivo: <?= nl2br(e((string) $emp['motivo'])) ?></p>
        <form method="post" action="<?= url('empresa-perfil') ?>" style="margin-top:8px"><?= csrf_field() ?><input
                type="hidden" name="acao" value="reenviar">
            <a class="ui-btn ui-btn-sm" href="<?= url('empresa-perfil') ?>">Corrigir cadastro</a> <button
                class="ui-btn ui-btn-sm ui-btn-primary" type="submit">Reenviar para análise</button>
        </form>
    </div>
</div>
<?php elseif ($aviso): ?>
<div class="ui-alert ui-alert-ok"><?= icon('check', 22) ?><div><strong>Empresa aprovada</strong>
        <p>Você já pode publicar vagas. Cada vaga passa por análise antes de ir ao ar.</p>
    </div>
    <div class="ui-actions">
        <form method="post" action="<?= url('aviso-dispensar') ?>"><?= csrf_field() ?><button class="ui-link-btn"
                type="submit">Dispensar</button></form>
    </div>
</div>
<?php endif; ?>
<?php if (!empty($cont['rejeitada'])): ?><div class="ui-alert ui-alert-danger" id="alerta-rejeitadas"><?= icon('info', 22) ?><div>
        <strong><?= (int) $cont['rejeitada'] ?> vaga(s) rejeitada(s)</strong>
        <p>Veja o motivo, corrija e reenvie para análise. <a
                href="<?= url('empresa-vagas', ['aba' => 'rejeitada']) ?>">Ver vagas rejeitadas</a></p>
    </div>
    <button type="button" class="ui-btn-icon" onclick="document.getElementById('alerta-rejeitadas').hidden=true;sessionStorage.setItem('hide-alerta-rejeitadas-<?= (int) $emp['id'] ?>','1')" aria-label="Fechar aviso"><?= icon('x', 16) ?></button>
</div>
<script>if(sessionStorage.getItem('hide-alerta-rejeitadas-<?= (int) $emp['id'] ?>')){var a=document.getElementById('alerta-rejeitadas');if(a)a.hidden=true;}</script>
<?php endif; ?>
<?php if ($vencendo): ?><div class="ui-alert ui-alert-warn"><?= icon('clock', 22) ?><div>
        <strong><?= count($vencendo) === 1 ? '1 vaga sai do ar em breve' : count($vencendo) . ' vagas saem do ar em breve' ?></strong>
        <p><?= implode(' · ', array_map(fn($v) => '<a href="' . e(url('empresa-vaga', ['id' => $v['id']])) . '">' . e($v['titulo']) . '</a> (' . e(dias_txt($v['dias'])) . ')', $vencendo)) ?></p>
        <p>Para estender o prazo, edite a data final. A vaga passa por nova aprovação.</p>
    </div>
</div><?php endif; ?>

<section class="dash-hero">
  <div class="dash-hero-text">
    <span class="dash-hero-kicker"><?= e($emp['nome']) ?><?= $me ? ' · ' . e(CompanyMember::roleLabel($me['papel'])) : '' ?></span>
    <h2><?= $live ? $live . ($live === 1 ? ' vaga no ar' : ' vagas no ar') . ' no mural do IFSP' : 'Nenhuma vaga no ar agora' ?></h2>
    <p><?= e($heroText) ?></p>
    <div class="ui-actions">
      <?php if ($approved): ?><a class="ui-btn ui-btn-primary" href="<?= url('vaga-cadastro') ?>"><?= icon('plus', 16) ?> Publicar vaga</a><?php endif; ?>
      <a class="ui-btn" href="<?= url('empresa-vagas') ?>"><?= icon('briefcase', 16) ?> Minhas vagas</a>
    </div>
  </div>
  <svg class="dash-hero-art" viewBox="0 0 220 150" aria-hidden="true" focusable="false">
    <?php foreach ([[0, 2], [1, 4], [2, 3], [3, 5], [4, 7], [5, 6]] as [$col, $height]): for ($row = 0; $row < $height; $row++): ?>
      <rect x="<?= 6 + $col * 36 ?>" y="<?= 132 - $row * 16 ?>" width="28" height="12" rx="3" class="<?= $row === $height - 1 ? 'is-top' : '' ?>"/>
    <?php endfor; endforeach; ?>
    <circle cx="200" cy="10" r="9" class="is-dot"/>
  </svg>
</section>

<div class="dash-kpis">
  <a class="dash-kpi" href="<?= url('empresa-vagas', ['aba' => 'publicada']) ?>">
    <span class="dash-kpi-icon tone-green"><?= icon('briefcase', 20) ?></span>
    <span class="dash-kpi-label">Vagas no ar</span>
    <strong><?= $live ?></strong>
    <small><?= $scheduled ?> agendada(s)</small>
  </a>
  <a class="dash-kpi" href="<?= url('empresa-vagas', ['aba' => 'aguardando']) ?>">
    <span class="dash-kpi-icon tone-amber"><?= icon('clock', 20) ?></span>
    <span class="dash-kpi-label">Em análise</span>
    <strong><?= $review ?></strong>
    <small>A Coordenação revisa cada vaga</small>
  </a>
  <?php if ($resumo): $now = $resumo['atual']; $before = $resumo['anterior']; ?>
  <a class="dash-kpi" href="<?= url('empresa-desempenho') ?>">
    <span class="dash-kpi-icon tone-blue"><?= icon('eye', 20) ?></span>
    <span class="dash-kpi-label">Visualizações na semana</span>
    <strong><?= (int) $now['views'] ?> <?= $trend((int) $now['views'], (int) $before['views']) ?></strong>
    <?= $spark(array_column($resumo['linhas'], 'atual')) ?>
  </a>
  <a class="dash-kpi" href="<?= url('empresa-desempenho') ?>">
    <span class="dash-kpi-icon tone-violet"><?= icon('external', 20) ?></span>
    <span class="dash-kpi-label">Cliques para o seu site</span>
    <strong><?= (int) $now['saidas'] ?> <?= $trend((int) $now['saidas'], (int) $before['saidas']) ?></strong>
    <small><?= (int) $now['uniq'] ?> pessoas visitaram</small>
  </a>
  <?php else: ?>
  <a class="dash-kpi" href="<?= url('empresa-vagas', ['aba' => 'encerrada']) ?>">
    <span class="dash-kpi-icon tone-violet"><?= icon('check', 20) ?></span>
    <span class="dash-kpi-label">Encerradas</span>
    <strong><?= $closed ?></strong>
    <small>Vencidas ou encerradas</small>
  </a>
  <a class="dash-kpi" href="<?= url('empresa-vagas') ?>">
    <span class="dash-kpi-icon tone-blue"><?= icon('grid', 20) ?></span>
    <span class="dash-kpi-label">Total de vagas</span>
    <strong><?= array_sum(array_column($jobStates, 1)) ?></strong>
    <small>Desde o cadastro</small>
  </a>
  <?php endif; ?>
</div>

<div class="dash-grid">
  <section class="ui-card dash-span-2">
    <div class="ui-chart-head"><h2>Vagas recentes</h2><a class="ui-link-sm" href="<?= url('empresa-vagas') ?>">Ver todas</a></div>
    <?php if (!$recentes): echo Ui::vazio('Nenhuma vaga ainda', $approved ? 'Publique a primeira vaga da sua empresa.' : 'Você poderá publicar vagas depois que a Coordenação aprovar o cadastro.', $approved ? ['Publicar vaga', url('vaga-cadastro')] : null);
    else: ?><div class="ui-grid" style="grid-template-columns:repeat(auto-fill,minmax(240px,1fr))">
      <?php foreach ($recentes as $v) { $ctx = 'empresa'; require __DIR__ . '/../partials/job-card-panel.php'; } ?>
    </div><?php endif; ?>
  </section>

  <div style="display:grid;gap:20px;align-content:start">
    <section class="ui-card">
      <h2>Suas vagas por situação</h2>
      <?= Ui::donut($jobStates, 'vagas') ?>
    </section>
    <?php if ($topJob): ?>
    <section class="ui-card">
      <h2>Mais vista da semana</h2>
      <p style="margin:0 0 6px"><a href="<?= url('empresa-vaga', ['id' => $topJob['id']]) ?>"><strong><?= e($topJob['titulo']) ?></strong></a></p>
      <p class="ui-sub" style="margin:0"><?= icon('eye', 14) ?> <?= (int) $topJob['views'] ?> visualizações · <?= (int) $topJob['uniq'] ?> pessoas</p>
      <a class="ui-btn ui-btn-sm" style="margin-top:12px" href="<?= url('empresa-desempenho') ?>"><?= icon('trending', 15) ?> Ver desempenho</a>
    </section>
    <?php endif; ?>
    <section class="ui-card">
      <h2><?= icon($tipIcon, 18) ?> Dica do dia</h2>
      <p style="margin:0"><?= e($tipText) ?></p>
    </section>
  </div>
</div>
