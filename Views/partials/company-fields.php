<?php
/** Etapas 1 a 3 (dados, endereço, contato). Usadas no cadastro e na edição do perfil. Espera $old, $erros, $modo ('cadastro'|'perfil'). */
$c = fn(array $o) => Ui::campo($o, $old, $erros);
$logoAtual = $old['logo'] ?? null; $bannerAtual = url_upload($old['banner'] ?? null);
?>
<fieldset class="ui-fieldset ui-step" data-step="0" data-title="Dados da empresa">
  <legend>Dados da empresa</legend><p class="ui-step-sub">Essas informações aparecem para os estudantes na página da empresa.</p>
  <?php if ($modo === 'cadastro'): ?>
    <?= $c(['name' => 'cnpj', 'label' => 'CNPJ', 'req' => true, 'hint' => 'Aceita o CNPJ novo, com letras e números, e também o só com números.', 'attrs' => ['maxlength' => 18, 'autocapitalize' => 'characters', 'spellcheck' => 'false', 'placeholder' => '12.ABC.345/01DE-35', 'data-mask' => 'cnpj', 'data-rev' => 'CNPJ']]) ?>
  <?php else: ?>
    <div class="ui-field"><span class="ui-label">CNPJ</span><div><span class="ui-code"><?= e(Cnpj::formatar($old['cnpj'])) ?></span> <small class="ui-hint" style="display:inline">Não pode ser alterado. Fale com a Coordenação se precisar corrigir.</small></div></div>
  <?php endif; ?>
  <div class="ui-cols">
    <?= $c(['name' => 'razao_social', 'label' => 'Razão social', 'req' => true, 'attrs' => ['maxlength' => 150, 'data-rev' => 'Razão social']]) ?>
    <?= $c(['name' => 'nome', 'label' => 'Nome fantasia', 'req' => true, 'hint' => 'É o nome exibido nas vagas.', 'attrs' => ['maxlength' => 120, 'autocomplete' => 'organization', 'data-rev' => 'Nome fantasia']]) ?>
  </div>
  <div class="ui-cols">
    <?= $c(['name' => 'segmento', 'label' => 'Segmento', 'type' => 'select', 'req' => true, 'opcoes' => SEGMENTOS, 'attrs' => ['data-rev' => 'Segmento']]) ?>
    <?= $c(['name' => 'porte', 'label' => 'Porte', 'type' => 'select', 'req' => true, 'opcoes' => PORTES, 'attrs' => ['data-rev' => 'Porte']]) ?>
  </div>
  <?= $c(['name' => 'descricao', 'label' => 'Sobre a empresa', 'type' => 'textarea', 'rows' => 5, 'req' => true, 'hint' => 'De 30 a 600 caracteres. Conte o que a empresa faz e como é o ambiente para estagiários.', 'attrs' => ['maxlength' => 600, 'minlength' => 30]]) ?>
  <div class="ui-cols">
    <?= $c(['name' => 'site', 'label' => 'Site oficial', 'attrs' => ['data-url' => '', 'placeholder' => 'https://www.suaempresa.com.br', 'data-rev' => 'Site', 'maxlength' => 200]]) ?>
    <?= $c(['name' => 'linkedin', 'label' => 'LinkedIn da empresa', 'attrs' => ['data-url' => '', 'data-host' => 'linkedin.com', 'placeholder' => 'https://www.linkedin.com/company/...', 'data-rev' => 'LinkedIn', 'maxlength' => 200]]) ?>
  </div>
  <div class="ui-field"><span class="ui-label">Para onde o estudante vai ao clicar na empresa?</span>
    <div class="ui-checks" role="radiogroup" aria-label="Link principal">
      <label class="ui-check"><input type="radio" name="link_principal" value="site" <?= ($old['link_principal'] ?? 'site') !== 'linkedin' ? 'checked' : '' ?>> Site oficial</label>
      <label class="ui-check"><input type="radio" name="link_principal" value="linkedin" <?= ($old['link_principal'] ?? '') === 'linkedin' ? 'checked' : '' ?>> LinkedIn</label>
    </div><small class="ui-hint">Se o escolhido estiver vazio, usamos o outro. O estudante é avisado de que está saindo do sistema.</small></div>
  <div class="ui-field"><label for="logo">Logo da empresa</label>
    <div class="ui-logo-pick">
      <?php if ($logoAtual): ?><img class="ui-logo" data-logo-prev width="64" height="64" src="<?= e(BASE_URL . $logoAtual) ?>" alt="Logo atual"><?php else: ?><span class="ui-logo ui-logo-ini" data-logo-prev style="width:64px;height:64px"><?= e(iniciais($old['nome'] ?: 'Empresa')) ?></span><?php endif; ?>
      <div><input id="logo" name="logo" type="file" accept=".jpg,.jpeg,.png" data-file data-max="2" data-img-only data-logo-pick><small class="ui-hint">JPG ou PNG, até 2 MB. Quadrado fica melhor.</small></div></div>
    <small class="ui-err" data-err><?= e($erros['logo'] ?? '') ?></small></div>
  <div class="ui-field"><label for="banner">Banner da empresa</label>
    <?php if ($bannerAtual): ?><div class="ui-cover" style="margin-bottom:8px;border-radius:8px"><img src="<?= e($bannerAtual) ?>" alt="Banner atual da empresa"></div><?php endif; ?>
    <input id="banner" name="banner" type="file" accept=".jpg,.jpeg,.png" data-file data-max="<?= UPLOAD_MAX_MB ?>" data-img-only>
    <small class="ui-hint">Opcional. JPG ou PNG de 1200 × 400 px, até <?= UPLOAD_MAX_MB ?> MB. Aparece no topo da página da empresa e no card das empresas parceiras. Sem imagem, usamos uma cor com o nome da empresa.<?= $bannerAtual ? ' Envie outra só se quiser trocar.' : '' ?></small>
    <small class="ui-err" data-err><?= e($erros['banner'] ?? '') ?></small></div>
</fieldset>

<fieldset class="ui-fieldset ui-step" data-step="1" data-title="Endereço">
  <legend>Endereço</legend><p class="ui-step-sub">Digite o CEP e preenchemos o resto. Você confirma ou corrige.</p>
  <div class="ui-field" style="max-width:240px"><label for="cep">CEP <span class="ui-req" aria-hidden="true">*</span></label>
    <input id="cep" name="cep" class="ui-input <?= isset($erros['cep']) ? 'is-invalid' : '' ?>" inputmode="numeric" maxlength="9" autocomplete="postal-code" placeholder="00000-000" data-mask="cep" data-viacep data-rev="CEP" value="<?= e($old['cep'] ?? '') ?>" required>
    <small class="ui-hint" data-cep-status aria-live="polite">Ao digitar o CEP, preenchemos rua, bairro, cidade e estado.</small><small class="ui-err" data-err><?= e($erros['cep'] ?? '') ?></small></div>
  <?= $c(['name' => 'logradouro', 'label' => 'Rua, avenida ou travessa', 'req' => true, 'attrs' => ['maxlength' => 120, 'data-rev' => 'Rua', 'data-cep-fill' => 'logradouro', 'autocomplete' => 'address-line1']]) ?>
  <div class="ui-cols ui-cols-num">
    <?= $c(['name' => 'numero', 'label' => 'Número', 'req' => true, 'attrs' => ['maxlength' => 10, 'data-rev' => 'Número', 'placeholder' => 'S/N se não houver']]) ?>
    <?= $c(['name' => 'complemento', 'label' => 'Complemento', 'attrs' => ['maxlength' => 60, 'data-rev' => 'Complemento', 'placeholder' => 'Sala, andar, bloco']]) ?>
  </div>
  <?= $c(['name' => 'bairro', 'label' => 'Bairro', 'req' => true, 'attrs' => ['maxlength' => 60, 'data-rev' => 'Bairro', 'data-cep-fill' => 'bairro']]) ?>
  <div class="ui-cols ui-cols-uf">
    <?= $c(['name' => 'cidade', 'label' => 'Cidade', 'req' => true, 'attrs' => ['maxlength' => 60, 'data-rev' => 'Cidade', 'data-cep-fill' => 'cidade', 'autocomplete' => 'address-level2']]) ?>
    <?= $c(['name' => 'uf', 'label' => 'Estado', 'type' => 'select', 'req' => true, 'vazio' => 'UF', 'opcoes' => UFS, 'attrs' => ['data-cep-fill' => 'uf', 'data-rev' => 'Estado']]) ?>
  </div>
</fieldset>

<fieldset class="ui-fieldset ui-step" data-step="2" data-title="Contato">
  <legend>Contato</legend><p class="ui-step-sub">Quem a Coordenação procura em caso de dúvida.</p>
  <div class="ui-cols">
    <?= $c(['name' => 'responsavel', 'label' => 'Nome do responsável', 'req' => true, 'attrs' => ['maxlength' => 100, 'autocomplete' => 'name', 'data-rev' => 'Responsável']]) ?>
    <?= $c(['name' => 'cargo', 'label' => 'Cargo ou função', 'req' => true, 'attrs' => ['maxlength' => 60, 'data-rev' => 'Cargo', 'placeholder' => 'Ex.: Analista de RH']]) ?>
  </div>
  <div class="ui-cols">
    <?= $c(['name' => 'telefone', 'label' => 'Telefone', 'req' => true, 'attrs' => ['inputmode' => 'tel', 'placeholder' => '11 4000-0000', 'data-mask' => 'tel', 'data-rev' => 'Telefone']]) ?>
    <?= $c(['name' => 'celular', 'label' => 'Celular / WhatsApp', 'attrs' => ['inputmode' => 'tel', 'placeholder' => '11 90000-0000', 'data-mask' => 'tel']]) ?>
  </div>
  <?= $c(['name' => 'email_contato', 'label' => 'E-mail de contato da empresa', 'type' => 'email', 'hint' => 'Opcional. Aparece no perfil da empresa. Se vazio, usamos o e-mail de acesso.', 'attrs' => ['maxlength' => 120]]) ?>
</fieldset>
