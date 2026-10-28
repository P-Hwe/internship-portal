<?php /** Card de empresa no admin: banner da empresa, logo, dados e ações. O card inteiro abre a empresa. Espera $e (com total_vagas/vagas_abertas); opcional $retCard e $comTermo. */
$retCard = $retCard ?? 'admin-empresas'; $comTermo = $comTermo ?? false;
$acoes = Ui::acoesEmpresaAdmin($e, $retCard);
?>
<article class="ui-vcard">
  <div class="ui-ccard-media<?= empresa_capa_attr($e) ?>"><?= empresa_banner($e) ?></div>
  <div class="ui-ccard-body" style="position:relative">
    <span class="ui-ccard-logo" style="top:-30px;bottom:auto"><?= Ui::logo($e, 56) ?></span>
    <h3><a class="ui-stretch" href="<?= url('admin-empresa', ['id' => $e['id']]) ?>"><?= e($e['nome']) ?></a></h3>
    <p><?= e(Empresa::segmentoTxt($e)) ?><?= $e['cidade'] ? ' · ' . e($e['cidade'] . '/' . $e['uf']) : '' ?></p>
    <div class="ui-actions"><?= Ui::statusEmpresa($e['status']) ?></div>
    <ul class="ui-vcard-meta">
      <li><?= icon('briefcase', 15) ?> <?= (int) $e['total_vagas'] ?> vaga(s), <?= (int) $e['vagas_abertas'] ?> no ar</li>
      <li><?= icon('user', 15) ?> <?= e($e['responsavel']) ?></li>
      <li><?= icon('clock', 15) ?> Cadastrada em <?= data_br(substr($e['criado_em'], 0, 10)) ?></li>
    </ul>
  </div>
  <?php if ($comTermo || str_contains($acoes, '<form')): ?><div class="ui-vcard-foot">
    <?php if ($comTermo && $e['termo_arquivo']): ?><a class="ui-btn ui-btn-sm" href="<?= url('admin-termo', ['id' => $e['id']]) ?>" target="_blank" rel="noopener"><?= icon('file', 15) ?> Ver termo</a><?php endif; ?>
    <?= $acoes ?></div><?php endif; ?>
</article>
