<?php
$checks = [
    ['Logo da empresa', !empty($emp['logo'])],
    ['Descrição completa', mb_strlen((string) $emp['descricao']) >= 30],
    ['Site ou LinkedIn', !empty($emp['site']) || !empty($emp['linkedin'])],
    ['Segmento e porte', !empty($emp['segmento']) && !empty($emp['porte'])],
    ['Celular de contato', !empty($emp['celular'])],
];
$feitos = count(array_filter($checks, fn($c) => $c[1]));
$pct = (int) round($feitos / count($checks) * 100);
?>
<?php if ($emp['status'] === 'pendente'): ?>
    <div class="ui-alert ui-alert-warn"><?= icon('clock', 22) ?><div><strong>Cadastro em análise</strong>
            <p>A Coordenação está conferindo os dados e o termo de cooperação. Você receberá um e-mail com o resultado.
                Enquanto isso, complete o perfil da empresa.</p>
        </div>
    </div>
<?php elseif ($emp['status'] === 'reprovada'): ?>
    <div class="ui-alert ui-alert-danger"><?= icon('info', 22) ?><div><strong>Cadastro não aprovado</strong>
            <p>Motivo: <?= nl2br(e((string) $emp['motivo'])) ?></p>
            <form method="post" action="<?= url('empresa-perfil') ?>" style="margin-top:8px"><?= csrf_field() ?><input
                    type="hidden" name="acao" value="reenviar">
                <a class="ui-btn ui-btn-sm" href="<?= url('empresa-perfil') ?>">Corrigir perfil</a> <button
                    class="ui-btn ui-btn-sm ui-btn-primary" type="submit">Reenviar para análise</button>
            </form>
        </div>
    </div>
<?php elseif ($aviso): ?>
    <div class="ui-alert ui-alert-ok"><?= icon('check', 22) ?><div><strong>Empresa aprovada</strong>
            <p>Você já pode publicar vagas. Cada vaga passa por análise antes de ir ao ar.</p>
        </div>
        <div class="ui-actions"><a class="ui-btn ui-btn-primary ui-btn-sm" href="<?= url('vaga-cadastro') ?>">Publicar
                vaga</a>
            <form method="post" action="<?= url('aviso-dispensar') ?>"><?= csrf_field() ?><button class="ui-link-btn"
                    type="submit">Dispensar</button></form>
        </div>
    </div>
<?php endif; ?>
<?php if (!empty($cont['rejeitada'])): ?><div class="ui-alert ui-alert-danger"><?= icon('info', 22) ?><div>
            <strong><?= (int) $cont['rejeitada'] ?> vaga(s) rejeitada(s)</strong>
            <p>Veja o motivo, corrija e reenvie para análise. <a
                    href="<?= url('empresa-vagas', ['aba' => 'rejeitada']) ?>">Ver vagas rejeitadas</a></p>
        </div>
    </div><?php endif; ?>
<?php foreach ($vencendo as $v): ?><div class="ui-alert ui-alert-warn"><?= icon('clock', 22) ?><div>
            <strong><?= e($v['titulo']) ?>: <?= e(dias_txt($v['dias'])) ?></strong>
            <p>Depois de <?= data_br($v['data_limite']) ?> a vaga sai do ar. Para estender o prazo, edite a data final (a
                vaga passa por nova aprovação).</p>
        </div>
    </div><?php endforeach; ?>

<div class="ui-metrics">
    <?= Ui::metrica('Publicadas', $cont['publicada'] ?? 0, 'check', url('empresa-vagas', ['aba' => 'publicada'])) ?>
    <?= Ui::metrica('Em análise', $cont['aguardando'] ?? 0, 'clock', url('empresa-vagas', ['aba' => 'aguardando']), 'warn') ?>
    <?= Ui::metrica('Agendadas', $cont['agendada'] ?? 0, 'clock', url('empresa-vagas', ['aba' => 'agendada']), 'info') ?>
    <?= Ui::metrica('Expiradas', $cont['expirada'] ?? 0, 'briefcase', url('empresa-vagas', ['aba' => 'expirada']), 'mute') ?>
    <?= Ui::metrica('Encerradas', $cont['encerrada'] ?? 0, 'x', url('empresa-vagas', ['aba' => 'encerrada']), 'mute') ?>
</div>

<?php require __DIR__ . '/../../public/css/empresa_painel.css'; ?>
<div class="ui-grid-2">
    <section class="ui-section">
        <div class="ui-row-between">
            <h2>Vagas recentes</h2><a class="ui-btn ui-btn-sm" href="<?= url('empresa-vagas') ?>">Ver
                todas</a>
        </div>
        <?php if (!$recentes): echo Ui::vazio('Nenhuma vaga ainda', $emp['status'] === 'aprovada' ? 'Publique a primeira vaga da sua empresa.' : 'Você poderá publicar vagas depois que a Coordenação aprovar o cadastro.', $emp['status'] === 'aprovada' ? ['Publicar vaga', url('vaga-cadastro')] : null);
        else: ?><div class="ui-grid"><?php foreach ($recentes as $v) {
                                            $ctx = 'empresa';
                                            require __DIR__ . '/partials/vaga_card_app.php';
                                        } ?></div>
        <?php endif; ?>
    </section>
    <aside class="ui-card">
        <h2>Perfil da empresa <?= Ui::badge($pct . '%', $pct === 100 ? 'ok' : 'warn') ?></h2>
        <div class="ui-progress"><i><?= $pct ?>%</i></div>
        <ul class="ui-rules"><?php foreach ($checks as [$t, $ok]): ?><li class="<?= $ok ? 'is-ok' : '' ?>"><?= e($t) ?>
                </li><?php endforeach; ?></ul>
        <p class="ui-sub">Quanto mais completo o perfil, melhor a apresentação para os estudantes.
        </p>
        <a class="ui-btn ui-btn-block" href="<?= url('empresa-perfil') ?>">Editar perfil</a>
    </aside>
</div>
