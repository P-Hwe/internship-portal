<?php
$isAdmin = $area === 'admin';
$member = $isAdmin ? null : ($emp['usuario'] ?? null);
$canManage = $member && CompanyMember::canManageUsers($member);
$navGroups = $isAdmin ? [
    'Visão geral' => [['admin', 'Início', 'grid', 'dashboard'], ['admin-aprovacoes', 'Aprovações', 'check', 'aprovacoes']],
    'Gestão'      => [['admin-vagas', 'Vagas', 'briefcase', 'vagas'], ['admin-empresas', 'Empresas', 'building', 'empresas']],
    'Análise'     => [['admin-desempenho', 'Desempenho', 'trending', 'desempenho'], ['admin-relatorios', 'Relatórios', 'chart', 'relatorios']],
    'Equipe'      => [['admin-usuarios', 'Usuários', 'users', 'usuarios']],
] : [
    'Painel'  => [['painel', 'Visão geral', 'grid', 'painel']],
    'Vagas'   => array_values(array_filter([
        ['empresa-vagas', 'Minhas vagas', 'briefcase', 'vagas'],
        ($emp['status'] ?? '') === 'aprovada' ? ['vaga-cadastro', 'Publicar vaga', 'plus', 'publicar'] : null,
    ])),
    'Empresa' => array_values(array_filter([
        ['empresa-perfil', 'Perfil da empresa', 'building', 'perfil'],
        $canManage ? ['empresa-desempenho', 'Desempenho', 'trending', 'desempenho'] : null,
        $canManage ? ['empresa-usuarios', 'Usuários', 'users', 'usuarios'] : null,
    ])),
];
$person = $isAdmin ? ($adm['nome'] ?? 'Administrador') : ($member['nome'] ?? $emp['nome'] ?? 'Empresa');
$personInitials = iniciais(trim((string) preg_replace('/[^\p{L}\s]/u', '', $person)) ?: 'U');
$personEmail = $isAdmin ? ($adm['email'] ?? '') : ($member ? ($emp['nome'] ?? '') . ' · ' . CompanyMember::roleLabel($member['papel']) : ($emp['email'] ?? ''));
$areaLabel = $isAdmin ? 'Coordenação de estágios' : 'Área da empresa';
$idleSeconds = (int) env('SESSION_IDLE_' . ($isAdmin ? 'ADMIN' : 'EMPRESA'), $isAdmin ? 1800 : 7200);
?><!DOCTYPE html>
<html lang="pt-br"><head><?php require __DIR__ . '/head.php'; ?><title><?= e($titulo ?? '') ?> · <?= e($areaLabel) ?> · ESTAGIA+</title></head>
<body class="app-body" data-session-idle="<?= $idleSeconds ?>" data-heartbeat-url="<?= e(BASE_URL . 'public/heartbeat.php') ?>" data-login-url="<?= e(url($isAdmin ? 'admin-login' : 'empresa-login')) ?>">
<div class="app-shell">
  <aside class="app-side" id="menu" aria-label="Menu principal">
    <?php $brandHref = url($isAdmin ? 'admin' : 'painel'); $brandSub = $areaLabel; require __DIR__ . '/../partials/brand.php'; ?>
    <nav class="app-nav">
      <?php foreach ($navGroups as $groupLabel => $items): ?>
        <div class="app-nav-group"><?= e($groupLabel) ?></div>
        <?php foreach ($items as [$route, $label, $iconName, $key]): $active = ($secao ?? '') === $key; ?>
          <a href="<?= url($route) ?>" class="<?= $active ? 'is-active' : '' ?>"<?= $active ? ' aria-current="page"' : '' ?>><?= icon($iconName, 18) ?> <?= e($label) ?>
            <?php if ($key === 'aprovacoes' && !empty($pendentes)): ?><span class="ui-count" aria-label="<?= (int) $pendentes ?> pendentes"><?= (int) $pendentes ?></span><?php endif; ?></a>
        <?php endforeach; ?>
      <?php endforeach; ?>
      <div class="app-nav-sep"></div>
      <a href="<?= url() ?>" target="_blank" rel="noopener"><?= icon('external', 18) ?> Ver site de vagas</a>
    </nav>
    <div class="app-side-foot">
      <div class="app-account">
        <span class="app-avatar" aria-hidden="true"><?= e($personInitials) ?></span>
        <span class="app-account-who"><strong><?= e($person) ?></strong><small><?= e($personEmail) ?></small></span>
        <form method="post" action="<?= url('sair') ?>"><?= csrf_field() ?><input type="hidden" name="perfil" value="<?= $isAdmin ? 'admin' : 'empresa' ?>">
          <button class="app-icon-btn" type="submit" aria-label="Sair da conta" title="Sair"><?= icon('logout', 16) ?></button></form>
      </div>
    </div>
  </aside>
  <div class="app-scrim"></div>
  <div class="app-main">
    <header class="app-top">
      <button type="button" class="app-icon-btn app-menu-btn" data-menu-open aria-controls="menu" aria-label="Abrir menu"><?= icon('menu', 20) ?></button>
      <div class="app-title"><small><?= e($areaLabel) ?></small><h1><?= e($titulo ?? '') ?></h1></div>
      <?php if ($isAdmin): $naoLidas = $naoLidas ?? Notification::naoLidas(); $notifs = $notifs ?? Notification::recentes(8); ?>
        <details class="ui-bell"><summary class="app-icon-btn" aria-label="Notificações: <?= (int) $naoLidas ?> novas"><?= icon('bell', 18) ?><?php if ($naoLidas): ?><span class="ui-bell-n"><?= (int) $naoLidas ?></span><?php endif; ?></summary>
          <div class="ui-pop"><strong>Notificações</strong>
            <?php foreach ($notifs as $n): [$stLabel, $stTone, $done] = Notification::estado($n); ?>
              <a class="ui-notif <?= $n['lida'] ? '' : 'is-new' ?> <?= $done ? 'is-done' : '' ?>" href="<?= url('admin-notificacao', ['id' => (int) $n['id']]) ?>">
                <span><?= e($n['mensagem']) ?></span>
                <small><?= e(date('d/m H:i', strtotime($n['criado_em']))) ?> · <?= $done ? 'Concluída' : 'Pendente' ?> <?= Ui::badge($stLabel, $stTone) ?></small></a>
            <?php endforeach; ?>
            <?php if (!$notifs): ?><p>Nenhuma notificação por enquanto.</p><?php endif; ?>
            <?php if ($naoLidas): ?><form method="post" action="<?= url('admin-acao') ?>"><?= csrf_field() ?><input type="hidden" name="tipo" value="notificacoes"><button class="ui-link-btn" type="submit">Marcar todas como lidas</button></form><?php endif; ?></div></details>
      <?php endif; ?>
      <button type="button" class="app-icon-btn" data-theme-toggle aria-label="Ativar modo noturno"><?= icon('moon', 18) ?></button>
      <span class="app-avatar app-top-chip" title="<?= e($person . ' · ' . $personEmail) ?>" aria-hidden="true"><?= e($personInitials) ?></span>
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
<script src="<?= e(BASE_URL) ?>public/js/motion.js?v=<?= (int) @filemtime(ROOT_PATH . '/public/js/motion.js') ?>"></script>
</body></html>
