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
  <svg class="app-mark" viewBox="0 0 30 39" aria-hidden="true" focusable="false">
    <circle class="dot" cx="4.5" cy="4.5" r="4.5"/>
    <rect class="sq" x="10.5" y="0" width="9" height="9" rx="1.6"/><rect class="sq" x="21" y="0" width="9" height="9" rx="1.6"/>
    <rect class="sq" x="0" y="10" width="9" height="9" rx="1.6"/><rect class="sq" x="10.5" y="10" width="9" height="9" rx="1.6"/>
    <rect class="sq" x="0" y="20" width="9" height="9" rx="1.6"/><rect class="sq" x="10.5" y="20" width="9" height="9" rx="1.6"/><rect class="sq" x="21" y="20" width="9" height="9" rx="1.6"/>
    <rect class="sq" x="0" y="30" width="9" height="9" rx="1.6"/><rect class="sq" x="10.5" y="30" width="9" height="9" rx="1.6"/>
  </svg>
  <span class="app-wordmark"><b>ESTAGIA<i>+</i></b><small><?= e($brandSub) ?></small></span>
  <?php endif; ?>
</a>
