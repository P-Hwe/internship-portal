<?php
$authContext = ($ambiente ?? '') === 'admin' ? 'admin' : 'empresa';
$pitch = $authContext === 'admin'
    ? ['Coordenação de estágios', 'Analise empresas e vagas, acompanhe quem visita o mural e mantenha o histórico de cada decisão.']
    : ['Vagas para estudantes do IFSP', 'Cadastre a sua empresa, publique vagas e acompanhe a análise da Coordenação do câmpus.'];
?><!DOCTYPE html>
<html lang="pt-br"><head><?php require __DIR__ . '/head.php'; ?><title><?= e($titulo ?? 'ESTAGIA+') ?> · ESTAGIA+</title></head>
<body class="auth-body">
  <aside class="auth-aside">
    <?php $brandSub = 'IFSP · Câmpus Guarulhos'; require __DIR__ . '/../partials/brand.php'; ?>
    <div class="auth-ifsp"><?php $ifspMark = 'vertical'; $ifspTone = 'branco'; require __DIR__ . '/../partials/ifsp-mark.php'; ?></div>
    <div class="auth-pitch">
      <h2><?= e($pitch[0]) ?></h2>
      <p><?= e($pitch[1]) ?></p>
      <a class="auth-guide" href="<?= e(url('guia')) ?>#<?= $authContext === 'admin' ? 'coordenacao' : 'empresa' ?>"><?= icon('info', 17) ?> Guia de primeiro acesso</a>
    </div>
    <footer>Instituto Federal de São Paulo · Câmpus Guarulhos · <a href="<?= url('sobre') ?>">Sobre e créditos</a></footer>
  </aside>
  <div class="auth-panel">
    <div class="auth-top">
      <?php $brandSub = 'IFSP Guarulhos'; require __DIR__ . '/../partials/brand.php'; ?>
      <a class="ui-btn ui-btn-sm" href="<?= url() ?>"><?= icon('briefcase', 15) ?> Ver vagas</a>
      <button type="button" class="app-icon-btn" data-theme-toggle aria-label="Ativar modo noturno"><?= icon('moon', 18) ?></button>
    </div>
    <main class="auth-wrap <?= !empty($larga) ? 'is-wide' : '' ?>">
      <?php foreach ($_SESSION['flash'] ?? [] as [$t, $m]): ?><div class="ui-alert ui-alert-<?= e($t) ?>" role="status"><div><?= e($m) ?></div></div><?php endforeach; unset($_SESSION['flash']); ?>
      <?= $conteudo ?>
    </main>
    <a class="auth-guide-foot" href="<?= e(url('guia')) ?>#<?= $authContext === 'admin' ? 'coordenacao' : 'empresa' ?>"><?= icon('info', 15) ?> Guia de primeiro acesso</a>
    <div class="auth-ifsp-foot"><?php $ifspMark = 'horizontal'; $ifspTone = 'cor'; require __DIR__ . '/../partials/ifsp-mark.php'; ?></div>
  </div>
  <script src="<?= e(BASE_URL) ?>public/js/app.js?v=<?= (int) @filemtime(ROOT_PATH . '/public/js/app.js') ?>"></script>
</body></html>
