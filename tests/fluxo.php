<?php

$base = rtrim($argv[1] ?? 'http://127.0.0.1:8123', '/');
$baseCurta = isset($argv[2]) ? rtrim($argv[2], '/') : null;
define('TESTING', true);
require_once __DIR__ . '/../config.php';

if (!function_exists('parse_dinheiro')) {
    function parse_dinheiro(string $s): ?float
    {
        return parse_money($s);
    }
}
if (!function_exists('resumo_da_descricao')) {
    function resumo_da_descricao(string $desc, int $linhas = 3, int $max = 220): string
    {
        return description_resume($desc, $linhas, $max);
    }
}
$falhas = 0;
$ok = 0;
function t(string $nome, bool $cond, string $extra = ''): void
{
    global $falhas, $ok;
    if ($cond) {
        $ok++;
        echo "  ok    $nome\n";
    } else {
        $falhas++;
        echo "  FALHA $nome $extra\n";
    }
}
function secao(string $s): void
{
    echo "== $s ==\n";
}
class Cli
{
    public $jar;
    public $base;
    function __construct($base)
    {
        $this->base = $base;
        $this->jar = tempnam(sys_get_temp_dir(), 'cj');
    }
    function req(string $url, $post = null, bool $follow = true): array
    {
        $ch = curl_init($this->base . $url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $this->jar, CURLOPT_COOKIEFILE => $this->jar, CURLOPT_FOLLOWLOCATION => $follow, CURLOPT_HEADER => false, CURLOPT_USERAGENT => 'teste-fluxo']);
        if ($post !== null) {
            if (is_array($post) && !array_filter($post, fn($v) => $v instanceof CURLFile)) $post = http_build_query($post);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $loc = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
        $url2 = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        return [$code, (string) $body, $loc, $url2];
    }
    function csrf(string $url = '/?p=empresa-login'): string
    {
        [, $b] = $this->req($url);
        preg_match('/name="_csrf" value="([a-f0-9]+)"/', $b, $m);
        return $m[1] ?? '';
    }
    function post(string $url, array $d, string $paginaToken = '/?p=empresa-login', bool $follow = true): array
    {
        return $this->req($url, ['_csrf' => $this->csrf($paginaToken)] + $d, $follow);
    }
}
function db()
{
    return Database::pdo();
}
function q(string $sql, array $p = [])
{
    return Database::val($sql, $p);
}
function loginAdmin(Cli $c, string $e = 'admin@ifsp.local', string $s = 'Admin@2026!')
{
    return $c->post('/?p=admin-login', ['email' => $e, 'senha' => $s], '/?p=admin-login');
}
function loginEmpresa(Cli $c, string $e, string $s)
{
    return $c->post('/?p=empresa-login', ['email' => $e, 'senha' => $s]);
}
function acaoAdmin(Cli $a, string $tipo, string $acao, int $id, string $motivo = '')
{
    return $a->post('/?p=admin-acao', ['tipo' => $tipo, 'acao' => $acao, 'id' => $id, 'motivo' => $motivo, 'ret' => 'admin'], '/?p=admin');
}

secao('Unidade: CNPJ, links, estados, helpers');
t('CNPJ numérico válido', Cnpj::valido('11.222.333/0001-81'));
t('CNPJ alfanumérico oficial 12.ABC.345/01DE-35', Cnpj::valido('12.ABC.345/01DE-35'));
t('CNPJ dígito errado / letra no DV / repetido', !Cnpj::valido('12.ABC.345/01DE-36') && !Cnpj::valido('12.ABC.345/01DE-3A') && !Cnpj::valido('00.000.000/0000-00'));
t('Link válido https', Link::valido('https://empresa.com.br/vagas?id=1'));
t('Link bloqueia javascript:, ftp, credenciais e espaços', !Link::valido('javascript:alert(1)') && !Link::valido('ftp://x.com') && !Link::valido('https://user:pw@x.com') && !Link::valido('https://a b.com'));
t('Link por plataforma: gupy e linkedin', Link::valido('https://x.gupy.io/jobs/1', 'gupy') && !Link::valido('https://evil.com/gupy.io', 'gupy') && Link::valido('https://www.linkedin.com/company/x', 'linkedin') && !Link::valido('https://notlinkedin.com/x', 'linkedin'));
t('Link normaliza domínio sem esquema', Link::normalizar('empresa.com.br') === 'https://empresa.com.br' && Link::normalizar('') === null);
t('Máquina de estados: vaga', Estado::vagaPode('aguardando_aprovacao', 'aprovada') && Estado::vagaPode('aprovada', 'aguardando_aprovacao') && Estado::vagaPode('aprovada', 'encerrada') && !Estado::vagaPode('encerrada', 'aprovada') && !Estado::vagaPode('rejeitada', 'aprovada') && !Estado::vagaPode('aguardando_aprovacao', 'encerrada'));
t('Máquina de estados: empresa', Estado::empresaPode('pendente', 'aprovada') && Estado::empresaPode('aprovada', 'bloqueada') && Estado::empresaPode('bloqueada', 'aprovada') && !Estado::empresaPode('pendente', 'bloqueada') && Estado::empresaPode('reprovada', 'pendente'));
$hj = date('Y-m-d');
$v0 = ['status' => 'aprovada', 'data_publicacao' => $hj, 'data_limite' => $hj];
t('Situação por datas: agendada / publicada / expirada', Estado::situacaoVaga(['data_publicacao' => '2999-01-01'] + $v0) === 'agendada' && Estado::situacaoVaga($v0) === 'publicada' && Estado::situacaoVaga(['data_limite' => '2000-01-01', 'data_publicacao' => '1999-01-01'] + $v0) === 'expirada');
t('Situação: demais estados persistidos', Estado::situacaoVaga(['status' => 'aguardando_aprovacao'] + $v0) === 'aguardando' && Estado::situacaoVaga(['status' => 'rejeitada'] + $v0) === 'rejeitada' && Estado::situacaoVaga(['status' => 'encerrada'] + $v0) === 'encerrada');
t('Carga horária estruturada em texto', Vaga::cargaTexto(6, 'seg,ter,qua,qui,sex', 'manha') === '6h/dia · Seg a Sex · Manhã' && Vaga::cargaTexto(4, 'seg,qua', 'tarde') === '4h/dia · Seg, Qua · Tarde');
t('Dinheiro, telefone e breve descrição', parse_dinheiro('1.500,50') === 1500.5 && parse_dinheiro('abc') === null && fone_fmt('11900001234') === '11 90000-1234' && resumo_da_descricao("Um.\n\nDois.\nTrês.\nQuatro.") === 'Um. Dois. Três.');
t('Senha forte', EmpresaForm::senhaForte('abc12345') && !EmpresaForm::senhaForte('12345678') && !EmpresaForm::senhaForte('abcdefgh') && !EmpresaForm::senhaForte('a1'));

$visitante = new Cli($base);
$A = new Cli($base);
$B = new Cli($base);
$adm = new Cli($base);

secao('Estudante: páginas públicas');
foreach (['/', '/?p=empresas', '/?p=encerradas', '/?p=tv'] as $u) t("público abre $u", $visitante->req($u)[0] === 200);
[, $h] = $visitante->req('/');
t('menu público sem links de admin e com "Empresas parceiras"', !str_contains($h, 'p=admin') && str_contains($h, 'Empresas parceiras') && str_contains($h, 'Sou empresa'));
[$c] = $visitante->req('/storage/database.sqlite');
t('banco não é baixável', in_array($c, [403, 404], true));
[$c] = $visitante->req('/.env');
t('.env não é baixável', in_array($c, [403, 404], true));
[$c] = $visitante->req('/docs/GUIA.md');
t('docs/ não é servida', in_array($c, [403, 404], true));

secao('Segurança: rotas protegidas sem login');
foreach (['admin', 'admin-vagas', 'admin-empresas', 'admin-aprovacoes', 'admin-usuarios', 'admin-acesso', 'admin-config', 'admin-relatorios', 'admin-exportar&tipo=vagas', 'admin-termo&id=1'] as $r) {
    [$c] = $visitante->req("/?p=$r", null, false);
    t("anônimo bloqueado em ?p=$r", $c === 302);
}
foreach (['painel', 'empresa-vagas', 'empresa-perfil', 'vaga-cadastro', 'vaga-editar&id=1', 'empresa-vaga&id=1'] as $r) {
    [$c] = $visitante->req("/?p=$r", null, false);
    t("anônimo bloqueado em ?p=$r", $c === 302);
}
[$c] = $visitante->req('/?p=admin-acao', ['tipo' => 'vaga', 'acao' => 'aprovar', 'id' => 1], false);
t('POST admin-acao sem login bloqueado', $c === 302);
[$c] = $visitante->req('/?p=empresa-login', ['email' => 'x@x.com', 'senha' => 'y']);
t('POST sem CSRF rejeitado (419)', $c === 419);
[, $lg] = $visitante->req('/?p=empresa-login');
t('login da empresa sem header/footer do IFSP', !str_contains($lg, 'gru.ifsp.edu.br') && !str_contains($lg, 'Início da navegação de rodapé') && str_contains($lg, 'Ambiente da empresa'));
[, $lg] = $visitante->req('/?p=admin-login');
t('login do admin sem header/footer do IFSP', !str_contains($lg, 'gru.ifsp.edu.br') && str_contains($lg, 'Ambiente administrativo'));
[, $lg] = $visitante->req('/?p=empresa-cadastro');
t('cadastro em 5 etapas, sem header/footer do IFSP', substr_count($lg, 'class="ui-fieldset ui-step"') === 5 && !str_contains($lg, 'gru.ifsp.edu.br') && str_contains($lg, 'data-viacep'));

secao('Empresa: cadastro em etapas');
$pdf = tempnam(sys_get_temp_dir(), 'pdf');
file_put_contents($pdf, "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF");
$exe = tempnam(sys_get_temp_dir(), 'x');
file_put_contents($exe, "<?php echo 1;");
$cnpjA = Cnpj::completar('K7P2M9Q1R0AB');
$cnpjB = '11222333000181';
$dadosA = [
    'cnpj' => Cnpj::formatar($cnpjA),
    'razao_social' => 'Verde Ágil Tecnologia Ltda',
    'nome' => 'Verde Ágil',
    'segmento' => 'Tecnologia da Informação',
    'porte' => 'Pequena empresa',
    'descricao' => 'Empresa de tecnologia que desenvolve sistemas para o varejo e recebe estagiários.',
    'site' => 'verdeagil.example',
    'linkedin' => 'https://www.linkedin.com/company/verdeagil',
    'link_principal' => 'linkedin',
    'cep' => '07011-000',
    'logradouro' => 'Rua das Flores',
    'numero' => '100',
    'complemento' => 'Sala 2',
    'bairro' => 'Centro',
    'cidade' => 'Guarulhos',
    'uf' => 'SP',
    'responsavel' => 'Marina Souza',
    'cargo' => 'Analista de RH',
    'telefone' => '11 98877-6655',
    'celular' => '11 98877-6655',
    'email_contato' => 'contato@verdeagil.example',
    'email' => 'rh@verdeagil.example',
    'senha' => 'senha-forte-1',
    'senha2' => 'senha-forte-1',
    'aceite_termos' => '1',
    'aceite_privacidade' => '1'
];
$envia = function (Cli $c, array $d, $arq = null) use ($pdf) {
    $tok = $c->csrf('/?p=empresa-cadastro');
    return $c->req('/?p=empresa-cadastro', ['_csrf' => $tok] + $d + ['termo' => new CURLFile($arq ?? $pdf, 'application/pdf', 't.pdf')]);
};
[, $r] = $envia($A, array_merge($dadosA, ['cnpj' => '12.ABC.345/01DE-36']));
t('CNPJ inválido recusado', str_contains($r, 'CNPJ inválido'));
[, $r] = $envia($A, array_merge($dadosA, ['senha' => '12345678', 'senha2' => '12345678']));
t('senha só com números recusada', str_contains($r, 'letras e números'));
[, $r] = $envia($A, array_merge($dadosA, ['aceite_termos' => '']));
t('sem aceite dos termos recusado', str_contains($r, 'aceitar os termos'));
[, $r] = $envia($A, array_merge($dadosA, ['cep' => '0701']));
t('CEP incompleto recusado', str_contains($r, 'CEP inválido'));
[, $r] = $envia($A, array_merge($dadosA, ['uf' => 'XX']));
t('UF inválida recusada', str_contains($r, 'Escolha o estado'));
[, $r] = $envia($A, array_merge($dadosA, ['linkedin' => 'https://evil.example/x']));
t('LinkedIn de outro domínio recusado', str_contains($r, 'LinkedIn da empresa'));
[, $r] = $envia($A, array_merge($dadosA, ['descricao' => 'curta']));
t('descrição curta recusada', str_contains($r, '30 a 600'));
[, $r] = $envia($A, $dadosA, $exe);
t('arquivo PHP disfarçado de PDF recusado pelo conteúdo', str_contains($r, 'Formato não permitido'));
t('nada foi gravado com dados inválidos', (int) q('SELECT COUNT(*) FROM empresas') === 0);
[, $r,, $u] = $envia($A, $dadosA);
t('cadastro concluído mostra etapa de conclusão', str_contains($r, 'Cadastro enviado') && str_contains($u, 'empresa-concluido'));
$idA = (int) q("SELECT id FROM empresas WHERE cnpj = :c", ['c' => $cnpjA]);
$rowA = Database::one('SELECT * FROM empresas WHERE id = :i', ['i' => $idA]);
t('empresa gravada como pendente com campos novos', $rowA['status'] === 'pendente' && $rowA['razao_social'] === 'Verde Ágil Tecnologia Ltda' && $rowA['segmento'] === 'Tecnologia da Informação' && $rowA['porte'] === 'Pequena empresa' && $rowA['cargo'] === 'Analista de RH');
t('endereço estruturado e composto', $rowA['cep'] === '07011000' && $rowA['uf'] === 'SP' && $rowA['endereco'] === 'Rua das Flores, 100, Sala 2 - Centro, Guarulhos/SP - CEP 07011-000');
t('site normalizado e link principal salvo', $rowA['site'] === 'https://verdeagil.example' && $rowA['link_principal'] === 'linkedin');
t('termos aceitos registrados, senha com hash, telefone só dígitos', $rowA['termos_aceitos_em'] !== null && str_starts_with($rowA['senha_hash'], '$2y$') && $rowA['telefone'] === '11988776655');
t('histórico registra o cadastro', (int) q("SELECT COUNT(*) FROM historico WHERE entidade='empresa' AND entidade_id = :i AND acao='cadastro'", ['i' => $idA]) === 1);
$logs = db()->query('SELECT destinatario, assunto FROM emails_log')->fetchAll(PDO::FETCH_ASSOC);
t('e-mail ao admin e confirmação à empresa', (bool) array_filter($logs, fn($l) => $l['destinatario'] === 'admin@ifsp.local' && str_contains($l['assunto'], 'Verde Ágil')) && (bool) array_filter($logs, fn($l) => $l['destinatario'] === 'rh@verdeagil.example'));
[, $pp] = $A->req('/?p=painel');
t('painel mostra "Cadastro em análise" (layout independente)', str_contains($pp, 'Cadastro em análise') && str_contains($pp, 'Sair') && !str_contains($pp, 'gru.ifsp.edu.br'));
[$c,, $l] = $A->req('/?p=vaga-cadastro', null, false);
t('empresa pendente não publica vaga', $c === 302 && str_contains($l, 'p=painel'));
[, $r] = $envia(new Cli($base), $dadosA);
t('CNPJ duplicado recusado', str_contains($r, 'já está cadastrado'));

secao('Administração: login, aprovação de empresa');
[, $r] = loginAdmin($adm, 'admin@ifsp.local', 'errada');
t('senha errada não entra', str_contains($r, 'incorretos'));
[, $r] = loginAdmin($adm);
t('admin entra no dashboard', str_contains($r, 'Dashboard') && str_contains($r, 'Aguardando sua análise'));
t('empresa pendente aparece em Aprovações com termo', str_contains($adm->req('/?p=admin-aprovacoes')[1], 'Verde Ágil') && $adm->req('/?p=admin-termo&id=' . $idA)[0] === 200);
[, $r] = acaoAdmin($adm, 'empresa', 'reprovar', $idA, '');
t('reprovar exige motivo (backend)', str_contains($r, 'Informe o motivo') && q('SELECT status FROM empresas WHERE id = :i', ['i' => $idA]) === 'pendente');
acaoAdmin($adm, 'empresa', 'aprovar', $idA);
t('empresa aprovada + histórico', q('SELECT status FROM empresas WHERE id = :i', ['i' => $idA]) === 'aprovada' && (int) q("SELECT COUNT(*) FROM historico WHERE entidade='empresa' AND entidade_id = :i AND acao='aprovada'", ['i' => $idA]) === 1);
t('e-mail de aprovação à empresa', (bool) q("SELECT 1 FROM emails_log WHERE destinatario='rh@verdeagil.example' AND assunto LIKE 'Cadastro aprovado%'"));
[, $r] = acaoAdmin($adm, 'empresa', 'aprovar', $idA);
t('estado inválido: aprovar de novo é recusado', str_contains($r, 'não é permitida'));
[, $pp] = $A->req('/?p=painel');
t('aviso "Empresa aprovada" aparece', str_contains($pp, 'Empresa aprovada') && str_contains($pp, 'Publicar vaga'));
$A->post('/?p=aviso-dispensar', [], '/?p=painel');
[, $pp] = $A->req('/?p=painel');
t('aviso some ao dispensar (controlado pelo backend)', !str_contains($pp, 'Empresa aprovada</strong>'));

secao('Empresa: publicar vaga em etapas e fluxo de aprovação');
$hoje = date('Y-m-d');
$lim = date('Y-m-d', strtotime('+20 days'));
$vagaPost = [
    'tipo' => 'Trainee',
    'titulo' => 'Trainee de Moda Sustentável',
    'area' => AREA_OUTRA,
    'area_outra' => 'moda sustentável',
    'modalidade' => 'Híbrido',
    'local' => 'Guarulhos/SP',
    'descricao' => "Programa de trainee em moda com foco em economia circular.\nVocê vai atuar com curadoria e produção.\nTerceira linha importante.\nQuarta linha que não deve aparecer no card.",
    'requisitos' => "Cursando graduação\nBoa comunicação",
    'requisitos_desejaveis' => 'Inglês',
    'beneficios' => "Vale-refeição\nPlano de saúde",
    'atividades' => 'Pesquisar tendências',
    'remuneracao_tipo' => 'valor',
    'bolsa' => '9000,00',
    'carga_horas_dia' => '8',
    'dias' => ['seg', 'ter', 'qua', 'qui', 'sex'],
    'carga_turno' => 'integral',
    'data_publicacao' => $hoje,
    'data_limite' => $lim,
    'link_plataforma' => 'gupy',
    'link_candidatura' => 'https://verdeagil.gupy.io/jobs/1',
    'link_site' => '',
    'link_linkedin' => '',
    'contato' => ''
];
$enviaVaga = fn(Cli $c, array $d, string $url = '/?p=vaga-cadastro') => $c->req($url, ['_csrf' => $c->csrf('/?p=painel')] + $d);
[, $r] = $enviaVaga($A, array_merge($vagaPost, ['requisitos' => '']));
t('requisito obrigatório', str_contains($r, 'ao menos um requisito'));
[, $r] = $enviaVaga($A, array_merge($vagaPost, ['remuneracao_tipo' => 'valor', 'bolsa' => 'abc']));
t('remuneração só numérica', str_contains($r, 'valor mensal válido'));
[, $r] = $enviaVaga($A, array_merge($vagaPost, ['dias' => []]));
t('ao menos um dia da semana', str_contains($r, 'ao menos um dia'));
[, $r] = $enviaVaga($A, array_merge($vagaPost, ['carga_horas_dia' => '20']));
t('horas por dia de 1 a 12', str_contains($r, '1 a 12'));
[, $r] = $enviaVaga($A, array_merge($vagaPost, ['data_limite' => $hoje]));
t('data final depois da inicial', str_contains($r, 'depois da inicial'));
[, $r] = $enviaVaga($A, array_merge($vagaPost, ['link_candidatura' => 'https://evil.example/x']));
t('link Gupy precisa ser gupy.io', str_contains($r, 'gupy.io'));
[, $r] = $enviaVaga($A, array_merge($vagaPost, ['link_candidatura' => 'javascript:alert(1)', 'link_plataforma' => 'outro']));
t('link javascript: recusado', str_contains($r, 'inválido'));
[, $r] = $enviaVaga($A, array_merge($vagaPost, ['link_candidatura' => '', 'link_plataforma' => '', 'contato' => '']));
t('exige e-mail ou link de candidatura', str_contains($r, 'e-mail ou um link'));
[, $r] = $enviaVaga($A, array_merge($vagaPost, ['area_outra' => '']));
t('"Outra" sem nome da área recusada', str_contains($r, 'nome da área'));
[, $r] = $enviaVaga($A, $vagaPost);
$idV = (int) q("SELECT id FROM vagas WHERE titulo='Trainee de Moda Sustentável'");
$V = Database::one('SELECT * FROM vagas WHERE id = :i', ['i' => $idV]);
t('vaga enviada: estado aguardando_aprovacao, versão 1, código gerado', $V['status'] === 'aguardando_aprovacao' && (int) $V['versao'] === 1 && preg_match('/^VG-\d{4}-\d{4}$/', $V['codigo']) === 1);
t('campos estruturados gravados', $V['carga_horas_dia'] == 8 && $V['carga_dias'] === 'seg,ter,qua,qui,sex' && $V['carga_turno'] === 'integral' && $V['carga_horaria'] === '8h/dia · Seg a Sex · Integral' && (float) $V['bolsa'] === 9000.0 && $V['link_plataforma'] === 'gupy');
t('área nova criada e normalizada', $V['area'] === 'Moda sustentável' && (int) q("SELECT COUNT(*) FROM areas WHERE nome LIKE 'Moda%'") === 1);
t('vaga em análise: não aparece no site (410) nem na home', $visitante->req("/?p=vaga&id=$idV")[0] === 410 && !str_contains($visitante->req('/')[1], 'Trainee de Moda'));
t('empresa vê a vaga em "Em análise"', str_contains($A->req('/?p=empresa-vagas&aba=aguardando')[1], 'Trainee de Moda Sustentável'));
t('admin recebeu aviso (notificação + e-mail)', (bool) q("SELECT 1 FROM notificacoes WHERE tipo='vaga' AND referencia_id = :i", ['i' => $idV]) && (bool) q("SELECT 1 FROM emails_log WHERE destinatario='admin@ifsp.local' AND assunto LIKE 'Nova vaga aguardando%'"));
t('admin vê a vaga em Aprovações', str_contains($adm->req('/?p=admin-aprovacoes')[1], 'Trainee de Moda Sustentável'));
[, $r] = acaoAdmin($adm, 'vaga', 'rejeitar', $idV, 'x');
t('rejeitar exige motivo com 5+ caracteres', str_contains($r, 'Informe o motivo') && q('SELECT status FROM vagas WHERE id = :i', ['i' => $idV]) === 'aguardando_aprovacao');
acaoAdmin($adm, 'vaga', 'aprovar', $idV);
t('vaga aprovada vira "publicada" (situação pelas datas)', q('SELECT status FROM vagas WHERE id = :i', ['i' => $idV]) === 'aprovada' && Estado::situacaoVaga(Database::one('SELECT * FROM vagas WHERE id = :i', ['i' => $idV])) === 'publicada');
$mailAprov = false;
foreach (glob(ROOT_PATH . '/storage/mail/*.html') as $f) {
    $h = file_get_contents($f);
    if (str_contains($h, 'Vaga aprovada') && str_contains($h, data_br($lim))) $mailAprov = true;
}
t('e-mail de vaga aprovada informa até quando fica no ar', $mailAprov);
[, $home] = $visitante->req('/');
t('vaga aprovada aparece na home (selo Trainee, 3 linhas)', str_contains($home, 'Trainee de Moda Sustentável') && str_contains($home, 'vg-tag-tipo">Trainee') && str_contains($home, 'Terceira linha importante.') && !str_contains($home, 'Quarta linha que não deve'));
t('área nova com 1 vaga vai para "Outras" no filtro', str_contains($home, '<option>Outras</option>') && !str_contains($home, '<option>Moda sustentável</option>'));
[$c, $d] = $visitante->req("/?p=vaga&id=$idV");
t('detalhe público com CNPJ alfanumérico, carga estruturada e botão Gupy', $c === 200 && str_contains($d, Cnpj::formatar($cnpjA)) && str_contains($d, '8h/dia · Seg a Sex · Integral') && str_contains($d, 'pela Gupy') && str_contains($d, 'rel="noopener noreferrer nofollow"'));
t('admin vê a vaga em "Publicadas"', str_contains($adm->req('/?p=admin-vagas&aba=publicada')[1], 'Trainee de Moda Sustentável') && !str_contains($adm->req('/?p=admin-aprovacoes')[1], 'Trainee de Moda Sustentável'));
t('histórico da vaga: criada e aprovada', (int) q("SELECT COUNT(*) FROM historico WHERE entidade='vaga' AND entidade_id = :i AND acao IN ('criada','aprovada')", ['i' => $idV]) === 2);

secao('BUG CRÍTICO: aprovada com data futura (agendada) não pode "sumir"');
$futura = array_merge($vagaPost, ['titulo' => 'Estágio Agendado de Teste', 'tipo' => 'Estágio', 'area' => 'Administração', 'area_outra' => '', 'data_publicacao' => date('Y-m-d', strtotime('+5 days')), 'data_limite' => date('Y-m-d', strtotime('+30 days'))]);
$enviaVaga($A, $futura);
$idF = (int) q("SELECT id FROM vagas WHERE titulo='Estágio Agendado de Teste'");
acaoAdmin($adm, 'vaga', 'aprovar', $idF);
t('aprovada com publicação futura fica "Agendada" para o admin', str_contains($adm->req('/?p=admin-vagas&aba=agendada')[1], 'Estágio Agendado de Teste'));
t('...e para a empresa', str_contains($A->req('/?p=empresa-vagas&aba=agendada')[1], 'Estágio Agendado de Teste'));
t('...mas não aparece no site e dá 410', !str_contains($visitante->req('/')[1], 'Estágio Agendado') && $visitante->req("/?p=vaga&id=$idF")[0] === 410);
t('...e não some das contagens do admin (todas = soma das situações)', array_sum(array_diff_key(Vaga::contagens(), ['todas' => 1, 'destaque' => 1])) === Vaga::contagens()['todas']);
db()->exec("UPDATE vagas SET data_publicacao = '$hoje' WHERE id = $idF");
t('ao chegar a data inicial, entra no ar sozinha', str_contains($visitante->req('/')[1], 'Estágio Agendado de Teste'));

secao('Edição de vaga aprovada exige nova aprovação');
$edit = array_merge($vagaPost, ['titulo' => 'Trainee de Moda Sustentável']);   // sem mudança
[, $r] = $enviaVaga($A, $edit, "/?p=vaga-editar&id=$idV");
t('salvar sem alterações não muda o estado', q('SELECT status FROM vagas WHERE id = :i', ['i' => $idV]) === 'aprovada' && (int) q('SELECT versao FROM vagas WHERE id = :i', ['i' => $idV]) === 1);
$crlf = array_merge($vagaPost, ['descricao' => str_replace("\n", "\r\n", $vagaPost['descricao']), 'requisitos' => str_replace("\n", "\r\n", $vagaPost['requisitos'])]);   // o navegador envia CRLF
$enviaVaga($A, $crlf, "/?p=vaga-editar&id=$idV");
t('salvar sem alterar com quebras de linha do navegador (CRLF) não pede reaprovação', q('SELECT status FROM vagas WHERE id = :i', ['i' => $idV]) === 'aprovada' && (int) q('SELECT versao FROM vagas WHERE id = :i', ['i' => $idV]) === 1);
$edit2 = array_merge($vagaPost, ['titulo' => 'Trainee de Moda Circular', 'bolsa' => '9500,00']);
$enviaVaga($A, $edit2, "/?p=vaga-editar&id=$idV");
$V = Database::one('SELECT * FROM vagas WHERE id = :i', ['i' => $idV]);
t('editar vaga aprovada volta para análise (versão 2)', $V['status'] === 'aguardando_aprovacao' && (int) $V['versao'] === 2 && $V['titulo'] === 'Trainee de Moda Circular' && $V['avaliado_em'] === null);
t('...sai do ar imediatamente', !str_contains($visitante->req('/')[1], 'Trainee de Moda Circular') && $visitante->req("/?p=vaga&id=$idV")[0] === 410);
t('...histórico registra a edição com os campos alterados', (bool) q("SELECT 1 FROM historico WHERE entidade='vaga' AND entidade_id = :i AND acao='editada' AND detalhe LIKE '%titulo%'", ['i' => $idV]));
t('...admin é avisado da nova análise', (bool) q("SELECT 1 FROM notificacoes WHERE mensagem LIKE 'Vaga editada aguarda nova aprovação%'") && (bool) q("SELECT 1 FROM emails_log WHERE assunto LIKE 'Vaga editada aguarda%'"));
acaoAdmin($adm, 'vaga', 'aprovar', $idV);
t('...só volta ao ar depois da nova aprovação', str_contains($visitante->req('/')[1], 'Trainee de Moda Circular'));

secao('Rejeição, correção e reenvio');
$enviaVaga($A, array_merge($vagaPost, ['titulo' => 'Vaga para Rejeitar', 'tipo' => 'Estágio', 'area' => 'Administração', 'area_outra' => '']));
$idR = (int) q("SELECT id FROM vagas WHERE titulo='Vaga para Rejeitar'");
acaoAdmin($adm, 'vaga', 'rejeitar', $idR, 'Faltam informações sobre as atividades.');
t('vaga rejeitada com motivo + e-mail', q('SELECT status FROM vagas WHERE id = :i', ['i' => $idR]) === 'rejeitada' && (bool) q("SELECT 1 FROM emails_log WHERE destinatario='rh@verdeagil.example' AND assunto LIKE 'Vaga não aprovada%'"));
t('empresa vê o motivo na vaga e na aba Rejeitadas', str_contains($A->req("/?p=empresa-vaga&id=$idR")[1], 'Faltam informações sobre as atividades') && str_contains($A->req('/?p=empresa-vagas&aba=rejeitada')[1], 'Vaga para Rejeitar'));
[, $r] = acaoAdmin($adm, 'vaga', 'aprovar', $idR);
t('estado inválido: não aprova vaga rejeitada', str_contains($r, 'não é permitida') && q('SELECT status FROM vagas WHERE id = :i', ['i' => $idR]) === 'rejeitada');
$enviaVaga($A, array_merge($vagaPost, ['titulo' => 'Vaga Corrigida e Reenviada', 'tipo' => 'Estágio', 'area' => 'Administração', 'area_outra' => '']), "/?p=vaga-editar&id=$idR");
t('corrigir e salvar reenvia para análise', q('SELECT status FROM vagas WHERE id = :i', ['i' => $idR]) === 'aguardando_aprovacao' && (bool) q("SELECT 1 FROM historico WHERE entidade='vaga' AND entidade_id = :i AND acao='reenviada'", ['i' => $idR]));

secao('Encerramento, expiração e histórico (nada é excluído)');
$tok = $A->csrf('/?p=painel');
[, $r] = $A->req('/?p=vaga-encerrar', ['_csrf' => $tok, 'id' => $idR]);
t('encerrar vaga que não está aprovada é recusado', q('SELECT status FROM vagas WHERE id = :i', ['i' => $idR]) === 'aguardando_aprovacao');
$antes = (int) q('SELECT COUNT(*) FROM vagas');
$A->req('/?p=vaga-encerrar', ['_csrf' => $tok, 'id' => $idF]);
t('empresa encerra a própria vaga: status encerrada, registro mantido', q('SELECT status FROM vagas WHERE id = :i', ['i' => $idF]) === 'encerrada' && (int) q('SELECT COUNT(*) FROM vagas') === $antes);
t('encerrada some da home mas o detalhe vira histórico (200 + aviso)', !str_contains($visitante->req('/')[1], 'Estágio Agendado') && str_contains($visitante->req("/?p=vaga&id=$idF")[1], 'não recebe mais inscrições') && str_contains($visitante->req('/?p=encerradas')[1], 'Estágio Agendado de Teste'));
t('detalhe encerrado não mostra botão de candidatura', !str_contains($visitante->req("/?p=vaga&id=$idF")[1], 'Como se candidatar'));
[$c,, $l] = $A->req("/?p=vaga-editar&id=$idF", null, false);
t('vaga encerrada não pode ser editada', $c === 302);
[, $r] = acaoAdmin($adm, 'vaga', 'encerrar', $idF);
t('estado inválido: admin não encerra de novo', str_contains($r, 'não é permitida'));
db()->exec("UPDATE vagas SET data_limite = '" . date('Y-m-d', strtotime('-2 days')) . "', data_publicacao = '" . date('Y-m-d', strtotime('-20 days')) . "' WHERE id = $idV");
t('prazo terminou: some da home, vira Expirada (não "encerrada")', !str_contains($visitante->req('/')[1], 'Trainee de Moda Circular') && str_contains($A->req('/?p=empresa-vagas&aba=expirada')[1], 'Trainee de Moda Circular') && q('SELECT status FROM vagas WHERE id = :i', ['i' => $idV]) === 'aprovada');
t('expirada continua acessível como histórico', str_contains($visitante->req("/?p=vaga&id=$idV")[1], 'não recebe mais inscrições') && str_contains($adm->req('/?p=admin-vagas&aba=expirada')[1], 'Trainee de Moda Circular'));
$out = shell_exec('DB_SQLITE=' . escapeshellarg(env('DB_SQLITE', ROOT_PATH . '/storage/database.sqlite')) . ' MAIL_DRIVER=log php ' . escapeshellarg(ROOT_PATH . '/cron/rotina.php'));
t('rotina diária só avisa: não altera o estado e não exclui', str_contains((string) $out, 'avisos') && q('SELECT status FROM vagas WHERE id = :i', ['i' => $idV]) === 'aprovada' && (int) q('SELECT aviso_encerrada FROM vagas WHERE id = :i', ['i' => $idV]) === 1 && (bool) q("SELECT 1 FROM historico WHERE entidade='vaga' AND entidade_id = :i AND acao='expirada'", ['i' => $idV]), (string) $out);

secao('Empresa B + isolamento entre empresas (IDOR)');
$dadosB = array_merge($dadosA, ['cnpj' => '11.222.333/0001-81', 'razao_social' => 'Beta Indústria Ltda', 'nome' => 'Beta Indústria', 'email' => 'rh@beta.example', 'site' => '', 'linkedin' => '', 'link_principal' => 'site']);
$envia($B, $dadosB);
$idB = (int) q("SELECT id FROM empresas WHERE cnpj = :c", ['c' => $cnpjB]);
acaoAdmin($adm, 'empresa', 'aprovar', $idB);
$B->req('/?p=empresa-login');
$enviaVaga($B, array_merge($vagaPost, ['titulo' => 'Vaga da Beta Industrial', 'tipo' => 'Estágio', 'area' => 'Engenharia', 'area_outra' => '', 'link_plataforma' => '', 'link_candidatura' => '', 'contato' => 'rh@beta.example']));
$idVB = (int) q("SELECT id FROM vagas WHERE titulo='Vaga da Beta Industrial'");
acaoAdmin($adm, 'vaga', 'aprovar', $idVB);
t('B existe e tem a própria vaga publicada', $idVB > 0 && q('SELECT empresa_id FROM vagas WHERE id = :i', ['i' => $idVB]) == $idB);
foreach (["empresa-vaga&id=$idV", "vaga-editar&id=$idV"] as $r) {
    [$c] = $B->req("/?p=$r");
    t("B não acessa vaga da A (?p=$r)", $c === 404);
}
$tokB = $B->csrf('/?p=painel');
$B->req('/?p=vaga-encerrar', ['_csrf' => $tokB, 'id' => $idV]);
t('B não consegue encerrar vaga da A (POST direto)', q('SELECT status FROM vagas WHERE id = :i', ['i' => $idV]) === 'aprovada');
$B->req("/?p=vaga-editar&id=$idV", ['_csrf' => $tokB] + array_merge($vagaPost, ['titulo' => 'SEQUESTRADA POR B']));
t('B não consegue editar vaga da A (POST direto)', q('SELECT titulo FROM vagas WHERE id = :i', ['i' => $idV]) === 'Trainee de Moda Circular');
t('B não vê a vaga da A na própria lista', !str_contains($B->req('/?p=empresa-vagas')[1], 'Trainee de Moda') && str_contains($B->req('/?p=empresa-vagas')[1], 'Vaga da Beta Industrial'));
foreach (['abc', '-1', '9999999', '1 OR 1=1', "1'"] as $x) {
    [$c] = $B->req('/?p=empresa-vaga&id=' . rawurlencode($x));
    t("id manipulado '$x' não vaza dados nem gera erro 500", in_array($c, [404], true));
}
[$c] = $B->req('/?p=empresa-vaga&id[]=1');
t('id como array não gera erro 500', $c === 404);
t('tentativas negadas ficam no log de acessos', (int) q("SELECT COUNT(*) FROM acessos_log WHERE evento='negado'") >= 4);

secao('Sessões isoladas: empresa x admin');
foreach (['admin', 'admin-vagas', 'admin-empresas', 'admin-usuarios', 'admin-acesso', 'admin-exportar&tipo=vagas', "admin-termo&id=$idA", 'admin-login'] as $r) {
    [$c,, $l] = $B->req("/?p=$r", null, false);
    t("empresa logada bloqueada em ?p=$r", $c === 302 && str_contains($l, 'p=painel'));
}
[$c] = $B->req('/?p=admin-acao', ['_csrf' => $tokB, 'tipo' => 'empresa', 'acao' => 'aprovar', 'id' => $idA], false);
t('empresa não executa ação administrativa', $c === 302);
t('empresa não consegue se aprovar/desbloquear', q('SELECT status FROM empresas WHERE id = :i', ['i' => $idB]) === 'aprovada');
foreach (['painel', 'empresa-vagas', 'empresa-perfil', 'vaga-cadastro', 'empresa-login', 'empresa-cadastro', "empresa-vaga&id=$idV"] as $r) {
    [$c,, $l] = $adm->req("/?p=$r", null, false);
    t("admin logado fora da área da empresa (?p=$r)", $c === 302 && str_contains($l, 'p=admin'));
}
[, $hAdm] = $adm->req('/?p=admin');
[, $hEmp] = $B->req('/?p=painel');
t('menus exclusivos por perfil', str_contains($hAdm, 'Controle de acesso') && !str_contains($hAdm, 'Publicar vaga') && str_contains($hEmp, 'Publicar vaga') && !str_contains($hEmp, 'Controle de acesso'));
[, $r] = $B->post('/?p=admin-login', ['email' => 'admin@ifsp.local', 'senha' => 'Admin@2026!'], '/?p=painel');
t('empresa logada não vira admin postando credenciais', !str_contains($r, 'Dashboard'));
[, $pub] = $adm->req('/');
t('site público mostra "Administração" só para admin e "Sair"', str_contains($pub, 'Administração') && str_contains($pub, 'Sair'));

secao('Bloqueio de empresa e sessão invalidada');
acaoAdmin($adm, 'empresa', 'bloquear', $idB, 'Teste de bloqueio');
t('empresa bloqueada: sessão cai na próxima requisição', $B->req('/?p=painel', null, false)[0] === 302);
t('...vagas dela saem do site', !str_contains($visitante->req('/')[1], 'Vaga da Beta Industrial') && $visitante->req("/?p=vaga&id=$idVB")[0] === 410);
[, $r] = loginEmpresa(new Cli($base), 'rh@beta.example', 'senha-forte-1');
t('...e não consegue entrar', str_contains($r, 'bloqueada'));
[, $r] = acaoAdmin($adm, 'empresa', 'desbloquear', $idB);
t('desbloquear restaura: vaga volta ao site', str_contains($visitante->req('/')[1], 'Vaga da Beta Industrial'));

secao('Força bruta e controle de acesso');
$bf = new Cli($base);
for ($i = 0; $i < 5; $i++) loginEmpresa($bf, 'rh@beta.example', 'errada-' . $i);
[, $r] = loginEmpresa($bf, 'rh@beta.example', 'senha-forte-1');
t('após 5 falhas o login é travado, mesmo com a senha certa', str_contains($r, 'Muitas tentativas'));
t('eventos de login (ok/falha/bloqueio) registrados', (int) q("SELECT COUNT(*) FROM acessos_log WHERE evento='login_falha'") >= 5 && (int) q("SELECT COUNT(*) FROM acessos_log WHERE evento='login_ok'") >= 3 && (int) q("SELECT COUNT(*) FROM acessos_log WHERE evento='bloqueado'") >= 1);
[, $ac] = $adm->req('/?p=admin-acesso');
t('tela de Controle de acesso mostra números reais', str_contains($ac, 'Bloqueios ativos agora') && str_contains($ac, 'Tentativas suspeitas recentes') && str_contains($ac, 'Falha de login'));

secao('Perfil da empresa e página pública');
[, $pf] = $A->req('/?p=empresa-perfil');
t('perfil mostra os dados e a prévia', str_contains($pf, 'Verde Ágil Tecnologia Ltda') && str_contains($pf, 'Como o estudante vê'));
$novo = array_diff_key($dadosA, array_flip(['cnpj', 'email', 'senha', 'senha2', 'aceite_termos', 'aceite_privacidade']));
$novo['descricao'] = 'Descrição atualizada da empresa, agora com mais detalhes sobre o programa de estágio.';
$novo['link_principal'] = 'site';
$A->req('/?p=empresa-perfil', ['_csrf' => $A->csrf('/?p=painel')] + $novo);
t('empresa edita o próprio perfil (sem alterar CNPJ/e-mail/status)', str_contains((string) q('SELECT descricao FROM empresas WHERE id = :i', ['i' => $idA]), 'atualizada') && q('SELECT status FROM empresas WHERE id = :i', ['i' => $idA]) === 'aprovada' && q('SELECT cnpj FROM empresas WHERE id = :i', ['i' => $idA]) === $cnpjA);
[, $ep] = $visitante->req('/?p=empresas');
t('Empresas Parceiras lista as aprovadas (e só elas)', str_contains($ep, 'Verde Ágil') && str_contains($ep, 'Empresas recentes') && str_contains($ep, 'Empresas mais frequentes') && !str_contains($ep, 'Casa Antiga'));
[, $pe] = $visitante->req("/?p=empresa&id=$idA");
t('página pública da empresa com vagas abertas', str_contains($pe, 'Verde Ágil') && str_contains($pe, 'Vagas abertas desta empresa'));
[$c] = $visitante->req('/?p=empresa&id=' . q("SELECT id FROM empresas WHERE status='pendente' LIMIT 1"));
t('empresa pendente não tem página pública', $c === 404 || (int) q("SELECT COUNT(*) FROM empresas WHERE status='pendente'") === 0);
[, $ex] = $visitante->req("/?p=externo&id=$idA&url=https://evil.example");
t('saída para site externo avisa e usa só o link salvo (sem open redirect)', str_contains($ex, 'Você está saindo do sistema') && str_contains($ex, 'verdeagil.example') && !str_contains($ex, 'evil.example'));
[$c,, $l] = $visitante->req("/?p=externo&id=$idB", null, false);
t('empresa sem links vai para a página interna', $c === 302 && str_contains($l, 'p=empresa'));

secao('Modo TV e QR Code por vaga');
[, $tv] = $visitante->req('/?p=tv');
$publicas = Vaga::publicas();
t('TV: um QR por vaga publicada', substr_count($tv, 'data-qr=') === count($publicas) && count($publicas) >= 1);
$todosOk = true;
foreach ($publicas as $v) {
    $esperado = rtrim((string) env('APP_URL'), '/') . (env('PRETTY_URLS') ? '/vaga/' . $v['id'] : '/?p=vaga&amp;id=' . $v['id']);
    if (!str_contains($tv, 'data-qr="' . $esperado . '"')) $todosOk = false;
}
t('TV: cada QR aponta para o link da PRÓPRIA vaga', $todosOk);
t('TV: biblioteca de QR local (sem CDN) e fallback com URL em texto', str_contains($tv, 'vendor/qrcode.js') && str_contains($tv, 'vg-tv-qr') && !str_contains($tv, 'cdn'));
$pretty = env('PRETTY_URLS') && env('PRETTY_URLS') !== '0';
if ($pretty) {
    t('link curto /vaga/ID abre a vaga', $visitante->req("/vaga/$idVB")[0] === 200 && str_contains($tv, '/vaga/' . $idVB));
}

secao('Dashboard e relatórios com números reais');
[, $dash] = $adm->req('/?p=admin');
$c = Vaga::contagens();
$e = Stats::empresas();
t('dashboard bate com o banco', str_contains($dash, '<span class="ui-metric-n">' . $c['todas'] . '</span><span class="ui-metric-l">Total de vagas') && str_contains($dash, '<span class="ui-metric-n">' . $e['total'] . '</span><span class="ui-metric-l">Total de empresas'));
t('gráficos são SVG com descrição acessível', str_contains($dash, 'role="img"') && str_contains($dash, 'aria-label="Gráfico de rosca'));
[, $csv] = $adm->req('/?p=admin-exportar&tipo=vagas');
t('CSV de vagas com BOM e cabeçalho', str_starts_with($csv, "\xEF\xBB\xBFCódigo;Título"));
$enviaVaga($A, array_merge($vagaPost, ['titulo' => '=HYPERLINK("http://x")', 'tipo' => 'Estágio', 'area' => 'Administração', 'area_outra' => '']));
[, $csv] = $adm->req('/?p=admin-exportar&tipo=vagas');
t('CSV neutraliza injeção de fórmula', str_contains($csv, "'=HYPERLINK") && !preg_match('/;=HYPERLINK/', $csv));
$enviaVaga($A, array_merge($vagaPost, ['titulo' => '<script>alert(1)</script> XSS', 'tipo' => 'Estágio', 'area' => 'Administração', 'area_outra' => '']));
$idX = (int) q("SELECT id FROM vagas WHERE titulo LIKE '%XSS'");
[, $x1] = $adm->req('/?p=admin-aprovacoes');
[, $x2] = $A->req("/?p=empresa-vaga&id=$idX");
t('XSS: título com <script> sai escapado no admin e na empresa', !str_contains($x1, '<script>alert(1)') && str_contains($x1, '&lt;script&gt;') && !str_contains($x2, '<script>alert(1)'));
[$c] = $visitante->req('/?q=' . rawurlencode("' OR 1=1 --"));
t('SQLi na busca: sem erro', $c === 200);
[$c] = $adm->req('/?p=admin-vagas&q=' . rawurlencode("'; DROP TABLE vagas; --") . '&ord=' . rawurlencode('x;DROP'));
t('SQLi na busca/ordenação do admin: sem erro e tabela intacta', $c === 200 && (int) q('SELECT COUNT(*) FROM vagas') > 0);

secao('Usuários administradores');
[, $r] = $adm->post('/?p=admin-acao', ['tipo' => 'admin', 'acao' => 'criar', 'nome' => 'Secretaria', 'email' => 'secretaria@ifsp.local', 'senha' => 'fraca', 'ret' => 'admin-usuarios'], '/?p=admin');
t('admin com senha fraca recusado', !q("SELECT 1 FROM admins WHERE email='secretaria@ifsp.local'") && str_contains($r, 'letras e números'));
$adm->post('/?p=admin-acao', ['tipo' => 'admin', 'acao' => 'criar', 'nome' => 'Secretaria', 'email' => 'secretaria@ifsp.local', 'senha' => 'Secretaria2026', 'ret' => 'admin-usuarios'], '/?p=admin');
t('novo administrador criado', (bool) q("SELECT 1 FROM admins WHERE email='secretaria@ifsp.local'"));
$meu = (int) q("SELECT id FROM admins WHERE email='admin@ifsp.local'");
[, $r] = $adm->post('/?p=admin-acao', ['tipo' => 'admin', 'acao' => 'desativar', 'id' => $meu, 'ret' => 'admin-usuarios'], '/?p=admin');
t('não pode desativar a própria conta', str_contains($r, 'própria conta') && (int) q('SELECT ativo FROM admins WHERE id = :i', ['i' => $meu]) === 1);
$sec = (int) q("SELECT id FROM admins WHERE email='secretaria@ifsp.local'");
$adm->post('/?p=admin-acao', ['tipo' => 'admin', 'acao' => 'desativar', 'id' => $sec, 'ret' => 'admin-usuarios'], '/?p=admin');
[, $r] = loginAdmin(new Cli($base), 'secretaria@ifsp.local', 'Secretaria2026');
t('administrador desativado não entra', str_contains($r, 'desativada'));

secao('Logout e sessão expirada');
[$c] = $B->req('/?p=sair', ['_csrf' => $B->csrf('/?p=painel'), 'perfil' => 'empresa']);
[$c2] = $B->req('/?p=painel', null, false);
t('logout encerra a sessão (rota protegida volta ao login)', $c2 === 302);
if ($baseCurta) {
    $S = new Cli($baseCurta);
    loginAdmin($S);
    t('(servidor com sessão curta) admin logado', $S->req('/?p=admin', null, false)[0] === 200);
    sleep(3);
    [$c,, $l] = $S->req('/?p=admin', null, false);
    t('sessão expira por inatividade e exige novo login', $c === 302 && str_contains($l, 'admin-login'));
}
echo "\n$ok ok, $falhas falhas\n";
exit($falhas ? 1 : 0);
