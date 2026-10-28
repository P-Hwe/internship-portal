<?php
$edit = (bool) $atual; $hoje = date('Y-m-d');
$c = fn(array $o) => Ui::campo($o, $old, $erros);
$etapaDe = ['tipo' => 0, 'titulo' => 0, 'area' => 0, 'modalidade' => 0, 'local' => 0, 'descricao' => 0, 'requisitos' => 1, 'bolsa' => 3, 'carga_horas_dia' => 4, 'dias' => 4, 'carga_turno' => 4,
            'data_publicacao' => 5, 'data_limite' => 5, 'link_site' => 6, 'link_linkedin' => 6, 'link_plataforma' => 6, 'link_candidatura' => 6, 'contato' => 6, 'banner' => 6];
$inicio = $erros ? min(array_map(fn($k) => $etapaDe[$k] ?? 0, array_keys($erros))) : 0;
$areaSel = $old['area'] ?? ''; $diasSel = (array) ($old['dias'] ?? []); $remTipo = ($old['remuneracao_tipo'] ?? 'valor') === 'combinar' ? 'combinar' : 'valor';
$sug = fn(array $l) => '<div class="ui-quick" data-quick aria-label="Sugestões"><span class="ui-hint" style="margin:0">Sugestões:</span>' . implode('', array_map(fn($x) => '<button type="button">' . e($x) . '</button>', $l)) . '</div>';
?>
<?php if ($edit && in_array($atual['status'], ['aprovada', 'rejeitada'], true)): ?>
<div class="ui-alert ui-alert-warn"><?= icon('info', 22) ?><div><strong>Alterações exigem nova aprovação</strong>
        <p><?= $atual['status'] === 'aprovada' ? 'Se você alterar algo, esta vaga sai do ar e só volta depois que a Coordenação aprovar a nova versão.' : 'Ao salvar, a vaga é reenviada para análise.' ?>
        </p>
    </div>
</div>
<?php endif; ?>
<?php if ($erros): ?><div class="ui-alert ui-alert-danger" role="alert">
    <div>Corrija os campos destacados e envie de novo.</div>
</div><?php endif; ?>
<div class="ui-card">
    <form class="ui-form" method="post"
        action="<?= $edit ? url('vaga-editar', ['id' => $atual['id']]) : url('vaga-cadastro') ?>"
        enctype="multipart/form-data" data-form data-wiz <?= $edit ? 'data-free' : '' ?>
        data-start="<?= (int) $inicio ?>" novalidate>
        <?= csrf_field() ?><span data-stepper></span>

        <fieldset class="ui-fieldset ui-step" data-step="0" data-title="Informações básicas">
            <legend>Informações básicas</legend>
            <p class="ui-step-sub">Publicando como <strong><?= e($emp['nome']) ?></strong>.</p>
            <div class="ui-cols">
                <?= $c(['name' => 'tipo', 'label' => 'Tipo de oportunidade', 'type' => 'select', 'req' => true, 'opcoes' => TIPOS_VAGA, 'attrs' => ['data-rev' => 'Tipo']]) ?>
                <?= $c(['name' => 'modalidade', 'label' => 'Modalidade', 'type' => 'select', 'req' => true, 'opcoes' => MODALIDADES, 'attrs' => ['data-rev' => 'Modalidade']]) ?>
            </div>
            <?= $c(['name' => 'titulo', 'label' => 'Nome da vaga', 'req' => true, 'attrs' => ['maxlength' => 80, 'minlength' => 5, 'placeholder' => 'Ex.: Estágio em Desenvolvimento Web', 'data-rev' => 'Vaga']]) ?>
            <div class="ui-field"><label for="area">Área <span class="ui-req" aria-hidden="true">*</span></label>
                <select id="area" name="area" class="ui-select <?= isset($erros['area']) ? 'is-invalid' : '' ?>"
                    data-area-select required data-rev="Área">
                    <option value="">Selecione</option>
                    <?php foreach ($areas as $a): ?><option <?= $areaSel === $a ? 'selected' : '' ?>><?= e($a) ?>
                    </option><?php endforeach; ?><option value="<?= AREA_OUTRA ?>"
                        <?= $areaSel === AREA_OUTRA ? 'selected' : '' ?>>Outra área...</option>
                </select>
                <div data-area-outra style="margin-top:10px" hidden><label for="area_outra">Qual é a área? <span
                            class="ui-req">*</span></label><input id="area_outra" class="ui-input" name="area_outra"
                        maxlength="60" placeholder="Ex.: Design Gráfico" value="<?= e($old['area_outra'] ?? '') ?>"
                        data-rev="Nova área">
                    <small class="ui-hint">Se várias vagas usarem essa área, ela vira filtro. Senão, entra em
                        "Outras".</small>
                </div>
                <small class="ui-err" data-err><?= e($erros['area'] ?? '') ?></small>
            </div>
            <?= $c(['name' => 'local', 'label' => 'Local', 'req' => true, 'attrs' => ['maxlength' => 80, 'placeholder' => 'Guarulhos/SP', 'data-rev' => 'Local']]) ?>
            <?= $c(['name' => 'descricao', 'label' => 'Descrição da vaga', 'type' => 'textarea', 'rows' => 7, 'req' => true, 'hint' => 'Escreva o mais importante primeiro: as 2 ou 3 primeiras linhas viram a breve descrição do card.', 'attrs' => ['minlength' => 40, 'data-rev' => 'Descrição']]) ?>
        </fieldset>

        <fieldset class="ui-fieldset ui-step" data-step="1" data-title="Requisitos">
            <legend>Requisitos</legend>
            <p class="ui-step-sub">Digite um item e pressione Enter. Cada item vira uma etiqueta.</p>
            <div class="ui-field" data-chips="20"><label for="req-in">Requisitos obrigatórios <span class="ui-req"
                        aria-hidden="true">*</span></label>
                <textarea id="requisitos" name="requisitos" rows="4" class="ui-textarea" data-chips-required
                    data-rev="Requisitos obrigatórios"><?= e($old['requisitos'] ?? '') ?></textarea>
                <div class="ui-chips"><input id="req-in" type="text" maxlength="120"
                        placeholder="Ex.: Cursando Análise e Desenvolvimento de Sistemas"></div>
                <?= $sug(['Cursando graduação ou curso técnico', 'Boa comunicação', 'Pacote Office', 'Disponibilidade de horário']) ?>
                <small class="ui-hint">Formação, conhecimentos e habilidades indispensáveis.</small><small
                    class="ui-err" data-err><?= e($erros['requisitos'] ?? '') ?></small>
            </div>
            <div class="ui-field" data-chips="20"><label for="des-in">Requisitos desejáveis</label>
                <textarea name="requisitos_desejaveis" rows="3" class="ui-textarea"
                    data-rev="Requisitos desejáveis"><?= e($old['requisitos_desejaveis'] ?? '') ?></textarea>
                <div class="ui-chips"><input id="des-in" type="text" maxlength="120"
                        placeholder="Ex.: Inglês intermediário, Git, SQL"></div>
                <?= $sug(['Inglês intermediário', 'Experiência anterior na área', 'Conhecimento em Git']) ?>
                <small class="ui-hint">Diferenciais: não eliminam o candidato.</small>
            </div>
            <div class="ui-field" data-chips="20"><label for="atv-in">Atividades do estagiário</label>
                <textarea name="atividades" rows="3" class="ui-textarea"
                    data-rev="Atividades"><?= e($old['atividades'] ?? '') ?></textarea>
                <div class="ui-chips"><input id="atv-in" type="text" maxlength="120"
                        placeholder="Ex.: Apoiar o desenvolvimento de telas"></div>
            </div>
        </fieldset>

        <fieldset class="ui-fieldset ui-step" data-step="2" data-title="Benefícios">
            <legend>Benefícios</legend>
            <p class="ui-step-sub">O que a empresa oferece além da remuneração.</p>
            <div class="ui-field" data-chips="20"><label for="ben-in">Benefícios</label>
                <textarea name="beneficios" rows="4" class="ui-textarea"
                    data-rev="Benefícios"><?= e($old['beneficios'] ?? '') ?></textarea>
                <div class="ui-chips"><input id="ben-in" type="text" maxlength="120"
                        placeholder="Digite e pressione Enter"></div>
                <?= $sug(['Vale-refeição', 'Vale-transporte', 'Plano de saúde', 'Recesso remunerado', 'Auxílio home office', 'Seguro de vida']) ?>
            </div>
        </fieldset>

        <fieldset class="ui-fieldset ui-step" data-step="3" data-title="Remuneração">
            <legend>Remuneração</legend>
            <p class="ui-step-sub">Valor mensal em reais (bolsa-auxílio ou salário).</p>
            <div class="ui-field"><span class="ui-label">Como será informada?</span>
                <div class="ui-checks" role="radiogroup">
                    <label class="ui-check"><input type="radio" name="remuneracao_tipo" value="valor"
                            data-rev="Remuneração" <?= $remTipo === 'valor' ? 'checked' : '' ?>> Informar valor
                        mensal</label>
                    <label class="ui-check"><input type="radio" name="remuneracao_tipo" value="combinar"
                            data-rev="Remuneração" <?= $remTipo === 'combinar' ? 'checked' : '' ?>> A combinar</label>
                </div>
            </div>
            <div class="ui-field" data-when="remuneracao_tipo=valor"><label for="bolsa">Valor mensal <span
                        class="ui-req" aria-hidden="true">*</span></label>
                <div class="ui-money" style="max-width:260px"><span>R$</span><input id="bolsa" name="bolsa"
                        class="ui-input <?= isset($erros['bolsa']) ? 'is-invalid' : '' ?>" inputmode="decimal"
                        placeholder="1500,00" data-mask="money" data-money-req value="<?= e($old['bolsa'] ?? '') ?>"
                        data-rev="Valor mensal" data-prefix="R$ "></div>
                <small class="ui-hint">Somente números, com vírgula para os centavos.</small><small class="ui-err"
                    data-err><?= e($erros['bolsa'] ?? '') ?></small>
            </div>
        </fieldset>

        <fieldset class="ui-fieldset ui-step" data-step="4" data-title="Carga horária">
            <legend>Carga horária</legend>
            <p class="ui-step-sub">Quantas horas, em quais dias e em que turno.</p>
            <div class="ui-cols">
                <?= $c(['name' => 'carga_horas_dia', 'label' => 'Horas por dia', 'req' => true, 'hint' => 'De 1 a 12 horas.', 'attrs' => ['inputmode' => 'numeric', 'data-mask' => 'int', 'data-min' => 1, 'data-max' => 12, 'placeholder' => '6', 'data-rev' => 'Horas por dia', 'data-suffix' => 'h']]) ?>
                <?= $c(['name' => 'carga_turno', 'label' => 'Turno', 'type' => 'select', 'req' => true, 'opcoes' => TURNOS, 'attrs' => ['data-rev' => 'Turno']]) ?>
            </div>
            <div class="ui-field"><span class="ui-label">Dias da semana <span class="ui-req"
                        aria-hidden="true">*</span></span>
                <div class="ui-checks" data-need-one="Marque ao menos um dia da semana." data-rev="Dias" data-rev-group>
                    <?php foreach (DIAS_SEMANA as $k => $r): ?><label class="ui-check"><input type="checkbox"
                            name="dias[]" value="<?= $k ?>" <?= in_array($k, $diasSel, true) ? 'checked' : '' ?>>
                        <?= e($r) ?></label><?php endforeach; ?></div>
                <small class="ui-err" data-err><?= e($erros['dias'] ?? '') ?></small>
            </div>
        </fieldset>

        <fieldset class="ui-fieldset ui-step" data-step="5" data-title="Período">
            <legend>Período da vaga</legend>
            <p class="ui-step-sub">Enquanto estiver dentro do período, a vaga fica ativa. Depois da data final ela vai
                para o histórico (não é excluída).</p>
            <div class="ui-cols">
                <?= $c(['name' => 'data_publicacao', 'label' => 'Publicar a partir de', 'type' => 'date', 'req' => true, 'attrs' => ['min' => $edit && $atual['data_publicacao'] < $hoje ? $atual['data_publicacao'] : $hoje, 'data-min-date' => $edit && $atual['data_publicacao'] < $hoje ? $atual['data_publicacao'] : $hoje, 'data-rev' => 'Publicação']]) ?>
                <?= $c(['name' => 'data_limite', 'label' => 'Inscrições até', 'type' => 'date', 'req' => true, 'hint' => 'Depois dessa data a vaga sai do ar sozinha.', 'attrs' => ['min' => date('Y-m-d', strtotime('+1 day')), 'data-after' => 'data_publicacao', 'data-rev' => 'Inscrições até']]) ?>
            </div>
            <div class="ui-period" data-period hidden><span data-period-txt></span>
                <div class="ui-period-bar"><i data-period-bar></i></div>
            </div>
        </fieldset>

        <fieldset class="ui-fieldset ui-step" data-step="6" data-title="Links">
            <legend>Links e candidatura</legend>
            <p class="ui-step-sub">Informe onde o processo seletivo acontece. O estudante é avisado quando sai do
                sistema.</p>
            <div class="ui-cols">
                <?= $c(['name' => 'link_plataforma', 'label' => 'Onde é o processo seletivo?', 'type' => 'select', 'vazio' => 'Por e-mail (sem link)', 'opcoes' => Link::PLATAFORMAS, 'attrs' => ['data-rev' => 'Processo seletivo']]) ?>
                <?= $c(['name' => 'link_candidatura', 'label' => 'Link para candidatura', 'attrs' => ['data-url' => '', 'data-host-from' => 'link_plataforma', 'maxlength' => 250, 'placeholder' => 'https://empresa.gupy.io/jobs/123', 'data-rev' => 'Link de candidatura']]) ?>
            </div>
            <div class="ui-cols">
                <?= $c(['name' => 'link_site', 'label' => 'Site oficial', 'attrs' => ['data-url' => '', 'maxlength' => 200, 'placeholder' => 'https://www.empresa.com.br']]) ?>
                <?= $c(['name' => 'link_linkedin', 'label' => 'LinkedIn (se a vaga também está lá)', 'attrs' => ['data-url' => '', 'data-host' => 'linkedin.com', 'maxlength' => 200, 'placeholder' => 'https://www.linkedin.com/jobs/...']]) ?>
            </div>
            <?= $c(['name' => 'contato', 'label' => 'E-mail para candidatura', 'type' => 'email', 'hint' => 'Obrigatório se não houver link de candidatura.', 'attrs' => ['maxlength' => 120, 'data-rev' => 'E-mail']]) ?>
            <div class="ui-field"><label for="banner">Imagem de divulgação (opcional)</label><input id="banner"
                    name="banner" type="file" accept=".jpg,.jpeg,.png" data-file data-max="<?= UPLOAD_MAX_MB ?>"
                    data-img-only>
                <small class="ui-hint">JPG ou PNG de 900 × 400 px, até <?= UPLOAD_MAX_MB ?> MB. Sem imagem, o card
                    mostra as iniciais da
                    empresa.<?= $edit && $atual['imagem'] ? ' Já existe uma imagem: envie outra só se quiser trocar.' : '' ?></small><small
                    class="ui-err" data-err><?= e($erros['banner'] ?? '') ?></small>
            </div>
        </fieldset>

        <fieldset class="ui-fieldset ui-step" data-step="7" data-title="Revisão">
            <legend>Revisão</legend>
            <p class="ui-step-sub">Confira tudo antes de enviar.
                <?= $edit ? 'Ao salvar, alterações em vaga aprovada voltam para análise.' : 'Depois de enviada, a vaga passa pela aprovação da Coordenação.' ?>
            </p>
            <div class="ui-review" data-review hidden>
                <h3>Resumo da vaga</h3>
                <dl></dl>
            </div>
        </fieldset>

        <div class="ui-wiz-nav">
            <button type="button" class="ui-btn ui-btn-lg" data-prev hidden><?= icon('arrow-left', 16) ?>
                Voltar</button>
            <button type="button" class="ui-btn ui-btn-primary ui-btn-lg" data-next hidden>Continuar
                <?= icon('arrow-right', 16) ?></button>
            <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg"
                data-submit><?= $edit ? 'Salvar alterações' : 'Enviar para análise' ?></button>
            <span class="ui-spacer"></span><a href="<?= url('empresa-vagas') ?>">Cancelar</a>
        </div>
    </form>
</div>