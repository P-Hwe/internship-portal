<?php
$ehAdmin = $area === 'admin';
$nav = $ehAdmin ? [
    ['admin', 'Início', 'grid', 'dashboard'], ['admin-aprovacoes', 'Aprovações', 'check', 'aprovacoes'], ['admin-vagas', 'Vagas', 'briefcase', 'vagas'],
    ['admin-empresas', 'Empresas', 'building', 'empresas'], ['admin-desempenho', 'Desempenho', 'trending', 'desempenho'], ['admin-usuarios', 'Usuários', 'users', 'usuarios'],
    ['admin-relatorios', 'Relatórios', 'chart', 'relatorios'],
] : array_values(array_filter([
    ['painel', 'Visão geral', 'grid', 'painel'], ['empresa-vagas', 'Minhas vagas', 'briefcase', 'vagas'],
    ($emp['status'] ?? '') === 'aprovada' ? ['vaga-cadastro', 'Publicar vaga', 'plus', 'publicar'] : null, ['empresa-perfil', 'Perfil da empresa', 'building', 'perfil'],
]));
$pessoa = $ehAdmin ? ($adm['nome'] ?? 'Administrador') : ($emp['nome'] ?? 'Empresa');
$sub = $ehAdmin ? 'Coordenação de estágios' : 'Área da empresa';
?><!DOCTYPE html>
<html lang="pt-br"><head><?php require __DIR__ . '/head.php'; ?><title><?= e($titulo ?? '') ?> · <?= e($sub) ?> · ESTAGIA+</title></head>
<body class="app-body">
<div class="app-shell">
  <aside class="app-side" id="menu" aria-label="Menu principal">
    <a class="app-brand" href="<?= url($ehAdmin ? 'admin' : 'painel') ?>">ESTAGIA<b>+</b><small><?= e($sub) ?> · IFSP Guarulhos</small></a>
    <nav class="app-nav">
      <?php foreach ($nav as [$rota, $rot, $ic, $chave]): $on = ($secao ?? '') === $chave || ($chave === 'publicar' && ($secao ?? '') === 'publicar'); ?>
        <a href="<?= url($rota) ?>" class="<?= $on ? 'is-active' : '' ?>"<?= $on ? ' aria-current="page"' : '' ?>><?= icon($ic, 18) ?> <?= e($rot) ?>
          <?php if ($chave === 'aprovacoes' && !empty($pendentes)): ?><span class="ui-count"><?= (int) $pendentes ?></span><?php endif; ?></a>
      <?php endforeach; ?>
      <div class="app-nav-sep"></div>
      <a href="<?= url() ?>" target="_blank" rel="noopener"><?= icon('external', 18) ?> Ver site de vagas</a>
    </nav>
    <div class="app-side-foot">
      <span>Conectado como <strong class="app-user-name"><?= e($pessoa) ?></strong></span>
      <form method="post" action="<?= url('sair') ?>"><?= csrf_field() ?><input type="hidden" name="perfil" value="<?= $ehAdmin ? 'admin' : 'empresa' ?>">
        <button class="ui-btn ui-btn-sm ui-btn-block" type="submit"><?= icon('logout', 16) ?> Sair</button></form>
    </div>
  </aside>
  <div class="app-scrim"></div>
  <div class="app-main">
    <header class="app-top">
      <button type="button" class="app-icon-btn app-menu-btn" data-menu-open aria-controls="menu" aria-label="Abrir menu"><?= icon('menu', 20) ?></button>
      <h1><?= e($titulo ?? '') ?></h1>
      <?php if ($ehAdmin) { $naoLidas = $naoLidas ?? Notification::naoLidas(); $notifs = $notifs ?? Notification::recentes(8); } ?>
      <?php if ($ehAdmin && isset($naoLidas)): ?>
        <details class="ui-bell"><summary class="app-icon-btn" aria-label="Notificações: <?= (int) $naoLidas ?> novas"><?= icon('bell', 18) ?><?php if ($naoLidas): ?><span class="ui-bell-n"><?= (int) $naoLidas ?></span><?php endif; ?></summary>
          <div class="ui-pop"><strong>Notificações</strong>
            <?php foreach ($notifs ?? [] as $n): [$stRot, $stTom, $feita] = Notification::estado($n); ?>
              <a class="ui-notif <?= $n['lida'] ? '' : 'is-new' ?> <?= $feita ? 'is-done' : '' ?>" href="<?= url('admin-notificacao', ['id' => (int) $n['id']]) ?>">
                <span><?= e($n['mensagem']) ?></span>
                <small><?= e(date('d/m H:i', strtotime($n['criado_em']))) ?> · <?= $feita ? 'Concluída' : 'Pendente' ?> <?= Ui::badge($stRot, $stTom) ?></small></a>
            <?php endforeach; ?>
            <?php if (empty($notifs)): ?><p>Nada por aqui ainda.</p><?php endif; ?>
            <?php if ($naoLidas): ?><form method="post" action="<?= url('admin-acao') ?>"><?= csrf_field() ?><input type="hidden" name="tipo" value="notificacoes"><button class="ui-link-btn" type="submit">Marcar todas como lidas</button></form><?php endif; ?></div></details>
      <?php endif; ?>
      <button type="button" class="app-icon-btn" data-theme-toggle aria-label="Ativar modo noturno"><?= icon('moon', 18) ?></button>
      <div class="app-user"><strong><?= e($pessoa) ?></strong><?= $ehAdmin ? e($adm['email'] ?? '') : e($emp['email'] ?? '') ?></div>
    </header>
    <main class="app-content" id="conteudo">
      <?php foreach ($_SESSION['flash'] ?? [] as [$t, $m]): ?><div class="ui-alert ui-alert-<?= e($t) ?>" role="status"><div><?= e($m) ?></div></div><?php endforeach; unset($_SESSION['flash']); ?>
      <?php if (!empty($lead)): ?><p class="app-lead"><?= e($lead) ?><?php if (!empty($leadLink)): ?> <a href="<?= e($leadLink[1]) ?>"><?= e($leadLink[0]) ?></a><?php endif; ?></p><?php endif; ?>
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
<?php
$idleMs = (int) env('SESSION_IDLE_' . strtoupper($area === 'admin' ? 'ADMIN' : 'EMPRESA'), $area === 'admin' ? 1800 : 7200) * 1000;
$heartbeatUrl = e(BASE_URL . 'public/heartbeat.php');
$csrfToken = e(Csrf::token());
$loginUrl = e(url($area === 'admin' ? 'admin-login' : 'empresa-login'));
?>
<script>
(function() {
  var IDLE_MS = <?= $idleMs ?>;
  var HEARTBEAT_URL = '<?= $heartbeatUrl ?>';
  var CSRF = '<?= $csrfToken ?>';
  var LOGIN_URL = '<?= $loginUrl ?>';
  var BEAT_INTERVAL = 4 * 60 * 1000; 
  var WARN_BEFORE = 60 * 1000;

  var lastActivity = Date.now();
  var warnTimer = null;
  var logoutTimer = null;
  var beatTimer = null;
  var warned = false;

  function resetTimers() {
    lastActivity = Date.now();
    warned = false;
    clearTimeout(warnTimer);
    clearTimeout(logoutTimer);

    warnTimer = setTimeout(showWarning, IDLE_MS - WARN_BEFORE);
    logoutTimer = setTimeout(doLogout, IDLE_MS);
  }

  function showWarning() {
    if (!warned) {
      warned = true;
      if (window.confirm('Sua sessão vai expirar em 1 minuto por inatividade. Clique OK para continuar conectado.')) {
        resetTimers();
        sendHeartbeat();
      }
    }
  }

  function doLogout() {
    window.location.href = LOGIN_URL + '?timeout=1';
  }

  function sendHeartbeat() {
    var fd = new FormData();
    fd.append('_csrf', CSRF);
    fetch(HEARTBEAT_URL, { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (!data.ok && data.reason === 'unauthenticated') {
          window.location.href = LOGIN_URL;
        }
      })
      .catch(function() {  });
  }

  ['mousemove','keydown','mousedown','touchstart','scroll','click'].forEach(function(ev) {
    document.addEventListener(ev, function() {
      resetTimers();
    }, { passive: true });
  });

  function startBeat() {
    beatTimer = setInterval(function() {
      if (!document.hidden) {
        sendHeartbeat();
      }
    }, BEAT_INTERVAL);
  }

  document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
      resetTimers();
      sendHeartbeat();
    }
  });

  resetTimers();
  startBeat();
})();
</script>
</body></html>
