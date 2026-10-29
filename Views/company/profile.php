<?php $modo = 'perfil'; $logoPrev = $old; $editando = !empty($erros); /* abrir direto em edição se houve erros de validação */ ?>
<div class="ui-grid-2" style="grid-template-columns:minmax(0,1fr) 300px;align-items:start">
    <div>
        <?php if ($emp['status'] === 'reprovada'): ?><div class="ui-alert ui-alert-danger">
            <div><strong>Cadastro não aprovado</strong>
                <p>Motivo: <?= nl2br(e((string) $emp['motivo'])) ?> Corrija os dados abaixo, salve e reenvie para análise.</p>
            </div>
        </div><?php endif; ?>
        <?php if ($erros): ?><div class="ui-alert ui-alert-danger" role="alert">
            <div>Corrija os campos destacados e salve de novo.</div>
        </div><?php endif; ?>

        <div class="ui-card">
            <?php if (!$editando): ?>
            <!-- Modo leitura -->
            <div id="profile-readonly">
                <div class="ui-row-between" style="margin-bottom:16px">
                    <h2 style="margin:0">Dados da empresa</h2>
                    <button type="button" class="ui-btn ui-btn-primary ui-btn-sm" id="btn-editar-perfil"><?= icon('edit', 16) ?> Editar</button>
                </div>
                <dl class="ui-kv">
                    <dt>Razão social</dt><dd><?= e($emp['razao_social'] ?? '') ?></dd>
                    <dt>Nome fantasia</dt><dd><?= e($emp['nome'] ?? '') ?></dd>
                    <dt>Descrição</dt><dd style="white-space:pre-line"><?= e(mb_substr((string)$emp['descricao'], 0, 300)) ?><?= mb_strlen((string)$emp['descricao']) > 300 ? '…' : '' ?></dd>
                    <dt>Segmento</dt><dd><?= e($emp['segmento'] ?? '') ?></dd>
                    <dt>Porte</dt><dd><?= e($emp['porte'] ?? '') ?></dd>
                    <dt>Endereço</dt><dd><?= e($emp['endereco'] ?? '') ?></dd>
                    <dt>Responsável</dt><dd><?= e($emp['responsavel'] ?? '') ?> · <?= e($emp['cargo'] ?? '') ?></dd>
                    <dt>Telefone</dt><dd><?= e(fone_fmt($emp['telefone'] ?? '')) ?><?= ($emp['celular'] ?? '') ? ' · ' . e(fone_fmt($emp['celular'])) : '' ?></dd>
                    <?php if ($emp['email_contato'] ?? ''): ?><dt>E-mail público</dt><dd><?= e($emp['email_contato']) ?></dd><?php endif; ?>
                    <?php if ($emp['site'] ?? ''): ?><dt>Site</dt><dd><a href="<?= e($emp['site']) ?>" target="_blank" rel="noopener"><?= e($emp['site']) ?></a></dd><?php endif; ?>
                    <?php if ($emp['linkedin'] ?? ''): ?><dt>LinkedIn</dt><dd><a href="<?= e($emp['linkedin']) ?>" target="_blank" rel="noopener"><?= e($emp['linkedin']) ?></a></dd><?php endif; ?>
                </dl>
            </div>
            <?php endif; ?>

            <!-- Modo edição (oculto por padrão, visível se há erros ou após clicar Editar) -->
            <div id="profile-form" <?= $editando ? '' : 'hidden' ?>>
                <div class="ui-row-between" style="margin-bottom:16px">
                    <h2 style="margin:0">Editar perfil</h2>
                    <?php if (!$editando): ?><button type="button" class="ui-btn ui-btn-sm" id="btn-cancelar-perfil"><?= icon('x', 16) ?> Cancelar</button><?php endif; ?>
                </div>
                <form class="ui-form" method="post" action="<?= url('empresa-perfil') ?>" enctype="multipart/form-data"
                    data-form data-wiz data-free data-start="0" novalidate id="form-perfil">
                    <?= csrf_field() ?><span data-stepper></span>
                    <?php require __DIR__ . '/../partials/company-fields.php'; ?>
                    <div class="ui-wiz-nav">
                        <button type="button" class="ui-btn" data-prev hidden><?= icon('arrow-left', 16) ?> Voltar</button>
                        <button type="button" class="ui-btn" data-next hidden>Continuar <?= icon('arrow-right', 16) ?></button>
                        <button type="submit" class="ui-btn ui-btn-primary" data-submit>Salvar perfil</button>
                    </div>
                </form>
            </div>
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
                    <p class="ui-sub" style="margin:0"><?= e(Company::segmentoTxt($emp)) ?></p>
                </div>
            </div>
            <p class="ui-sub" style="margin:0 0 10px">
                <?= e(mb_substr((string) $emp['descricao'], 0, 140)) ?><?= mb_strlen((string) $emp['descricao']) > 140 ? '…' : '' ?>
            </p>
            <p class="ui-sub" style="margin:0"><?= (int) ($cont['publicada'] ?? 0) ?> vaga(s) no ar</p>
            <?php if ($emp['status'] === 'aprovada'): ?><a class="ui-btn ui-btn-sm ui-btn-block" style="margin-top:12px"
                target="_blank" rel="noopener" href="<?= url('empresa', ['id' => $emp['id']]) ?>">Ver página pública</a><?php endif; ?>
        </div>
        <div class="ui-card" style="margin-top:14px">
            <h2>Situação <?= Ui::statusEmpresa($emp['status']) ?></h2>
            <dl class="ui-kv" style="grid-template-columns:100px 1fr">
                <dt>CNPJ</dt><dd><?= e(Cnpj::formatar($emp['cnpj'])) ?></dd>
                <dt>E-mail de acesso</dt><dd><?= e($emp['email']) ?></dd>
                <dt>Cadastro</dt><dd><?= data_br(substr($emp['criado_em'], 0, 10)) ?></dd>
            </dl>
        </div>
    </aside>
</div>

<!-- Toast de confirmação de salvamento -->
<div id="profile-saved-toast" class="vg-toast" hidden role="status" aria-live="polite">
    <?= icon('check', 18) ?> Perfil atualizado com sucesso!
</div>

<script>
(function() {
    var btnEditar   = document.getElementById('btn-editar-perfil');
    var btnCancelar = document.getElementById('btn-cancelar-perfil');
    var readonly    = document.getElementById('profile-readonly');
    var formDiv     = document.getElementById('profile-form');
    var toast       = document.getElementById('profile-saved-toast');

    if (btnEditar) {
        btnEditar.addEventListener('click', function() {
            readonly.hidden = true;
            formDiv.hidden  = false;
            formDiv.querySelector('input,select,textarea') && formDiv.querySelector('input,select,textarea').focus();
        });
    }
    if (btnCancelar) {
        btnCancelar.addEventListener('click', function() {
            formDiv.hidden  = true;
            readonly.hidden = false;
        });
    }

    // Show toast if redirected back with ?saved=1
    if (window.location.search.indexOf('saved=1') !== -1) {
        if (toast) {
            toast.hidden = false;
            setTimeout(function() { toast.hidden = true; }, 4000);
        }
        // Clean up URL
        var url = window.location.href.replace(/[?&]saved=1/, '').replace(/\?$/, '');
        history.replaceState(null, '', url);
    }
})();
</script>
