<!DOCTYPE html>
<html lang="pt-br"><head><?php require __DIR__ . '/head.php'; ?><title><?= e($titulo ?? 'ESTAGIA+') ?> · ESTAGIA+</title></head>
<body class="auth-body">
  <div class="auth-top">
    <a class="app-brand" href="<?= url() ?>">ESTAGIA<b>+</b><small>IFSP Guarulhos</small></a>
    <div class="ui-actions">
      <a class="ui-btn ui-btn-sm" href="<?= url() ?>">Ver vagas</a>
      <button type="button" class="app-icon-btn" data-theme-toggle aria-label="Ativar modo noturno"><?= icon('moon', 18) ?></button>
    </div>
  </div>
  <main class="auth-wrap <?= !empty($larga) ? 'is-wide' : '' ?>">
    <?php foreach ($_SESSION['flash'] ?? [] as [$t, $m]): ?><div class="ui-alert ui-alert-<?= e($t) ?>" role="status"><div><?= e($m) ?></div></div><?php endforeach; unset($_SESSION['flash']); ?>
    <?= $conteudo ?>
  </main>
  <script src="<?= e(BASE_URL) ?>public/js/app.js?v=<?= (int) @filemtime(ROOT_PATH . '/public/js/app.js') ?>"></script>
</body></html>
