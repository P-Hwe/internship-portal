<?php
$abas = ['todas' => ['Todas', $cont['todas'] ?? 0], 'aguardando' => ['Em análise', $cont['aguardando'] ?? 0], 'publicada' => ['Publicadas', $cont['publicada'] ?? 0]];
if (!empty($cont['agendada'])) $abas['agendada'] = ['Agendadas', $cont['agendada']];
$abas += ['destaque' => ['Em destaque', $cont['destaque'] ?? 0], 'expirada' => ['Expiradas', $cont['expirada'] ?? 0], 'encerrada' => ['Encerradas', $cont['encerrada'] ?? 0], 'rejeitada' => ['Rejeitadas', $cont['rejeitada'] ?? 0]];
?>
<div class="ui-row-between"><p class="ui-sub" style="margin:0">Vagas expiradas e encerradas ficam guardadas no histórico; nada é excluído.</p>
  <?php if ($emp['status'] === 'aprovada'): ?><a class="ui-btn ui-btn-primary" href="<?= url('vaga-cadastro') ?>"><?= icon('plus', 16) ?> Publicar vaga</a><?php endif; ?></div>
<?= Ui::abas($abas, $aba, 'empresa-vagas', ['q' => $q]) ?>
<div class="ui-toolbar"><form method="get" action="<?= url('empresa-vagas') ?>" role="search"><?= campo_rota('empresa-vagas') ?><input type="hidden" name="aba" value="<?= e($aba) ?>">
  <label class="ui-sr" for="q">Buscar</label><input id="q" class="ui-input" type="search" name="q" value="<?= e($q) ?>" placeholder="Buscar por título, área ou código"><button class="ui-btn" type="submit"><?= icon('search', 16) ?> Buscar</button></form></div>
<?php if (!$lista): echo Ui::vazio($q ? 'Nada encontrado' : 'Nenhuma vaga nesta categoria', $q ? 'Tente outro termo ou limpe a busca.' : '', $aba === 'todas' && $emp['status'] === 'aprovada' ? ['Publicar vaga', url('vaga-cadastro')] : null);
else: ?><div class="ui-grid"><?php foreach ($lista as $v) { $ctx = 'empresa'; require __DIR__ . '/../partials/job-card-panel.php'; } ?></div>
<?= Ui::paginacao($pg, $paginas, 'empresa-vagas', ['aba' => $aba, 'q' => $q]) ?><?php endif; ?>
