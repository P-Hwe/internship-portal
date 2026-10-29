<?php
require_once __DIR__ . '/../config.php';

// Carrega os helpers/bootstrap caso a função app_env() não esteja no config.php
if (file_exists(__DIR__ . '/../app/helpers.php')) {
    require_once __DIR__ . '/../app/helpers.php';
} elseif (file_exists(__DIR__ . '/../app/bootstrap.php')) {
    require_once __DIR__ . '/../app/bootstrap.php';
}

require_once __DIR__ . '/schema.php';

$env = function_exists('app_env') ? app_env() : ($_ENV['APP_ENV'] ?? 'dev');

if ($env === 'prod' && !in_array('--forcar-producao', $argv ?? [], true)) {
    fwrite(STDERR, "Recusado: APP_ENV=prod. Dados fictícios são só para dev/hml (use --forcar-producao se tiver certeza).\n");
    exit(1);
}
migrar();

const DEMO_DOMINIO = '@demo.example';
const DEMO_SENHA = 'demo12345';
$args = $argv ?? [];

function demo_limpar(): void
{
    $emps = array_column(Database::all('SELECT id FROM empresas WHERE email LIKE :d', ['d' => '%' . DEMO_DOMINIO]), 'id');
    foreach ($emps as $eid) {
        $vs = array_column(Database::all('SELECT id FROM vagas WHERE empresa_id = :e', ['e' => $eid]), 'id');
        foreach ($vs as $vid) {
            Database::run("DELETE FROM historico WHERE entidade = 'vaga' AND entidade_id = :i", ['i' => $vid]);
            Database::run("DELETE FROM notificacoes WHERE tipo = 'vaga' AND referencia_id = :i", ['i' => $vid]);
            Database::run("DELETE FROM visitas WHERE tipo = 'vaga' AND ref_id = :i", ['i' => $vid]);
        }
        Database::run('DELETE FROM vagas WHERE empresa_id = :e', ['e' => $eid]);
        Database::run("DELETE FROM historico WHERE entidade = 'empresa' AND entidade_id = :i", ['i' => $eid]);
        Database::run("DELETE FROM notificacoes WHERE tipo = 'empresa' AND referencia_id = :i", ['i' => $eid]);
        Database::run("DELETE FROM visitas WHERE tipo IN ('empresa', 'saida') AND ref_id = :i", ['i' => $eid]);
        Database::run("DELETE FROM acessos_log WHERE perfil = 'empresa' AND usuario_id = :i", ['i' => $eid]);
        Database::run('DELETE FROM empresas WHERE id = :i', ['i' => $eid]);
    }
    foreach (array_column(Database::all('SELECT id FROM admins WHERE email LIKE :d', ['d' => '%' . DEMO_DOMINIO]), 'id') as $aid) {
        Database::run("DELETE FROM historico WHERE ator_tipo = 'admin' AND ator_id = :i", ['i' => $aid]);
        Database::run("DELETE FROM acessos_log WHERE perfil = 'admin' AND usuario_id = :i", ['i' => $aid]);
        Database::run('DELETE FROM admins WHERE id = :i', ['i' => $aid]);
    }
    Database::run("DELETE FROM visitas WHERE visitante LIKE 'de000%'");
    foreach (glob(ROOT_PATH . '/public/uploads/*/demo-*') ?: [] as $f) @unlink($f);
    echo 'Dados de teste removidos (' . count($emps) . " empresas).\n";
}

if (in_array('--limpar', $args, true) || in_array('--recriar', $args, true)) demo_limpar();
if (in_array('--limpar', $args, true)) exit;
if (Database::val('SELECT 1 FROM empresas WHERE email LIKE :d LIMIT 1', ['d' => '%' . DEMO_DOMINIO])) {
    echo "Os dados de teste já existem. Use --recriar para apagar e criar de novo.\n";
    exit;
}
if (!Database::val('SELECT 1 FROM admins LIMIT 1')) {
    echo "Crie o administrador antes: php database/seed.php\n";
    exit(1);
}

mt_srand(20261015);
$hoje = strtotime('today');
$dia = fn(int $n): string => date('Y-m-d', $hoje + ($n * 86400));
$quando = fn(int $dias, int $hora = 10, int $min = 0): string => date('Y-m-d H:i:s', $hoje + ($dias * 86400) + ($hora * 3600) + ($min * 60));
$raiz = (int) Database::val("SELECT id FROM admins WHERE papel = 'raiz' ORDER BY id LIMIT 1") ?: (int) Database::val('SELECT MIN(id) FROM admins');
$origem = __DIR__ . '/demo/img';

function demo_img(string $arquivo, string $pasta, string $prefixo): ?string
{
    global $origem;
    $src = "$origem/$arquivo"; if (!is_file($src)) return null;
    $ext = pathinfo($arquivo, PATHINFO_EXTENSION);
    $destDir = ROOT_PATH . "/public/uploads/$pasta"; if (!is_dir($destDir)) @mkdir($destDir, 0775, true);
    $nome = 'demo-' . $prefixo . '-' . preg_replace('/\.[a-z]+$/', '', $arquivo) . ".$ext";
    @copy($src, "$destDir/$nome");
    return "public/uploads/$pasta/$nome";
}
$hist = function (string $ent, int $id, string $acao, ?string $de, ?string $para, string $ator, ?int $atorId, ?string $det, string $em) {
    Database::insert('historico', ['entidade' => $ent, 'entidade_id' => $id, 'acao' => $acao, 'de' => $de, 'para' => $para, 'ator_tipo' => $ator, 'ator_id' => $atorId, 'detalhe' => $det !== null ? mb_substr($det, 0, 300) : null, 'criado_em' => $em]);
};

$sec = Admin::create('Secretaria (teste)', 'secretaria' . DEMO_DOMINIO, DEMO_SENHA, 'admin', false);
$novo = Admin::create('Novo administrador (teste)', 'novoadmin' . DEMO_DOMINIO, DEMO_SENHA, 'admin', true);
Database::update('admins', $sec, ['criado_em' => $quando(-40), 'ultimo_acesso' => $quando(-1, 14, 20)]);
Database::update('admins', $novo, ['criado_em' => $quando(-1)]);
$hist('admin', $sec, 'cadastro', null, null, 'admin', $raiz, 'Secretaria (teste)', $quando(-40));
$hist('admin', $novo, 'cadastro', null, null, 'admin', $raiz, 'Novo administrador (teste)', $quando(-1));

$E = [
  ['TechNova Sistemas', 'TechNova Sistemas de Informação Ltda', 'AB1CD2EF0001', 'Tecnologia da Informação', 'Média empresa', 'Camila Duarte', 'Gerente de Pessoas', 'technova', 'capa-1.jpg', 'aprovada', 75, 'Guarulhos', 'Centro', 'Desenvolvemos sistemas web e aplicativos para o varejo. Time jovem, mentoria constante e projetos reais desde o primeiro dia.'],
  ['Alfa Logística', 'Alfa Logística e Transportes S.A.', 'AB2CD3EF0001', 'Logística e Transporte', 'Grande empresa', 'Rogério Tavares', 'Coordenador de RH', 'alfalog', 'capa-2.jpg', 'aprovada', 68, 'Guarulhos', 'Cumbica', 'Operadora logística com centros de distribuição na Grande São Paulo, foco em eficiência, segurança e desenvolvimento de jovens talentos.'],
  ['Órbita Dados', 'Órbita Dados Tecnologia Ltda', 'AB3CD4EF0001', 'Tecnologia da Informação', 'Pequena empresa', 'Júlia Nakamura', 'Head de Pessoas', 'orbita', 'capa-3.jpg', 'aprovada', 55, 'São Paulo', 'Vila Olímpia', 'Startup de análise de dados e dashboards para pequenas e médias empresas. Ritmo acelerado e muito aprendizado.'],
  ['MecPrime Indústria', 'MecPrime Indústria Mecânica Ltda', 'AB4CD5EF0001', 'Indústria', 'Grande empresa', 'Antônio Ferreira', 'Analista de RH Sênior', 'mecprime', null, 'aprovada', 90, 'Guarulhos', 'Distrito Industrial', 'Fábrica de componentes mecânicos com programa de estágio em rodízio entre as áreas de produção, qualidade e manutenção.'],
  ['Verde Oeste Alimentos', 'Verde Oeste Alimentos Ltda', 'AB5CD6EF0001', 'Indústria', 'Média empresa', 'Patrícia Gomes', 'Analista de Talentos', 'verdeoeste', 'capa-4.jpg', 'aprovada', 47, 'Arujá', 'Centro', 'Alimentos naturais e orgânicos, com cultura de sustentabilidade e oportunidades em qualidade, logística e marketing.'],
  ['Clínica Vida Plena', 'Clínica Vida Plena Serviços de Saúde Ltda', 'AB6CD7EF0001', 'Saúde', 'Média empresa', 'Dra. Helena Prado', 'Diretora Administrativa', 'clinicavida', null, 'aprovada', 38, 'Guarulhos', 'Macedo', 'Rede de clínicas com atendimento multidisciplinar. Estágios em administração, atendimento e tecnologia.'],
  ['EduTec Cursos', 'EduTec Educação Profissional Ltda', 'AB7CD8EF0001', 'Educação', 'Pequena empresa', 'Marcos Vinícius Lima', 'Sócio-fundador', 'edutec', 'capa-5.jpg', 'aprovada', 31, 'São Paulo', 'Tatuapé', 'Plataforma de cursos técnicos online em parceria com institutos federais. Vagas em produto, conteúdo e suporte.'],
  ['Banco Sul Digital', 'Banco Sul Digital Instituição de Pagamento S.A.', 'AB8CD9EF0001', 'Financeiro', 'Grande empresa', 'Fernanda Albuquerque', 'Business Partner de RH', 'bancosul', 'capa-6.jpg', 'aprovada', 26, 'São Paulo', 'Faria Lima', 'Instituição de pagamento digital com squads ágeis. Programa de estágio com bolsa acima do mercado.'],
  ['Varejo Mix', 'Varejo Mix Comércio de Variedades Ltda', 'AB9CD0EF0001', 'Comércio', 'Média empresa', 'Sandra Oliveira', 'Supervisora de Pessoas', 'varejomix', null, 'aprovada', 20, 'Guarulhos', 'Vila Galvão', 'Rede de lojas de variedades com oportunidades em vendas, atendimento e gestão de estoque.'],
  ['Construmax Engenharia', 'Construmax Engenharia e Obras Ltda', 'AC1CD2EF0001', 'Serviços', 'Média empresa', 'Eng. Paulo Henrique Souza', 'Gerente de Obras', 'construmax', null, 'aprovada', 14, 'Guarulhos', 'Bonsucesso', 'Construtora com obras na Grande São Paulo. Estágios em engenharia civil, planejamento e segurança do trabalho.'],
  ['AgroVale Soluções', 'AgroVale Soluções Agrícolas Ltda', 'AC2CD3EF0001', 'Serviços', 'Pequena empresa', 'Beatriz Cardoso', 'Assistente de RH', 'agrovale', null, 'pendente', 2, 'Mairiporã', 'Centro', 'Consultoria agrícola e monitoramento por drones. Cadastro enviado, aguardando a análise da Coordenação.'],
  ['Lumina Marketing', 'Lumina Marketing e Comunicação Ltda', 'AC3CD4EF0001', 'Marketing e Comunicação', 'Microempresa', 'Thiago Barros', 'Diretor Criativo', 'lumina', null, 'pendente', 0, 'São Paulo', 'Pinheiros', 'Agência de marketing digital focada em pequenos negócios. Cadastro enviado hoje.'],
  ['Casa & Cia Utilidades', 'Casa e Cia Utilidades Domésticas ME', 'AC4CD5EF0001', 'Comércio', 'Microempresa', 'Lúcia Mendes', 'Proprietária', null, null, 'reprovada', 22, 'Guarulhos', 'Gopoúva', 'Loja de utilidades domésticas. Cadastro reprovado para demonstrar o motivo que a empresa recebe.'],
  ['Express Entregas', 'Express Entregas Rápidas Ltda', 'AC5CD6EF0001', 'Logística e Transporte', 'Pequena empresa', 'Valdir Santos', 'Sócio', null, null, 'bloqueada', 52, 'Guarulhos', 'Taboão', 'Entregas rápidas. Conta bloqueada para demonstrar o estado (vaga divulgada sem relação com estágio).'],
];
$emp = [];
$ruas = ['Avenida Tiradentes', 'Rua Sete de Setembro', 'Avenida Monteiro Lobato', 'Rua Dom Pedro II', 'Avenida Paulista', 'Rua das Acácias'];
foreach ($E as $i => [$nome, $razao, $base, $seg, $porte, $resp, $cargo, $slug, $capa, $st, $criada, $cidade, $bairro, $desc]) {
    $log = $slug ? demo_img("logo-$slug.png", 'logos', 'l') : null;
    $cap = $capa ? demo_img($capa, 'capas', 'c') : null;
    $rua = $ruas[$i % count($ruas)]; 
    $num = (string) (100 + ($i * 37));
    $primeiro = strtolower(preg_replace('/[^a-z]/i', '', iconv('UTF-8', 'ASCII//TRANSLIT', explode(' ', $resp)[0] === 'Dra.' || explode(' ', $resp)[0] === 'Eng.' ? explode(' ', $resp)[1] : explode(' ', $resp)[0])));
    $slugEmail = $slug ?: 'empresa' . $i;
    $id = Database::insert('empresas', [
        'cnpj' => Cnpj::complete($base), 'razao_social' => $razao, 'nome' => $nome, 'descricao' => $desc, 'segmento' => $seg, 'porte' => $porte,
        'endereco' => "$rua, $num - $bairro, $cidade/SP - CEP 07011-000", 'cep' => '07011000', 'logradouro' => $rua, 'numero' => $num, 'complemento' => $i % 3 === 0 ? 'Sala ' . (10 + $i) : null, 'bairro' => $bairro, 'cidade' => $cidade, 'uf' => 'SP',
        'responsavel' => $resp, 'cargo' => $cargo, 'telefone' => '1121' . str_pad((string) (3000 + ($i * 111)), 4, '0'), 'celular' => '119' . str_pad((string) (87000000 + ($i * 12345)), 8, '0', STR_PAD_LEFT),
        'email' => "$primeiro.$slugEmail" . DEMO_DOMINIO, 'email_contato' => "rh.$slugEmail" . DEMO_DOMINIO,
        'site' => "https://www.$slugEmail.example", 'linkedin' => "https://www.linkedin.com/company/$slugEmail", 'link_principal' => $i % 2 ? 'linkedin' : 'site',
        'logo' => $log, 'banner' => $cap, 'senha_hash' => Auth::hash(DEMO_SENHA), 'termos_aceitos_em' => $quando(-$criada), 'status' => $st,
        'motivo' => $st === 'reprovada' ? 'O termo de cooperação enviado está ilegível. Envie uma cópia digitalizada com boa resolução.' : ($st === 'bloqueada' ? 'Vaga divulgada sem relação com estágio.' : null),
        'aprovacao_vista' => $st === 'aprovada' ? 1 : 0, 'criado_em' => $quando(-$criada, 9, 15 + $i),
        'avaliado_em' => $st === 'pendente' ? null : $quando(-$criada + 1, 11, 5), 'ultimo_acesso' => $st === 'aprovada' ? $quando(-($i % 6), 15, 30) : null,
    ]);
    $emp[$i] = $id;
    $hist('empresa', $id, 'cadastro', null, 'pendente', 'empresa', $id, null, $quando(-$criada, 9, 15 + $i));
    if ($st === 'aprovada') $hist('empresa', $id, 'aprovada', 'pendente', 'aprovada', 'admin', $raiz, null, $quando(-$criada + 1, 11, 5));
    if ($st === 'reprovada') $hist('empresa', $id, 'reprovada', 'pendente', 'reprovada', 'admin', $sec, 'O termo de cooperação enviado está ilegível.', $quando(-$criada + 1, 16, 40));
    if ($st === 'bloqueada') { $hist('empresa', $id, 'aprovada', 'pendente', 'aprovada', 'admin', $raiz, null, $quando(-$criada + 1, 10, 0)); $hist('empresa', $id, 'bloqueada', 'aprovada', 'bloqueada', 'admin', $raiz, 'Vaga divulgada sem relação com estágio.', $quando(-9, 13, 10)); }
    if ($st === 'aprovada' && $i % 3 === 1) $hist('empresa', $id, 'perfil', null, null, 'empresa', $id, null, $quando(-((int)($criada / 2)), 14, 0));
    if ($st === 'pendente') Database::insert('notificacoes', ['tipo' => 'empresa', 'referencia_id' => $id, 'mensagem' => "Nova empresa para aprovar: $nome", 'lida' => 0, 'criado_em' => $quando(-$criada, 9, 16)]);
    elseif ($criada < 40) Database::insert('notificacoes', ['tipo' => 'empresa', 'referencia_id' => $id, 'mensagem' => "Nova empresa para aprovar: $nome", 'lida' => 1, 'criado_em' => $quando(-$criada, 9, 16)]);
}

$V = [
  [0, 'Estágio em Desenvolvimento Web', 'Estágio', 'Tecnologia da Informação', 'Híbrido', 2100, 'aprovada', 12, -10, 20, 1, 'banner-1.jpg', 'Atue com React e PHP em projetos reais, com mentoria semanal.'],
  [2, 'Estágio em Análise de Dados', 'Estágio', 'Tecnologia da Informação', 'Remoto', 2300, 'aprovada', 9, -7, 25, 1, 'banner-6.jpg', 'Limpeza, modelagem e visualização de dados com SQL, Python e Power BI.'],
  [7, 'Trainee em Engenharia de Software', 'Trainee', 'Tecnologia da Informação', 'Híbrido', 5200, 'aprovada', 20, -18, 40, 1, 'banner-4.jpg', 'Programa de 12 meses com rodízio entre squads de produto, dados e infraestrutura.'],
  [1, 'Jovem Aprendiz em Operações Logísticas', 'Jovem Aprendiz', 'Logística', 'Presencial', 1150, 'aprovada', 15, -14, 30, 1, 'banner-7.jpg', 'Primeiro emprego com carteira assinada no centro de distribuição, com formação teórica.'],
  [3, 'Estágio em Engenharia de Produção', 'Estágio', 'Engenharia', 'Presencial', 1900, 'aprovada', 25, -22, 18, 1, 'banner-3.jpg', 'Melhoria contínua na linha de produção com acompanhamento dos supervisores.'],
  [4, 'Estágio em Qualidade de Alimentos', 'Estágio', 'Qualidade', 'Presencial', 1700, 'aprovada', 8, -6, 27, 1, 'banner-2.jpg', 'Apoio ao laboratório de controle de qualidade e às auditorias internas.'],
  [6, 'Estágio em Produto Educacional', 'Estágio', 'Educação', 'Remoto', 1600, 'aprovada', 5, -4, 35, 1, 'banner-5.jpg', 'Apoio na criação de trilhas de aprendizagem e na análise de métricas dos alunos.'],
  [0, 'Estágio em QA e Testes Automatizados', 'Estágio', 'Qualidade', 'Remoto', 1900, 'aprovada', 18, -16, 22, 0, null, 'Escreva testes automatizados e ajude a garantir a qualidade das entregas.'],
  [2, 'Jovem Profissional em Business Intelligence', 'Jovem Profissional', 'Tecnologia da Informação', 'Remoto', 5400, 'aprovada', 30, -28, 33, 0, 'banner-6.jpg', 'Para quem acabou de se formar e quer atuar com dashboards e modelagem de dados.'],
  [5, 'Estágio em Atendimento e Recepção', 'Estágio', 'Saúde', 'Presencial', 1350, 'aprovada', 11, -9, 28, 0, null, 'Recepção de pacientes, agendamentos e apoio administrativo.'],
  [5, 'Estágio em Tecnologia da Informação (Suporte)', 'Estágio', 'Tecnologia da Informação', 'Híbrido', 1700, 'aprovada', 22, -20, 15, 0, null, 'Suporte de primeiro nível aos computadores e sistemas da rede de clínicas.'],
  [7, 'Estágio em Prevenção a Fraudes', 'Estágio', 'Financeiro', 'Híbrido', 3000, 'aprovada', 16, -13, 24, 0, 'banner-8.jpg', 'Análise de transações e indicadores de risco com a equipe de segurança.'],
  [8, 'Estágio em Vendas e Atendimento ao Cliente', 'Estágio', 'Comercial', 'Presencial', 1300, 'aprovada', 14, -12, 19, 0, null, 'Atendimento no salão de vendas, reposição e organização das gôndolas.'],
  [8, 'Jovem Aprendiz em Gestão de Estoque', 'Jovem Aprendiz', 'Logística', 'Presencial', 1100, 'aprovada', 10, -8, 30, 0, null, 'Controle de entrada e saída de mercadorias e inventário.'],
  [9, 'Estágio em Engenharia Civil', 'Estágio', 'Engenharia', 'Presencial', 2200, 'aprovada', 21, -19, 21, 0, 'banner-3.jpg', 'Acompanhamento de obras, medições e planejamento com os engenheiros.'],
  [9, 'Estágio em Segurança do Trabalho', 'Estágio', 'Saúde', 'Presencial', 1800, 'aprovada', 13, -11, 26, 0, null, 'Inspeções, treinamentos de segurança e controle de EPIs.'],
  [1, 'Estágio em Planejamento de Rotas', 'Estágio', 'Logística', 'Híbrido', 1450, 'aprovada', 17, -15, 23, 0, 'banner-7.jpg', 'Auxílio no planejamento de rotas e no controle de custos da frota.'],
  [6, 'Estágio em Suporte ao Aluno', 'Estágio', 'Educação', 'Remoto', 1400, 'aprovada', 19, -17, 29, 0, null, 'Atendimento aos alunos por chat e e-mail, com base de conhecimento.'],
  [7, 'Estágio em Design de Produto (UX/UI)', 'Estágio', 'Design Gráfico', 'Híbrido', 2600, 'aprovada', 3, -2, 38, 0, 'banner-4.jpg', 'Desenho de telas e protótipos no Figma, com pesquisa com usuários.'],
  [0, 'Estágio em Segurança da Informação', 'Estágio', 'Tecnologia da Informação', 'Remoto', 2400, 'aprovada', 2, -1, 42, 0, 'banner-1.jpg', 'Análise de vulnerabilidades e boas práticas de segurança em aplicações web.'],
  [2, 'Estágio em Marketing de Dados', 'Estágio', 'Marketing', 'Remoto', 1700, 'aprovada', 1, 0, 45, 0, null, 'Análise de campanhas, funis de conversão e relatórios para clientes.'],
  [4, 'Jovem Aprendiz em Marketing', 'Jovem Aprendiz', 'Marketing', 'Presencial', 1120, 'aprovada', 2, -1, 31, 0, null, 'Apoio na criação de conteúdo e no atendimento nas redes sociais.'],
  [3, 'Estágio em Manutenção Industrial', 'Estágio', 'Engenharia', 'Presencial', 1850, 'aprovada', 24, -21, 3, 0, null, 'Manutenção preventiva de máquinas com a equipe técnica. Inscrições acabando!'],
  [1, 'Estágio em Compras e Suprimentos', 'Estágio', 'Administração', 'Presencial', 1450, 'aprovada', 26, -23, 1, 0, null, 'Cotações, negociação com fornecedores e controle de pedidos. Último dia!'],
  [8, 'Estágio em Visual Merchandising', 'Estágio', 'Marketing', 'Presencial', 1300, 'aprovada', 23, -20, 5, 0, null, 'Montagem de vitrines e comunicação visual das lojas.'],
  [0, 'Estágio em Ciência de Dados (agendada)', 'Estágio', 'Tecnologia da Informação', 'Híbrido', 2500, 'aprovada', 4, 6, 40, 0, 'banner-6.jpg', 'Aprovada pela Coordenação com publicação futura: aparece como Agendada.'],
  [7, 'Trainee Financeiro (agendada)', 'Trainee', 'Financeiro', 'Presencial', 4800, 'aprovada', 3, 12, 50, 0, null, 'Programa trainee com início de publicação na próxima semana.'],
  [0, 'Estágio em Suporte Técnico (prazo encerrado)', 'Estágio', 'Tecnologia da Informação', 'Presencial', 1500, 'aprovada', 60, -55, -10, 0, null, 'Vaga cujo prazo terminou: continua no sistema como Expirada.'],
  [8, 'Estágio em Caixa e Atendimento (prazo encerrado)', 'Estágio', 'Comercial', 'Presencial', 1250, 'aprovada', 70, -65, -20, 0, null, 'Vaga expirada há algumas semanas.'],
  [1, 'Jovem Aprendiz em Almoxarifado (prazo encerrado)', 'Jovem Aprendiz', 'Logística', 'Presencial', 1100, 'aprovada', 85, -80, -35, 0, null, 'Vaga expirada há mais de um mês.'],
  [3, 'Estágio em Planejamento da Produção (encerrada)', 'Estágio', 'Engenharia', 'Presencial', 1900, 'encerrada', 45, -40, 10, 0, null, 'Vaga encerrada pela empresa antes do prazo: fica no histórico.'],
  [6, 'Estágio em Conteúdo Digital (encerrada)', 'Estágio', 'Educação', 'Remoto', 1500, 'encerrada', 35, -30, 8, 0, 'banner-5.jpg', 'Vaga preenchida: a empresa encerrou antes do prazo.'],
  [2, 'Estágio em Analytics de Produto (em análise)', 'Estágio', 'Tecnologia da Informação', 'Remoto', 2200, 'aguardando_aprovacao', 1, 0, 30, 0, 'banner-6.jpg', 'Aguardando a análise da Coordenação.'],
  [4, 'Estágio em Logística Reversa (em análise)', 'Estágio', 'Logística', 'Híbrido', 1550, 'aguardando_aprovacao', 0, 0, 25, 0, null, 'Enviada hoje, aguardando aprovação.'],
  [9, 'Estágio em Orçamentos de Obras (em análise)', 'Estágio', 'Engenharia', 'Presencial', 2000, 'aguardando_aprovacao', 2, 0, 32, 0, null, 'Aguardando a análise da Coordenação.'],
  [5, 'Jovem Profissional em Gestão Hospitalar (em análise)', 'Jovem Profissional', 'Saúde', 'Presencial', null, 'aguardando_aprovacao', 1, 0, 28, 0, null, 'Remuneração a combinar. Aguardando a análise.'],
  [8, 'Estágio sem informações de remuneração (rejeitada)', 'Estágio', 'Comercial', 'Presencial', null, 'rejeitada', 14, 0, 20, 0, null, 'Vaga rejeitada para demonstrar o motivo exibido à empresa.'],
  [0, 'Trainee em Dados (rejeitada)', 'Trainee', 'Tecnologia da Informação', 'Híbrido', 3000, 'rejeitada', 33, 0, 30, 0, null, 'Vaga rejeitada: descrição insuficiente.'],
  [7, 'Estágio em Atendimento Bancário (rejeitada)', 'Estágio', 'Financeiro', 'Presencial', 1200, 'rejeitada', 6, 0, 30, 0, null, 'Vaga rejeitada: bolsa abaixo do piso da região.'],
];
$motivosRej = ['Informe a remuneração e a carga horária da vaga.', 'A descrição está muito curta. Detalhe as atividades e os requisitos.', 'A bolsa informada está abaixo do valor praticado para estágios na região.'];
$atividadesPad = ["Apoiar a equipe nas rotinas do dia a dia", "Participar de reuniões e treinamentos", "Elaborar relatórios e apresentar resultados", "Aprender com mentoria de profissionais da área"];
$benefPad = ['Vale-refeição', 'Vale-transporte', 'Plano de saúde', 'Recesso remunerado', 'Auxílio home office', 'Seguro de vida', 'Gympass', 'Bolsa de estudos'];
$vagas = []; $rej = 0; $seq = 1;
foreach ($V as $n => [$ie, $titulo, $tipo, $area, $mod, $bolsa, $st, $criada, $pub, $lim, $dest, $banner, $extra]) {
    $eid = $emp[$ie]; $nomeEmp = $E[$ie][0]; $cidade = $E[$ie][11];
    if ($area === 'Design Gráfico' && !Database::val("SELECT 1 FROM areas WHERE nome = 'Design Gráfico'")) Database::insert('areas', ['nome' => 'Design Gráfico', 'padrao' => 0]);
    $estagio = $tipo === 'Estágio'; $horas = $estagio ? 6 : 8; $turno = $estagio ? ['manha', 'tarde', 'noite'][$n % 3] : 'integral';
    $turnoTxt = TURNOS[$turno];
    $img = $banner ? demo_img($banner, 'banners', 'v' . ($n % 5)) : null;
    $benef = array_slice($benefPad, $n % 3, 3 + $n % 4);
    $plataforma = $n % 4 === 0 ? 'gupy' : ($n % 4 === 1 ? 'linkedin' : null);
    $aval = $st === 'aguardando_aprovacao' ? null : $quando(-$criada + 1, 15, 20 + $n);
    $motivo = $st === 'rejeitada' ? $motivosRej[$rej++ % 3] : null;
    $slugEmp = strtolower(preg_replace('/[^a-z]/i', '', iconv('UTF-8', 'ASCII//TRANSLIT', explode(' ', $nomeEmp)[0])));
    $id = Database::insert('vagas', [
        'codigo' => sprintf('VG-DEMO-%03d', $seq++), 'empresa_id' => $eid, 'titulo' => mb_substr($titulo, 0, 80), 'tipo' => $tipo, 'area' => $area, 'modalidade' => $mod,
        'local' => $mod === 'Remoto' ? 'Remoto (' . $cidade . '/SP)' : $cidade . '/SP', 'bolsa' => $bolsa, 'carga_horaria' => "{$horas}h/dia · Seg a Sex · $turnoTxt", 'carga_horas_dia' => $horas, 'carga_dias' => 'seg,ter,qua,qui,sex', 'carga_turno' => $turno,
        'descricao' => $extra . "\n\nVocê vai trabalhar com $nomeEmp, com acompanhamento de um supervisor e oportunidade de efetivação ao final do período. Vaga criada apenas para testes (dados fictícios).",
        'atividades' => implode("\n", array_slice($atividadesPad, 0, 3 + $n % 2)), 'requisitos' => "Cursando graduação ou curso técnico na área\nBoa comunicação e vontade de aprender\nDisponibilidade de $horas horas por dia",
        'requisitos_desejaveis' => $n % 2 ? "Inglês intermediário\nExperiência anterior na área" : "Conhecimento de pacote Office\nProatividade", 'beneficios' => implode("\n", $benef),
        'data_publicacao' => $dia($pub), 'data_limite' => $dia($lim), 'contato' => "rh.$slugEmp" . DEMO_DOMINIO, 'imagem' => $img,
        'link_site' => $n % 3 === 0 ? "https://www.$slugEmp.example/carreiras" : null, 'link_linkedin' => $n % 3 === 1 ? "https://www.linkedin.com/company/$slugEmp/jobs" : null,
        'link_candidatura' => $plataforma ? "https://$slugEmp.example/vagas/" . (1000 + $n) : null, 'link_plataforma' => $plataforma,
        'destaque' => $dest, 'status' => $st, 'motivo' => $motivo, 'versao' => $n % 7 === 0 ? 2 : 1,
        'criado_em' => $quando(-$criada, 9 + $n % 6, 10 + $n), 'atualizado_em' => $n % 7 === 0 ? $quando(-$criada + 1, 11, 0) : null, 'avaliado_em' => $aval,
    ]);
    $vagas[$n] = ['id' => $id, 'st' => $st, 'pub' => $pub, 'lim' => $lim, 'dest' => $dest, 'emp' => $eid];
    $hist('vaga', $id, 'criada', null, 'aguardando_aprovacao', 'empresa', $eid, null, $quando(-$criada, 9 + $n % 6, 10 + $n));
    if ($n % 7 === 0) $hist('vaga', $id, 'editada', 'aprovada', 'aguardando_aprovacao', 'empresa', $eid, null, $quando(-$criada + 1, 11, 0));
    if ($st === 'aprovada' || $st === 'encerrada') {
        $hist('vaga', $id, 'aprovada', 'aguardando_aprovacao', 'aprovada', 'admin', $n % 3 === 0 ? $sec : $raiz, null, $aval);
        if ($dest) $hist('vaga', $id, 'destaque', null, null, 'admin', $raiz, null, $quando(-$criada + 2, 9, 0));
    }
    if ($st === 'encerrada') $hist('vaga', $id, 'encerrada', 'aprovada', 'encerrada', 'empresa', $eid, null, $quando(-4 - ($n % 5), 17, 0));
    if ($st === 'rejeitada') $hist('vaga', $id, 'rejeitada', 'aguardando_aprovacao', 'rejeitada', 'admin', $sec, $motivo, $aval);
    if ($st === 'aprovada' && $lim < 0) $hist('vaga', $id, 'expirada', 'aprovada', null, 'sistema', null, null, $quando($lim, 0, 5));
    Database::insert('notificacoes', ['tipo' => 'vaga', 'referencia_id' => $id, 'mensagem' => 'Nova vaga para aprovar: ' . mb_substr($titulo, 0, 60), 'lida' => $st === 'aguardando_aprovacao' ? 0 : 1, 'criado_em' => $quando(-$criada, 9 + $n % 6, 11 + $n)]);
}

$peso = [];
foreach ($vagas as $n => $v) if ($v['st'] === 'aprovada' && $v['pub'] <= 0 && $v['lim'] >= 0) $peso[$n] = 2 + ($v['dest'] ? 6 : 0) + (($n + 7) % 5);
$pesoTotal = array_sum($peso); $vagasPesadas = []; foreach ($peso as $n => $p) for ($k = 0; $k < $p; $k++) $vagasPesadas[] = $n;
$visitantes = []; for ($k = 0; $k < 90; $k++) $visitantes[] = 'de000' . substr(md5("v$k"), 0, 19);
$rows = [];
$add = function (string $tipo, ?int $ref, int $diasAtras) use (&$rows, $visitantes, $quando) {
    $rows[] = [$tipo, $ref, $visitantes[mt_rand(0, count($visitantes) - 1)], $quando(-$diasAtras, mt_rand(7, 22), mt_rand(0, 59))];
};
$ativas = array_keys(array_filter($E, fn($e) => $e[9] === 'aprovada'));
for ($d = 59; $d >= 0; $d--) {
    $base = 6 + (int) round((60 - $d) / 4);
    for ($k = 0, $q = $base + mt_rand(0, 8); $k < $q; $k++) {
        $add('home', null, $d);
        if (mt_rand(0, 99) < 70 && $vagasPesadas) { $n = $vagasPesadas[mt_rand(0, count($vagasPesadas) - 1)]; $add('vaga', $vagas[$n]['id'], $d); }
        if (mt_rand(0, 99) < 25) $add('empresas', null, $d);
        if (mt_rand(0, 99) < 22) { $ie = $ativas[mt_rand(0, count($ativas) - 1)]; $add('empresa', $emp[$ie], $d); if (mt_rand(0, 99) < 35) $add('saida', $emp[$ie], $d); }
    }
}
foreach (array_chunk($rows, 300) as $lote) {
    $ph = implode(',', array_fill(0, count($lote), '(?,?,?,?)')); $flat = [];
    foreach ($lote as $r) foreach ($r as $x) $flat[] = $x;
    Database::pdo()->prepare("INSERT INTO visitas (tipo, ref_id, visitante, criado_em) VALUES $ph")->execute($flat);
}
foreach ([[$raiz, 'admin', 0], [$sec, 'admin', 1], [$raiz, 'admin', 2], [$sec, 'admin', 5]] as [$uid, $perfil, $d]) {
    Database::insert('acessos_log', ['perfil' => $perfil, 'usuario_id' => $uid, 'email' => null, 'evento' => 'login_ok', 'ip' => null, 'agente' => null, 'criado_em' => $quando(-$d, 9, 3)]);
}
foreach ($ativas as $k => $ie) {
    foreach ([0, 2, 3] as $j => $d) if (($k + $j) % 2 === 0) Database::insert('acessos_log', ['perfil' => 'empresa', 'usuario_id' => $emp[$ie], 'email' => null, 'evento' => 'login_ok', 'ip' => null, 'agente' => null, 'criado_em' => $quando(-($d + $k % 4), 10 + $j, 30)]);
}

echo "\nDados de teste criados.\n";
echo '  Empresas: ' . count($emp) . ' | Vagas: ' . count($vagas) . ' | Visitas: ' . count($rows) . "\n";
echo '  Senha das contas de teste: ' . DEMO_SENHA . "\n";
echo "  Admin comum:    secretaria" . DEMO_DOMINIO . "  (só o administrador principal cria/desativa admins)\n";
echo "  1º acesso:      novoadmin" . DEMO_DOMINIO . "  (será obrigado a trocar a senha ao entrar)\n";
echo "  Empresa aprovada (com vagas): " . Database::val('SELECT email FROM empresas WHERE id = :i', ['i' => $emp[0]]) . "\n";
echo "  Empresa pendente:  " . Database::val('SELECT email FROM empresas WHERE id = :i', ['i' => $emp[10]]) . "\n";
echo "  Empresa reprovada: " . Database::val('SELECT email FROM empresas WHERE id = :i', ['i' => $emp[12]]) . "\n";
echo "  Empresa bloqueada: " . Database::val('SELECT email FROM empresas WHERE id = :i', ['i' => $emp[13]]) . "\n";