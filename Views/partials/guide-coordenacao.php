<?php /** Guia de primeiro acesso da Coordenação (modal da tela de login da Coordenação). */ ?>
<ol class="gd-steps">
  <li><strong>Primeiro acesso do administrador</strong>
    <p>Na instalação, o portal cria o administrador principal com o e-mail configurado no servidor e envia uma senha provisória e o link <strong>Criar minha senha</strong>, válido por 72 horas. O portal guarda só o hash da senha; ninguém consegue vê-la.</p></li>
  <li><strong>Adicione outros administradores</strong>
    <p>Em <strong>Usuários</strong>, cadastre cada pessoa da Coordenação com o próprio e-mail. Ela recebe a senha provisória e o link de primeiro acesso. Depois você pode desativar, reativar ou excluir acessos.</p></li>
  <li><strong>Analise as empresas</strong>
    <p>Em <strong>Aprovações</strong>, abra o cadastro pelo botão <strong>Analisar</strong>. Baixe a via assinada pela empresa, colha as assinaturas do(a) Diretor(a)-Geral e da Coordenação de estágio e anexe o PDF final no cartão <strong>Convênio</strong>. O botão <strong>Aprovar</strong> só é liberado depois disso.</p></li>
  <li><strong>Analise as vagas</strong>
    <p>As vagas enviadas pelas empresas aparecem em <strong>Aprovações</strong>. Aprove ou rejeite informando o motivo; a empresa é avisada por e-mail.</p></li>
  <li><strong>Esqueceu a senha?</strong>
    <p>Clique em <strong>Redefinir por e-mail</strong> nesta tela. Chegam um link e um código de 6 dígitos, válidos por 30 minutos.</p></li>
</ol>
<h3 class="gd-subtitle">E-mails e avisos da Coordenação</h3>
<ul class="gd-mails">
  <li><?= icon('building', 16) ?> Nova empresa cadastrada para analisar</li>
  <li><?= icon('briefcase', 16) ?> Nova vaga ou vaga alterada aguardando aprovação</li>
  <li><?= icon('users', 16) ?> Acesso de administrador criado, desativado ou reativado</li>
  <li><?= icon('users', 16) ?> Novo usuário ou cargo alterado dentro de uma empresa</li>
  <li><?= icon('info', 16) ?> Empresa que encerrou a parceria</li>
</ul>
<p class="gd-note">Os mesmos avisos aparecem no sino do painel, mesmo que o e-mail não chegue.</p>
