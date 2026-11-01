<?php require __DIR__ . '/../header.php'; $ativa = 'home'; require __DIR__ . '/../partials/nav.php';
$emp = $vaga['empresa'];
$badges = $aberta ? array_slice(array_diff_key($vaga['selos'], ['tipo' => 1]), 0, 3, true) : [];
$platform = Link::PLATAFORMAS[$vaga['link_plataforma'] ?? ''] ?? null;
$mailto = $vaga['contato'] ? 'mailto:' . $vaga['contato'] . '?subject=' . rawurlencode('Candidatura ' . $vaga['codigo'] . ' - ' . $vaga['titulo']) : null;
$applyUrl = $vaga['link_candidatura'] ?: $mailto;
$daysLeft = dias_restantes($vaga['data_limite']);
$facts = [
    ['money', $vaga['tipo'] === 'Estágio' ? 'Bolsa-auxílio' : 'Salário', $vaga['bolsa_txt']],
    ['clock', 'Carga horária', $vaga['carga_txt'] ?: 'A combinar'],
    ['pin', 'Local', $vaga['local'] . ' · ' . $vaga['modalidade']],
    ['briefcase', 'Área', $vaga['area']],
];
?>
<nav class="vb-crumbs" aria-label="Você está aqui"><a href="<?= url() ?>">Vagas</a><span aria-hidden="true">/</span><span><?= e($vaga['titulo']) ?></span></nav>

<?php if (!$aberta): ?>
<div class="vb-notice" role="status"><?= icon('info', 18) ?><p><strong>Esta vaga não recebe mais candidaturas.</strong>
  <?= $vaga['situacao'] === 'encerrada' ? 'A empresa encerrou o processo.' : 'O prazo terminou em ' . data_br($vaga['data_limite']) . '.' ?> As informações ficam disponíveis como histórico.</p></div>
<?php endif; ?>

<article class="vb-detail">
  <div class="vb-panel vb-detail-head">
    <?php $coverImage = url_upload($vaga['imagem'] ?? null); ?>
    <div class="vb-detail-cover<?= $coverImage ? ' has-img' : '' ?>"<?= $coverImage ? ' style="--bn:url(\'' . e($coverImage) . '\')"' : '' ?>><?= vaga_imagem($vaga) ?></div>
    <div class="vb-detail-top">
      <a class="vb-detail-logo" href="<?= url('empresa', ['id' => $emp['id']]) ?>"><?= Ui::logo($emp, 64) ?></a>
      <div class="vb-detail-title">
        <a class="vb-detail-company" href="<?= url('empresa', ['id' => $emp['id']]) ?>"><?= e($emp['nome']) ?></a>
        <h1><?= e($vaga['titulo']) ?></h1>
        <p class="vb-job-meta">
          <span><?= icon('pin', 14) ?> <?= e($vaga['local']) ?></span>
          <span><?= e($vaga['tipo']) ?> · <?= e($vaga['modalidade']) ?></span>
          <span>Publicada em <?= e(data_br($vaga['data_publicacao'])) ?></span>
        </p>
        <?php if ($badges): ?><ul class="vb-badges"><?php foreach ($badges as [$txt, $cls]): ?><li class="vb-badge <?= e($cls) ?>"><?= e($txt) ?></li><?php endforeach; ?></ul><?php endif; ?>
      </div>
    </div>
    <div class="vb-detail-actions">
      <?php if ($aberta && $applyUrl): ?>
        <a class="vb-btn vb-btn-primary vb-btn-lg" href="<?= e($applyUrl) ?>"<?= $vaga['link_candidatura'] ? ' target="_blank" rel="noopener noreferrer nofollow"' : '' ?>><?= icon($vaga['link_candidatura'] ? 'external' : 'mail', 16) ?> Candidatar-se<?= $vaga['link_candidatura'] && $platform ? ' pela ' . e($platform) : '' ?></a>
      <?php endif; ?>
      <button type="button" class="vb-btn" data-copy="<?= e(url_vaga_abs((int) $vaga['id'])) ?>"><?= icon('copy', 15) ?> Copiar link</button>
      <span class="vb-detail-deadline<?= $aberta && $daysLeft <= DIAS_VAGA_VENCENDO ? ' is-soon' : '' ?>"><?= icon('clock', 15) ?> <?= $aberta ? 'Inscrições até ' . e(data_br($vaga['data_limite'])) . ' · ' . e(dias_txt($daysLeft)) : 'Inscrições encerradas em ' . e(data_br($vaga['data_limite'])) ?></span>
    </div>
  </div>

  <dl class="vb-facts">
    <?php foreach ($facts as [$factIcon, $label, $value]): ?>
      <div><dt><?= icon($factIcon, 16) ?> <?= e($label) ?></dt><dd><?= e($value) ?></dd></div>
    <?php endforeach; ?>
  </dl>

  <section class="vb-panel vb-prose">
    <h2>Sobre a vaga</h2>
    <p><?= nl2br(e($vaga['descricao'])) ?></p>
    <?php if ($vaga['atividades']): ?><h3>Atividades</h3>
      <ul><?php foreach ($vaga['atividades'] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <?php if ($vaga['requisitos']): ?><h3>Requisitos</h3>
      <ul><?php foreach ($vaga['requisitos'] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <?php if ($vaga['requisitos_desejaveis']): ?><h3>Desejáveis</h3>
      <ul><?php foreach ($vaga['requisitos_desejaveis'] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <?php if ($vaga['beneficios']): ?><h3>Benefícios</h3>
      <ul class="vb-pills"><?php foreach ($vaga['beneficios'] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?></ul><?php endif; ?>
  </section>

  <?php if ($aberta): ?>
  <section class="vb-panel vb-apply" aria-labelledby="vb-apply-title">
    <h2 id="vb-apply-title">Como se candidatar</h2>
    <p>A candidatura é feita direto com a empresa<?= $vaga['link_candidatura'] && $platform ? ', pela ' . e($platform) : '' ?>. Informe o código <span class="vb-code"><?= e($vaga['codigo']) ?></span> no contato.</p>
    <div class="vb-apply-actions">
      <?php if ($vaga['link_candidatura']): ?><a class="vb-btn vb-btn-primary" href="<?= e($vaga['link_candidatura']) ?>" target="_blank" rel="noopener noreferrer nofollow"><?= icon('external', 15) ?> Abrir formulário</a><?php endif; ?>
      <?php if ($mailto): ?><a class="vb-btn" href="<?= e($mailto) ?>"><?= icon('mail', 15) ?> <?= e($vaga['contato']) ?></a><?php endif; ?>
      <?php if ($emp['telefone']): ?><a class="vb-btn" href="tel:<?= e(preg_replace('/\D/', '', $emp['telefone'])) ?>"><?= icon('phone', 15) ?> <?= e(fone_fmt($emp['telefone'])) ?></a><?php endif; ?>
      <?php foreach (['link_site' => 'Site', 'link_linkedin' => 'LinkedIn'] as $key => $label): if ($vaga[$key]): ?><a class="vb-btn" href="<?= e($vaga[$key]) ?>" target="_blank" rel="noopener noreferrer nofollow"><?= icon('external', 15) ?> <?= $label ?></a><?php endif; endforeach; ?>
      <button type="button" class="vb-btn" data-copy="<?= e($vaga['codigo']) ?>"><?= icon('copy', 15) ?> Copiar código</button>
    </div>
  </section>
  <?php endif; ?>

  <section class="vb-panel vb-company-card" aria-labelledby="vb-company-title">
    <h2 id="vb-company-title">Sobre a empresa</h2>
    <div class="vb-company-row">
      <?= Ui::logo($emp, 48) ?>
      <div>
        <strong><?= e($emp['nome']) ?></strong>
        <p><?= e(implode(' · ', array_filter([$emp['segmento'] ?? '', 'CNPJ ' . Cnpj::formatar($emp['cnpj'])]))) ?></p>
        <p><?= e($emp['endereco']) ?></p>
      </div>
      <a class="vb-btn" href="<?= url('empresa', ['id' => $emp['id']]) ?>">Ver empresa</a>
    </div>
  </section>
</article>

<?php if ($relacionadas): ?>
<section class="vb-section" aria-labelledby="vb-related">
  <h2 id="vb-related" class="vb-section-title">Vagas parecidas</h2>
  <div class="vb-cards"><?php foreach ($relacionadas as $v) require __DIR__ . '/../partials/job-card.php'; ?></div>
</section>
<?php endif; ?>
</div>
<?php require __DIR__ . '/../footer.php'; ?>
