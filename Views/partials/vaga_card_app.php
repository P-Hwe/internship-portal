<?php

$ctx = $ctx ?? 'empresa';
$ret = $ret ?? 'admin-vagas';
$sit = $v['situacao'];
$ver = $ctx === 'admin' ? url('admin-vaga', ['id' => $v['id']]) : url('empresa-vaga', ['id' => $v['id']]);
$img = url_upload($v['imagem'] ?? null);
$selos = array_slice($v['selos'] ?? [], 0, 3, true);
$acoes = $ctx === 'admin' ? Ui::acoesVagaAdmin($v, $ret) : '';
?>
<article class="ui-vcard">
    <div class="ui-vcard-media<?= $img ? ' has-img' : '' ?>"
        <?= $img ? ' style="--bn:url(\'' . e($img) . '\')"' : '' ?>>
        <?= vaga_imagem($v) ?>
        <ul class="ui-vtags" aria-label="Destaques da vaga"><?php foreach ($selos as [$txt, $cls]): ?><li
                class="ui-vtag <?= e($cls) ?>"><?= e($txt) ?></li><?php endforeach; ?></ul>
    </div>
    <div class="ui-vcard-body">
        <div class="ui-vcard-emp">
            <?= Ui::logo($v['empresa'], 26) ?><span><?= e($v['empresa']['nome']) ?></span><?= Ui::situacao($sit) ?>
        </div>
        <h3><a class="ui-stretch" href="<?= e($ver) ?>"><?= e($v['titulo']) ?></a></h3>
        <ul class="ui-vcard-meta">
            <li><?= icon('pin', 15) ?> <?= e($v['local']) ?> · <?= e($v['modalidade']) ?></li>
            <li class="money"><?= icon('money', 15) ?> <?= e($v['bolsa_txt']) ?></li>
            <?php if ($sit === 'publicada'): $prazo = selo_prazo($v); ?><li
                class="<?= $v['dias'] <= DIAS_VAGA_VENCENDO ? 'is-warn' : '' ?>"><?= icon('clock', 15) ?>
                <?= e($prazo[0]) ?></li>
            <?php elseif ($sit === 'agendada'): ?><li><?= icon('clock', 15) ?> Entra no ar em
                <?= data_br($v['data_publicacao']) ?></li>
            <?php else: ?><li><?= icon('clock', 15) ?> <?= data_br($v['data_publicacao']) ?> a
                <?= data_br($v['data_limite']) ?></li><?php endif; ?>
        </ul>
        <?php if ($sit === 'rejeitada' && $v['motivo']): ?><div class="ui-vcard-flag">Motivo: <?= e($v['motivo']) ?>
        </div><?php endif; ?>
        <p class="ui-vcard-area"><?= e($v['area']) ?></p>
    </div>
    <?php if ($ctx === 'empresa'): ?>
    <div class="ui-vcard-foot">
        <?php if ($v['status'] !== 'encerrada'): ?><a class="ui-btn ui-btn-sm"
            href="<?= url('vaga-editar', ['id' => $v['id']]) ?>"><?= icon('edit', 15) ?> Editar</a><?php endif; ?>
        <?php if ($v['status'] === 'aprovada'): echo Ui::formConfirmar(url('vaga-encerrar'), ['id' => $v['id']], 'Encerrar vaga', 'Encerrar esta vaga?', 'A vaga "' . $v['titulo'] . '" deixa de aparecer para os estudantes imediatamente. Ela continua guardada no seu histórico e não será excluída.', 'danger', 'ui-btn ui-btn-sm ui-btn-danger-o');
      endif; ?>
    </div>
    <?php elseif (str_contains($acoes, '<form')): ?><div class="ui-vcard-foot"><?= $acoes ?></div><?php endif; ?>
</article>