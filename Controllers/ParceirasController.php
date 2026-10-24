<?php

class ParceirasController
{
    public function index(): void
    {
        $trilhos = [];
        foreach ([['recentes', 'Empresas recentes', 'Cadastradas há pouco tempo.'], ['frequentes', 'Empresas mais frequentes', 'As que mais publicam vagas.'], ['abertas', 'Com vagas abertas agora', 'Oportunidades disponíveis neste momento.']] as [$k, $t, $sub]) {
            $l = Empresa::parceiras($k, 12);
            if ($l) {
                $trilhos[] = ['id' => $k, 'titulo' => $t, 'sub' => $sub, 'itens' => $l];
            }
        }
        view('empresas_parceiras', ['titulo' => 'Empresas parceiras', 'trilhos' => $trilhos, 'todas' => Empresa::parceiras('nome', 200)]);
    }

    public function perfil(): void
    {
        $e = Empresa::parceiraPorId((int) ($_GET['id'] ?? 0));
        if (!$e) {
            http_response_code(404);
            view('vaga_indisponivel', ['titulo' => 'Empresa não encontrada']);
            return;
        }
        $vagas = array_values(array_filter(Vaga::publicas(), fn($v) => (int) $v['empresa_id'] === (int) $e['id']));
        view('empresa_publica', ['titulo' => $e['nome'], 'e' => $e, 'vagas' => $vagas]);
    }

    public function externo(): void
    {
        $e = Empresa::parceiraPorId((int) ($_GET['id'] ?? 0));
        $destino = $e ? Empresa::linkExterno($e) : null;
        if (!$e || !$destino) {
            redirect($e ? 'empresa' : 'empresas', $e ? ['id' => $e['id']] : []);
        }
        view('externo', ['titulo' => 'Saindo do sistema', 'e' => $e, 'destino' => $destino]);
    }
}
