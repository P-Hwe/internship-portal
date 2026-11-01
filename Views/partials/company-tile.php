<article class="vb-org">
  <div class="vb-org-head">
    <?= Ui::logo($e, 48) ?>
    <div>
      <h3><a class="vb-stretch" href="<?= e(url('empresa', ['id' => $e['id']])) ?>"><?= e($e['nome']) ?></a></h3>
      <p><?= e(Company::segmentoTxt($e)) ?></p>
    </div>
  </div>
  <?php if (!empty($e['descricao'])): ?><p class="vb-org-desc"><?= e(mb_strimwidth((string) $e['descricao'], 0, 140, '…')) ?></p><?php endif; ?>
  <p class="vb-org-foot">
    <span class="<?= (int) $e['vagas_abertas'] ? 'is-open' : '' ?>"><?= icon('briefcase', 14) ?> <?= (int) $e['vagas_abertas'] === 1 ? '1 vaga aberta' : (int) $e['vagas_abertas'] . ' vagas abertas' ?></span>
    <?php if (!empty($e['cidade'])): ?><span><?= icon('pin', 14) ?> <?= e($e['cidade'] . '/' . $e['uf']) ?></span><?php endif; ?>
  </p>
</article>
