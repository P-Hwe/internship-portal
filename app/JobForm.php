<?php
class JobForm
{
    public static function validate(array $d, array $files, ?array $current = null): array
    {
        $errors  = [];
        $today   = date('Y-m-d');
        $d['descricao'] = str_replace(["\r\n", "\r"], "\n", (string) ($d['descricao'] ?? ''));
        $len = fn($k) => mb_strlen($d[$k] ?? '');

        if ($len('titulo') < 5 || $len('titulo') > 80)           $errors['titulo']     = 'Informe o nome da vaga (5 a 80 caracteres).';
        if (!in_array($d['tipo'] ?? '', JOB_TYPES, true))         $errors['tipo']       = 'Escolha o tipo da oportunidade.';
        if (!in_array($d['modalidade'] ?? '', MODALITIES, true))  $errors['modalidade'] = 'Escolha a modalidade.';

        [$area, $areaError] = Vaga::resolverArea($d['area'] ?? '', $d['area_outra'] ?? '');
        if ($areaError) $errors['area'] = $areaError;

        if ($len('local') < 2 || $len('local') > 80) $errors['local'] = 'Informe o local.';
        if ($len('descricao') < 40) $errors['descricao'] = 'Descreva a vaga com mais detalhes (mínimo 40 caracteres). As primeiras linhas viram a breve descrição do card.';

        $d['requisitos']            = Vaga::normalizarLista($d['requisitos'] ?? '');
        $d['requisitos_desejaveis'] = Vaga::normalizarLista($d['requisitos_desejaveis'] ?? '');
        $d['beneficios']            = Vaga::normalizarLista($d['beneficios'] ?? '');
        $d['atividades']            = Vaga::normalizarLista($d['atividades'] ?? '');
        if ($d['requisitos'] === '') $errors['requisitos'] = 'Informe ao menos um requisito obrigatório.';

        $salary = null;
        if (($d['remuneracao_tipo'] ?? 'valor') === 'ocultar') {
            $salary = 0;
        } elseif (($d['remuneracao_tipo'] ?? 'valor') !== 'combinar') {
            $salary = parse_money($d['bolsa'] ?? '');
            if ($salary === null || $salary <= 0 || $salary > 99999.99) $errors['bolsa'] = 'Informe um valor mensal válido (somente números) ou escolha "A combinar".';
        }

        $hours = ctype_digit((string) ($d['carga_horas_dia'] ?? '')) ? (int) $d['carga_horas_dia'] : 0;
        if ($hours < 1 || $hours > 12) $errors['carga_horas_dia'] = 'Informe de 1 a 12 horas por dia.';

        $days = array_values(array_intersect(array_keys(WEEK_DAYS), (array) ($d['dias'] ?? [])));
        if (!$days) $errors['dias'] = 'Marque ao menos um dia da semana.';

        if (!isset(SHIFTS[$d['carga_turno'] ?? ''])) $errors['carga_turno'] = 'Escolha o turno.';

        $pubDate  = $d['data_publicacao'] ?? '';
        $endDate  = $d['data_limite'] ?? '';
        $validDate = fn($s) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $s) && checkdate((int) substr($s, 5, 2), (int) substr($s, 8, 2), (int) substr($s, 0, 4));
        $samePublishDate = $current && $pubDate === $current['data_publicacao'];

        if (!$validDate($pubDate) || ($pubDate < $today && !$samePublishDate)) $errors['data_publicacao'] = 'Escolha uma data de hoje em diante.';
        if (!$validDate($endDate) || $endDate <= $pubDate)                     $errors['data_limite']      = 'A data final deve ser depois da inicial.';
        elseif ((strtotime($endDate) - strtotime($pubDate)) > 366 * 86400)     $errors['data_limite']      = 'O período máximo é de 1 ano.';
        elseif ($endDate < $today)                                             $errors['data_limite']      = 'A data final já passou.';

        foreach (['link_site' => null, 'link_linkedin' => 'linkedin'] as $k => $platform) {
            $d[$k] = Link::normalize($d[$k] ?? '');
            if ($d[$k] && !Link::isValid($d[$k], $platform)) $errors[$k] = $platform ? 'Use um endereço do LinkedIn.' : 'Endereço inválido.';
        }

        $d['link_candidatura'] = Link::normalize($d['link_candidatura'] ?? '');
        $platform = $d['link_plataforma'] ?? '';
        if ($platform !== '' && !isset(Link::PLATAFORMAS[$platform])) $errors['link_plataforma'] = 'Escolha a plataforma do processo seletivo.';
        if ($d['link_candidatura'] && !Link::isValid($d['link_candidatura'], in_array($platform, ['gupy', 'linkedin'], true) ? $platform : null)) {
            $errors['link_candidatura'] = $platform === 'gupy' ? 'O link da Gupy deve ser de gupy.io.' : ($platform === 'linkedin' ? 'O link deve ser do LinkedIn.' : 'Endereço inválido.');
        }
        if ($d['link_candidatura'] && $platform === '') $errors['link_plataforma'] = 'Informe onde o processo seletivo acontece.';
        if (($d['contato'] ?? '') !== '' && !filter_var($d['contato'], FILTER_VALIDATE_EMAIL)) $errors['contato'] = 'E-mail inválido.';
        if (($d['contato'] ?? '') === '' && !$d['link_candidatura']) $errors['contato'] = 'Informe um e-mail ou um link para candidatura.';

        $filePrefix    = isset($current['id']) ? 'job_' . (int) $current['id'] . '_banner' : 'job_banner';
        [$uploadedFile, $uploadError] = Upload::save($files['banner'] ?? null, ['image/jpeg', 'image/png'], ROOT_PATH . '/public/uploads/banners', false, UPLOAD_MAX_MB, $filePrefix);
        if ($uploadError) $errors['banner'] = $uploadError;
        elseif ($uploadedFile) $d['imagem'] = 'public/uploads/banners/' . $uploadedFile;

        if ($errors) {
            if ($uploadedFile) @unlink(ROOT_PATH . '/public/uploads/banners/' . $uploadedFile);
            return [$d, $errors];
        }

        return [[
            'titulo'               => $d['titulo'],
            'tipo'                 => $d['tipo'],
            'area'                 => $area,
            'modalidade'           => $d['modalidade'],
            'local'                => $d['local'],
            'bolsa'                => $salary,
            'carga_horas_dia'      => $hours,
            'carga_dias'           => implode(',', $days),
            'carga_turno'          => $d['carga_turno'],
            'descricao'            => $d['descricao'],
            'atividades'           => $d['atividades'],
            'requisitos'           => $d['requisitos'],
            'requisitos_desejaveis'=> $d['requisitos_desejaveis'],
            'beneficios'           => $d['beneficios'],
            'data_publicacao'      => $pubDate,
            'data_limite'          => $endDate,
            'contato'              => $d['contato'] ?? '',
            'imagem'               => $d['imagem'] ?? ($current['imagem'] ?? null),
            'link_site'            => $d['link_site'],
            'link_linkedin'        => $d['link_linkedin'],
            'link_candidatura'     => $d['link_candidatura'],
            'link_plataforma'      => $platform ?: null,
        ], []];
    }

    public static function toFormData(array $v): array
    {
        return [
            'titulo'               => $v['titulo'],
            'tipo'                 => $v['tipo'],
            'area'                 => $v['area'],
            'modalidade'           => $v['modalidade'],
            'local'                => $v['local'],
            'descricao'            => $v['descricao'],
            'requisitos'           => implode("\n", $v['requisitos']),
            'requisitos_desejaveis'=> implode("\n", $v['requisitos_desejaveis']),
            'beneficios'           => implode("\n", $v['beneficios']),
            'atividades'           => implode("\n", $v['atividades']),
            'remuneracao_tipo'     => $v['bolsa'] === null ? 'combinar' : ((float) $v['bolsa'] === 0.0 ? 'ocultar' : 'valor'),
            'bolsa'                => ($v['bolsa'] === null || (float) $v['bolsa'] === 0.0) ? '' : number_format((float) $v['bolsa'], 2, ',', ''),
            'carga_horas_dia'      => (string) ($v['carga_horas_dia'] ?? ''),
            'dias'                 => array_filter(explode(',', (string) $v['carga_dias'])),
            'carga_turno'          => (string) $v['carga_turno'],
            'data_publicacao'      => $v['data_publicacao'],
            'data_limite'          => $v['data_limite'],
            'contato'              => $v['contato'],
            'link_site'            => (string) $v['link_site'],
            'link_linkedin'        => (string) $v['link_linkedin'],
            'link_candidatura'     => (string) $v['link_candidatura'],
            'link_plataforma'      => (string) $v['link_plataforma'],
        ];
    }

    /** @deprecated Use validate() */
    public static function validar(array $d, array $files, ?array $atual = null): array { return self::validate($d, $files, $atual); }
    /** @deprecated Use toFormData() */
    public static function paraFormulario(array $v): array { return self::toFormData($v); }
}
