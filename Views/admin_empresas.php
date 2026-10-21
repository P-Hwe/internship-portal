<?php
<?= Ui::abas($abas, $aba, 'admin-empresas', ['q' => $q]) ?>
<div class="ui-toolbar"><form method="get" action="<?= url('admin-empresas') ?>" role="search"><input type="hidden" name="p" value="admin-empresas"><input type="hidden" name="aba" value="<?= e($aba) ?>">
  <label class="ui-sr" for="q">Buscar</label><input id="q" class="ui-input" type="search" name="q" value="<?= e($q) ?>" placeholder="Nome, razão social, CNPJ ou e-mail"><button class="ui-btn" type="submit"><?= icon('search', 16) ?> Buscar</button></form>
  <a class="ui-btn" href="<?= url('admin-exportar', ['tipo' => 'empresas']) ?>"><?= icon('file', 16) ?> Exportar CSV</a></div>
<p class="ui-sub"><?= (int) $total ?> empresa(s) encontrada(s).</p>
<?php if (!$lista): echo Ui::vazio('Nenhuma empresa nesta categoria', $q ? 'Tente outro termo.' : '');
else: ?><div class="ui-grid"><?php foreach ($lista as $e) require __DIR__ . '/partials/empresa_card_app.php'; ?></div><?= Ui::paginacao($pg, $paginas, 'admin-empresas', ['aba' => $aba, 'q' => $q]) ?><?php endif; ?>
?>
