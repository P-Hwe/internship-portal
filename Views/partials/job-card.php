<?php
$cardOpen = ($v['situacao'] ?? 'publicada') === 'publicada';
$cardBadges = $cardOpen ? array_slice(array_diff_key($v['selos'] ?? [], ['tipo' => 1]), 0, 2, true) : [];
$cardImage = url_upload($v['imagem'] ?? null);
$cardDays = dias_restantes($v['data_limite']);
$cardClosedLabel = ($v['situacao'] ?? '') === 'encerrada' ? 'Encerrada pela empresa' : 'Expirou em ' . date('d/m', strtotime($v['data_limite']));
?>
<article class="vb-card<?= $cardOpen && !empty($v['destaque']) ? ' is-featured' : '' ?><?= $cardOpen ? '' : ' is-closed' ?>"<?= $cardOpen ? '' : ' aria-disabled="true"' ?>>
  <div class="vb-card-media<?= $cardImage ? ' has-img' : '' ?>"<?= $cardImage ? ' style="--bn:url(\'' . e($cardImage) . '\')"' : '' ?>>
    <?= vaga_imagem($v) ?>
    <span class="vb-card-type"><?= e($cardOpen ? $v['tipo'] : ($v['situacao'] === 'encerrada' ? 'Encerrada' : 'Expirada')) ?></span>
  </div>
  <div class="vb-card-body">
    <div class="vb-card-company"><?= Ui::logo($v['empresa'], 32) ?><span><?= e($v['empresa']['nome']) ?></span></div>
    <h3 class="vb-card-title"><?php if ($cardOpen): ?><a class="vb-stretch" href="<?= url('vaga', ['id' => $v['id']]) ?>"><?= e($v['titulo']) ?></a><?php else: ?><?= e($v['titulo']) ?><?php endif; ?></h3>
    <ul class="vb-card-meta">
      <li><?= icon('pin', 14) ?> <?= e($v['local']) ?> · <?= e($v['modalidade']) ?></li>
      <li class="vb-money"><?= icon('money', 14) ?> <?= e($v['bolsa_txt']) ?></li>
    </ul>
    <?php if ($cardBadges): ?><ul class="vb-badges"><?php foreach ($cardBadges as [$txt, $cls]): ?><li class="vb-badge <?= e($cls) ?>"><?= e($txt) ?></li><?php endforeach; ?></ul><?php endif; ?>
  </div>
  <div class="vb-card-foot">
    <span class="vb-job-area"><?= e($v['area_exibicao'] ?? $v['area']) ?></span>
    <?php if ($cardOpen): ?>
      <span class="vb-job-date<?= $cardDays <= DIAS_VAGA_VENCENDO ? ' is-soon' : '' ?>"><?= icon('clock', 13) ?> <?= $cardDays <= 0 ? 'Último dia' : 'Até ' . e(date('d/m', strtotime($v['data_limite']))) ?></span>
    <?php else: ?>
      <span class="vb-job-date is-closed"><?= icon('lock', 13) ?> <?= e($cardClosedLabel) ?></span>
    <?php endif; ?>
  </div>
</article>
