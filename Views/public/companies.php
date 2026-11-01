<?php require __DIR__ . '/../header.php'; $ativa = 'empresas'; require __DIR__ . '/../partials/nav.php';
$hiring = $trilhos ? (array_values(array_filter($trilhos, fn($t) => $t['id'] === 'abertas'))[0]['itens'] ?? []) : [];
?>
<div class="vb-intro">
  <h1>Empresas parceiras</h1>
  <p>Empresas aprovadas pela Coordenação que divulgam vagas para os estudantes do câmpus.</p>
</div>

<?php if ($hiring && $pg === 1): ?>
<section class="vb-section" aria-labelledby="vb-hiring">
  <h2 id="vb-hiring" class="vb-section-title">Contratando agora</h2>
  <div class="vb-orgs"><?php foreach (array_slice($hiring, 0, 4) as $e) require __DIR__ . '/../partials/company-tile.php'; ?></div>
</section>
<?php endif; ?>

<section class="vb-section" id="todas-emp" aria-labelledby="vb-all-companies">
  <div class="vb-list-head"><h2 id="vb-all-companies" class="vb-section-title">Todas as empresas <span class="vb-count"><?= (int) $total ?></span></h2></div>
  <?php if (!$todas): ?>
    <div class="vb-empty"><strong>Nenhuma empresa parceira ainda</strong><p>As empresas aparecem aqui depois de aprovadas pela Coordenação.</p></div>
  <?php else: ?>
    <div class="vb-orgs"><?php foreach ($todas as $e) require __DIR__ . '/../partials/company-tile.php'; ?></div>
    <?php $pgCur = $pg; $pgTotal = $totalPaginas; $pgRoute = 'empresas'; $pgParams = []; require __DIR__ . '/../partials/board-pagination.php'; ?>
  <?php endif; ?>
</section>
</div>
<?php require __DIR__ . '/../footer.php'; ?>
