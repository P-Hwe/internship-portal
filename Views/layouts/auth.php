<?php
$authContext = ($ambiente ?? '') === 'admin' ? 'admin' : 'empresa';
$pitch = $authContext === 'admin'
    ? ['Coordenação de estágios', 'Analise empresas e vagas, acompanhe quem visita o mural e mantenha o histórico de cada decisão.']
    : ['Vagas para estudantes do IFSP', 'Cadastre a sua empresa, publique vagas e acompanhe a análise da Coordenação do câmpus.'];
$glyphOn = ['1-1' => 1, '1-2' => 1, '2-0' => 1, '2-1' => 1, '3-0' => 1, '3-1' => 1, '3-2' => 1, '4-0' => 1, '4-1' => 1];
?><!DOCTYPE html>
<html lang="pt-br"><head><?php require __DIR__ . '/head.php'; ?><title><?= e($titulo ?? 'ESTAGIA+') ?> · ESTAGIA+</title></head>
<body class="auth-body">
  <aside class="auth-aside">
    <?php $brandSub = 'IFSP · Câmpus Guarulhos'; require __DIR__ . '/../partials/brand.php'; ?>
    <svg class="auth-glyph" viewBox="0 0 252 252" aria-hidden="true" focusable="false">
      <?php for ($row = 0; $row < 5; $row++): for ($col = 0; $col < 5; $col++): $x = $col * 52; $y = $row * 52; ?>
        <?php if ($row === 1 && $col === 0): ?><circle class="dot" cx="<?= $x + 22 ?>" cy="<?= $y + 22 ?>" r="22"/>
        <?php else: ?><rect class="sq<?= isset($glyphOn[$row . '-' . $col]) ? ' on' : '' ?>" x="<?= $x ?>" y="<?= $y ?>" width="44" height="44" rx="7"/><?php endif; ?>
      <?php endfor; endfor; ?>
    </svg>
    <div class="auth-pitch">
      <h2><?= e($pitch[0]) ?></h2>
      <p><?= e($pitch[1]) ?></p>
    </div>
    <footer>Instituto Federal de São Paulo · Câmpus Guarulhos</footer>
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
  </div>
  <script src="<?= e(BASE_URL) ?>public/js/app.js?v=<?= (int) @filemtime(ROOT_PATH . '/public/js/app.js') ?>"></script>
</body></html>
