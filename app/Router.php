<?php
class Router
{
    public const ROUTES = [
        'home' => '', 'vaga' => 'vaga/{id}', 'encerradas' => 'encerradas', 'tv' => 'tv',
        'empresas' => 'empresas', 'empresa' => 'empresa/{id}', 'externo' => 'externo/{id}',
        'empresa-login' => 'empresa/login', 'empresa-cadastro' => 'empresa/cadastro',
        'empresa-concluido' => 'empresa/concluido', 'admin-login' => 'admin/login', 'sair' => 'sair',
        'senha-esqueci' => 'senha/esqueci', 'senha-redefinir' => 'senha/redefinir',
        'painel' => 'painel', 'empresa-vagas' => 'painel/vagas', 'vaga-cadastro' => 'painel/vagas/nova',
        'empresa-vaga' => 'painel/vagas/{id}', 'vaga-editar' => 'painel/vagas/{id}/editar',
        'vaga-encerrar' => 'painel/vagas/{id}/encerrar', 'empresa-perfil' => 'painel/perfil',
        'aviso-dispensar' => 'painel/aviso-dispensar', 'admin' => 'admin',
        'admin-vagas' => 'admin/vagas', 'admin-vaga' => 'admin/vagas/{id}',
        'admin-empresas' => 'admin/empresas', 'admin-empresa' => 'admin/empresas/{id}',
        'admin-notificacao' => 'admin/notificacao/{id}', 'admin-senha' => 'admin/senha',
        'admin-atividade' => 'admin/atividade', 'admin-aprovacoes' => 'admin/aprovacoes',
        'admin-usuarios' => 'admin/usuarios', 'admin-desempenho' => 'admin/desempenho',
        'admin-relatorios' => 'admin/relatorios', 'admin-acao' => 'admin/acao',
        'admin-termo' => 'admin/termo/{id}', 'admin-exportar' => 'admin/exportar',
    ];

    public static function ativo(): bool { $v = env('PRETTY_URLS', '1'); return $v !== '0' && $v !== ''; }

    public static function caminho(string $route, array &$params): ?string
    {
        if (!isset(self::ROUTES[$route])) return null;
        $path = self::ROUTES[$route];
        if (str_contains($path, '{id}')) {
            if (!isset($params['id']) || !ctype_digit((string) $params['id'])) return null;
            $path = str_replace('{id}', (string) $params['id'], $path);
            unset($params['id']);
        }
        return $path;
    }

    public static function caminhoAtual(): string
    {
        $path = rawurldecode((string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH));
        $base = BASE_URL;
        if (str_starts_with($path, $base)) $path = substr($path, strlen($base));
        elseif ($path . '/' === $base) $path = '';
        $path = trim($path, '/');
        return preg_replace('#^index\.php/?#', '', $path);
    }

    public static function resolver(string $path): ?array
    {
        $path = trim($path, '/');
        foreach (self::ROUTES as $route => $template) {
            if ($template === '' ? $path === '' : false) return [$route, []];
            if ($template === '') continue;
            $pattern = '#^' . str_replace('\{id\}', '(\d+)', preg_quote($template, '#')) . '$#';
            if (preg_match($pattern, $path, $match)) return [$route, isset($match[1]) ? ['id' => $match[1]] : []];
        }
        return null;
    }
}
