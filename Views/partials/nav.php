<?php $ativa = $ativa ?? 'home'; $perfilAtual = Auth::perfil(); $empLog = $perfilAtual === 'empresa' ? Auth::empresa() : null; ?>
<div id="topo" class="vg">
  <nav class="vg-tabs" aria-label="Navegação">
    <a href="<?= url() ?>" class="<?= $ativa === 'home' ? 'is-active' : '' ?>"><?= icon('briefcase', 16) ?> Vagas</a>
    <a href="<?= url('empresas') ?>" class="<?= $ativa === 'empresas' ? 'is-active' : '' ?>"><?= icon('building', 16) ?> Empresas parceiras</a>
    <?php if ($perfilAtual === 'admin'): ?><a href="<?= url('admin') ?>"><?= icon('lock', 16) ?> Administração</a>
    <?php else: ?><a href="<?= url($empLog ? 'painel' : 'empresa-login') ?>"><?= icon('user', 16) ?> Sou empresa<?= $empLog ? ' (minha área)' : '' ?></a><?php endif; ?>
    <a href="<?= url('tv') ?>" target="_blank" rel="noopener" class="vg-tab-right"><?= icon('tv', 16) ?> Modo TV</a>
  </nav>
  <?php foreach ($_SESSION['flash'] ?? [] as [$t, $m]): ?><div class="vg-flash vg-flash-<?= e($t) ?>" role="status"><?= icon($t === 'erro' ? 'info' : 'check', 18) ?> <span><?= e($m) ?></span></div><?php endforeach; unset($_SESSION['flash']); ?>
