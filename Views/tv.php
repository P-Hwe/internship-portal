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
        <p>Nenhuma vaga em destaque ou com melhor salário no momento.</p>
    </div>
    <?php else: foreach ($vagas as $i => $v): [$pt] = selo_prazo($v);
            $link = url_vaga_abs((int) $v['id']); ?>
    <section class="vg-tv-slide <?= $i === 0 ? 'is-active' : '' ?>">
        <div class="vg-tv-media"><?= vaga_imagem($v) ?></div>
        <div class="vg-tv-body">
            <div class="vg-tv-info">
                <p class="vg-tv-tag"><?= e($v['tipo']) ?> · IFSP Guarulhos</p>
                <h1><?= e($v['titulo']) ?></h1>
                <h2><?= e($v['empresa']['nome']) ?></h2>
                <p class="vg-tv-text"><?= e($v['resumo']) ?></p>
                <ul>
                    <li><?= e($v['modalidade']) ?> · <?= e($v['local']) ?></li>
                    <li><?= e($v['tipo']) ?> ·
                        <?= e($v['bolsa_txt']) ?><?= $v['carga_txt'] ? ' · ' . e($v['carga_txt']) : '' ?></li>
                    <li>Inscrições até <?= data_br($v['data_limite']) ?> (<?= e($pt) ?>)</li>
                </ul>
                <p class="vg-tv-code">Código <strong><?= e($v['codigo']) ?></strong> · Aponte a câmera para abrir a vaga
                </p>
            </div>
            <div class="vg-tv-qr">
                <div data-qr="<?= e($link) ?>" class="vg-tv-qrimg"></div>
                <small><?= e(preg_replace('#^https?://#', '', $link)) ?></small>
                <img class="vg-tv-logo" src="<?= BASE_URL ?>images/if.png"
                    alt="Instituto Federal de São Paulo - Campus Guarulhos">
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