<?php

class VagaForm
{
    public static function validar(array $d, array $files, ?array $atual = null): array
    {
        $e = [];
        $hoje = date('Y-m-d');
        $d['descricao'] = str_replace(["\r\n", "\r"], "\n", (string) ($d['descricao'] ?? ''));   // grava sempre com \n
        $len = fn($k) => mb_strlen($d[$k] ?? '');
        if ($len('titulo') < 5 || $len('titulo') > 80) {
            $e['titulo'] = 'Informe o nome da vaga (5 a 80 caracteres).';
        }
        if (!in_array($d['tipo'] ?? '', TIPOS_VAGA, true)) {
            $e['tipo'] = 'Escolha o tipo da oportunidade.';
        }
        if (!in_array($d['modalidade'] ?? '', MODALIDADES, true)) {
            $e['modalidade'] = 'Escolha a modalidade.';
        }
        [$area, $eArea] = Vaga::resolverArea($d['area'] ?? '', $d['area_outra'] ?? '');
        if ($eArea) {
            $e['area'] = $eArea;
        }
        if ($len('local') < 2 || $len('local') > 80) {
            $e['local'] = 'Informe o local.';
        }
        if ($len('descricao') < 40) {
            $e['descricao'] = 'Descreva a vaga com mais detalhes (mínimo 40 caracteres). As primeiras linhas viram a breve descrição do card.';
        }
        $d['requisitos'] = Vaga::normalizarLista($d['requisitos'] ?? '');
        $d['requisitos_desejaveis'] = Vaga::normalizarLista($d['requisitos_desejaveis'] ?? '');
        $d['beneficios'] = Vaga::normalizarLista($d['beneficios'] ?? '');
        $d['atividades'] = Vaga::normalizarLista($d['atividades'] ?? '');
        if ($d['requisitos'] === '') {
            $e['requisitos'] = 'Informe ao menos um requisito obrigatório.';
        }
        $bolsa = null;
        if (($d['remuneracao_tipo'] ?? 'valor') === 'combinar') {
            $bolsa = null;
        } else {
            $bolsa = parse_money($d['bolsa'] ?? '');
            if ($bolsa === null || $bolsa <= 0 || $bolsa > 99999.99) {
                $e['bolsa'] = 'Informe um valor mensal válido (somente números) ou escolha "A combinar".';
            }
        }
        $h = $d['carga_horas_dia'] ?? '';
        $horas = ctype_digit((string) $h) ? (int) $h : 0;
        if ($horas < 1 || $horas > 12) {
            $e['carga_horas_dia'] = 'Informe de 1 a 12 horas por dia.';
        }
        $dias = array_values(array_intersect(array_keys(DIAS_SEMANA), (array) ($d['dias'] ?? [])));
        if (!$dias) {
            $e['dias'] = 'Marque ao menos um dia da semana.';
        }
        if (!isset(TURNOS[$d['carga_turno'] ?? ''])) {
            $e['carga_turno'] = 'Escolha o turno.';
        }

        $pub = $d['data_publicacao'] ?? '';
        $lim = $d['data_limite'] ?? '';
        $okData = fn($s) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $s) && checkdate((int) substr($s, 5, 2), (int) substr($s, 8, 2), (int) substr($s, 0, 4));
        $pubAntiga = $atual && $pub === $atual['data_publicacao'];
        if (!$okData($pub) || ($pub < $hoje && !$pubAntiga)) {
            $e['data_publicacao'] = 'Escolha uma data de hoje em diante.';
        }
        if (!$okData($lim) || $lim <= $pub) {
            $e['data_limite'] = 'A data final deve ser depois da inicial.';
        } elseif ((strtotime($lim) - strtotime($pub)) > 366 * 86400) {
            $e['data_limite'] = 'O período máximo é de 1 ano.';
        } elseif ($lim < $hoje) {
            $e['data_limite'] = 'A data final já passou.';
        }

        foreach (['link_site' => null, 'link_linkedin' => 'linkedin'] as $k => $plat) {
            $d[$k] = Link::normalizar($d[$k] ?? '');
            if ($d[$k] && !Link::valido($d[$k], $plat)) {
                $e[$k] = $plat ? 'Use um endereço do LinkedIn.' : 'Endereço inválido.';
            }
        }
        $d['link_candidatura'] = Link::normalizar($d['link_candidatura'] ?? '');
        $plat = $d['link_plataforma'] ?? '';
        if ($plat !== '' && !isset(Link::PLATAFORMAS[$plat])) {
            $e['link_plataforma'] = 'Escolha a plataforma do processo seletivo.';
        }
        if ($d['link_candidatura'] && !Link::valido($d['link_candidatura'], in_array($plat, ['gupy', 'linkedin'], true) ? $plat : null)) {
            $e['link_candidatura'] = $plat === 'gupy' ? 'O link da Gupy deve ser de gupy.io.' : ($plat === 'linkedin' ? 'O link deve ser do LinkedIn.' : 'Endereço inválido.');
        }
        if ($d['link_candidatura'] && $plat === '') {
            $e['link_plataforma'] = 'Informe onde o processo seletivo acontece.';
        }
        if (($d['contato'] ?? '') !== '' && !filter_var($d['contato'], FILTER_VALIDATE_EMAIL)) {
            $e['contato'] = 'E-mail inválido.';
        }
        if (($d['contato'] ?? '') === '' && !$d['link_candidatura']) {
            $e['contato'] = 'Informe um e-mail ou um link para candidatura.';
        }
        // banner
        [$img, $eImg] = Upload::salvar($files['banner'] ?? null, ['image/jpeg', 'image/png'], ROOT_PATH . '/public/uploads/banners', false);
        if ($eImg) {
            $e['banner'] = $eImg;
        } elseif ($img) {
            $d['imagem'] = 'public/uploads/banners/' . $img;
        }

        if ($e) {
            if ($img) {
                @unlink(ROOT_PATH . '/public/uploads/banners/' . $img);
            } return [$d, $e];
        }
        return [[
            'titulo' => $d['titulo'], 'tipo' => $d['tipo'], 'area' => $area, 'modalidade' => $d['modalidade'], 'local' => $d['local'], 'bolsa' => $bolsa,
            'carga_horas_dia' => $horas, 'carga_dias' => implode(',', $dias), 'carga_turno' => $d['carga_turno'], 'descricao' => $d['descricao'], 'atividades' => $d['atividades'],
            'requisitos' => $d['requisitos'], 'requisitos_desejaveis' => $d['requisitos_desejaveis'], 'beneficios' => $d['beneficios'], 'data_publicacao' => $pub, 'data_limite' => $lim,
            'contato' => $d['contato'] ?? '', 'imagem' => $d['imagem'] ?? ($atual['imagem'] ?? null), 'link_site' => $d['link_site'], 'link_linkedin' => $d['link_linkedin'],
            'link_candidatura' => $d['link_candidatura'], 'link_plataforma' => $plat ?: null,
        ], []];
    }

    public static function paraFormulario(array $v): array
    {
        return ['titulo' => $v['titulo'], 'tipo' => $v['tipo'], 'area' => $v['area'], 'modalidade' => $v['modalidade'], 'local' => $v['local'], 'descricao' => $v['descricao'],
            'requisitos' => implode("\n", $v['requisitos']), 'requisitos_desejaveis' => implode("\n", $v['requisitos_desejaveis']), 'beneficios' => implode("\n", $v['beneficios']), 'atividades' => implode("\n", $v['atividades']),
            'remuneracao_tipo' => $v['bolsa'] === null ? 'combinar' : 'valor', 'bolsa' => $v['bolsa'] === null ? '' : number_format((float) $v['bolsa'], 2, ',', ''),
            'carga_horas_dia' => (string) ($v['carga_horas_dia'] ?? ''), 'dias' => array_filter(explode(',', (string) $v['carga_dias'])), 'carga_turno' => (string) $v['carga_turno'],
            'data_publicacao' => $v['data_publicacao'], 'data_limite' => $v['data_limite'], 'contato' => $v['contato'], 'link_site' => (string) $v['link_site'],
            'link_linkedin' => (string) $v['link_linkedin'], 'link_candidatura' => (string) $v['link_candidatura'], 'link_plataforma' => (string) $v['link_plataforma']];
    }
}
