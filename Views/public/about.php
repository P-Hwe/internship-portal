<?php require __DIR__ . '/../header.php'; $ativa = 'sobre'; require __DIR__ . '/../partials/nav.php';
$objetivos = ['Centralizar as oportunidades de estágio em um único lugar.', 'Simplificar o caminho do estudante até a vaga.', 'Facilitar a comunicação entre estudantes, professores, empresas e a Coordenação de estágios.'];
?>
<div class="vb-intro ab-intro">
  <h1>Sobre o ESTAGIA+</h1>
  <p>O ESTAGIA+ nasce para aproximar estudantes, professores e empresas em um espaço mais claro, acessível e humano. É o mural de vagas de estágio do IFSP Câmpus Guarulhos, desenvolvido por estudantes do câmpus em um projeto de extensão.</p>
</div>

<section class="vb-section ab-why" aria-labelledby="ab-why">
  <div>
    <h2 id="ab-why" class="vb-section-title">Por que ele existe</h2>
    <p>A ideia surgiu de uma necessidade da Coordenadoria de Estágios. Antes, as vagas eram divulgadas de forma manual e espalhada, em murais, grupos e e-mails avulsos, sem um canal único para acompanhar o que estava aberto.</p>
  </div>
  <ul class="ab-goals">
    <?php foreach ($objetivos as $o): ?><li><?= icon('check-s', 18) ?><span><?= e($o) ?></span></li><?php endforeach; ?>
  </ul>
</section>

<section class="vb-section ab-mvv" aria-labelledby="ab-mvv">
  <h2 id="ab-mvv" class="vb-section-title">Missão, visão e valores</h2>
  <div class="ab-mvv-grid">
    <div class="ab-block"><h3>Missão</h3><p>Oferecer aos estudantes uma plataforma integrada e acessível que centraliza oportunidades de estágio.</p></div>
    <div class="ab-block"><h3>Visão</h3><p>Tornar-se a principal plataforma acadêmica de apoio à empregabilidade estudantil no IFSP.</p></div>
    <div class="ab-block ab-values"><h3>Valores</h3><ul><?php foreach ($valores as $v): ?><li><?= e($v) ?></li><?php endforeach; ?></ul></div>
  </div>
</section>

<section class="vb-section" id="equipe" aria-labelledby="ab-team">
  <h2 id="ab-team" class="vb-section-title">Quem desenvolveu</h2>
  <p class="ab-lead">Estudantes do IFSP Câmpus Guarulhos que criaram e mantêm a ferramenta.</p>
  <ul class="ab-team">
    <?php foreach ($equipe as $m): $foto = Credits::photo($m['slug']); ?>
    <li class="ab-person">
      <?php if ($foto): ?><img class="ab-avatar" src="<?= e($foto) ?>" alt="" width="72" height="72" loading="lazy">
      <?php else: ?><span class="ab-avatar" aria-hidden="true"><?= e(iniciais($m['curto'])) ?></span><?php endif; ?>
      <div>
        <strong><?= e($m['nome']) ?></strong>
        <span class="ab-role"><?= e($m['papel']) ?></span>
        <span class="ab-area"><?= e($m['area']) ?></span>
      </div>
    </li>
    <?php endforeach; ?>
  </ul>
</section>

<section class="vb-section ab-credit" aria-labelledby="ab-credit">
  <h2 id="ab-credit" class="vb-section-title">Créditos</h2>
  <div class="ab-credit-grid">
    <div class="ab-block">
      <h3><?= e($demandante['nome']) ?></h3>
      <span class="ab-role"><?= e($demandante['papel']) ?></span>
      <p><?= e($demandante['sobre']) ?></p>
    </div>
    <div class="ab-block">
      <h3>Realização</h3>
      <p>Projeto de extensão do Instituto Federal de São Paulo, Câmpus Guarulhos, em parceria com a Coordenadoria de Estágios.</p>
      <p><a href="https://estagiamais.simplifica.gru.br/" target="_blank" rel="noopener">Conheça a equipe no site do projeto</a></p>
    </div>
  </div>
</section>
</div>
<?php require __DIR__ . '/../footer.php'; ?>
