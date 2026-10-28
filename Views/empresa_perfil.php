<?php $modo = 'perfil';
$logoPrev = $old; ?>
<div class="ui-grid-2" style="grid-template-columns:minmax(0,1fr) 300px;align-items:start">
    <div>
        <?php if ($emp['status'] === 'reprovada'): ?><div class="ui-alert ui-alert-danger">
            <div><strong>Cadastro não aprovado</strong>
                <p>Motivo: <?= nl2br(e((string) $emp['motivo'])) ?> Corrija os dados abaixo, salve e reenvie para
                    análise.</p>
            </div>
        </div><?php endif; ?>
        <?php if ($erros): ?><div class="ui-alert ui-alert-danger" role="alert">
            <div>Corrija os campos destacados e salve de novo.</div>
        </div><?php endif; ?>
        <div class="ui-card">
            <form class="ui-form" method="post" action="<?= url('empresa-perfil') ?>" enctype="multipart/form-data"
                data-form data-wiz data-free data-start="0" novalidate>
                <?= csrf_field() ?><span data-stepper></span>
                <?php require __DIR__ . '/partials/empresa_campos.php'; ?>
                <div class="ui-wiz-nav"><button type="button" class="ui-btn" data-prev
                        hidden><?= icon('arrow-left', 16) ?> Voltar</button><button type="button" class="ui-btn"
                        data-next hidden>Continuar <?= icon('arrow-right', 16) ?></button>
                    <button type="submit" class="ui-btn ui-btn-primary" data-submit>Salvar perfil</button>
                </div>
            </form>
        </div>
        <?php if ($emp['status'] === 'reprovada'): ?><form method="post" action="<?= url('empresa-perfil') ?>"
            style="margin-top:14px"><?= csrf_field() ?><input type="hidden" name="acao" value="reenviar"><button
                class="ui-btn ui-btn-lg" type="submit">Reenviar para análise</button></form><?php endif; ?>
    </div>
    <aside>
        <div class="ui-card">
            <h2>Como o estudante vê</h2>
            <div class="ui-cover" style="aspect-ratio:3/1;border-radius:8px;margin-bottom:10px">
                <?= empresa_banner($emp) ?></div>
            <div class="ui-comp-head" style="margin-bottom:10px"><?= Ui::logo($emp, 56) ?><div>
                    <strong><?= e($emp['nome']) ?></strong>
                    <p class="ui-sub" style="margin:0"><?= e(Empresa::segmentoTxt($emp)) ?></p>
                </div>
            </div>
            <p class="ui-sub" style="margin:0 0 10px">
                <?= e(mb_substr((string) $emp['descricao'], 0, 140)) ?><?= mb_strlen((string) $emp['descricao']) > 140 ? '…' : '' ?>
            </p>
            <p class="ui-sub" style="margin:0"><?= (int) ($cont['publicada'] ?? 0) ?> vaga(s) no ar</p>
            <?php if ($emp['status'] === 'aprovada'): ?><a class="ui-btn ui-btn-sm ui-btn-block" style="margin-top:12px"
                target="_blank" rel="noopener" href="<?= url('empresa', ['id' => $emp['id']]) ?>">Ver página
                pública</a><?php endif; ?>
        </div>
        <div class="ui-card" style="margin-top:14px">
            <h2>Situação <?= Ui::statusEmpresa($emp['status']) ?></h2>
            <dl class="ui-kv" style="grid-template-columns:100px 1fr">
                <dt>CNPJ</dt>
                <dd><?= e(Cnpj::formatar($emp['cnpj'])) ?></dd>
                <dt>E-mail de acesso</dt>
                <dd><?= e($emp['email']) ?></dd>
                <dt>Cadastro</dt>
                <dd><?= data_br(substr($emp['criado_em'], 0, 10)) ?></dd>
            </dl>
        </div>
    </aside>
</div>