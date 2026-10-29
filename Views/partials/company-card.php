<?php /** Card de empresa parceira (estudante). Espera $e. O clique principal leva ao aviso de saída para o site/LinkedIn; "Ver vagas" abre a página interna. */
$ext = Company::linkExterno($e); ?>
<article class="vg-card vg-comp vg-card-trilho">
  <div class="vg-comp-cover<?= empresa_capa_attr($e) ?>"><?= empresa_banner($e) ?></div>
  <div class="vg-comp-top"><span class="vg-comp-logo"><?= Ui::logo($e, 56) ?></span><div><h3 class="vg-card-title" style="margin:0"><a class="vg-stretched" href="<?= e($ext ? url('externo', ['id' => $e['id']]) : url('empresa', ['id' => $e['id']])) ?>"><?= e($e['nome']) ?></a></h3><p class="vg-comp-seg"><?= e(Company::segmentoTxt($e)) ?></p></div></div>
  <p class="vg-comp-desc"><?= e(mb_strimwidth((string) $e['descricao'], 0, 110, '…')) ?></p>
  <p class="vg-comp-foot"><strong><?= (int) $e['vagas_abertas'] ?></strong> vaga(s) aberta(s)<?= $e['cidade'] ? ' · ' . e($e['cidade'] . '/' . $e['uf']) : '' ?></p>
  <a class="vg-comp-link" href="<?= url('empresa', ['id' => $e['id']]) ?>">Ver vagas e informações</a>
</article>
