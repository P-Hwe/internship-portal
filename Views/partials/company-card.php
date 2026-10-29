<?php $ext = Company::linkExterno($e); ?>
<article class="vg-card vg-comp vg-card-trilho" style="cursor:pointer" onclick="window.location.href='<?= e(url('empresa', ['id' => $e['id']])) ?>'">
  <div class="vg-comp-cover<?= empresa_capa_attr($e) ?>"><?= empresa_banner($e) ?></div>
  <div class="vg-comp-top">
    <span class="vg-comp-logo"><?= Ui::logo($e, 56) ?></span>
    <div>
      <h3 class="vg-card-title" style="margin:0"><?= e($e['nome']) ?></h3>
      <p class="vg-comp-seg"><?= e(Company::segmentoTxt($e)) ?></p>
    </div>
  </div>
  <p class="vg-comp-desc"><?= e(mb_strimwidth((string) $e['descricao'], 0, 110, '…')) ?></p>
  <p class="vg-comp-foot"><strong><?= (int) $e['vagas_abertas'] ?></strong> vaga(s) aberta(s)<?= $e['cidade'] ? ' · ' . e($e['cidade'] . '/' . $e['uf']) : '' ?></p>
  <span class="vg-comp-link" style="position:relative;z-index:2">Ver vagas e informações</span>
  <?php if ($ext): ?>
  <a class="vg-comp-ext" href="<?= e(url('externo', ['id' => $e['id']])) ?>" onclick="event.stopPropagation()" target="_blank" rel="noopener" style="position:relative;z-index:3;display:inline-flex;align-items:center;gap:5px;font-size:12px;color:var(--muted,#5d6b62);margin:0 16px 14px;text-decoration:underline">
    <?= icon('external', 12) ?> Ver site / LinkedIn
  </a>
  <?php endif; ?>
</article>
