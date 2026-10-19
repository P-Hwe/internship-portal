<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/schema.php';
migrar();

$email = mb_strtolower((string) env('ADMIN_EMAIL', 'admin@ifsp.local'));
$senha = (string) env('ADMIN_PASSWORD', '');
if (!Database::val('SELECT 1 FROM admins WHERE email = :e', ['e' => $email])) {
    if (strlen($senha) < 8) { $senha = bin2hex(random_bytes(6)); echo "ADMIN_PASSWORD não definida: senha temporária gerada -> $senha\n"; }
    Database::insert('admins', ['nome' => 'Coordenação', 'email' => $email, 'senha_hash' => Auth::hash($senha), 'criado_em' => Database::agora()]);
    echo "Administrador criado: $email\n";
} else { echo "Administrador já existe: $email\n"; }

if (!in_array('--demo', $argv ?? [], true)) exit;

if (Database::val('SELECT COUNT(*) FROM empresas') > 0) { echo "Já existem empresas; demo ignorada.\n"; exit; }
$emps = json_decode(file_get_contents(__DIR__ . '/seed/empresas.json'), true);
$vagas = json_decode(file_get_contents(__DIR__ . '/seed/vagas.json'), true);
$hoje = strtotime('today'); $d = fn(int $n) => date('Y-m-d', $hoje + $n * 86400);
$agora = Database::agora();
$perfil = [
    1 => ['Tecnologia da Informação', 'Média empresa', 'Desenvolvemos sistemas web e mobile para o varejo. Time jovem, mentoria constante e projetos reais desde o primeiro dia.', 'site'],
    2 => ['Indústria', 'Grande empresa', 'Indústria de componentes mecânicos com fábrica em Guarulhos. Programa de estágio com rodízio entre áreas.', 'site'],
    3 => ['Logística e Transporte', 'Média empresa', 'Operadora logística com centros de distribuição na Grande São Paulo e foco em eficiência e segurança.', 'linkedin'],
    4 => ['Tecnologia da Informação', 'Pequena empresa', 'Consultoria em dados e inteligência de negócios para pequenas e médias empresas.', 'site'],
    5 => ['Serviços', 'Pequena empresa', 'Escritório de serviços administrativos e financeiros para empresas locais.', 'site'],
    6 => ['Educação', 'Microempresa', 'Plataforma de cursos técnicos online com parcerias com institutos federais.', 'site'],
];
$idMap = [];
foreach ($emps as $e) {
    [$seg, $porte, $desc, $lp] = $perfil[$e['id']] ?? ['Serviços', 'Pequena empresa', 'Empresa parceira do campus Guarulhos com foco em oportunidades para estudantes.', 'site'];
    $st = ['aprovada' => 'aprovada', 'pendente' => 'pendente', 'reprovada' => 'reprovada'][$e['status']] ?? 'pendente';
    $site = $e['site'] ?: null;
    $idMap[$e['id']] = Database::insert('empresas', [
        'cnpj' => Cnpj::limpar($e['cnpj']), 'razao_social' => $e['nome'] . ' Ltda', 'nome' => $e['nome'], 'descricao' => $desc, 'segmento' => $seg, 'porte' => $porte,
        'endereco' => $e['endereco'], 'cep' => '07011000', 'logradouro' => 'Avenida Tiradentes', 'numero' => (string) (100 + $e['id']), 'bairro' => 'Centro', 'cidade' => 'Guarulhos', 'uf' => 'SP',
        'responsavel' => 'RH ' . $e['nome'], 'cargo' => 'Analista de RH', 'telefone' => preg_replace('/\D/', '', $e['telefone']), 'celular' => '11988776655', 'email' => $e['email'], 'email_contato' => $e['email'],
        'site' => $site, 'linkedin' => $lp === 'linkedin' || $e['id'] % 2 ? 'https://www.linkedin.com/company/' . preg_replace('/\W/', '', strtolower($e['nome'])) : null, 'link_principal' => $lp, 'senha_hash' => Auth::hash('demo12345'),
        'termos_aceitos_em' => $agora, 'status' => $st, 'motivo' => $st === 'reprovada' ? 'Termo de cooperação ilegível. Envie uma cópia digitalizada com boa resolução.' : null,
        'aprovacao_vista' => 1, 'criado_em' => date('Y-m-d H:i:s', $hoje - (30 - $e['id'] * 3) * 86400), 'avaliado_em' => $st === 'pendente' ? null : date('Y-m-d H:i:s', $hoje - (28 - $e['id'] * 3) * 86400),
        'ultimo_acesso' => in_array($e['id'], [1, 3], true) ? $agora : null,
    ]);
    Historico::registrar('empresa', $idMap[$e['id']], 'cadastro', null, 'pendente', 'empresa', $idMap[$e['id']]);
    if ($st !== 'pendente') Historico::registrar('empresa', $idMap[$e['id']], $st === 'aprovada' ? 'aprovada' : 'reprovada', 'pendente', $st, 'admin', 1);
}
$alfa = Database::insert('empresas', ['cnpj' => Cnpj::completar('AB3DE5GH0001'), 'razao_social' => 'Orbita Dados Tecnologia Ltda', 'nome' => 'Orbita Dados', 'descricao' => 'Startup de análise de dados com foco em dashboards e ciência de dados aplicada ao varejo.',
    'segmento' => 'Tecnologia da Informação', 'porte' => 'Pequena empresa', 'endereco' => 'Avenida Tiradentes, 800 - Centro, Guarulhos/SP - CEP 07011-000', 'cep' => '07011000', 'logradouro' => 'Avenida Tiradentes', 'numero' => '800', 'bairro' => 'Centro', 'cidade' => 'Guarulhos', 'uf' => 'SP',
    'responsavel' => 'Equipe de Talentos', 'cargo' => 'Head de Pessoas', 'telefone' => '11940001234', 'email' => 'talentos@orbita.example', 'site' => 'https://orbita.example', 'link_principal' => 'site', 'senha_hash' => Auth::hash('demo12345'),
    'termos_aceitos_em' => $agora, 'status' => 'aprovada', 'aprovacao_vista' => 1, 'criado_em' => date('Y-m-d H:i:s', $hoje - 5 * 86400), 'avaliado_em' => date('Y-m-d H:i:s', $hoje - 4 * 86400), 'ultimo_acesso' => $agora]);
Historico::registrar('empresa', $alfa, 'cadastro', null, 'pendente', 'empresa', $alfa); Historico::registrar('empresa', $alfa, 'aprovada', 'pendente', 'aprovada', 'admin', 1);
// empresa bloqueada (para demonstrar o estado)
$blq = Database::insert('empresas', ['cnpj' => Cnpj::completar('ZZ9XY8WV0001'), 'razao_social' => 'Casa Antiga Comércio Ltda', 'nome' => 'Casa Antiga', 'descricao' => 'Comércio varejista. Conta bloqueada para demonstração.', 'segmento' => 'Comércio', 'porte' => 'Microempresa',
    'endereco' => 'Rua Sete, 7 - Centro, Guarulhos/SP - CEP 07011-000', 'cep' => '07011000', 'logradouro' => 'Rua Sete', 'numero' => '7', 'bairro' => 'Centro', 'cidade' => 'Guarulhos', 'uf' => 'SP', 'responsavel' => 'Dono', 'cargo' => 'Sócio', 'telefone' => '1140001111',
    'email' => 'contato@casaantiga.example', 'senha_hash' => Auth::hash('demo12345'), 'termos_aceitos_em' => $agora, 'status' => 'bloqueada', 'motivo' => 'Vaga divulgada sem relação com estágio.', 'aprovacao_vista' => 1, 'criado_em' => date('Y-m-d H:i:s', $hoje - 20 * 86400), 'avaliado_em' => date('Y-m-d H:i:s', $hoje - 19 * 86400)]);
Historico::registrar('empresa', $blq, 'bloqueada', 'aprovada', 'bloqueada', 'admin', 1, 'Vaga divulgada sem relação com estágio.');

$mapV = ['aprovada' => 'aprovada', 'pendente' => 'aguardando_aprovacao', 'reprovada' => 'rejeitada'];
$n = 0;
$novaVaga = function (array $x) use ($agora) {
    $id = Database::insert('vagas', $x + ['criado_em' => $agora, 'versao' => 1]);
    $st = $x['status']; Historico::registrar('vaga', $id, 'criada', null, 'aguardando_aprovacao', 'empresa', (int) $x['empresa_id']);
    if ($st !== 'aguardando_aprovacao') Historico::registrar('vaga', $id, $st === 'rejeitada' ? 'rejeitada' : 'aprovada', 'aguardando_aprovacao', $st === 'rejeitada' ? 'rejeitada' : 'aprovada', 'admin', 1, $st === 'rejeitada' ? ($x['motivo'] ?? null) : null);
    if ($st === 'encerrada') Historico::registrar('vaga', $id, 'encerrada', 'aprovada', 'encerrada', 'empresa', (int) $x['empresa_id']);
    return $id;
};
foreach ($vagas as $v) {
    $n++; $st = $mapV[$v['status']] ?? 'aguardando_aprovacao';
    $novaVaga([
        'codigo' => $v['codigo'], 'empresa_id' => $idMap[$v['empresa_id']], 'titulo' => $v['titulo'], 'tipo' => 'Estágio', 'area' => $v['area'], 'modalidade' => $v['modalidade'], 'local' => $v['local'],
        'bolsa' => parse_money(str_replace('R$', '', (string) $v['bolsa'])), 'carga_horaria' => '6h/dia · Seg a Sex · Manhã', 'carga_horas_dia' => 6, 'carga_dias' => 'seg,ter,qua,qui,sex', 'carga_turno' => 'manha',
        'descricao' => $v['resumo'] . "\n" . $v['descricao'], 'atividades' => implode("\n", $v['atividades']), 'requisitos' => implode("\n", $v['requisitos']), 'requisitos_desejaveis' => "Inglês intermediário\nExperiência anterior na área",
        'beneficios' => implode("\n", $v['beneficios']), 'data_publicacao' => $st === 'aprovada' ? $d(-($n * 3 % 14)) : $d(0), 'data_limite' => $st === 'aprovada' ? $d(5 + $n * 13) : $d(30), 'contato' => $v['contato'],
        'imagem' => $v['imagem'] ?: null, 'destaque' => !empty($v['destaque']) ? 1 : 0, 'status' => $st, 'motivo' => $st === 'rejeitada' ? 'Informe a remuneração e a carga horária.' : null,
        'avaliado_em' => $st === 'aguardando_aprovacao' ? null : $agora, 'link_candidatura' => $n % 3 === 0 ? 'https://empresa.gupy.io/jobs/' . (1000 + $n) : null, 'link_plataforma' => $n % 3 === 0 ? 'gupy' : null,
    ]);
}

$ex = [
  ['TechNova Sistemas', 'Trainee de Engenharia de Software', 'Trainee', 'Tecnologia da Informação', 'Híbrido', 4800, 'aprovada', -3, 40, "Programa de 12 meses com rodízio entre squads de produto, dados e infraestrutura.\nVocê terá mentoria, projetos reais e plano de carreira."],
  ['Alfa Logística', 'Jovem Aprendiz em Operações', 'Jovem Aprendiz', 'Logística', 'Presencial', 1100, 'aprovada', -1, 25, "Primeiro emprego com carteira assinada no centro de distribuição, com formação teórica em parceria com o campus.\nAprenda rotinas de estoque e expedição."],
  ['Orbita Dados', 'Jovem Profissional em Análise de Dados', 'Jovem Profissional', 'Tecnologia da Informação', 'Remoto', 5200, 'aprovada', -2, 30, "Para quem acabou de se formar e quer atuar com SQL, Python e dashboards.\nTime enxuto, ritmo de startup e muito aprendizado."],
  ['Orbita Dados', 'Estágio em Ciência de Dados', 'Estágio', 'Tecnologia da Informação', 'Remoto', 2300, 'aprovada', 0, 6, "Apoio na limpeza, modelagem e visualização de dados de clientes.\nBolsa acima da média do mercado e horário flexível."],
  ['MecPrime Indústria', 'Jovem Aprendiz em Manufatura', 'Jovem Aprendiz', 'Engenharia', 'Presencial', 1150, 'aprovada', -4, 18, "Aprendizado na linha de produção com acompanhamento de supervisores.\nVale-transporte e refeição no local."],
  ['Alfa Logística', 'Estágio em Planejamento de Frota', 'Estágio', 'Logística', 'Híbrido', 1450, 'aprovada', 0, 3, "Auxílio no planejamento de rotas e controle de custos da frota.\nIdeal para quem cursa Logística ou Administração."],
  ['Orbita Dados', 'Estágio em Design Gráfico', 'Estágio', 'Design Gráfico', 'Híbrido', 1600, 'aprovada', -1, 45, "Criação de peças para redes sociais e apresentações.\nUso de Figma e Illustrator."],

  ['TechNova Sistemas', 'Estágio em QA e Testes (agendada)', 'Estágio', 'Qualidade', 'Remoto', 1800, 'aprovada', 10, 40, "Vaga aprovada com publicação futura: aparece como Agendada para a empresa e para a administração.\nTestes manuais e automatizados."],
  ['TechNova Sistemas', 'Estágio em Suporte (prazo encerrado)', 'Estágio', 'Tecnologia da Informação', 'Presencial', 1500, 'aprovada', -40, -5, "Vaga cujo prazo terminou: continua no sistema como Expirada.\nAtendimento de primeiro nível."],
  ['Alfa Logística', 'Estágio em Compras (encerrada pela empresa)', 'Estágio', 'Administração', 'Presencial', 1400, 'encerrada', -30, 10, "Vaga encerrada pela empresa antes do prazo: fica no histórico.\nApoio ao setor de compras."],
  ['Orbita Dados', 'Estágio em Marketing de Dados (em análise)', 'Estágio', 'Marketing', 'Remoto', 1700, 'aguardando_aprovacao', 0, 30, "Vaga aguardando aprovação da Coordenação.\nAnálise de campanhas e relatórios."],
  ['TechNova Sistemas', 'Trainee em Dados (rejeitada)', 'Trainee', 'Tecnologia da Informação', 'Híbrido', 3000, 'rejeitada', 0, 30, "Vaga rejeitada para demonstrar o motivo exibido à empresa.\nPrograma de formação em dados."],
];
foreach ($ex as $i => [$nomeEmp, $tit, $tipo, $area, $mod, $sal, $st, $pub, $lim, $desc]) {
    $eid = (int) Database::val('SELECT id FROM empresas WHERE nome = :n', ['n' => $nomeEmp]);
    if ($area === 'Design Gráfico' && !Database::val("SELECT 1 FROM areas WHERE nome = 'Design Gráfico'")) Database::insert('areas', ['nome' => 'Design Gráfico', 'padrao' => 0]);
    $novaVaga(['codigo' => 'VG-2026-1' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT), 'empresa_id' => $eid, 'titulo' => $tit, 'tipo' => $tipo, 'area' => $area, 'modalidade' => $mod, 'local' => 'Guarulhos/SP', 'bolsa' => $sal,
        'carga_horaria' => ($tipo === 'Estágio' ? '6h/dia' : '8h/dia') . ' · Seg a Sex · ' . ($tipo === 'Estágio' ? 'Manhã' : 'Integral'), 'carga_horas_dia' => $tipo === 'Estágio' ? 6 : 8, 'carga_dias' => 'seg,ter,qua,qui,sex', 'carga_turno' => $tipo === 'Estágio' ? 'manha' : 'integral',
        'descricao' => $desc, 'atividades' => "Apoiar a equipe nas rotinas do dia a dia\nParticipar de treinamentos", 'requisitos' => "Boa comunicação\nVontade de aprender", 'requisitos_desejaveis' => 'Inglês básico', 'beneficios' => "Vale-refeição\nVale-transporte\nPlano de saúde",
        'data_publicacao' => $d($pub), 'data_limite' => $d($lim), 'contato' => 'vagas@' . strtolower(preg_replace('/\W/', '', $nomeEmp)) . '.example', 'status' => $st, 'motivo' => $st === 'rejeitada' ? 'A descrição não informa as atividades do trainee. Detalhe o programa e reenvie.' : null,
        'avaliado_em' => $st === 'aguardando_aprovacao' ? null : $agora, 'aviso_encerrada' => 0]);
}
echo "Dados de demonstração criados (empresas: senha demo12345).\n";
