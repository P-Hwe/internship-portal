<?php
class Ui
{
    public static function badge(string $texto, string $tom = 'mute'): string
    {
        return '<span class="ui-badge ui-badge-' . e($tom) . '">' . e($texto) . '</span>';
    }
    public static function situacao(string $s): string
    {
        return self::badge(Status::rotuloSituacao($s), Status::tomSituacao($s));
    }
    public static function statusEmpresa(string $s): string
    {
        [$t, $c] = Status::STATUS_EMPRESA[$s] ?? [$s, 'mute'];
        return self::badge($t, $c);
    }

    public static function vazio(string $titulo, string $texto = '', ?array $acao = null): string
    {
        return '<div class="ui-empty"><strong>' . e($titulo) . '</strong>' . ($texto ? '<p>' . e($texto) . '</p>' : '')
            . ($acao ? '<a class="ui-btn ui-btn-primary" href="' . e($acao[1]) . '">' . e($acao[0]) . '</a>' : '') . '</div>';
    }

    public static function abas(array $abas, string $ativa, string $pagina, array $params = [], string $chave = 'aba'): string
    {
        $h = '<nav class="ui-tabs" aria-label="Filtrar por situação">';
        foreach ($abas as $k => [$rot, $n]) {
            $u = url($pagina, array_merge($params, [$chave => $k]));
            $h .= '<a href="' . e($u) . '" class="' . ($k === $ativa ? 'is-active' : '') . '"' . ($k === $ativa ? ' aria-current="page"' : '') . '>' . e($rot) . ($n !== null ? ' <span class="ui-count">' . (int) $n . '</span>' : '') . '</a>';
        }
        return $h . '</nav>';
    }
    public static function paginacao(int $pagina, int $paginas, string $rota, array $params = []): string
    {
        if ($paginas <= 1) {
            return '';
        }
        $l = fn(int $p, string $t, bool $on = true, string $c = '') => $on ? '<a class="' . $c . '" href="' . e(url($rota, array_merge($params, ['pg' => $p]))) . '">' . $t . '</a>' : '<span class="is-off ' . $c . '">' . $t . '</span>';
        $h = '<nav class="ui-pages" aria-label="Paginação">' . $l($pagina - 1, 'Anterior', $pagina > 1);
        for ($p = max(1, $pagina - 2); $p <= min($paginas, $pagina + 2); $p++) {
            $h .= $l($p, (string) $p, $p !== $pagina, $p === $pagina ? 'is-cur' : '');
        }
        return $h . $l($pagina + 1, 'Próxima', $pagina < $paginas) . '</nav>';
    }
    public static function metrica(string $rotulo, $valor, string $icone = 'briefcase', ?string $href = null, string $tom = ''): string
    {
        $in = '<span class="ui-metric-ico ' . e($tom) . '">' . icon($icone, 20) . '</span><span class="ui-metric-n">' . e((string) $valor) . '</span><span class="ui-metric-l">' . e($rotulo) . '</span>';
        return $href ? '<a class="ui-metric" href="' . e($href) . '">' . $in . '</a>' : '<div class="ui-metric">' . $in . '</div>';
    }
    public static function logo(array $emp, int $px = 44): string
    {
        if (!empty($emp['logo'])) {
            return '<img class="ui-logo" width="' . $px . '" height="' . $px . '" src="' . e(BASE_URL . $emp['logo']) . '" alt="Logo de ' . e($emp['nome']) . '" loading="lazy">';
        }
        return '<span class="ui-logo ui-logo-ini" style="width:' . $px . 'px;height:' . $px . 'px" aria-hidden="true">' . e(iniciais($emp['nome'])) . '</span>';
    }

    public static function formConfirmar(string $acao, array $campos, string $botao, string $titulo, string $texto, string $tom = 'danger', string $classe = 'ui-btn ui-btn-sm'): string
    {
        $h = '<form method="post" action="' . e($acao) . '" data-confirm="' . e($texto) . '" data-confirm-title="' . e($titulo) . '" data-confirm-ok="' . e($botao) . '" data-confirm-tone="' . e($tom) . '">' . csrf_field();
        foreach ($campos as $k => $v) {
            $h .= '<input type="hidden" name="' . e($k) . '" value="' . e((string) $v) . '">';
        }
        return $h . '<button class="' . e($classe) . '" type="submit">' . e($botao) . '</button></form>';
    }

    public static function formMotivo(string $acao, array $campos, string $botao, string $titulo, string $classe = 'ui-btn ui-btn-sm ui-btn-danger-o'): string
    {
        $h = '<form method="post" action="' . e($acao) . '" data-motivo="' . e($titulo) . '" data-confirm-ok="' . e($botao) . '">' . csrf_field();
        foreach ($campos as $k => $v) {
            $h .= '<input type="hidden" name="' . e($k) . '" value="' . e((string) $v) . '">';
        }
        return $h . '<input type="hidden" name="motivo" value=""><button class="' . e($classe) . '" type="submit">' . e($botao) . '</button></form>';
    }

    public static function campo(array $o, array $old = [], array $erros = []): string
    {
        $n = $o['name'];
        $t = $o['type'] ?? 'text';
        $v = $old[$n] ?? ($o['value'] ?? '');
        if (is_array($v)) {
            $v = '';
        }
        $id = $o['id'] ?? $n;
        $err = $erros[$n] ?? '';
        $req = !empty($o['req']);
        $attr = '';
        foreach (($o['attrs'] ?? []) as $k => $x) {
            $attr .= is_int($k) ? ' ' . e($x) : ' ' . e($k) . '="' . e($x) . '"';
        }
        $cls = ($t === 'select' ? 'ui-select' : ($t === 'textarea' ? 'ui-textarea' : 'ui-input')) . ($err ? ' is-invalid' : '');
        $h = '<div class="ui-field"' . (!empty($o['when']) ? ' data-when="' . e($o['when']) . '"' : '') . '><label for="' . e($id) . '">' . e($o['label']) . ($req ? ' <span class="ui-req" aria-hidden="true">*</span>' : '') . '</label>';
        $base = 'id="' . e($id) . '" name="' . e($n) . '" class="' . $cls . '"' . ($req ? ' required' : '') . $attr . ($err ? ' aria-invalid="true"' : '');
        if ($t === 'select') {
            $h .= '<select ' . $base . '><option value="">' . e($o['vazio'] ?? 'Selecione') . '</option>';
            foreach ($o['opcoes'] as $k => $r) {
                $val = array_is_list($o['opcoes']) ? $r : $k;
                $h .= '<option value="' . e($val) . '"' . ((string) $v === (string) $val ? ' selected' : '') . '>' . e($r) . '</option>';
            }
            $h .= '</select>';
        } elseif ($t === 'textarea') {
            $h .= '<textarea ' . $base . ' rows="' . (int) ($o['rows'] ?? 4) . '">' . e($v) . '</textarea>';
        } else {
            $h .= '<input type="' . e($t) . '" ' . $base . ' value="' . e($v) . '">';
        }
        if (!empty($o['hint'])) {
            $h .= '<small class="ui-hint">' . e($o['hint']) . '</small>';
        }
        return $h . '<small class="ui-err" data-err>' . e($err) . '</small></div>';
    }


    public static function acoesVagaAdmin(array $v, string $ret = 'admin-vagas', ?int $retId = null): string
    {
        $c = ['tipo' => 'vaga', 'id' => $v['id'], 'ret' => $ret] + ($retId ? ['ret_id' => $retId] : []);
        $a = url('admin-acao');
        $h = '<div class="ui-actions">';
        if ($v['status'] === Status::V_AGUARDANDO) {
            $h .= self::formConfirmar($a, $c + ['acao' => 'aprovar'], 'Aprovar', 'Aprovar esta vaga?', 'A vaga "' . $v['titulo'] . '" será publicada de ' . data_br($v['data_publicacao']) . ' até ' . data_br($v['data_limite']) . ' e a empresa receberá um e-mail.', 'primary', 'ui-btn ui-btn-sm ui-btn-primary');
            $h .= self::formMotivo($a, $c + ['acao' => 'rejeitar'], 'Rejeitar', 'Rejeitar esta vaga');
        } elseif ($v['status'] === Status::V_APROVADA) {
            if ($v['situacao'] === 'publicada') {
                $h .= '<form method="post" action="' . e($a) . '">' . csrf_field() . '<input type="hidden" name="acao" value="destaque">' . implode('', array_map(fn($k, $x) => '<input type="hidden" name="' . e($k) . '" value="' . e((string) $x) . '">', array_keys($c), $c)) . '<button class="ui-btn ui-btn-sm" type="submit">' . ($v['destaque'] ? 'Tirar do destaque' : 'Destacar') . '</button></form>';
            }
            $h .= self::formConfirmar($a, $c + ['acao' => 'encerrar'], 'Encerrar', 'Encerrar esta vaga?', 'A vaga "' . $v['titulo'] . '" deixa de ser exibida aos estudantes, continua no histórico e a empresa será avisada.', 'danger', 'ui-btn ui-btn-sm ui-btn-danger-o');
        }
        return $h . '</div>';
    }

    public static function acoesEmpresaAdmin(array $e, string $ret = 'admin-empresas', ?int $retId = null): string
    {
        $c = ['tipo' => 'empresa', 'id' => $e['id'], 'ret' => $ret] + ($retId ? ['ret_id' => $retId] : []);
        $a = url('admin-acao');
        $h = '<div class="ui-actions">';
        if ($e['status'] === Status::E_PENDENTE) {
            $h .= self::formConfirmar($a, $c + ['acao' => 'aprovar'], 'Aprovar', 'Aprovar a empresa?', $e['nome'] . ' poderá publicar vagas e receberá um e-mail de confirmação.', 'primary', 'ui-btn ui-btn-sm ui-btn-primary');
            $h .= self::formMotivo($a, $c + ['acao' => 'reprovar'], 'Reprovar', 'Reprovar o cadastro');
        } elseif ($e['status'] === Status::E_APROVADA) {
            $h .= self::formMotivo($a, $c + ['acao' => 'bloquear'], 'Bloquear', 'Bloquear a empresa', 'ui-btn ui-btn-sm ui-btn-danger-o');
        } elseif ($e['status'] === Status::E_BLOQUEADA) {
            $h .= self::formConfirmar($a, $c + ['acao' => 'desbloquear'], 'Desbloquear', 'Desbloquear a empresa?', 'A empresa volta a acessar a plataforma e as vagas aprovadas voltam ao ar nas datas previstas.', 'primary', 'ui-btn ui-btn-sm');
        }
        return $h . '</div>';
    }


    public static function donut(array $itens, string $centro = ''): string
    {
        $total = array_sum(array_column($itens, 1));
        if ($total <= 0) {
            return self::vazio('Sem dados ainda', 'Os números aparecem quando houver registros.');
        }
        $r = 42;
        $c = 2 * M_PI * $r;
        $off = 0;
        $arcos = '';
        foreach ($itens as [$rot, $v, $cor]) {
            if ($v <= 0) {
                continue;
            }
            $len = $v / $total * $c;
            $arcos .= '<circle cx="60" cy="60" r="' . $r . '" fill="none" stroke="' . e($cor) . '" stroke-width="16" stroke-dasharray="' . round($len, 2) . ' ' . round($c - $len, 2) . '" stroke-dashoffset="' . round(-$off, 2) . '" transform="rotate(-90 60 60)"/>';
            $off += $len;
        }
        $li = '';
        foreach ($itens as [$rot, $v, $cor]) {
            $li .= '<li><i style="background:' . e($cor) . '"></i>' . e($rot) . ': <strong>' . (int) $v . '</strong></li>';
        }
        return '<div class="ui-donut-wrap"><svg viewBox="0 0 120 120" width="150" height="150" role="img" aria-label="Gráfico de rosca: ' . e(implode(', ', array_map(fn($i) => $i[0] . ' ' . (int) $i[1], $itens))) . '">' . $arcos
            . '<text x="60" y="58" text-anchor="middle" style="font:700 20px system-ui;fill:var(--text)">' . (int) $total . '</text><text x="60" y="73" text-anchor="middle" style="font:11px system-ui;fill:var(--muted)">' . e($centro) . '</text></svg><ul>' . $li . '</ul></div>';
    }

    public static function comparar(array $linhas, string $descricao): string
    {
        return self::barras($linhas, [['atual', 'Período atual', 'var(--chart-1)'], ['anterior', 'Período anterior', 'var(--chart-prev)']], $descricao);
    }

    public static function barras(array $linhas, array $series, string $descricao): string
    {
        $max = 0;
        foreach ($linhas as $l) {
            foreach ($series as [$k]) {
                $max = max($max, (int) ($l[$k] ?? 0));
            }
        }
        if ($max <= 0) {
            return self::vazio('Sem dados no período', 'Os gráficos aparecem quando houver registros.');
        }
        $W = 560;
        $H = 200;
        $pt = 10;
        $pb = 26;
        $pl = 28;
        $n = count($linhas);
        $gw = ($W - $pl) / $n;
        $bw = min(22, ($gw - 8) / count($series));
        $ih = $H - $pt - $pb;
        $svg = '';
        $topo = max(4, (int) ceil($max / 4) * 4);
        for ($i = 0; $i <= 4; $i++) {
            $y = $pt + $ih - $ih * $i / 4;
            $svg .= '<line class="grid" x1="' . $pl . '" x2="' . $W . '" y1="' . $y . '" y2="' . $y . '"/><text x="' . ($pl - 6) . '" y="' . ($y + 4) . '" text-anchor="end">' . round($topo * $i / 4) . '</text>';
        }
        foreach ($linhas as $i => $l) {
            $x0 = $pl + $i * $gw + ($gw - $bw * count($series)) / 2;
            foreach ($series as $j => [$k, $rot, $cor]) {
                $v = (int) ($l[$k] ?? 0);
                $h = $ih * $v / $topo;
                $svg .= '<rect x="' . round($x0 + $j * $bw, 1) . '" y="' . round($pt + $ih - $h, 1) . '" width="' . round($bw - 2, 1) . '" height="' . round($h, 1) . '" rx="3" fill="' . e($cor) . '"><title>' . e($rot . ' ' . $l['rot'] . ': ' . $v) . '</title></rect>';
            }
            if ($i % max(1, (int) ceil($n / 10)) === 0) {
                $svg .= '<text x="' . round($pl + $i * $gw + $gw / 2, 1) . '" y="' . ($H - 8) . '" text-anchor="middle">' . e($l['rot']) . '</text>';
            }
        }
        $lg = implode('', array_map(fn($s) => '<span><i style="background:' . e($s[2]) . '"></i>' . e($s[1]) . '</span>', $series));
        return '<svg class="ui-chart" viewBox="0 0 ' . $W . ' ' . $H . '" role="img" aria-label="' . e($descricao) . '">' . $svg . '</svg><div class="ui-legend">' . $lg . '</div>';
    }
}
