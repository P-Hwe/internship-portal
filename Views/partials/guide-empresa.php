<?php /** Guia de primeiro acesso da empresa (modal da tela de login da empresa). */ ?>
<ol class="gd-steps">
  <li><strong>Cadastre a empresa</strong>
    <p>Em <a href="<?= url('empresa-cadastro') ?>">Cadastrar empresa</a>, informe o CNPJ, os dados de contato e crie a senha da pessoa responsável. Na última etapa, baixe o modelo do Convênio de Concessão de Estágio, preencha, assine e anexe a via assinada pela empresa.</p></li>
  <li><strong>Aguarde a análise e o convênio</strong>
    <p>A Coordenação confere o cadastro e colhe as assinaturas do IFSP: o(a) Diretor(a)-Geral e a Coordenação de estágio. O cadastro é aprovado junto com a via assinada por todas as partes, e o convênio final fica na aba <strong>Convênio</strong>.</p></li>
  <li><strong>Entre e publique vagas</strong>
    <p>Use o e-mail e a senha do cadastro nesta tela. A publicação de vagas é liberada depois da aprovação com o convênio anexado. Cada vaga passa pela análise da Coordenação antes de ir ao ar.</p></li>
  <li><strong>Adicione a sua equipe</strong>
    <p>Em <strong>Usuários</strong>, a pessoa responsável (ou um gestor) cadastra quem vai ajudar:</p>
    <ul class="gd-list">
      <li><strong><?= e(CompanyMember::roleLabel(CompanyMember::PRINCIPAL)) ?>:</strong> <?= e(CompanyMember::ROLE_HINTS[CompanyMember::PRINCIPAL]) ?>.</li>
      <li><strong><?= e(CompanyMember::roleLabel(CompanyMember::MANAGER)) ?>:</strong> <?= e(CompanyMember::ROLE_HINTS[CompanyMember::MANAGER]) ?>.</li>
      <li><strong><?= e(CompanyMember::roleLabel(CompanyMember::MEMBER)) ?>:</strong> <?= e(CompanyMember::ROLE_HINTS[CompanyMember::MEMBER]) ?>.</li>
    </ul>
    <p>A pessoa adicionada recebe por e-mail uma senha provisória e o link <strong>Criar minha senha</strong>, válido por 72 horas. No primeiro login o portal pede uma senha nova.</p></li>
  <li><strong>Esqueceu a senha?</strong>
    <p>Clique em <strong>Redefinir por e-mail</strong> nesta tela. Chegam um link e um código de 6 dígitos, válidos por 30 minutos.</p></li>
  <li><strong>Encerrar a parceria</strong>
    <p>A pessoa responsável pode encerrar em <strong>Perfil da empresa</strong>. As vagas saem do mural, os dados de cadastro são apagados e o CNPJ fica livre para um novo cadastro no futuro.</p></li>
</ol>
<h3 class="gd-subtitle">E-mails que a empresa recebe</h3>
<ul class="gd-mails">
  <li><?= icon('check', 16) ?> Cadastro aprovado ou não aprovado, com o motivo</li>
  <li><?= icon('briefcase', 16) ?> Vaga aprovada ou rejeitada, com o motivo</li>
  <li><?= icon('clock', 16) ?> Vaga perto de encerrar (3 dias antes) e vaga encerrada pelo prazo</li>
  <li><?= icon('users', 16) ?> Acesso criado para um novo usuário, com o link de primeiro acesso</li>
  <li><?= icon('file', 16) ?> Link e código para redefinir a senha e aviso de senha alterada</li>
  <li><?= icon('file', 16) ?> Convênio assinado por todas as partes disponível para download</li>
</ul>
<p class="gd-note">Não recebeu? Confira a caixa de spam e se o e-mail do cadastro está correto.</p>
