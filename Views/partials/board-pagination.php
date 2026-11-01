<?php
if ($pgTotal <= 1) return;
$pgLink = fn(int $n) => e(url($pgRoute, array_merge($pgParams, ['pg' => $n])));
$pgFrom = max(1, $pgCur - 2);
$pgTo = min($pgTotal, $pgCur + 2);
?>
<nav class="vb-pages" aria-label="Paginação">
  <?php if ($pgCur > 1): ?><a href="<?= $pgLink($pgCur - 1) ?>" rel="prev"><?= icon('chevron-left', 16) ?> Anterior</a><?php else: ?><span class="is-off"><?= icon('chevron-left', 16) ?> Anterior</span><?php endif; ?>
  <?php if ($pgFrom > 1): ?><a href="<?= $pgLink(1) ?>">1</a><?php if ($pgFrom > 2): ?><span class="is-gap">…</span><?php endif; ?><?php endif; ?>
  <?php for ($n = $pgFrom; $n <= $pgTo; $n++): ?>
    <?php if ($n === $pgCur): ?><span class="is-cur" aria-current="page"><?= $n ?></span><?php else: ?><a href="<?= $pgLink($n) ?>"><?= $n ?></a><?php endif; ?>
  <?php endfor; ?>
  <?php if ($pgTo < $pgTotal): ?><?php if ($pgTo < $pgTotal - 1): ?><span class="is-gap">…</span><?php endif; ?><a href="<?= $pgLink($pgTotal) ?>"><?= $pgTotal ?></a><?php endif; ?>
  <?php if ($pgCur < $pgTotal): ?><a href="<?= $pgLink($pgCur + 1) ?>" rel="next">Próxima <?= icon('chevron-right', 16) ?></a><?php else: ?><span class="is-off">Próxima <?= icon('chevron-right', 16) ?></span><?php endif; ?>
</nav>
