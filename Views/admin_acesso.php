<?php
/**
 * @var array $resumo
 * @var array $resumo30
 * @var int $bloqueios
 * @var array $contas
 * @var array $ultAdmins
 * @var array $ultEmpresas
 * @var array $suspeitos
 * @var array $eventos
 * @var array $logins
 */
?>
<?php
$rot = ['login_ok' => ['Login', 'ok'], 'login_falha' => ['Falha de login', 'warn'], 'negado' => ['Acesso negado', 'danger'], 'bloqueado' => ['Bloqueio por tentativas', 'danger'], 'timeout' => ['Sessão expirada', 'mute'], 'logout' => ['Saída', 'mute']];
$linha = function ($ev) use ($rot) {
    [$t, $c] = $rot[$ev['evento']] ?? [$ev['evento'], 'mute'];
    return '<tr><td>' . e(date('d/m H:i', strtotime($ev['criado_em']))) . '</td><td>' . Ui::badge($t, $c) . '</td><td>' . e($ev['perfil']) . '</td><td>' . e($ev['email'] ?: '-') . '</td><td><span class="ui-code">' . e($ev['ip'] ?: '-') . '</span></td></tr>';
};
?>
<div class="ui-metrics">
  <?= Ui::metrica('Logins (7 dias)', $resumo['login_ok'], 'user') ?><?= Ui::metrica('Falhas de login (7 dias)', $resumo['login_falha'], 'info', null, 'warn') ?>
  <?= Ui::metrica('Acessos negados (7 dias)', $resumo['negado'], 'shield', null, 'danger') ?><?= Ui::metrica('Bloqueios ativos agora', $bloqueios, 'lock', null, $bloqueios ? 'danger' : 'mute') ?></div>
<div class="ui-grid-2">
  <div class="ui-card"><h2>Logins e falhas por dia</h2><?= Ui::barras($logins, [['ok', 'Logins', 'var(--chart-1)'], ['falha', 'Falhas', 'var(--chart-4)']], 'Logins e falhas por dia nos últimos 7 dias') ?></div>
  <div class="ui-card"><h2>Contas e proteção</h2><dl class="ui-kv" style="grid-template-columns:200px 1fr">
    <dt>Administradores ativos</dt><dd><?= (int) $contas['admins_ativos'] ?></dd><dt>Administradores desativados</dt><dd><?= (int) $contas['admins_inativos'] ?></dd><dt>Empresas bloqueadas</dt><dd><?= (int) $contas['empresas_bloqueadas'] ?></dd>
    <dt>Eventos em 30 dias</dt><dd><?= (int) array_sum($resumo30) ?></dd><dt>Limite de tentativas</dt><dd>5 por e-mail/IP a cada 15 minutos</dd><dt>Expiração de sessão</dt><dd>Admin: 30 min sem atividade · Empresa: 2 h · máximo 12 h</dd></dl>
    <p class="ui-sub" style="margin:10px 0 0">Sessões ativas ainda não são armazenadas no banco (preparado para evolução).</p></div>
</div>
<div class="ui-grid-2" style="margin-top:16px">
  <div class="ui-card"><h2>Últimos acessos: administradores</h2><?php if (!$ultAdmins): echo Ui::vazio('Sem acessos registrados');
  else: ?><ul style="list-style:none;margin:0;padding:0;display:grid;gap:8px"><?php foreach ($ultAdmins as $u): ?><li style="display:flex;justify-content:space-between;gap:8px"><span><?= e($u['nome']) ?><br><small class="ui-hint" style="margin:0"><?= e($u['email']) ?></small></span><small><?= e(date('d/m H:i', strtotime($u['ultimo_acesso']))) ?></small></li><?php endforeach; ?></ul><?php endif; ?></div>
  <div class="ui-card"><h2>Últimos acessos: empresas</h2><?php if (!$ultEmpresas): echo Ui::vazio('Sem acessos registrados');
  else: ?><ul style="list-style:none;margin:0;padding:0;display:grid;gap:8px"><?php foreach ($ultEmpresas as $u): ?><li style="display:flex;justify-content:space-between;gap:8px"><span><a href="<?= url('admin-empresa', ['id' => $u['id']]) ?>"><?= e($u['nome']) ?></a><br><small class="ui-hint" style="margin:0"><?= e($u['email']) ?></small></span><small><?= e(date('d/m H:i', strtotime($u['ultimo_acesso']))) ?></small></li><?php endforeach; ?></ul><?php endif; ?></div>
</div>
<section class="ui-section" style="margin-top:22px"><h2>Tentativas suspeitas recentes</h2><?php if (!$suspeitos): echo Ui::vazio('Nenhuma tentativa suspeita registrada');
else: ?><div class="ui-table-wrap"><table class="ui-table"><thead><tr><th>Quando</th><th>Evento</th><th>Perfil</th><th>E-mail</th><th>IP</th></tr></thead><tbody><?php foreach ($suspeitos as $ev) {
    echo $linha($ev);
} ?></tbody></table></div><?php endif; ?></section>
<section class="ui-section"><h2>Registro de eventos</h2><?php if (!$eventos): echo Ui::vazio('Nenhum evento ainda');
else: ?><div class="ui-table-wrap"><table class="ui-table"><thead><tr><th>Quando</th><th>Evento</th><th>Perfil</th><th>E-mail</th><th>IP</th></tr></thead><tbody><?php foreach ($eventos as $ev) {
    echo $linha($ev);
} ?></tbody></table></div><?php endif; ?></section>
