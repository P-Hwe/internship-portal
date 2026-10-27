<?php
$ehAdmin = $area === 'admin';
$nav = $ehAdmin ? [
    ['admin', 'Dashboard', 'grid', 'dashboard'], ['admin-aprovacoes', 'Aprovações', 'check', 'aprovacoes'], ['admin-vagas', 'Vagas', 'briefcase', 'vagas'],
    ['admin-empresas', 'Empresas', 'building', 'empresas'], ['admin-usuarios', 'Usuários', 'users', 'usuarios'], ['admin-acesso', 'Controle de acesso', 'shield', 'acesso'],
    ['admin-relatorios', 'Relatórios', 'chart', 'relatorios'], ['admin-config', 'Configurações', 'settings', 'config'],
] : array_values(array_filter([
    ['painel', 'Visão geral', 'grid', 'painel'], ['empresa-vagas', 'Minhas vagas', 'briefcase', 'vagas'],
    ($emp['status'] ?? '') === 'aprovada' ? ['vaga-cadastro', 'Publicar vaga', 'plus', 'publicar'] : null, ['empresa-perfil', 'Perfil da empresa', 'building', 'perfil'],
]));
$pessoa = $ehAdmin ? ($adm['nome'] ?? 'Administrador') : ($emp['nome'] ?? 'Empresa');
$sub = $ehAdmin ? 'Administração' : 'Área da empresa';
?><!DOCTYPE html>
<html lang="pt-br"><head><?php require __DIR__ . '/head.php'; ?><title><?= e($titulo ?? '') ?> · <?= e($sub) ?> · ESTAGIA+</title></head>
<body class="app-body">
<div class="app-shell">
  <aside class="app-side" id="menu" aria-label="Menu principal">
    <a class="app-brand" href="<?= url($ehAdmin ? 'admin' : 'painel') ?>"><span class="app-brand-mark">E+</span><span>ESTAGIA+<small><?= e($sub) ?></small></span></a>
    <nav class="app-nav">
      <?php foreach ($nav as [$rota, $rot, $ic, $chave]): $on = ($secao ?? '') === $chave || ($chave === 'publicar' && ($secao ?? '') === 'publicar'); ?>
        <a href="<?= url($rota) ?>" class="<?= $on ? 'is-active' : '' ?>"<?= $on ? ' aria-current="page"' : '' ?>><?= icon($ic, 18) ?> <?= e($rot) ?>
          <?php if ($chave === 'aprovacoes' && !empty($pendentes)): ?><span class="ui-count"><?= (int) $pendentes ?></span><?php endif; ?></a>
      <?php endforeach; ?>
      <div class="app-nav-sep"></div>
      <a href="<?= url() ?>"><?= icon('external', 18) ?> Ver site de vagas</a>
    </nav>
    <div class="app-side-foot">
      <strong style="color:var(--text)"><?= e($pessoa) ?></strong>
      <form method="post" action="<?= url('sair') ?>"><?= csrf_field() ?><input type="hidden" name="perfil" value="<?= $ehAdmin ? 'admin' : 'empresa' ?>">
        <button class="ui-btn ui-btn-sm ui-btn-block" type="submit"><?= icon('logout', 16) ?> Sair</button></form>
    </div>
  </aside>
  <div class="app-scrim"></div>
  <div class="app-main">
    <header class="app-top">
      <button type="button" class="app-icon-btn app-menu-btn" data-menu-open aria-controls="menu" aria-label="Abrir menu"><?= icon('menu', 20) ?></button>
      <h1><?= e($titulo ?? '') ?></h1>
      <?php if ($ehAdmin && isset($naoLidas)): ?>
        <details class="ui-bell"><summary class="app-icon-btn" aria-label="Notificações: <?= (int) $naoLidas ?> novas"><?= icon('bell', 18) ?><?php if ($naoLidas): ?><span class="ui-bell-n"><?= (int) $naoLidas ?></span><?php endif; ?></summary>
          <div class="ui-pop"><strong>Notificações</strong>
            <?php foreach ($notifs ?? [] as $n): ?><p class="<?= $n['lida'] ? '' : 'is-new' ?>"><span><?= e($n['mensagem']) ?></span><small><?= e(date('d/m H:i', strtotime($n['criado_em']))) ?></small></p><?php endforeach; ?>
            <?php if (empty($notifs)): ?><p>Nada por aqui ainda.</p><?php endif; ?>
            <?php if ($naoLidas): ?><form method="post" action="<?= url('admin-acao') ?>" style="margin-top:8px"><?= csrf_field() ?><input type="hidden" name="tipo" value="notificacoes"><button class="ui-link-btn" type="submit">Marcar todas como lidas</button></form><?php endif; ?></div></details>
      <?php endif; ?>
      <button type="button" class="app-icon-btn" data-theme-toggle aria-label="Ativar modo noturno"><?= icon('moon', 18) ?></button>
      <div class="app-user"><strong><?= e($pessoa) ?></strong><?= $ehAdmin ? e($adm['email'] ?? '') : e($emp['email'] ?? '') ?></div>
    </header>
    <main class="app-content" id="conteudo">
      <?php foreach ($_SESSION['flash'] ?? [] as [$t, $m]): ?><div class="ui-alert ui-alert-<?= e($t) ?>" role="status"><div><?= e($m) ?></div></div><?php endforeach;
unset($_SESSION['flash']); ?>
      <?= $conteudo ?>
    </main>
  </div>
</div>
<dialog id="ui-dialog" class="ui-modal" aria-labelledby="ui-dlg-title"><div class="ui-modal-in">
  <h2 id="ui-dlg-title"></h2><p id="ui-dlg-text"></p>
  <textarea id="ui-dlg-motivo" class="ui-textarea" rows="3" hidden placeholder="Descreva o motivo" aria-label="Motivo"></textarea><small class="ui-err" id="ui-dlg-err"></small>
  <div class="ui-modal-actions"><button type="button" class="ui-btn" id="ui-dlg-cancel">Cancelar</button><button type="button" class="ui-btn ui-btn-primary" id="ui-dlg-ok">Confirmar</button></div>
</div></dialog>
<script src="<?= e(BASE_URL) ?>public/js/app.js?v=<?= (int) @filemtime(ROOT_PATH . '/public/js/app.js') ?>"></script>
</body></html>