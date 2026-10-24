<?php require __DIR__ . '/header.php';
$ativa = 'empresas';
require __DIR__ . '/partials/nav.php'; ?>
<div class="vg-hero">
    <h1>Empresas parceiras</h1>
    <p>Conheça as empresas que divulgam oportunidades para os estudantes do campus. Clique em uma empresa para conhecer
        o site ou o LinkedIn dela.</p>
</div>
<?php foreach ($trilhos as $t): ?>
    <section class="vg-rail" data-rail aria-labelledby="rail-<?= e($t['id']) ?>">
        <div class="vg-rail-head">
            <div>
                <h2 id="rail-<?= e($t['id']) ?>"><?= icon('building', 20) ?> <?= e($t['titulo']) ?></h2>
                <p><?= e($t['sub']) ?></p>
            </div>
            <div class="vg-rail-ctrl"><button type="button" class="vg-rail-btn" data-rail-prev
                    aria-label="Voltar"><?= icon('chevron-left', 20) ?></button><button type="button" class="vg-rail-btn"
                    data-rail-next aria-label="Avançar"><?= icon('chevron-right', 20) ?></button></div>
        </div>
        <div class="vg-rail-track" data-rail-track tabindex="0">
            <?php foreach ($t['itens'] as $e) require __DIR__ . '/partials/card_empresa_publico.php'; ?></div>
    </section>
<?php endforeach; ?>
<section aria-labelledby="todas-emp">
    <div class="vg-list-head">
        <h2 id="todas-emp">Todas as empresas <span class="vg-count"><?= count($todas) ?></span></h2>
    </div>
    <?php if (!$todas): ?><p class="vg-empty">Ainda não há empresas parceiras aprovadas.</p><?php else: ?>
        <div class="vg-grid"><?php foreach ($todas as $e) require __DIR__ . '/partials/card_empresa_publico.php'; ?></div>
    <?php endif; ?>
</section>
</div>
<?php require __DIR__ . '/footer.php'; ?>
