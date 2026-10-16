<?php /** Card de empresa no admin. Espera $e (com total_vagas/vagas_abertas). */ ?>
<article class="ui-comp ui-job">
  <div class="ui-comp-head"><?= Ui::logo($e, 52) ?><div><h3><a href="<?= url('admin-empresa', ['id' => $e['id']]) ?>"><?= e($e['nome']) ?></a></h3><p><?= e(Empresa::segmentoTxt($e)) ?></p></div></div>
  <div class="ui-actions"><?= Ui::statusEmpresa($e['status']) ?><?php if ($e['cidade']): ?><span class="ui-badge ui-badge-mute"><?= e($e['cidade'] . '/' . $e['uf']) ?></span><?php endif; ?></div>
  <ul class="ui-job-meta"><li><?= icon('briefcase', 15) ?> <?= (int) $e['total_vagas'] ?> vaga(s), <?= (int) $e['vagas_abertas'] ?> no ar</li><li><?= icon('clock', 15) ?> Cadastrada em <?= data_br(substr($e['criado_em'], 0, 10)) ?></li>
    <li><?= icon('user', 15) ?> <?= e($e['responsavel']) ?></li></ul>
  <div class="ui-job-foot"><a class="ui-btn ui-btn-sm" href="<?= url('admin-empresa', ['id' => $e['id']]) ?>"><?= icon('eye', 15) ?> Abrir</a><?= Ui::acoesEmpresaAdmin($e, 'admin-empresas') ?></div>
</article>