<?php
$vista = ($vista ?? 'cards') === 'lista' ? 'lista' : 'cards';
$abas = ['todas' => ['Todas', $cont['todas'] ?? 0], 'aguardando' => ['Em análise', $cont['aguardando'] ?? 0], 'publicada' => ['Publicadas', $cont['publicada'] ?? 0]];
if (!empty($cont['agendada'])) $abas['agendada'] = ['Agendadas', $cont['agendada']];
$abas += ['destaque' => ['Em destaque', $cont['destaque'] ?? 0], 'expirada' => ['Expiradas', $cont['expirada'] ?? 0], 'encerrada' => ['Encerradas', $cont['encerrada'] ?? 0], 'rejeitada' => ['Rejeitadas', $cont['rejeitada'] ?? 0]];
$listParams = ['aba' => $aba, 'q' => $q] + ($vista === 'lista' ? ['vista' => 'lista'] : []);
?>
<div class="ui-row-between"><p class="ui-sub ui-sub-flush">Vagas expiradas e encerradas ficam guardadas no histórico; nada é excluído.</p>
  <?php if (Company::podePublicar($emp)): ?><a class="ui-btn ui-btn-primary" href="<?= url('vaga-cadastro') ?>"><?= icon('plus', 16) ?> Publicar vaga</a><?php endif; ?></div>
<?= Ui::abas($abas, $aba, 'empresa-vagas', array_diff_key($listParams, ['aba' => 1])) ?>
<div class="ui-toolbar"><form method="get" action="<?= url('empresa-vagas') ?>" role="search"><?= campo_rota('empresa-vagas') ?><input type="hidden" name="aba" value="<?= e($aba) ?>"><?php if ($vista === 'lista'): ?><input type="hidden" name="vista" value="lista"><?php endif; ?>
  <label class="ui-sr" for="q">Buscar</label><input id="q" class="ui-input" type="search" name="q" value="<?= e($q) ?>" placeholder="Buscar por título, área ou código"><button class="ui-btn" type="submit"><?= icon('search', 16) ?> Buscar</button></form>
  <div class="ui-seg" role="group" aria-label="Modo de exibição"><a href="<?= e(url('empresa-vagas', array_diff_key($listParams, ['vista' => 1]))) ?>" class="<?= $vista === 'cards' ? 'is-active' : '' ?>"><?= icon('grid', 14) ?> Cards</a><a href="<?= e(url('empresa-vagas', array_merge($listParams, ['vista' => 'lista']))) ?>" class="<?= $vista === 'lista' ? 'is-active' : '' ?>"><?= icon('menu', 14) ?> Lista</a></div></div>
<?php if (!$lista): echo Ui::vazio($q ? 'Nada encontrado' : 'Nenhuma vaga nesta categoria', $q ? 'Tente outro termo ou limpe a busca.' : '', $aba === 'todas' && Company::podePublicar($emp) ? ['Publicar vaga', url('vaga-cadastro')] : null);
elseif ($vista === 'lista'): ?>
<div class="ui-rows">
  <?php foreach ($lista as $v): ?>
  <div class="ui-row">
    <span class="ui-row-status"><?= Ui::situacao($v['situacao']) ?></span>
    <div class="ui-row-main">
      <a class="ui-row-title" href="<?= url('empresa-vaga', ['id' => $v['id']]) ?>"><?= e($v['titulo']) ?></a>
      <p class="ui-row-meta"><span class="ui-code"><?= e($v['codigo']) ?></span> · <?= e($v['tipo']) ?> · <?= e($v['area']) ?> · <?= e($v['modalidade']) ?></p>
    </div>
    <div class="ui-row-dates"><span><?= data_br($v['data_publicacao']) ?> a <?= data_br($v['data_limite']) ?></span><small><?= e($v['bolsa_txt']) ?></small></div>
    <div class="ui-row-actions"><div class="ui-actions">
      <?php if ($v['status'] !== 'encerrada'): ?><a class="ui-btn ui-btn-sm" href="<?= url('vaga-editar', ['id' => $v['id']]) ?>"><?= icon('edit', 14) ?> Editar</a><?php endif; ?>
      <?php if ($v['status'] === 'aprovada'): echo Ui::formConfirmar(url('vaga-encerrar'), ['id' => $v['id']], 'Encerrar', 'Encerrar esta vaga?', 'A vaga "' . $v['titulo'] . '" deixa de aparecer para os estudantes imediatamente. Ela continua no seu histórico.', 'danger', 'ui-btn ui-btn-sm ui-btn-danger-o'); endif; ?>
    </div></div>
  </div>
  <?php endforeach; ?>
</div>
<?= Ui::paginacao($pg, $paginas, 'empresa-vagas', $listParams) ?>
<?php else: ?>
<div class="ui-grid"><?php foreach ($lista as $v) { $ctx = 'empresa'; require __DIR__ . '/../partials/job-card-panel.php'; } ?></div>
<?= Ui::paginacao($pg, $paginas, 'empresa-vagas', $listParams) ?>
<?php endif; ?>
