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
    <p>O ESTAGIA+ foi desenvolvido por uma das startups acadêmicas do projeto, formada por estudantes do curso de <?= e($extensao['curso']) ?>, a partir de uma demanda da Coordenadoria de Estágios do câmpus.</p>
    <dl class="ab-ext-facts">
      <?php foreach ($extensao['fatos'] as [$rotulo, $valor]): ?><div><dt><?= e($rotulo) ?></dt><dd><?= e($valor) ?></dd></div><?php endforeach; ?>
    </dl>
  </div>

  <div class="ab-ext-grid">
    <article class="ab-ext-card">
      <h3><?= icon('info', 18) ?> Sobre o projeto</h3>
      <p><?= e($extensao['resumo']) ?></p>
    </article>
    <article class="ab-ext-card">
      <h3><?= icon('star', 18) ?> Objetivo</h3>
      <p><?= e($extensao['objetivo']) ?></p>
      <ul class="ab-ext-goals"><?php foreach ($extensao['objetivos'] as $o): ?><li><?= icon('check', 15) ?> <span><?= e($o) ?></span></li><?php endforeach; ?></ul>
    </article>
  </div>

  <h3 class="ab-ext-sub">Como o projeto acontece</h3>
  <ol class="ab-ext-phases">
    <?php foreach ($extensao['fases'] as $i => [$titulo, $quando, $texto]): ?>
    <li><span class="ab-ext-num"><?= $i + 1 ?></span><div><strong><?= e($titulo) ?></strong><small><?= e($quando) ?></small><p><?= e($texto) ?></p></div></li>
    <?php endforeach; ?>
  </ol>

  <h3 class="ab-ext-sub">Objetivos de Desenvolvimento Sustentável (ONU)</h3>
  <ul class="ab-ext-ods"><?php foreach ($extensao['ods'] as [$n, $nome]): ?><li><b>ODS <?= e($n) ?></b> <?= e($nome) ?></li><?php endforeach; ?></ul>
</section>
</div>
<?php require __DIR__ . '/../footer.php'; ?>
