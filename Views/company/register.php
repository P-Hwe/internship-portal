<?php
$larga = true; $modo = 'cadastro'; $old = $old + ['logo' => null, 'banner' => null, 'cnpj' => '', 'nome' => ''];
$etapaDe = ['cnpj' => 0, 'razao_social' => 0, 'nome' => 0, 'descricao' => 0, 'segmento' => 0, 'porte' => 0, 'site' => 0, 'linkedin' => 0, 'logo' => 0, 'banner' => 0,
    'cep' => 1, 'logradouro' => 1, 'numero' => 1, 'complemento' => 1, 'bairro' => 1, 'cidade' => 1, 'uf' => 1, 'responsavel' => 2, 'cargo' => 2, 'telefone' => 2, 'celular' => 2, 'email_contato' => 2,
    'email' => 3, 'senha' => 3, 'senha2' => 3, 'termo' => 4, 'aceite_termos' => 4, 'aceite_privacidade' => 4];
$inicio = $erros ? min(array_map(fn($k) => $etapaDe[$k] ?? 0, array_keys($erros))) : 0;
?>
<div class="auth-card">
    <span class="auth-env"><?= icon('building', 14) ?> Cadastro de empresa parceira</span>
    <h1>Cadastre sua empresa</h1>
    <p class="lead">Informe apenas os dados essenciais. Depois que a Coordenação aprovar o cadastro, sua empresa poderá
        publicar vagas de estágio.</p>
    <form class="ui-form" method="post" action="<?= url('empresa-cadastro') ?>" enctype="multipart/form-data" data-form
        data-wiz data-start="<?= (int) $inicio ?>" novalidate>
        <?= csrf_field() ?><span data-stepper></span>
        <?php if ($erros): ?><div class="ui-alert ui-alert-danger" role="alert">
            <div>Corrija os campos destacados e envie de novo.</div>
        </div><?php endif; ?>
        <?php require __DIR__ . '/../partials/company-fields.php'; ?>

        <fieldset class="ui-fieldset ui-step" data-step="3" data-title="Acesso">
            <legend>Acesso à plataforma</legend>
            <p class="ui-step-sub">Com esses dados você entra na área da empresa.</p>
            <?= Ui::campo(['name' => 'email', 'label' => 'E-mail de acesso', 'type' => 'email', 'req' => true, 'hint' => 'Recebe os avisos de aprovação e é usado para entrar.', 'attrs' => ['maxlength' => 120, 'autocomplete' => 'username', 'data-rev' => 'E-mail de acesso']], $old, $erros) ?>
            <div class="ui-cols">
                <?= Ui::campo(['name' => 'senha', 'label' => 'Senha', 'type' => 'password', 'req' => true, 'attrs' => ['autocomplete' => 'new-password', 'minlength' => 8, 'data-pwd' => '']], [], $erros) ?>
                <?= Ui::campo(['name' => 'senha2', 'label' => 'Repita a senha', 'type' => 'password', 'req' => true, 'attrs' => ['autocomplete' => 'new-password']], [], $erros) ?>
            </div>
            <ul class="ui-rules" data-pwd-rules>
                <li>Pelo menos 8 caracteres</li>
                <li>Pelo menos uma letra</li>
                <li>Pelo menos um número</li>
            </ul>
        </fieldset>

        <fieldset class="ui-fieldset ui-step" data-step="4" data-title="Termos">
            <legend>Termos e confirmação</legend>
            <p class="ui-step-sub">Leia o convênio, anexe a via assinada pela empresa e confirme.</p>
            <div class="ui-terms" tabindex="0" aria-label="Termos de uso e política de privacidade">
                <h3>Termos de uso</h3>
                <p>A empresa declara que os dados informados são verdadeiros e atualizados, e que as vagas divulgadas
                    respeitam a legislação de estágio e as regras do campus. Cada vaga passa por análise da Coordenação
                    antes de ser publicada e pode ser reprovada, bloqueada ou encerrada quando houver informação
                    incorreta ou descumprimento do termo de cooperação.</p>
                <h3>Privacidade</h3>
                <p>O sistema armazena somente dados da empresa e de seus responsáveis (nome, cargo, telefone e e-mail),
                    usados para análise do cadastro, comunicação e exibição do perfil. Nenhum dado pessoal de estudante
                    é coletado. Os dados são tratados conforme a LGPD. A empresa pode encerrar a parceria quando quiser, pela área
                    da empresa: as vagas saem do mural e os dados de cadastro são apagados do portal.</p>
                <p><em>Texto-base: revisar e aprovar com a Coordenação do IFSP antes da publicação.</em></p>
            </div>
            <?php if ($termoOficial = Credits::officialTerm()): ?>
            <div class="ui-term-doc">
                <h3>Convênio de Concessão de Estágio</h3>
                <p>Este é o modelo oficial do IFSP Câmpus Guarulhos. Ele só vale assinado pelas duas partes:</p>
                <ol class="ui-term-steps">
                    <li>Baixe o modelo e preencha os campos em vermelho: razão social, CNPJ, endereço completo e o representante legal da empresa.</li>
                    <li>O representante legal assina e a empresa indica uma testemunha (Testemunha 2).</li>
                    <li>Anexe abaixo a via assinada pela empresa.</li>
                    <li>Durante a análise do cadastro, a Coordenação colhe as assinaturas do IFSP: o(a) Diretor(a)-Geral do câmpus e o(a) responsável pelo setor de estágio (Testemunha 1). O cadastro é aprovado junto com a via assinada por todas as partes.</li>
                </ol>
                <iframe class="ui-term-pdf" src="<?= e($termoOficial) ?>#view=FitH" title="Modelo do Convênio de Concessão de Estágio do IFSP Câmpus Guarulhos" loading="lazy"></iframe>
                <a class="ui-btn ui-btn-sm" href="<?= e($termoOficial) ?>" download="convenio-concessao-estagio-ifsp-guarulhos.pdf"><?= icon('file', 15) ?> Baixar o modelo do convênio</a>
                <p class="ui-hint" style="margin:0">Se o documento não aparecer acima, use o botão para baixá-lo.</p>
            </div>
            <?php endif; ?>
            <div class="ui-field"><label for="termo">Convênio assinado pela empresa <span class="ui-req"
                        aria-hidden="true">*</span></label>
                <input id="termo" name="termo" type="file" accept=".pdf,.jpg,.jpeg,.png" data-file
                    data-max="<?= UPLOAD_MAX_MB ?>" required data-rev="Termo anexado">
                <small class="ui-hint">PDF, JPG ou PNG, até <?= UPLOAD_MAX_MB ?>
                    MB.<?= $erros ? ' Por segurança o navegador não guarda o arquivo: anexe de novo.' : '' ?></small><small
                    class="ui-err" data-err><?= e($erros['termo'] ?? '') ?></small>
            </div>
            <div class="ui-field"><label class="ui-consent"><input type="checkbox" name="aceite_termos" value="1"
                        required <?= !empty($old['aceite_termos']) ? 'checked' : '' ?>> <span>Li e aceito os termos de
                        uso.</span></label><small class="ui-err"
                    data-err><?= e($erros['aceite_termos'] ?? '') ?></small></div>
            <div class="ui-field"><label class="ui-consent"><input type="checkbox" name="aceite_privacidade" value="1"
                        required <?= !empty($old['aceite_privacidade']) ? 'checked' : '' ?>> <span>Confirmo que li a
                        política de privacidade e autorizo o tratamento dos dados da empresa.</span></label><small
                    class="ui-err" data-err><?= e($erros['aceite_privacidade'] ?? '') ?></small></div>
            <div class="ui-review" data-review hidden>
                <h3>Confira antes de enviar</h3>
                <dl></dl>
            </div>
        </fieldset>

        <div class="ui-wiz-nav">
            <button type="button" class="ui-btn ui-btn-lg" data-prev hidden><?= icon('arrow-left', 16) ?>
                Voltar</button>
            <button type="button" class="ui-btn ui-btn-primary ui-btn-lg" data-next hidden>Continuar
                <?= icon('arrow-right', 16) ?></button>
            <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg" data-submit>Enviar cadastro</button>
            <span class="ui-spacer"></span><a href="<?= url('empresa-login') ?>">Já tenho cadastro</a>
        </div>
    </form>
</div>