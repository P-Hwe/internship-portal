<?php
$rowOpen = ($v['situacao'] ?? 'publicada') === 'publicada';
$rowBadges = $rowOpen ? array_slice(array_diff_key($v['selos'] ?? [], ['tipo' => 1]), 0, 2, true) : [];
$rowDays = $rowOpen ? dias_restantes($v['data_limite']) : null;
?>
<article class="vb-job<?= !empty($v['destaque']) && $rowOpen ? ' is-featured' : '' ?><?= $rowOpen ? '' : ' is-closed' ?>"<?= $rowOpen ? '' : ' aria-disabled="true"' ?>>
  <div class="vb-job-logo"><?= Ui::logo($v['empresa'], 56) ?></div>
  <div class="vb-job-main">
    <h3 class="vb-job-title"><?php if ($rowOpen): ?><a class="vb-stretch" href="<?= url('vaga', ['id' => $v['id']]) ?>"><?= e($v['titulo']) ?></a><?php else: ?><?= e($v['titulo']) ?><?php endif; ?></h3>
    <p class="vb-job-company"><?= e($v['empresa']['nome']) ?></p>
    <p class="vb-job-meta">
      <span><?= icon('pin', 14) ?> <?= e($v['local']) ?></span>
      <span><?= icon('briefcase', 14) ?> <?= e($v['tipo']) ?> · <?= e($v['modalidade']) ?></span>
      <span class="vb-money"><?= icon('money', 14) ?> <?= e($v['bolsa_txt']) ?></span>
    </p>
    <?php if (!empty($withSummary) && !empty($v['resumo'])): ?><p class="vb-job-summary"><?= e($v['resumo']) ?></p><?php endif; ?>
    <?php if ($rowBadges): ?><ul class="vb-badges" aria-label="Destaques da vaga"><?php foreach ($rowBadges as [$txt, $cls]): ?><li class="vb-badge <?= e($cls) ?>"><?= e($txt) ?></li><?php endforeach; ?></ul><?php endif; ?>
  </div>
  <div class="vb-job-side">
    <span class="vb-job-area"><?= e($v['area_exibicao'] ?? $v['area']) ?></span>
    <?php if ($rowOpen): ?>
      <span class="vb-job-date<?= $rowDays <= DIAS_VAGA_VENCENDO ? ' is-soon' : '' ?>"><?= $rowDays <= 0 ? 'Último dia' : 'Até ' . e(date('d/m', strtotime($v['data_limite']))) ?></span>
    <?php else: ?>
      <span class="vb-job-date is-closed"><?= icon('lock', 13) ?> <?= $v['situacao'] === 'encerrada' ? 'Encerrada pela empresa' : 'Expirou em ' . e(date('d/m', strtotime($v['data_limite']))) ?></span>
    <?php endif; ?>
    <small>Publicada em <?= e(data_br($v['data_publicacao'])) ?></small>
  </div>
</article>
