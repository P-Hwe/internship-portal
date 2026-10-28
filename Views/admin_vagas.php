<?php
$ret = 'admin-vagas';
$abas = ['todas' => ['Todas', $cont['todas'] ?? 0], 'aguardando' => ['Aguardando aprovação', $cont['aguardando'] ?? 0], 'publicada' => ['Publicadas', $cont['publicada'] ?? 0], 'agendada' => ['Agendadas', $cont['agendada'] ?? 0],
    'destaque' => ['Em destaque', $cont['destaque'] ?? 0], 'expirada' => ['Expiradas', $cont['expirada'] ?? 0], 'encerrada' => ['Encerradas', $cont['encerrada'] ?? 0], 'rejeitada' => ['Rejeitadas', $cont['rejeitada'] ?? 0]];
$pq = ['aba' => $aba, 'q' => $q, 'tipo' => $tipo, 'ord' => $ord, 'vista' => $vista];
?>
<?= Ui::abas($abas, $aba, 'admin-vagas', ['q' => $q, 'tipo' => $tipo, 'ord' => $ord, 'vista' => $vista]) ?>
<div class="ui-toolbar"><form method="get" action="<?= url('admin-vagas') ?>" role="search"><?= campo_rota('admin-vagas') ?><input type="hidden" name="aba" value="<?= e($aba) ?>"><input type="hidden" name="vista" value="<?= e($vista) ?>">
  <label class="ui-sr" for="q">Buscar</label><input id="q" class="ui-input" type="search" name="q" value="<?= e($q) ?>" placeholder="Título, empresa, área ou código">
  <label class="ui-sr" for="tipo">Tipo</label><select id="tipo" class="ui-select" name="tipo"><option value="">Todos os tipos</option><?php foreach (TIPOS_VAGA as $t): ?><option <?= $tipo === $t ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select>
  <label class="ui-sr" for="ord">Ordenar</label><select id="ord" class="ui-select" name="ord"><?php foreach (['recentes' => 'Mais recentes', 'prazo' => 'Prazo final', 'titulo' => 'Título (A-Z)', 'empresa' => 'Empresa (A-Z)'] as $k => $r): ?><option value="<?= $k ?>" <?= $ord === $k ? 'selected' : '' ?>><?= $r ?></option><?php endforeach; ?></select>
  <button class="ui-btn" type="submit"><?= icon('search', 16) ?> Filtrar</button></form>
  <div class="ui-seg" role="group" aria-label="Modo de exibição"><a href="<?= e(url('admin-vagas', array_merge($pq, ['vista' => 'cards']))) ?>" class="<?= $vista === 'cards' ? 'is-active' : '' ?>">Cards</a><a href="<?= e(url('admin-vagas', array_merge($pq, ['vista' => 'tabela']))) ?>" class="<?= $vista === 'tabela' ? 'is-active' : '' ?>">Tabela</a></div></div>
<p class="ui-sub"><?= (int) $total ?> vaga(s) encontrada(s).</p>
<?php if (!$lista): echo Ui::vazio('Nenhuma vaga encontrada', 'Ajuste a busca, o tipo ou a aba.');
elseif ($vista === 'tabela'): ?><div class="ui-table-wrap"><table class="ui-table"><thead><tr><th>Código</th><th>Vaga</th><th>Empresa</th><th>Situação</th><th>Período</th><th>Ações</th></tr></thead><tbody>
  <?php foreach ($lista as $v): ?><tr><td><span class="ui-code"><?= e($v['codigo']) ?></span></td><td><a href="<?= url('admin-vaga', ['id' => $v['id']]) ?>"><?= e($v['titulo']) ?></a><br><small><?= e($v['tipo']) ?> · <?= e($v['area']) ?></small></td>
    <td><?= e($v['empresa']['nome']) ?></td><td><?= Ui::situacao($v['situacao']) ?></td><td><?= data_br($v['data_publicacao']) ?> a <?= data_br($v['data_limite']) ?></td><td><?= Ui::acoesVagaAdmin($v, 'admin-vagas') ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php else: ?><div class="ui-grid"><?php foreach ($lista as $v) { $ctx = 'admin'; require __DIR__ . '/partials/vaga_card_app.php'; } ?></div><?php endif; ?>
<?= Ui::paginacao($pg, $paginas, 'admin-vagas', $pq) ?>
