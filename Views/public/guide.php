<?php require __DIR__ . '/../header.php'; $ativa = 'sobre'; require __DIR__ . '/../partials/nav.php'; ?>
<div class="vb-intro gd-intro">
  <h1>Guia de primeiro acesso</h1>
  <p>Como entrar pela primeira vez, adicionar pessoas, redefinir senhas e quais e-mails o portal envia. Escolha o seu perfil.</p>
  <nav class="gd-switch" aria-label="Escolher perfil">
    <a class="vb-btn vb-btn-primary" href="#empresa"><?= icon('building', 16) ?> Sou empresa</a>
    <a class="vb-btn" href="#coordenacao"><?= icon('users', 16) ?> Sou da Coordenação</a>
  </nav>
</div>

<section class="vb-section gd-block" id="empresa" aria-labelledby="gd-emp">
  <h2 id="gd-emp" class="vb-section-title">Empresas</h2>
  <ol class="gd-steps">
    <li><strong>Cadastre a empresa</strong>
      <p>Em <a href="<?= url('empresa-cadastro') ?>">Cadastrar empresa</a>, informe o CNPJ, os dados de contato e crie a senha da pessoa responsável. Na última etapa, baixe o modelo do Convênio de Concessão de Estágio, preencha, assine e anexe a via assinada pela empresa.</p></li>
    <li><strong>Aguarde a análise e o convênio</strong>
      <p>A Coordenação confere o cadastro e colhe as assinaturas do IFSP: o(a) Diretor(a)-Geral e a Coordenação de estágio. O cadastro é aprovado junto com a via assinada por todas as partes. Você recebe um e-mail e pode baixar o convênio final na aba <strong>Convênio</strong>.</p></li>
    <li><strong>Entre na área da empresa</strong>
      <p>Use o e-mail e a senha do cadastro em <a href="<?= url('empresa-login') ?>">Área da empresa</a>. A publicação de vagas fica liberada depois da aprovação com o convênio anexado. Cada vaga passa pela análise da Coordenação antes de ir ao ar.</p></li>
    <li><strong>Adicione a sua equipe</strong>
      <p>Em <strong>Usuários</strong>, a pessoa responsável (ou um gestor) cadastra quem vai ajudar. Os cargos são:</p>
      <ul class="gd-list">
        <li><strong><?= e(CompanyMember::roleLabel(CompanyMember::PRINCIPAL)) ?>:</strong> <?= e(CompanyMember::ROLE_HINTS[CompanyMember::PRINCIPAL]) ?>.</li>
        <li><strong><?= e(CompanyMember::roleLabel(CompanyMember::MANAGER)) ?>:</strong> <?= e(CompanyMember::ROLE_HINTS[CompanyMember::MANAGER]) ?>.</li>
        <li><strong><?= e(CompanyMember::roleLabel(CompanyMember::MEMBER)) ?>:</strong> <?= e(CompanyMember::ROLE_HINTS[CompanyMember::MEMBER]) ?>.</li>
      </ul>
      <p>A pessoa adicionada recebe por e-mail uma senha provisória e o link <strong>Criar minha senha</strong>, válido por 72 horas. No primeiro login o portal pede uma senha nova.</p></li>
    <li><strong>Esqueceu a senha?</strong>
      <p>Na tela de login, clique em <strong>Redefinir por e-mail</strong>. Chegam um link e um código de 6 dígitos, válidos por 30 minutos. Use um ou outro para criar a senha nova.</p></li>
    <li><strong>Encerrar a parceria</strong>
      <p>A pessoa responsável pode encerrar em <strong>Perfil da empresa</strong>. As vagas saem do mural, os dados de cadastro são apagados e o CNPJ fica livre para um novo cadastro no futuro.</p></li>
  </ol>
  <h3 class="gd-subtitle">E-mails que a empresa recebe</h3>
  <ul class="gd-mails">
    <li><?= icon('check', 16) ?> Cadastro aprovado ou não aprovado, com o motivo</li>
    <li><?= icon('briefcase', 16) ?> Vaga aprovada ou rejeitada, com o motivo</li>
    <li><?= icon('clock', 16) ?> Vaga perto de encerrar (3 dias antes) e vaga encerrada pelo prazo</li>
    <li><?= icon('users', 16) ?> Acesso criado para um novo usuário, com o link de primeiro acesso</li>
    <li><?= icon('file', 16) ?> Link e código para redefinir a senha e aviso quando a senha é alterada</li>
    <li><?= icon('file', 16) ?> Convênio assinado por todas as partes disponível para download</li>
  </ul>
  <p class="gd-note">Não recebeu? Confira a caixa de spam e se o e-mail do cadastro está correto.</p>
</section>

<section class="vb-section gd-block" id="coordenacao" aria-labelledby="gd-adm">
  <h2 id="gd-adm" class="vb-section-title">Coordenação</h2>
  <ol class="gd-steps">
    <li><strong>Primeiro acesso do administrador</strong>
      <p>Na instalação, o portal cria o administrador principal com o e-mail configurado no servidor e envia uma senha provisória e o link <strong>Criar minha senha</strong> (válido por 72 horas). O portal guarda só o hash da senha; ninguém consegue vê-la.</p></li>
    <li><strong>Adicione outros administradores</strong>
      <p>Em <strong>Usuários</strong>, cadastre cada pessoa da Coordenação com o próprio e-mail. Ela recebe a senha provisória e o link de primeiro acesso. Você pode desativar, reativar ou excluir acessos depois.</p></li>
    <li><strong>Analise as empresas</strong>
      <p>Em <strong>Aprovações</strong>, abra o cadastro pelo botão <strong>Analisar</strong>. Baixe a via assinada pela empresa, colha as assinaturas do(a) Diretor(a)-Geral e da Coordenação de estágio e anexe o PDF final no cartão <strong>Convênio</strong>. O botão <strong>Aprovar</strong> só é liberado depois disso.</p></li>
    <li><strong>Analise as vagas</strong>
      <p>Vagas enviadas pelas empresas aparecem em <strong>Aprovações</strong>. Aprove ou rejeite informando o motivo; a empresa é avisada por e-mail.</p></li>
    <li><strong>Esqueceu a senha?</strong>
      <p>Na tela de login da Coordenação, clique em <strong>Redefinir por e-mail</strong>. Chegam um link e um código de 6 dígitos, válidos por 30 minutos.</p></li>
  </ol>
  <h3 class="gd-subtitle">E-mails e avisos da Coordenação</h3>
  <ul class="gd-mails">
    <li><?= icon('building', 16) ?> Nova empresa cadastrada para analisar</li>
    <li><?= icon('briefcase', 16) ?> Nova vaga ou vaga alterada aguardando aprovação</li>
    <li><?= icon('users', 16) ?> Acesso criado, desativado ou reativado para administradores</li>
    <li><?= icon('users', 16) ?> Novo usuário ou cargo alterado dentro de uma empresa</li>
    <li><?= icon('info', 16) ?> Empresa que encerrou a parceria</li>
  </ul>
  <p class="gd-note">Os mesmos avisos aparecem no sino do painel, mesmo que o e-mail não chegue.</p>
</section>
</div>
<?php require __DIR__ . '/../footer.php'; ?>
