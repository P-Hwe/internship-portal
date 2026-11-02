<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>Modo TV · Vagas de Estágio · IFSP Guarulhos</title>
  <link rel="icon" href="<?= BASE_URL ?>public/img/ifsp/if.png">
  <link rel="stylesheet" href="<?= BASE_URL ?>public/css/vagas.css?v=<?= @filemtime(ROOT_PATH . '/public/css/vagas.css') ?>">
  <link rel="stylesheet" href="<?= BASE_URL ?>public/css/tv.css?v=<?= @filemtime(ROOT_PATH . '/public/css/tv.css') ?>">
</head>
<body class="tv" data-tv>
  <header class="tv-top">
    <div class="tv-brand">
      <img src="<?= BASE_URL ?>public/img/ifsp/if.png" alt="" width="34" height="34">
      <div><strong>Vagas de estágio e emprego</strong><span>IFSP · Câmpus Guarulhos</span></div>
    </div>
    <div class="tv-clock"><span data-tv-time>--:--</span><small data-tv-date></small></div>
  </header>

  <?php if (!$vagas): ?>
    <main class="tv-empty">
      <h1>Nenhuma vaga aberta no momento</h1>
      <p>As novas vagas aprovadas pela Coordenação aparecem aqui automaticamente.</p>
    </main>
  <?php else: ?>
    <main class="tv-stage">
      <?php foreach ($vagas as $i => $v): [$deadlineLabel] = selo_prazo($v); $link = url_vaga_abs((int) $v['id']); $image = url_upload($v['imagem'] ?? null); ?>
      <section class="tv-slide <?= $i === 0 ? 'is-active' : '' ?>" data-title="<?= e($v['titulo'] . ' · ' . $v['empresa']['nome']) ?>" aria-roledescription="slide" aria-label="<?= $i + 1 ?> de <?= count($vagas) ?>">
        <div class="tv-media<?= $image ? ' has-img' : '' ?>"<?= $image ? ' style="--bn:url(\'' . e($image) . '\')"' : '' ?>><?= vaga_imagem($v) ?></div>
        <div class="tv-info">
          <div class="tv-head">
          <div class="tv-tags">
            <span class="tv-tag tv-tag-type"><?= e($v['tipo']) ?></span>
            <?php if (!empty($v['destaque'])): ?><span class="tv-tag tv-tag-star">Destaque</span><?php endif; ?>
            <?php if (!empty($v['melhor_salario'])): ?><span class="tv-tag">Melhor remuneração</span><?php endif; ?>
          </div>
          <h1><?= e($v['titulo']) ?></h1>
          <div class="tv-company"><?= Ui::logo($v['empresa'], 48) ?><span><?= e($v['empresa']['nome']) ?></span></div>
          </div>
          <dl class="tv-facts">
            <div><dt><?= $v['tipo'] === 'Estágio' ? 'Bolsa-auxílio' : 'Salário' ?></dt><dd><?= e($v['bolsa_txt']) ?></dd></div>
            <div><dt>Modalidade</dt><dd><?= e($v['modalidade']) ?></dd></div>
            <div><dt>Local</dt><dd><?= e($v['local']) ?></dd></div>
            <div><dt>Inscrições até</dt><dd><?= data_br($v['data_limite']) ?> <small><?= e($deadlineLabel) ?></small></dd></div>
          </dl>
          <div class="tv-qr">
            <div data-qr="<?= e($link) ?>" class="tv-qr-img"></div>
            <div>
              <strong>Aponte a câmera do celular</strong>
              <span>para ver a vaga e se candidatar</span>
              <small>Código <?= e($v['codigo']) ?></small>
            </div>
          </div>
        </div>
      </section>
      <?php endforeach; ?>
    </main>
    <footer class="tv-bottom">
      <div class="tv-progress"><i data-tv-progress></i></div>
      <div class="tv-bottom-row">
        <span class="tv-next" data-tv-next></span>
        <span class="tv-count" data-tv-count></span>
      </div>
    </footer>
  <?php endif; ?>
  <script src="<?= BASE_URL ?>public/js/vendor/qrcode.js"></script>
  <script src="<?= BASE_URL ?>public/js/vagas.js?v=<?= @filemtime(ROOT_PATH . '/public/js/vagas.js') ?>"></script>
</body>
</html>
