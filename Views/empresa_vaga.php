<?php $sit = $v['situacao']; ?>
<div class="ui-row-between">
    <div class="ui-actions"><a class="ui-btn ui-btn-sm" href="<?= url('empresa-vagas') ?>"><?= icon('arrow-left', 15) ?>
            Minhas vagas</a> <?= Ui::situacao($sit) ?> <span class="ui-badge ui-badge-info"><?= e($v['tipo']) ?></span>
    </div>
    <div class="ui-actions">
        <?php if ($sit === 'publicada'): ?><a class="ui-btn ui-btn-sm" href="<?= e(url_vaga((int) $v['id'])) ?>"
                target="_blank" rel="noopener"><?= icon('external', 15) ?> Ver no site</a><?php endif; ?>
        <?php if ($v['status'] !== 'encerrada'): ?><a class="ui-btn ui-btn-sm"
                href="<?= url('vaga-editar', ['id' => $v['id']]) ?>"><?= icon('edit', 15) ?> Editar</a><?php endif; ?>
        <?php if ($v['status'] === 'aprovada') echo Ui::formConfirmar(url('vaga-encerrar'), ['id' => $v['id']], 'Encerrar vaga', 'Encerrar esta vaga?', 'A vaga "' . $v['titulo'] . '" deixa de aparecer para os estudantes imediatamente. Ela continua guardada no histórico e não será excluída.', 'danger', 'ui-btn ui-btn-sm ui-btn-danger-o'); ?>
    </div>
</div>
<h2 style="font-size:22px;margin-bottom:14px"><?= e($v['titulo']) ?></h2>
<?php if ($sit === 'rejeitada'): ?><div class="ui-alert ui-alert-danger">
        <div><strong>Vaga rejeitada</strong>
            <p>Motivo: <?= nl2br(e((string) $v['motivo'])) ?></p>
            <p>Edite e salve para reenviar à análise.</p>
        </div>
    </div>
<?php elseif ($sit === 'aguardando'): ?><div class="ui-alert ui-alert-warn">
        <div><strong>Em análise</strong>
            <p>A vaga ainda não aparece para os estudantes. Você receberá um e-mail quando a Coordenação decidir.</p>
        </div>
    </div>
<?php elseif ($sit === 'agendada'): ?><div class="ui-alert ui-alert-info">
        <div><strong>Aprovada e agendada</strong>
            <p>Entra no ar em <?= data_br($v['data_publicacao']) ?>.</p>
        </div>
    </div>
<?php elseif ($sit === 'expirada'): ?><div class="ui-alert ui-alert-info">
        <div><strong>Prazo encerrado em <?= data_br($v['data_limite']) ?></strong>
            <p>A vaga continua no histórico. Para reabrir, edite a data final: ela passa por nova aprovação.</p>
        </div>
    </div>
<?php elseif ($sit === 'publicada'): ?><div class="ui-alert ui-alert-ok">
        <div><strong>No ar</strong>
            <p>Visível para os estudantes até <?= data_br($v['data_limite']) ?>. Alterações exigem nova aprovação e tiram a
                vaga do ar até lá.</p>
        </div>
    </div><?php endif; ?>
<?php require __DIR__ . '/partials/vaga_detalhe.php'; ?>
<div class="ui-card" style="margin-top:16px">
    <h2>Histórico</h2><?php require __DIR__ . '/partials/timeline.php'; ?>
</div>
