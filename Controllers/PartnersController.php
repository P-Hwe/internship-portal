<?php
class PartnersController
{
    private const COMPANIES_PER_PAGE = 12;

    public function index(): void
    {
        Visits::record('empresas');
        $tracks = [];
        foreach ([
            ['recentes',  'Empresas recentes',       'Cadastradas há pouco tempo.'],
            ['frequentes','Empresas mais frequentes', 'As que mais publicam vagas.'],
            ['abertas',   'Com vagas abertas agora',  'Oportunidades disponíveis neste momento.'],
        ] as [$key, $title, $subtitle]) {
            $list = Company::parceiras($key, 12);
            if ($list) $tracks[] = ['id' => $key, 'titulo' => $title, 'sub' => $subtitle, 'itens' => $list];
        }

        $allCompanies = Company::parceiras('nome', 9999);
        $total        = count($allCompanies);
        $perPage      = self::COMPANIES_PER_PAGE;
        $pg           = max(1, (int) ($_GET['pg'] ?? 1));
        $totalPages   = max(1, (int) ceil($total / $perPage));
        $pg           = min($pg, $totalPages);
        $offset       = ($pg - 1) * $perPage;
        $page         = array_slice($allCompanies, $offset, $perPage);

        view('public/companies', [
            'titulo'      => 'Empresas parceiras',
            'trilhos'     => $tracks,
            'todas'       => $page,
            'total'       => $total,
            'pg'          => $pg,
            'totalPaginas'=> $totalPages,
        ]);
    }

    public function perfil(): void
    {
        $company = Company::parceiraPorId((int) ($_GET['id'] ?? 0));
        if (!$company) { http_response_code(404); view('shared/job-unavailable', ['titulo' => 'Empresa não encontrada']); return; }
        Visits::record('empresa', (int) $company['id']);
        $jobs = array_values(array_filter(Vaga::publicas(), fn($v) => (int) $v['empresa_id'] === (int) $company['id']));
        view('public/company', ['titulo' => $company['nome'], 'e' => $company, 'vagas' => $jobs]);
    }

    public function externo(): void
    {
        $company     = Company::parceiraPorId((int) ($_GET['id'] ?? 0));
        $destination = $company ? Company::linkExterno($company) : null;
        if (!$company || !$destination) redirect($company ? 'empresa' : 'empresas', $company ? ['id' => $company['id']] : []);
        Visits::record('saida', (int) $company['id']);
        view('public/redirect-external', ['titulo' => 'Saindo do sistema', 'e' => $company, 'destino' => $destination]);
    }
}

class_alias('PartnersController', 'ParceirasController');
