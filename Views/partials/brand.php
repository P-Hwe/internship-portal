<?php
$brandHref = $brandHref ?? url();
$brandSub  = $brandSub ?? 'IFSP Guarulhos';
$brandLogo = Credits::officialLogo();
?>
<a class="app-brand<?= $brandLogo ? ' has-logo' : '' ?>" href="<?= e($brandHref) ?>">
  <?php if ($brandLogo): ?>
  <img class="app-logo" src="<?= e($brandLogo) ?>" alt="ESTAGIA+" height="40">
  <span class="app-wordmark"><small><?= e($brandSub) ?></small></span>
  <?php else: ?>
  <svg class="app-mark" viewBox="0 0 32 32" aria-hidden="true" focusable="false">
    <rect class="tile" width="32" height="32" rx="9"/>
    <path class="plus" d="M16 8.5v15M8.5 16h15"/>
  </svg>
  <span class="app-wordmark"><b>ESTAGIA<i>+</i></b><small><?= e($brandSub) ?></small></span>
  <?php endif; ?>
</a>
