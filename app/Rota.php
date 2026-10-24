<?php

class Rota
{
    public const CAMINHOS = [
        'home' => '',
        'vaga' => 'vaga/{id}',
        'encerradas' => 'encerradas',
        'tv' => 'tv',
        'empresas' => 'empresas',
        'empresa' => 'empresa/{id}',
        'externo' => 'externo/{id}',
        'empresa-login' => 'empresa/login',
        'empresa-cadastro' => 'empresa/cadastro',
        'empresa-concluido' => 'empresa/concluido',
        'admin-login' => 'admin/login',
        'sair' => 'sair',
        'senha-esqueci' => 'senha/esqueci',
        'senha-redefinir' => 'senha/redefinir',
        'painel' => 'painel',
        'empresa-vagas' => 'painel/vagas',
        'vaga-cadastro' => 'painel/vagas/nova',
        'empresa-vaga' => 'painel/vagas/{id}',
        'vaga-editar' => 'painel/vagas/{id}/editar',
        'vaga-encerrar' => 'painel/vagas/{id}/encerrar',
        'empresa-perfil' => 'painel/perfil',
        'aviso-dispensar' => 'painel/aviso-dispensar',
        'admin' => 'admin',
        'admin-vagas' => 'admin/vagas',
        'admin-vaga' => 'admin/vagas/{id}',
        'admin-empresas' => 'admin/empresas',
        'admin-empresa' => 'admin/empresas/{id}',
        'admin-notificacao' => 'admin/notificacao/{id}',
        'admin-senha' => 'admin/senha',
        'admin-atividade' => 'admin/atividade',
        'admin-aprovacoes' => 'admin/aprovacoes',
        'admin-usuarios' => 'admin/usuarios',
        'admin-desempenho' => 'admin/desempenho',
        'admin-relatorios' => 'admin/relatorios',
        'admin-acao' => 'admin/acao',
        'admin-termo' => 'admin/termo/{id}',
        'admin-exportar' => 'admin/exportar',
    ];

    public static function ativo(): bool
    {
        $v = env('PRETTY_URLS', '1');
        return $v !== '0' && $v !== '';
    }

    public static function caminho(string $pagina, array &$params): ?string
    {
        if (!isset(self::CAMINHOS[$pagina])) return null;
        $c = self::CAMINHOS[$pagina];
        if (str_contains($c, '{id}')) {
            if (!isset($params['id']) || !ctype_digit((string) $params['id'])) return null;
            $c = str_replace('{id}', (string) $params['id'], $c);
            unset($params['id']);
        }
        return $c;
    }

    public static function caminhoAtual(): string
    {
        $p = rawurldecode((string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH));
        $base = BASE_URL;
        if (str_starts_with($p, $base)) $p = substr($p, strlen($base));
        elseif ($p . '/' === $base) $p = '';
        $p = trim($p, '/');
        return preg_replace('#^index\.php/?#', '', $p);
    }

    public static function resolver(string $caminho): ?array
    {
        $caminho = trim($caminho, '/');
        foreach (self::CAMINHOS as $pagina => $modelo) {
            if ($modelo === '' ? $caminho === '' : false) return [$pagina, []];
            if ($modelo === '') continue;
            $re = '#^' . str_replace('\{id\}', '(\d+)', preg_quote($modelo, '#')) . '$#';
            if (preg_match($re, $caminho, $m)) return [$pagina, isset($m[1]) ? ['id' => $m[1]] : []];
        }
        return null;
    }
}
