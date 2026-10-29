<?php if (!$vagas): ?>
<div class="vg-tv-empty">
    <h1>Vagas de Est├ígio</h1>
    <p>Nenhuma vaga em destaque ou com melhor sal├írio no momento.</p>
</div>
<?php else: foreach ($vagas as $i => $v): [$pt] = selo_prazo($v);
        $link = url_vaga_abs((int) $v['id']); ?>
<section class="vg-tv-slide <?= $i === 0 ? 'is-active' : '' ?>">
    <div class="vg-tv-media"><?= vaga_imagem($v) ?></div>
    <div class="vg-tv-body">
        <div class="vg-tv-info">
            <p class="vg-tv-tag"><?= e($v['tipo']) ?> ┬À IFSP Guarulhos</p>
            <h1><?= e($v['titulo']) ?></h1>
            <h2><?= e($v['empresa']['nome']) ?></h2>
            <p class="vg-tv-text"><?= e($v['resumo']) ?></p>
            <ul>
                <li><?= e($v['modalidade']) ?> ┬À <?= e($v['local']) ?></li>
                <li><?= e($v['tipo']) ?> ┬À
                    <?= e($v['bolsa_txt']) ?><?= $v['carga_txt'] ? ' ┬À ' . e($v['carga_txt']) : '' ?></li>
                <li>Inscri├º├Áes at├® <?= data_br($v['data_limite']) ?> (<?= e($pt) ?>)</li>
            </ul>
            <p class="vg-tv-code">C├│digo <strong><?= e($v['codigo']) ?></strong> ┬À Aponte a c├ómera para abrir a vaga
            </p>
        </div>
        <div class="vg-tv-qr">
            <div data-qr="<?= e($link) ?>" class="vg-tv-qrimg"></div>
            <small><?= e(preg_replace('#^https?://#', '', $link)) ?></small>
            <img class="vg-tv-logo" src="<?= BASE_URL ?>public/img/ifsp/if.png"
                alt="Instituto Federal de S├úo Paulo - Campus Guarulhos">
        </div>
    </div>
</section>
<?php endforeach;
endif; ?>