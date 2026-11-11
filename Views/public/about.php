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
  <p class="ab-credit-line">Idealizado por <?= e($demandante['nome']) ?> (<?= e(mb_strtolower($demandante['papel'])) ?>).</p>
</section>

<section class="vb-section" aria-labelledby="ab-ext">
  <div class="ab-ext">
    <span class="ab-ext-kicker"><?= icon('star', 14) ?> Projeto de extensão</span>
    <h2 id="ab-ext">O ESTAGIA+ fez parte do projeto de extensão</h2>
    <p class="ab-ext-name"><?= e($extensao['nome']) ?></p>
    <p>Desenvolvido por estudantes do curso de <?= e($extensao['curso']) ?> do IFSP Câmpus Guarulhos, a partir de uma demanda da Coordenadoria de Estágios.</p>
    <a class="vb-btn vb-btn-primary" href="<?= e($extensao['url']) ?>" target="_blank" rel="noopener"><?= icon('external', 16) ?> Ver o projeto no SUAP</a>
  </div>
</section>
</div>
<?php require __DIR__ . '/../footer.php'; ?>
