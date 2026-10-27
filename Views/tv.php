<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Vagas de Estágio - IFSP Guarulhos (Modo TV)</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>public/css/vagas.css">
</head>

<body class="vg-tv" data-tv>
  <?php if (!$vagas): ?>
    <div class="vg-tv-empty">
      <h1>Vagas de Estágio</h1>
      <p>Nenhuma vaga aberta no momento.</p>
    </div>
    <?php else: foreach ($vagas as $i => $v): [$pt] = selo_prazo($v); ?>
      <section class="vg-tv-slide <?= $i === 0 ? 'is-active' : '' ?>">
        <div class="vg-tv-media"><?= vaga_imagem($v) ?></div>
        <div class="vg-tv-info">
          <p class="vg-tv-tag"><?= e($v['tipo']) ?> · IFSP Guarulhos</p>
          <h1><?= e($v['titulo']) ?></h1>
          <h2><?= e($v['empresa']['nome']) ?></h2>
          <p class="vg-tv-text"><?= e($v["resumo"]) ?></p>
          <ul>
            <li><?= e($v['modalidade']) ?> · <?= e($v['local']) ?></li>
            <li><?= e($v['tipo']) ?> ·
              <?= e($v['bolsa_txt']) ?><?= $v['carga_txt'] ? ' · ' . e($v['carga_txt']) : '' ?></li>
            <li>Inscrições até <?= data_br($v['data_limite']) ?> (<?= e($pt) ?>)</li>
          </ul>
          <div class="vg-tv-foot">
            <p class="vg-tv-code">Código <strong><?= e($v['codigo']) ?></strong><br>Aponte a câmera para abrir a
              vaga</p>
            <div class="vg-tv-qr">
              <div data-qr="<?= e(url_vaga_abs((int) $v['id'])) ?>" class="vg-tv-qrimg"></div>
              <small><?= e(preg_replace('#^https?://#', '', url_vaga_abs((int) $v['id']))) ?></small>
            </div>
          </div>
        </div>
      </section>
  <?php endforeach;
  endif; ?>
  <div class="vg-tv-bar"><span data-tv-count></span><span>Tecla F: tela cheia</span></div>
  <script src="<?= BASE_URL ?>public/js/vendor/qrcode.js"></script>
  <script src="<?= BASE_URL ?>public/js/vagas.js"></script>
</body>

</html>
