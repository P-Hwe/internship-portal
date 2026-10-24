<?php
/**
 * @var array $v
 */
?>
<?php $sit = $v['situacao'];
$e = $v['empresa']; ?>
<div class="ui-row-between">
  <div class="ui-actions"><a class="ui-btn ui-btn-sm" href="<?= url('admin-vagas') ?>"><?= icon('arrow-left', 15) ?>
      Vagas</a> <?= Ui::situacao($sit) ?> <span class="ui-badge ui-badge-info"><?= e($v['tipo']) ?></span></div>
  <?= Ui::acoesVagaAdmin($v, 'admin-vaga', (int) $v['id']) ?>
</div>
<h2 style="font-size:22px;margin-bottom:4px"><?= e($v['titulo']) ?></h2>
<p class="ui-sub"><a href="<?= url('admin-empresa', ['id' => $e['id']]) ?>"><?= e($e['nome']) ?></a>
  <?= Ui::statusEmpresa($e['status']) ?><?= $sit === 'publicada' ? ' · <a href="' . e(url_job((int) $v['id'])) . '" target="_blank" rel="noopener">ver no site</a>' : '' ?>
</p>
<?php if ($v['status'] === 'aguardando_aprovacao' && $e['status'] !== 'aprovada'): ?><div
    class="ui-alert ui-alert-warn">
    <div><strong>Empresa ainda não aprovada</strong>
      <p>Aprove a empresa antes de aprovar esta vaga.</p>
    </div>
  </div><?php endif; ?>
<?php if ($sit === 'rejeitada'): ?><div class="ui-alert ui-alert-danger">
    <div><strong>Rejeitada</strong>
      <p>Motivo: <?= nl2br(e((string) $v['motivo'])) ?></p>
    </div>
  </div><?php endif; ?>
<?php require __DIR__ . '/partials/vaga_detalhe.php'; ?>
<div class="ui-card" style="margin-top:16px">
  <h2>Histórico</h2><?php require __DIR__ . '/partials/timeline.php'; ?>
</div>
