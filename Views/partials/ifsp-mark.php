<?php
/** Marca oficial do IFSP Câmpus Guarulhos (Manual de Aplicação da Marca IF, 2015). $ifspMark: 'horizontal' ou 'vertical';
 *  $ifspTone: 'cor' (fundo claro), 'branco' (fundo escuro ou verde) ou 'auto' (cor no tema claro, branco no escuro). */
$ifspMark = $ifspMark ?? 'horizontal';
$ifspTone = $ifspTone ?? 'auto';
$ifspSize = $ifspMark === 'vertical' ? [147, 210] : [323, 90];
$ifspSrc  = fn(string $tone) => BASE_URL . 'public/img/ifsp/marca/ifsp-gru-' . $ifspMark . '-' . $tone . '.png?v=' . (int) @filemtime(ROOT_PATH . '/public/img/ifsp/marca/ifsp-gru-' . $ifspMark . '-' . $tone . '.png');
?>
<span class="ifsp-mark is-<?= e($ifspMark) ?><?= $ifspTone === 'auto' ? ' is-auto' : '' ?>">
  <?php if ($ifspTone === 'auto'): ?>
  <img class="ifsp-mark-light" src="<?= e($ifspSrc('cor')) ?>" alt="Instituto Federal de São Paulo, Câmpus Guarulhos" width="<?= $ifspSize[0] ?>" height="<?= $ifspSize[1] ?>">
  <img class="ifsp-mark-dark" src="<?= e($ifspSrc('branco')) ?>" alt="" width="<?= $ifspSize[0] ?>" height="<?= $ifspSize[1] ?>">
  <?php else: ?>
  <img src="<?= e($ifspSrc($ifspTone)) ?>" alt="Instituto Federal de São Paulo, Câmpus Guarulhos" width="<?= $ifspSize[0] ?>" height="<?= $ifspSize[1] ?>">
  <?php endif; ?>
</span>
