<?php require __DIR__ . '/../header.php'; $ativa = 'home'; require __DIR__ . '/../partials/nav.php';
$order = (string) ($filtros['ord'] ?? '');
$viewMode = ($_GET['vista'] ?? '') === 'lista' ? 'lista' : 'cards';
$activeFilters = array_filter(['busca' => $filtros['busca'] ?? '', 'tipo' => $filtros['tipo'] ?? '', 'area' => $filtros['area'] ?? '', 'modalidade' => $filtros['modalidade'] ?? '']);
$filterLabels = ['busca' => 'Busca', 'tipo' => 'Tipo', 'area' => 'Área', 'modalidade' => 'Modalidade'];
$sortOptions = ['' => 'Mais recentes', 'salario' => 'Maior remuneração', 'prazo' => 'Encerram em breve', 'encerradas' => 'Encerradas', 'expiradas' => 'Expiradas'];
$isArchive = in_array($order, ['encerradas', 'expiradas'], true);
$listTitle = $isArchive ? ($order === 'encerradas' ? 'Vagas encerradas' : 'Vagas expiradas') : ($activeFilters ? 'Resultado da busca' : 'Vagas abertas');
$baseParams = array_filter(array_merge($filtros, ['vista' => $viewMode === 'lista' ? 'lista' : '']));
?>
<div class="vb-intro">
  <h1>Vagas de estágio e primeiro emprego</h1>
  <p>Oportunidades de empresas parceiras analisadas pela Coordenação do IFSP Guarulhos. A candidatura é feita direto com a empresa.</p>
</div>

<?php if ($destaques): ?>
<section class="vg-carousel vb-carousel" data-carousel aria-roledescription="carrossel" aria-label="Vagas em destaque">
  <div class="vg-slides">
    <?php foreach ($destaques as $i => $v): ?>
    <a class="vg-slide <?= $i === 0 ? 'is-active' : '' ?>" href="<?= url('vaga', ['id' => $v['id']]) ?>"
       aria-label="<?= $i + 1 ?> de <?= count($destaques) ?>: <?= e($v['titulo']) ?> - <?= e($v['empresa']['nome']) ?>">
      <span class="vb-slide-media"><?= vaga_imagem($v) ?></span>
      <span class="vg-slide-caption">
        <span class="vg-slide-tag"><?= icon('star', 12) ?> Destaque · <?= e($v['tipo']) ?></span>
        <strong><?= e($v['titulo']) ?></strong>
        <span class="vb-slide-meta"><?= Ui::logo($v['empresa'], 28) ?> <?= e($v['empresa']['nome']) ?> · <?= e($v['modalidade']) ?> · <?= e($v['bolsa_txt']) ?></span>
      </span>
    </a>
    <?php endforeach; ?>
  </div>
  <?php if (count($destaques) > 1): ?>
  <button type="button" class="vg-car-btn vg-prev" data-prev aria-label="Vaga anterior"><?= icon('chevron-left', 22) ?></button>
  <button type="button" class="vg-car-btn vg-next" data-next aria-label="Próxima vaga"><?= icon('chevron-right', 22) ?></button>
  <div class="vg-dots" role="tablist" aria-label="Escolher vaga em destaque">
    <?php foreach ($destaques as $i => $v): ?><button type="button" class="<?= $i === 0 ? 'is-active' : '' ?>" data-dot="<?= $i ?>" aria-label="Ir para a vaga <?= $i + 1 ?>"></button><?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>
<?php endif; ?>

<form class="vb-search" method="get" action="<?= url('home') ?>" role="search" data-autosubmit>
  <?= campo_rota('home') ?>
  <?php if ($order !== ''): ?><input type="hidden" name="ord" value="<?= e($order) ?>"><?php endif; ?>
  <?php if ($viewMode === 'lista'): ?><input type="hidden" name="vista" value="lista"><?php endif; ?>
  <div class="vb-search-main">
    <label class="vg-sr" for="f-busca">Buscar vaga</label>
    <span class="vb-search-input"><?= icon('search', 18) ?><input id="f-busca" type="search" name="busca" value="<?= e($filtros['busca'] ?? '') ?>" placeholder="Cargo, empresa ou palavra-chave"></span>
    <button class="vb-btn vb-btn-primary" type="submit">Buscar vagas</button>
  </div>
  <div class="vb-search-filters">
    <label><span>Tipo</span>
      <select name="tipo"><option value="">Todos</option><?php foreach ($tipos as $t): ?><option <?= ($filtros['tipo'] ?? '') === $t ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select></label>
    <label><span>Área</span>
      <select name="area"><option value="">Todas</option><?php foreach ($areas as $a): ?><option <?= ($filtros['area'] ?? '') === $a ? 'selected' : '' ?>><?= e($a) ?></option><?php endforeach; ?></select></label>
    <label><span>Modalidade</span>
      <select name="modalidade"><option value="">Todas</option><?php foreach ($modalidades as $m): ?><option <?= ($filtros['modalidade'] ?? '') === $m ? 'selected' : '' ?>><?= e($m) ?></option><?php endforeach; ?></select></label>
  </div>
  <?php if ($activeFilters): ?>
  <div class="vb-active">
    <?php foreach ($activeFilters as $key => $value): ?>
      <a class="vb-chip" href="<?= e(url('home', array_diff_key($baseParams, [$key => 1]))) ?>" aria-label="Remover filtro <?= e($filterLabels[$key]) ?>: <?= e($value) ?>"><?= e($filterLabels[$key]) ?>: <strong><?= e($value) ?></strong> <?= icon('x', 13) ?></a>
    <?php endforeach; ?>
    <a class="vb-clear" href="<?= e(url('home', array_intersect_key($baseParams, ['vista' => 1]))) ?>">Limpar filtros</a>
  </div>
  <?php endif; ?>
</form>

<section class="vb-section" id="todas" aria-labelledby="vb-list-title">
  <div class="vb-list-head">
    <h2 id="vb-list-title" class="vb-section-title"><?= e($listTitle) ?> <span class="vb-count"><?= (int) $totalVagas ?></span></h2>
    <div class="vb-view" role="group" aria-label="Modo de exibição">
      <a href="<?= e(url('home', array_diff_key($baseParams, ['vista' => 1, 'pg' => 1]) + ($pagina > 1 ? ['pg' => $pagina] : []))) ?>" class="<?= $viewMode === 'cards' ? 'is-active' : '' ?>" aria-label="Ver em cards"<?= $viewMode === 'cards' ? ' aria-current="true"' : '' ?>><?= icon('grid', 16) ?> Cards</a>
      <a href="<?= e(url('home', array_merge($baseParams, ['vista' => 'lista'], $pagina > 1 ? ['pg' => $pagina] : []))) ?>" class="<?= $viewMode === 'lista' ? 'is-active' : '' ?>" aria-label="Ver em lista"<?= $viewMode === 'lista' ? ' aria-current="true"' : '' ?>><?= icon('menu', 16) ?> Lista</a>
    </div>
  </div>
  <nav class="vb-sort" aria-label="Ordenar vagas">
    <?php foreach ($sortOptions as $key => $label): ?>
      <a href="<?= e(url('home', array_filter(array_merge($baseParams, ['ord' => $key, 'pg' => ''])))) ?>" class="<?= $order === $key ? 'is-active' : '' ?>"<?= $order === $key ? ' aria-current="true"' : '' ?>><?= e($label) ?></a>
    <?php endforeach; ?>
  </nav>

  <?php if ($vagas): ?>
    <?php if ($viewMode === 'lista'): ?>
      <div class="vb-list"><?php foreach ($vagas as $v) { $withSummary = true; require __DIR__ . '/../partials/job-row.php'; } ?></div>
    <?php else: ?>
      <div class="vb-cards"><?php foreach ($vagas as $v) require __DIR__ . '/../partials/job-card.php'; ?></div>
    <?php endif; ?>
    <?php $pgCur = $pagina; $pgTotal = $totalPaginas; $pgRoute = 'home'; $pgParams = $baseParams; require __DIR__ . '/../partials/board-pagination.php'; ?>
  <?php else: ?>
    <div class="vb-empty">
      <strong><?= $activeFilters ? 'Nenhuma vaga com esses filtros' : 'Nenhuma vaga aberta no momento' ?></strong>
      <p><?= $activeFilters ? 'Remova algum filtro ou busque por outro termo.' : 'Novas vagas aparecem aqui assim que a Coordenação aprova.' ?></p>
      <?php if ($activeFilters): ?><a class="vb-btn" href="<?= url('home') ?>">Ver todas as vagas</a><?php endif; ?>
    </div>
  <?php endif; ?>
</section>

<?php if (!Auth::role()): ?>
<aside class="vb-cta">
  <div>
    <h2>Sua empresa quer contratar estudantes do IFSP?</h2>
    <p>Cadastre a empresa, aguarde a aprovação da Coordenação e publique as vagas para os estudantes do câmpus.</p>
  </div>
  <div class="vb-cta-actions">
    <a class="vb-btn vb-btn-primary" href="<?= url('empresa-cadastro') ?>">Cadastrar empresa</a>
    <a class="vb-btn" href="<?= url('empresa-login') ?>">Já sou parceira</a>
  </div>
</aside>
<?php endif; ?>
<?php require __DIR__ . '/../partials/team-banner.php'; ?>
</div>
<?php require __DIR__ . '/../footer.php'; ?>
