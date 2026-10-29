<?php
class JobController
{
    public function index(): void
    {
        Visits::record('home');
        $allJobs = Vaga::publicas();
        $perPage = PAGINA_PER_PAGE;

        $filters = [
            'busca' => $_GET['busca'] ?? '',
            'tipo' => $_GET['tipo'] ?? '',
            'area' => $_GET['area'] ?? '',
            'modalidade' => $_GET['modalidade'] ?? '',
            'ord' => $_GET['ord'] ?? '',
        ];

        $filtered = $allJobs;
        if ($filters['busca']) {
            $q = strtolower(trim($filters['busca']));
            $filtered = array_filter($filtered, fn($v) => stripos($v['titulo'] . ' ' . $v['empresa']['nome'] . ' ' . $v['resumo'], $q) !== false);
        }
        if ($filters['tipo']) {
            $filtered = array_filter($filtered, fn($v) => $v['tipo'] === $filters['tipo']);
        }
        if ($filters['area']) {
            $filtered = array_filter($filtered, fn($v) => $v['area_exibicao'] === $filters['area'] || $filters['area'] === 'Todas');
        }
        if ($filters['modalidade']) {
            $filtered = array_filter($filtered, fn($v) => $v['modalidade'] === $filters['modalidade']);
        }

        $page = max(1, (int) ($_GET['pg'] ?? 1));
        $total = count($filtered);

        // Apply sorting
        if ($filters['ord'] === 'salario') {
            usort($filtered, fn($a, $b) => ($b['bolsa'] ?? -1) <=> ($a['bolsa'] ?? -1));
        } elseif ($filters['ord'] === 'prazo') {
            usort($filtered, fn($a, $b) => strcmp($a['data_limite'], $b['data_limite']));
        }
        // default: already sorted newest-first from Vaga::publicas()

        [$offset] = Paginator::calc($total, $page, $perPage);
        $paginatedJobs = array_slice($filtered, $offset, $perPage);

        $sorter = function (array $l, callable $cmp) {
            usort($l, $cmp);
            return $l;
        };
        $tracks = [
            ['id' => 'novas',    'titulo' => 'Vagas novas',                          'icone' => 'flame',    'chave' => 'nova',    'itens' => array_slice(array_values(array_filter($filtered, fn($v) => isset($v['selos']['nova']))), 0, 12)],
            ['id' => 'ativos',   'titulo' => 'Recrutador ativo',                     'icone' => 'user',     'chave' => 'ativo',   'sub' => 'Empresas que acessaram o painel nos últimos ' . DIAS_RECRUTADOR_ATIVO . ' dias.', 'itens' => array_slice(array_values(array_filter($filtered, fn($v) => $v['recrutador_ativo'])), 0, 12)],
            ['id' => 'salarios', 'titulo' => 'Melhores salários',                   'icone' => 'trending',  'chave' => 'salario', 'itens' => array_slice($sorter(array_filter($filtered, fn($v) => $v['bolsa'] !== null), fn($a, $b) => $b['bolsa'] <=> $a['bolsa']), 0, 12)],
            ['id' => 'jovem',    'titulo' => 'Jovem Aprendiz e Jovem Profissional', 'icone' => 'star',      'chave' => 'tipo',    'itens' => array_values(array_filter($filtered, fn($v) => in_array($v['tipo'], ['Jovem Aprendiz', 'Jovem Profissional'], true)))],
            ['id' => 'trainee',  'titulo' => 'Trainee',                             'icone' => 'briefcase', 'chave' => 'tipo',    'itens' => array_values(array_filter($filtered, fn($v) => $v['tipo'] === 'Trainee'))],
            ['id' => 'estagio',  'titulo' => 'Estágio',                             'icone' => 'building',  'chave' => 'tipo',    'itens' => array_slice(array_values(array_filter($filtered, fn($v) => $v['tipo'] === 'Estágio')), 0, 12)],
            ['id' => 'vencendo', 'titulo' => 'Encerrando em breve',                'icone' => 'clock',     'chave' => 'vencendo', 'itens' => $sorter(array_filter($filtered, fn($v) => isset($v['selos']['vencendo'])), fn($a, $b) => strcmp($a['data_limite'], $b['data_limite']))],
        ];

        view('public/home', [
            'titulo' => 'Vagas de Estágio e Primeiro Emprego',
            'vagas' => $paginatedJobs,
            'destaques' => Vaga::destaques($filtered),
            'trilhos' => array_values(array_filter($tracks, fn($t) => $t['itens'])),
            'areas' => Vaga::areasFiltro($filtered),
            'tipos' => array_values(array_intersect(TIPOS_VAGA, array_column($filtered, 'tipo'))),
            'modalidades' => array_values(array_intersect(MODALIDADES, array_column($filtered, 'modalidade'))),
            'pagina' => $page,
            'totalPaginas' => ceil($total / $perPage),
            'totalVagas' => $total,
            'filtros' => $filters,
        ]);
    }

    public function mostrar(): void
    {
        $id  = (int) ($_GET['id'] ?? 0);
        $job = Vaga::visivelPorId($id);
        if (!$job) {
            http_response_code(Vaga::existe($id) ? 410 : 404);
            view('shared/job-unavailable', ['titulo' => 'Vaga indisponível']);
            return;
        }
        Visits::record('vaga', (int) $job['id']);
        $isOpen = $job['situacao'] === 'publicada';
        view('public/job', ['titulo' => $job['titulo'] . ' - ' . $job['empresa']['nome'], 'vaga' => $job, 'aberta' => $isOpen, 'relacionadas' => $isOpen ? Vaga::relacionadas($job, Vaga::publicas()) : []]);
    }

    public function encerradas(): void
    {
        view('public/jobs-closed', ['titulo' => 'Vagas encerradas', 'vagas' => Vaga::encerradasPublico()]);
    }

    public function tv(): void
    {
        view('public/tv', ['vagas' => Vaga::tv(Vaga::publicas())]);
    }
}

class_alias('JobController', 'VagaController');