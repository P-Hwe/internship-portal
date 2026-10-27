<?php
class VagaController
{
    public function index(): void
    {
        Visitas::registrar('home');
        $vagas = Vaga::publicas();
        $ord = function (array $l, callable $cmp) {
            usort($l, $cmp);
            return $l;
        };
        $trilhos = [
            [
                'id' => 'novas',
                'titulo' => 'Vagas novas',
                'icone' => 'flame',
                'chave' => 'nova',
                'itens' => array_slice(array_values(array_filter($vagas, fn($v) => isset($v['selos']['nova']))), 0, 12)
            ],
            [
                'id' => 'ativos',
                'titulo' => 'Recrutador ativo',
                'icone' => 'user',
                'chave' => 'ativo',
                'sub' => 'Empresas que acessaram o painel nos últimos ' . DIAS_RECRUTADOR_ATIVO . ' dias.',
                'itens' => array_slice(array_values(array_filter($vagas, fn($v) => $v['recrutador_ativo'])), 0, 12)
            ],
            [
                'id' => 'salarios',
                'titulo' => 'Melhores salários',
                'icone' => 'trending',
                'chave' => 'salario',
                'itens' => array_slice($ord(array_filter($vagas, fn($v) => $v['bolsa'] !== null), fn($a, $b) => $b['bolsa'] <=> $a['bolsa']), 0, 12)
            ],
            [
                'id' => 'jovem',
                'titulo' => 'Jovem Aprendiz e Jovem Profissional',
                'icone' => 'star',
                'chave' => 'tipo',
                'itens' => array_values(array_filter($vagas, fn($v) => in_array($v['tipo'], ['Jovem Aprendiz', 'Jovem Profissional'], true)))
            ],
            [
                'id' => 'trainee',
                'titulo' => 'Trainee',
                'icone' => 'briefcase',
                'chave' => 'tipo',
                'itens' => array_values(array_filter($vagas, fn($v) => $v['tipo'] === 'Trainee'))
            ],
            [
                'id' => 'estagio',
                'titulo' => 'Estágio',
                'icone' => 'building',
                'chave' => 'tipo',
                'itens' => array_slice(array_values(array_filter($vagas, fn($v) => $v['tipo'] === 'Estágio')), 0, 12)
            ],
            [
                'id' => 'vencendo',
                'titulo' => 'Encerrando em breve',
                'icone' => 'clock',
                'chave' => 'vencendo',
                'itens' => $ord(array_filter($vagas, fn($v) => isset($v['selos']['vencendo'])), fn($a, $b) => strcmp($a['data_limite'], $b['data_limite']))
            ],
        ];
        view('home', [
            'titulo' => 'Vagas de Estágio e Primeiro Emprego',
            'vagas' => $vagas,
            'destaques' => Vaga::destaques($vagas),
            'trilhos' => array_values(array_filter($trilhos, fn($t) => $t['itens'])),
            'areas' => Vaga::areasFiltro($vagas),
            'tipos' => array_values(array_intersect(TIPOS_VAGA, array_column($vagas, 'tipo'))),
            'modalidades' => array_values(array_intersect(MODALIDADES, array_column($vagas, 'modalidade'))),
        ]);
    }

    public function mostrar(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $vaga = Vaga::visivelPorId($id);
        if (!$vaga) {
            http_response_code(Vaga::existe($id) ? 410 : 404);
            view('vaga_indisponivel', ['titulo' => 'Vaga indisponível']);
            return;
        }
        Visitas::registrar('vaga', (int) $vaga['id']);
        $aberta = $vaga['situacao'] === 'publicada';
        view('vaga', ['titulo' => $vaga['titulo'] . ' - ' . $vaga['empresa']['nome'], 'vaga' => $vaga, 'aberta' => $aberta, 'relacionadas' => $aberta ? Vaga::relacionadas($vaga, Vaga::publicas()) : []]);
    }

    public function encerradas(): void
    {
        view('vagas_encerradas', ['titulo' => 'Vagas encerradas', 'vagas' => Vaga::encerradasPublico()]);
    }

    public function tv(): void
    {
        view('tv', ['vagas' => Vaga::publicas()]);
    }
}