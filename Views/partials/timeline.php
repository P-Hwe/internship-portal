<?php /** Histórico (auditoria). Espera $hist. */
?>
<?php if (!$hist): ?><p class="ui-sub">Sem registros ainda.</p><?php else: ?>
<ol class="ui-timeline"><?php foreach ($hist as $h): ?>
  <li><strong><?= e(AuditLog::rotulo($h)) ?></strong><small><?= e(AuditLog::quem($h)) ?> · <?= e(date('d/m/Y H:i', strtotime($h['criado_em']))) ?></small><?php if ($h['detalhe']): ?><small style="color:var(--text)"><?= e($h['detalhe']) ?></small><?php endif; ?></li>
<?php endforeach; ?></ol><?php endif; ?>
