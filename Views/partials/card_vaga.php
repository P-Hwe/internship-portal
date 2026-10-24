<?php
// Espera $v. Opcional: $chave (selo prioritário do carrossel) e $trilho (true = card de carrossel)
$chave = $chave ?? null;
$trilho = $trilho ?? false;
$selos = $v['selos'] ?? [];
if ($chave && isset($selos[$chave])) {
    $selos = [$chave => $selos[$chave]] + $selos;
}
$selos = array_slice($selos, 0, 3, true);
$prazo = selo_prazo($v);
?>
<article class="vg-card <?= $trilho ? 'vg-card-trilho' : '' ?>" data-tipo="<?= e($v['tipo']) ?>"
    data-area="<?= e($v['area_exibicao'] ?? $v['area']) ?>" data-modalidade="<?= e($v['modalidade']) ?>"
    data-busca="<?= e($v['titulo'] . ' ' . $v['empresa']['nome'] . ' ' . $v['resumo'] . ' ' . $v['area'] . ' ' . $v['tipo'] . ' ' . $v['local']) ?>">
    <div class="vg-card-media">
        <?= vaga_imagem($v) ?>
        <ul class="vg-tags-print" aria-label="Destaques da vaga">
            <?php foreach ($selos as $k => [$txt, $cls]): ?><li class="vg-tag <?= e($cls) ?>"><?= e($txt) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="vg-card-body">
        <div class="vg-card-emp"><span class="vg-avatar"
                aria-hidden="true"><?= e(iniciais($v['empresa']['nome'])) ?></span><span
                class="vg-card-company"><?= e($v['empresa']['nome']) ?></span></div>
        <h3 class="vg-card-title"><a class="vg-stretched"
                href="<?= url('vaga', ['id' => $v['id']]) ?>"><?= e($v['titulo']) ?></a></h3>
        <ul class="vg-meta">
            <li><?= icon('pin', 15) ?> <?= e($v['local']) ?> · <?= e($v['modalidade']) ?></li>
            <li class="vg-meta-money"><?= icon('money', 15) ?> <?= e($v['bolsa_txt']) ?></li>
            <li><?= icon('clock', 15) ?> <?= e($prazo[0]) ?></li>
        </ul>
        <?php if (!$trilho): ?><p class="vg-card-text"><?= e($v['resumo']) ?></p><?php endif; ?>
        <p class="vg-card-area"><?= e($v['area_exibicao'] ?? $v['area']) ?></p>
    </div>
</article>
