<?php
/** Banner da equipe do ESTAGIA+. $tbPage = true na página /sobre (título como h1 e sem o botão para a própria página). */
$tbPage = $tbPage ?? false;
$tbSite = 'https://estagiamais.simplifica.gru.br/';
?>
<section class="tb-banner<?= $tbPage ? ' is-page' : '' ?>" id="sobre" aria-labelledby="tb-title">
  <div class="tb-text">
    <?php if ($tbPage): ?><h1 id="tb-title">Quem fez o ESTAGIA+</h1><?php else: ?><h2 id="tb-title">Feito por estudantes do IFSP Guarulhos</h2><?php endif; ?>
    <p>Cinco estudantes do câmpus criaram este mural de vagas em um projeto de extensão, a partir de uma necessidade da Coordenadoria de Estágios.</p>
    <div class="tb-actions">
      <?php if (!$tbPage): ?><a class="vb-btn vb-btn-lg tb-btn-light" href="<?= url('sobre') ?>"><?= icon('users', 17) ?> Conheça a equipe</a><?php endif; ?>
      <a class="vb-btn vb-btn-lg <?= $tbPage ? 'tb-btn-light' : 'tb-btn-ghost' ?>" href="<?= e($tbSite) ?>" target="_blank" rel="noopener"><?= icon('external', 17) ?> Site do ESTAGIA+</a>
    </div>
  </div>
  <ul class="tb-faces" aria-label="Integrantes">
    <?php foreach (Credits::TEAM as $i => $m): $foto = Credits::photo($m['slug']); ?>
    <li style="--i:<?= $i ?>" title="<?= e($m['nome']) ?>">
      <?php if ($foto): ?><img src="<?= e($foto) ?>" alt="" width="64" height="64" loading="lazy"><?php else: ?><span aria-hidden="true"><?= e(iniciais($m['curto'])) ?></span><?php endif; ?>
      <small><?= e($m['curto']) ?></small>
    </li>
    <?php endforeach; ?>
  </ul>
</section>
