<?php require __DIR__ . '/../header.php'; $ativa = 'empresas'; require __DIR__ . '/../partials/nav.php'; $ext = Company::linkExterno($e); ?>
<nav class="vb-crumbs" aria-label="Você está aqui"><a href="<?= url('empresas') ?>">Empresas parceiras</a><span aria-hidden="true">/</span><span><?= e($e['nome']) ?></span></nav>

<div class="vb-panel vb-org-page">
  <div class="vb-org-cover<?= empresa_capa_attr($e) ?>"><?= empresa_banner($e) ?></div>
  <div class="vb-org-identity">
    <span class="vb-org-avatar"><?= Ui::logo($e, 88) ?></span>
    <div class="vb-org-name">
      <h1><?= e($e['nome']) ?></h1>
      <p><?= e(implode(' · ', array_filter([$e['segmento'] ?? '', $e['porte'] ?? '', $e['cidade'] ? $e['cidade'] . '/' . $e['uf'] : '']))) ?></p>
    </div>
    <?php if ($ext): ?><a class="vb-btn vb-btn-primary" href="<?= url('externo', ['id' => $e['id']]) ?>"><?= icon('external', 15) ?> <?= $e['link_principal'] === 'linkedin' && $e['linkedin'] ? 'LinkedIn da empresa' : 'Site da empresa' ?></a><?php endif; ?>
  </div>
  <dl class="vb-org-stats">
    <div><dt>Vagas abertas</dt><dd><?= (int) $e['vagas_abertas'] ?></dd></div>
    <div><dt>Vagas publicadas</dt><dd><?= (int) $e['total_vagas'] ?></dd></div>
    <div><dt>Contato</dt><dd><?= $e['email_contato'] ? '<a href="mailto:' . e($e['email_contato']) . '">' . e($e['email_contato']) . '</a>' : 'Não informado' ?></dd></div>
  </dl>
</div>

<?php if ($e['descricao']): ?>
<section class="vb-panel vb-prose"><h2>Sobre</h2><p><?= nl2br(e($e['descricao'])) ?></p></section>
<?php endif; ?>

<section class="vb-section" aria-labelledby="vb-org-jobs">
  <h2 id="vb-org-jobs" class="vb-section-title">Vagas abertas <span class="vb-count"><?= count($vagas) ?></span></h2>
  <?php if (!$vagas): ?>
    <div class="vb-empty"><strong>Nenhuma vaga aberta agora</strong><p>As próximas vagas desta empresa aparecem aqui.</p></div>
  <?php else: ?>
    <div class="vb-cards"><?php foreach ($vagas as $v) require __DIR__ . '/../partials/job-card.php'; ?></div>
  <?php endif; ?>
</section>
</div>
<?php require __DIR__ . '/../footer.php'; ?>
