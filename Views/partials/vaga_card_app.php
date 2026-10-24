<?php
/** Card de vaga na área logada. Espera $v e $ctx ('empresa'|'admin'); admin: $ret (rota de retorno das ações). */
$ctx = $ctx ?? 'empresa';
$ret = $ret ?? 'admin-vagas';
$sit = $v['situacao'];
$ver = $ctx === 'admin' ? url('admin-vaga', ['id' => $v['id']]) : url('empresa-vaga', ['id' => $v['id']]);
$cls = $sit === 'publicada' ? ($v['dias'] <= DIAS_VAGA_VENCENDO ? 'is-warn' : '') : 'is-off';
?>
<article class="ui-job">
  <div class="ui-job-top"><?= Ui::situacao($sit) ?><span class="ui-badge ui-badge-info"><?= e($v['tipo']) ?></span></div>
  <div><h3><a href="<?= e($ver) ?>"><?= e($v['titulo']) ?></a></h3>
    <?php if ($ctx === 'admin'): ?><div class="ui-job-co"><?= Ui::logo($v['empresa'], 22) ?> <a href="<?= url('admin-empresa', ['id' => $v['empresa_id']]) ?>" style="color:inherit"><?= e($v['empresa']['nome']) ?></a></div><?php endif; ?></div>
  <ul class="ui-job-meta">
    <li><?= icon('pin', 15) ?> <?= e($v['local']) ?> · <?= e($v['modalidade']) ?></li>
    <li><?= icon('money', 15) ?> <?= e($v['bolsa_txt']) ?></li>
    <li><?= icon('clock', 15) ?> <?= data_br($v['data_publicacao']) ?> a <?= data_br($v['data_limite']) ?></li>
    <?php if (in_array($sit, ['publicada', 'expirada'], true)): ?><li class="ui-job-days <?= $cls ?>"><?= icon('trending', 15) ?> <?= e(dias_txt($v['dias'])) ?></li><?php endif; ?>
    <?php if ($sit === 'agendada'): ?><li class="ui-job-days"><?= icon('clock', 15) ?> Entra no ar em <?= data_br($v['data_publicacao']) ?></li><?php endif; ?>
  </ul>
  <?php if ($sit === 'rejeitada' && $v['motivo']): ?><div class="ui-job-flag">Motivo: <?= e($v['motivo']) ?></div><?php endif; ?>
  <?php if (!empty($v['destaque']) && $sit === 'publicada'): ?><span class="ui-badge ui-badge-ok" style="align-self:flex-start">Em destaque</span><?php endif; ?>
  <div class="ui-job-foot">
    <a class="ui-btn ui-btn-sm" href="<?= e($ver) ?>"><?= icon('eye', 15) ?> Ver</a>
    <?php if ($ctx === 'empresa'): ?>
      <?php if ($v['status'] !== 'encerrada'): ?><a class="ui-btn ui-btn-sm" href="<?= url('vaga-editar', ['id' => $v['id']]) ?>"><?= icon('edit', 15) ?> Editar</a><?php endif; ?>
      <?php if ($v['status'] === 'aprovada'): echo Ui::formConfirmar(url('vaga-encerrar'), ['id' => $v['id']], 'Encerrar vaga', 'Encerrar esta vaga?', 'A vaga "' . $v['titulo'] . '" deixa de aparecer para os estudantes imediatamente. Ela continua guardada no seu histórico e não será excluída.', 'danger', 'ui-btn ui-btn-sm ui-btn-danger-o'); endif; ?>
    <?php else: echo Ui::acoesVagaAdmin($v, $ret); endif; ?>
  </div>
</article>