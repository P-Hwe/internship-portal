<?php
<div class="ui-metrics"><?= Ui::metrica('Empresas', $emp['total'] ?? 0, 'building') ?><?= Ui::metrica('Vagas', $vag['todas'] ?? 0, 'briefcase') ?><?= Ui::metrica('Vagas publicadas', $vag['publicada'] ?? 0, 'check') ?><?= Ui::metrica('Vagas expiradas e encerradas', ($vag['expirada'] ?? 0) + ($vag['encerrada'] ?? 0), 'clock', null, 'mute') ?></div>
<div class="ui-grid-2">
  <div class="ui-card"><h2>Exportar vagas</h2><p class="ui-sub">Todas as vagas, com situação, período e empresa. Arquivo CSV (abre no Excel).</p><a class="ui-btn ui-btn-primary" href="<?= url('admin-exportar', ['tipo' => 'vagas']) ?>"><?= icon('file', 16) ?> Baixar vagas.csv</a></div>
  <div class="ui-card"><h2>Exportar empresas</h2><p class="ui-sub">Cadastro das empresas com situação e quantidade de vagas.</p><a class="ui-btn ui-btn-primary" href="<?= url('admin-exportar', ['tipo' => 'empresas']) ?>"><?= icon('file', 16) ?> Baixar empresas.csv</a></div>
</div>
<p class="ui-sub" style="margin-top:16px">Relatórios gráficos e por período ficam para uma próxima sprint; os dados e os gráficos do dashboard já usam os mesmos números do banco.</p>
?>
