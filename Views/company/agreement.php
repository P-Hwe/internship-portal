<?php
$signed = !empty($emp['convenio_arquivo']);
$approved = $emp['status'] === Status::E_APROVADA || $emp['status'] === Status::E_BLOQUEADA;
$steps = [
    ['A empresa assinou e enviou o convênio', !empty($emp['termo_arquivo']), $emp['termos_aceitos_em'] ? 'Enviado em ' . date('d/m/Y', strtotime($emp['termos_aceitos_em'])) : null],
    ['A Coordenação analisou o cadastro', $approved, $approved && $emp['avaliado_em'] ? 'Aprovado em ' . date('d/m/Y', strtotime($emp['avaliado_em'])) : ($emp['status'] === Status::E_PENDENTE ? 'Em análise' : null)],
    ['O IFSP assinou: Diretor(a)-Geral e setor de estágio', $signed, $signed ? null : 'As assinaturas do IFSP são feitas no sistema oficial do instituto'],
    ['Via final disponível para download', $signed, $signed ? 'Disponível desde ' . date('d/m/Y', strtotime($emp['convenio_assinado_em'])) : null],
];
?>
<div class="ui-grid-2" style="grid-template-columns:minmax(0,1.2fr) minmax(0,1fr);align-items:start">
    <section class="ui-card">
        <h2>Convênio de Concessão de Estágio <?= $signed ? Ui::badge('Assinado por todas as partes', 'ok') : Ui::badge('Aguardando assinaturas do IFSP', 'warn') ?></h2>
        <ol class="ag-steps">
            <?php foreach ($steps as [$label, $done, $note]): ?>
            <li class="<?= $done ? 'is-done' : '' ?>">
                <span class="ag-dot" aria-hidden="true"><?= $done ? icon('check-s', 14) : '' ?></span>
                <div><strong><?= e($label) ?></strong><?php if ($note): ?><small><?= e($note) ?></small><?php endif; ?></div>
            </li>
            <?php endforeach; ?>
        </ol>
    </section>
    <aside class="ui-card">
        <?php if ($signed): ?>
        <h2>Via final</h2>
        <p class="ui-sub" style="margin:0 0 14px">Convênio assinado pela empresa, pelo(a) Diretor(a)-Geral do câmpus e pelo setor de estágio. Guarde uma cópia junto com os documentos da empresa.</p>
        <a class="ui-btn ui-btn-primary ui-btn-block" href="<?= url('empresa-convenio-arquivo', ['via' => 'final']) ?>" target="_blank" rel="noopener"><?= icon('file', 16) ?> Baixar convênio assinado</a>
        <?php else: ?>
        <h2>Enquanto isso</h2>
        <p class="ui-sub" style="margin:0 0 14px">Quando o IFSP terminar as assinaturas, a via final aparece aqui e você recebe um e-mail avisando. A publicação de vagas é liberada nesse momento.</p>
        <?php endif; ?>
        <?php if (!empty($emp['termo_arquivo'])): ?>
        <a class="ui-btn ui-btn-block" style="margin-top:10px" href="<?= url('empresa-convenio-arquivo', ['via' => 'empresa']) ?>" target="_blank" rel="noopener"><?= icon('file', 16) ?> Ver a via enviada pela empresa</a>
        <?php endif; ?>
    </aside>
</div>
