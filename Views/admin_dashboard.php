<?php
/**
 * @var array $emp
 * @var array $vag
 * @var array $semanas
 * @var array $atividade
 * @var array $empresasRecentes
 * @var array $logins
 */
?>
<?php
$pend = (int) ($emp['pendente'] ?? 0) + (int) ($vag['aguardando'] ?? 0);
$evento = ['login_ok' => 'Logins', 'login_falha' => 'Falhas de login', 'negado' => 'Acessos negados', 'bloqueado' => 'Bloqueios'];
?>
<?php if ($pend): ?><div class="ui-alert ui-alert-warn"><?= icon('bell', 22) ?><div><strong>Aguardando sua
        análise</strong>
      <p><?= (int) $emp['pendente'] ?> empresa(s) e <?= (int) $vag['aguardando'] ?> vaga(s). <a
          href="<?= url('admin-aprovacoes') ?>">Abrir aprovações</a></p>
    </div>
  </div><?php endif; ?>
<div class="ui-section">
  <h2>Empresas</h2>
  <div class="ui-metrics">
    <?= Ui::metrica('Total de empresas', $emp['total'] ?? 0, 'building', url('admin-empresas', ['aba' => 'todas'])) ?>
    <?= Ui::metrica('Aprovadas', $emp['aprovada'] ?? 0, 'check', url('admin-empresas', ['aba' => 'aprovadas'])) ?>
    <?= Ui::metrica('Pendentes', $emp['pendente'] ?? 0, 'clock', url('admin-empresas', ['aba' => 'pendentes']), 'warn') ?>
    <?= Ui::metrica('Bloqueadas', $emp['bloqueada'] ?? 0, 'lock', url('admin-empresas', ['aba' => 'bloqueadas']), 'danger') ?>
  </div>
</div>
<div class="ui-section">
  <h2>Vagas</h2>
  <div class="ui-metrics">
    <?= Ui::metrica('Total de vagas', $vag['todas'] ?? 0, 'briefcase', url('admin-vagas')) ?>
    <?= Ui::metrica('Publicadas', $vag['publicada'] ?? 0, 'check', url('admin-vagas', ['aba' => 'publicada'])) ?>
    <?= Ui::metrica('Aguardando aprovação', $vag['aguardando'] ?? 0, 'clock', url('admin-vagas', ['aba' => 'aguardando']), 'warn') ?>
    <?= Ui::metrica('Agendadas', $vag['agendada'] ?? 0, 'clock', url('admin-vagas', ['aba' => 'agendada']), 'info') ?>
    <?= Ui::metrica('Expiradas', $vag['expirada'] ?? 0, 'briefcase', url('admin-vagas', ['aba' => 'expirada']), 'mute') ?>
    <?= Ui::metrica('Encerradas', $vag['encerrada'] ?? 0, 'x', url('admin-vagas', ['aba' => 'encerrada']), 'mute') ?>
  </div>
</div>
<div class="ui-grid-2">
  <div class="ui-card">
    <h2>Vagas por situação</h2>
    <?= Ui::donut([
        ['Publicadas', $vag['publicada'] ?? 0, 'var(--chart-1)'],
        ['Em análise', $vag['aguardando'] ?? 0, 'var(--chart-3)'],
        ['Agendadas', $vag['agendada'] ?? 0, 'var(--chart-2)'],
        ['Expiradas', $vag['expirada'] ?? 0, 'var(--chart-5)'],
        ['Encerradas', $vag['encerrada'] ?? 0, 'var(--border-strong)'],
        ['Rejeitadas', $vag['rejeitada'] ?? 0, 'var(--chart-4)'],
    ], 'vagas') ?>
  </div>
  <div class="ui-card">
    <h2>Cadastros e envios por semana</h2>
    <?= Ui::barras($semanas, [['empresas', 'Empresas', 'var(--chart-1)'], ['vagas', 'Vagas', 'var(--chart-2)']], 'Empresas cadastradas e vagas enviadas nas últimas 8 semanas') ?>
  </div>
</div>
<?php require __DIR__ . '/../../public/css/admin_dashboard.css'; ?>
<div class="ui-grid-2"><div class="ui-card">
    <h2>Atividade recente</h2>
    <?php if (!$atividade): echo Ui::vazio('Nenhuma atividade ainda');
    else: ?><ol class="ui-timeline">
        <?php foreach ($atividade as $h): $nome = $h['entidade'] === 'vaga' ? $h['v_titulo'] : ($h['entidade'] === 'empresa' ? $h['e_nome'] : '');
            $link = $h['entidade'] === 'vaga' ? url('admin-vaga', ['id' => $h['entidade_id']]) : ($h['entidade'] === 'empresa' ? url('admin-empresa', ['id' => $h['entidade_id']]) : null); ?>
          <li><strong><?= e(Historico::rotulo($h)) ?></strong>
            <?= $link && $nome ? '<a href="' . e($link) . '">' . e($nome) . '</a>' : e((string) $nome) ?><small><?= e(date('d/m H:i', strtotime($h['criado_em']))) ?></small>
          </li><?php endforeach; ?>
      </ol><?php endif; ?>
  </div>
  <div class="ui-card">
    <h2>Empresas recentes</h2>
    <?php if (!$empresasRecentes): echo Ui::vazio('Nenhuma empresa cadastrada');
    else: ?><ul><?php foreach ($empresasRecentes as $r): ?>
          <li><span><a href="<?= url('admin-empresa', ['id' => $r['id']]) ?>"><?= e($r['nome']) ?></a><br><small class="ui-hint"><?= data_br(substr($r['criado_em'], 0, 10)) ?></small></span><?= Ui::statusEmpresa($r['status']) ?>
          </li><?php endforeach; ?>
      </ul><?php endif; ?>
  </div>
</div>
<div class="ui-card ui-card-margin-top">
  <div class="ui-row-between"><h2>Acessos nos últimos 7 dias</h2><a class="ui-btn ui-btn-sm" href="<?= url('admin-acesso') ?>">Controle de acesso</a></div>
  <div class="ui-metrics"><span><?php foreach ($evento as $k => $rot): echo Ui::metrica($rot, $acessos[$k] ?? 0, $k === 'login_ok' ? 'user' : 'shield', null, $k === 'login_ok' ? '' : ($k === 'login_falha' ? 'warn' : 'danger')); endforeach; ?></span></div>
  <?= Ui::barras($logins, [['ok', 'Logins', 'var(--chart-1)'], ['falha', 'Falhas', 'var(--chart-4)']], 'Logins e falhas por dia nos últimos 7 dias') ?>
</div>
?>
