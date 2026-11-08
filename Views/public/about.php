<?php require __DIR__ . '/../header.php'; $ativa = 'sobre'; require __DIR__ . '/../partials/nav.php'; ?>
<?php $tbPage = true; require __DIR__ . '/../partials/team-banner.php'; ?>

<section class="vb-section" aria-labelledby="ab-team">
  <h2 id="ab-team" class="vb-section-title">Integrantes</h2>
  <ul class="ab-team">
    <?php foreach ($equipe as $m): $foto = Credits::photo($m['slug']); ?>
    <li class="ab-person">
      <?php if ($foto): ?><img class="ab-avatar" src="<?= e($foto) ?>" alt="" width="88" height="88" loading="lazy">
      <?php else: ?><span class="ab-avatar" aria-hidden="true"><?= e(iniciais($m['curto'])) ?></span><?php endif; ?>
      <strong><?= e($m['nome']) ?></strong>
      <span class="ab-role"><?= e($m['papel']) ?></span>
      <span class="ab-area"><?= e($m['area']) ?></span>
    </li>
    <?php endforeach; ?>
  </ul>
  <p class="ab-credit-line">Projeto de extensão do IFSP Câmpus Guarulhos, idealizado por <?= e($demandante['nome']) ?> (<?= e(mb_strtolower($demandante['papel'])) ?>).</p>
</section>
</div>
<?php require __DIR__ . '/../footer.php'; ?>
