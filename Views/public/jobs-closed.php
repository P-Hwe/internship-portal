<?php require __DIR__ . '/../header.php'; $ativa = 'home'; require __DIR__ . '/../partials/nav.php'; ?>
<nav class="vb-crumbs" aria-label="Você está aqui"><a href="<?= url() ?>">Vagas</a><span aria-hidden="true">/</span><span>Encerradas</span></nav>
<div class="vb-intro">
  <h1>Vagas encerradas</h1>
  <p>Vagas que saíram do ar nos últimos 120 dias. Elas não recebem mais candidaturas e ficam aqui para consulta.</p>
</div>
<section class="vb-section" aria-labelledby="vb-closed">
  <h2 id="vb-closed" class="vb-section-title">Histórico <span class="vb-count"><?= count($vagas) ?></span></h2>
  <?php if (!$vagas): ?>
    <div class="vb-empty"><strong>Nenhuma vaga encerrada recentemente</strong><p>As vagas aparecem aqui quando o prazo termina ou a empresa encerra o processo.</p><a class="vb-btn" href="<?= url() ?>">Ver vagas abertas</a></div>
  <?php else: ?>
    <div class="vb-list"><?php foreach ($vagas as $v) { $withSummary = false; require __DIR__ . '/../partials/job-row.php'; } ?></div>
  <?php endif; ?>
</section>
</div>
<?php require __DIR__ . '/../footer.php'; ?>
