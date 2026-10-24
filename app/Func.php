<?php

if (!function_exists('url')) {
    function url($nome = null, $params = [], $absolute = true)
    {
        $base = 'http://' . $_SERVER['HTTP_HOST'];
        if ($absolute) {
            return $base . '/vagas/' . $nome . '.php' . (array_merge(['id' => 0], $params) ?: ['id' => 0])['id'] ? '?' . http_build_query($params) : '';
        }
        return '/vagas/' . $nome . '.php' . (array_merge(['id' => 0], $params) ?: ['id' => 0])['id'] ? '?' . http_build_query($params) : '';
    }
}

if (!function_exists('e')) {
    function e($string)
    {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('icon')) {
    function icon($nome, $tamanho = 16)
    {
        return '<i class="fas fa-' . $nome . '" style="font-size:' . $tamanho . 'px"></i>';
    }
}

// Função para obter imagem da vaga
if (!function_exists('vaga_imagem')) {
    function vaga_imagem($vaga)
    {
        if (empty($vaga['imagem']) || !file_exists(ROOT_PATH . '/public/uploads/vagas/' . $vaga['imagem'])) {
            return '<img src="/public/images/default-vaga.png" alt="' . e($vaga['titulo']) . '" class="vg-slide-img" width="100%" height="auto">';
        }
        return '<img src="/public/uploads/vagas/' . e($vaga['imagem']) . '" alt="' . e($vaga['titulo']) . '" class="vg-slide-img" width="100%" height="auto">';
    }
}

// Função para obter dias em texto
if (!function_exists('dias_txt')) {
    function dias_txt($numero)
    {
        $dias = ['', 'domingo', 'segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado'];
        return $dias[$numero] ?? '';
    }
}

// Função para obter banner da empresa
if (!function_exists('empresa_banner')) {
    function empresa_banner($empresa)
    {
        if (empty($empresa['banner']) || !file_exists(ROOT_PATH . '/public/uploads/banners/' . $empresa['banner'])) {
            return '<img src="/public/images/default-banner.png" alt="' . e($empresa['nome']) . '" style="max-width:100%;height:auto;">';
        }
        return '<img src="/public/uploads/banners/' . e($empresa['banner']) . '" alt="' . e($empresa['nome']) . '" style="max-width:100%;height:auto;">';
    }
}

// Função para obter URL da vaga
if (!function_exists('url_vaga')) {
    function url_vaga($id)
    {
        return '/vaga.php?id=' . $id;
    }
}

// Função para obter selo de prazo
if (!function_exists('selo_prazo')) {
    function sello_prazo($prazo, $data_limite = null)
    {
        if (empty($prazo)) {
            return '<span class="badge bg-secondary">Sem prazo</span>';
        }
        if ($data_limite) {
            $diff = strtotime($data_limite) - time();
            $dias = floor($diff / 86400);
            if ($dias <= 0) {
                return '<span class="badge bg-danger">Encerrado</span>';
            }
            if ($dias <= 3) {
                return '<span class="badge bg-warning text-dark">Urgente (' . $dias . ' dias)</span>';
            }
        }
        return '<span class="badge bg-success">' . $prazo . '</span>';
    }
}

// Função para obter iniciais
if (!function_exists('iniciais')) {
    function iniciais($texto, $qtd = 2)
    {
        $texto = trim((string)$texto);
        if (strlen($texto) <= $qtd * 3) {
            return substr($texto, 0, $qtd * 3);
        }
        $palavras = preg_split('/\s+/', $texto);
        $iniciais = '';
        foreach (array_slice($palavras, 0, $qtd) as $palavra) {
            $iniciais .= strtoupper(substr($palavra, 0, 1));
        }
        return $iniciais;
    }
}

// Função para formatar data
if (!function_exists('data_fmt')) {
    function data_fmt($data)
    {
        if (empty($data)) return '';
        return date('d/m/Y', strtotime($data));
    }
}

// Função para formatar moeda
if (!function_exists('moeda')) {
    function moeda($valor, $moeda = 'BRL')
    {
        if (empty($valor)) return 'R$ 0,00';
        return 'R$ ' . number_format((float)$valor, 2, ',', '.');
    }
}

// Função para obter tipo de vaga
if (!function_exists('tipo_vaga')) {
    function tipo_vaga($tipo)
    {
        $tipos = [
            'presencial' => 'Presencial',
            'remoto' => 'Remoto',
            'híbrido' => 'Híbrido',
            'presencial_hospedagem' => 'Presencial (com hospedagem)',
            'remoto_hospedagem' => 'Remoto (com hospedagem)'
        ];
        return $tipos[$tipo] ?? ucfirst($tipo);
    }
}

// Função para obter estado
if (!function_exists('estado')) {
    function estado($codigo)
    {
        $estados = [
            'AC' => 'Acre',
            'AL' => 'Alagoas',
            'AP' => 'Amapá',
            'AM' => 'Amazonas',
            'BA' => 'Bahia',
            'CE' => 'Ceará',
            'DF' => 'Distrito Federal',
            'ES' => 'Espírito Santo',
            'GO' => 'Goiás',
            'MA' => 'Maranhão',
            'MT' => 'Mato Grosso',
            'MS' => 'Mato Grosso do Sul',
            'MG' => 'Minas Gerais',
            'PA' => 'Pará',
            'PB' => 'Paraíba',
            'PR' => 'Paraná',
            'PE' => 'Pernambuco',
            'PI' => 'Piauí',
            'RJ' => 'Rio de Janeiro',
            'RN' => 'Rio Grande do Norte',
            'RS' => 'Rio Grande do Sul',
            'RO' => 'Rondônia',
            'RR' => 'Roraima',
            'SC' => 'Santa Catarina',
            'SP' => 'São Paulo',
            'SE' => 'Sergipe',
            'TO' => 'Tocantins'
        ];
        return $estados[$codigo] ?? $codigo;
    }
}
