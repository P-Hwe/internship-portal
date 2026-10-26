<?php

/** Central de avisos: e-mail + notificação dentro do painel do administrador. */
class Notificador
{
    private static function emailsAdmin(): array
    {
        $l = array_filter(array_map('trim', explode(',', (string) env('ADMIN_NOTIFY_EMAIL', ''))));
        if (!$l) {
            $l = array_column(Database::all('SELECT email FROM admins'), 'email');
        }
        return array_values(array_unique($l));
    }
    private static function paraAdmins(string $assunto, string $html): void
    {
        foreach (self::emailsAdmin() as $e) {
            Mailer::enviar($e, $assunto, $html);
        }
    }
    public static function interna(string $tipo, int $ref, string $msg): void
    {
        Database::insert('notificacoes', ['tipo' => $tipo, 'referencia_id' => $ref, 'mensagem' => $msg, 'lida' => 0, 'criado_em' => Database::agora()]);
    }

    // ---- empresa ----
    public static function empresaCadastrada(array $emp): void
    {
        self::interna('empresa', (int) $emp['id'], 'Nova empresa para aprovar: ' . $emp['nome']);
        self::paraAdmins('Nova empresa aguardando aprovação: ' . $emp['nome'], Mailer::layout(
            'Nova empresa cadastrada',
            '<p>A empresa <strong>' . e($emp['nome']) . '</strong> (CNPJ ' . e(Cnpj::formatar($emp['cnpj'])) . ') pediu cadastro e aguarda análise.</p>'
            . '<p>Responsável: ' . e($emp['responsavel']) . '<br>E-mail: ' . e($emp['email']) . '<br>Telefone: ' . e(fone_fmt($emp['telefone'])) . '</p>',
            ['Analisar no painel', url_abs('admin-aprovacoes')],
        ));
        Mailer::enviar($emp['email'], 'Recebemos o cadastro da sua empresa', Mailer::layout(
            'Cadastro recebido',
            '<p>Olá, ' . e($emp['responsavel']) . '!</p><p>Recebemos o cadastro da <strong>' . e($emp['nome']) . '</strong>. A Coordenação vai analisar os dados e o termo de cooperação. Você receberá um e-mail com o resultado.</p>',
        ));
    }

    public static function empresaAvaliada(array $emp): void
    {
        if ($emp['status'] === Estado::E_BLOQUEADA) {
            Mailer::enviar($emp['email'], 'Conta bloqueada', Mailer::layout(
                'Conta bloqueada',
                '<p>Olá, ' . e($emp['responsavel']) . '!</p><p>O acesso da <strong>' . e($emp['nome']) . '</strong> foi bloqueado pela Coordenação e as vagas deixaram de aparecer no site.</p>'
                . '<p><strong>Motivo:</strong><br>' . nl2br(e((string) $emp['motivo'])) . '</p><p>Para esclarecimentos, fale com a Coordenação.</p>',
            ));
            return;
        }
        if ($emp['status'] === Estado::E_PENDENTE) {
            return;
        }
        if ($emp['status'] === 'aprovada') {
            Mailer::enviar($emp['email'], 'Cadastro aprovado: sua empresa já pode publicar vagas', Mailer::layout(
                'Cadastro aprovado',
                '<p>Olá, ' . e($emp['responsavel']) . '!</p><p>O cadastro da <strong>' . e($emp['nome']) . '</strong> foi <strong>aprovado</strong>. Entre na área da empresa para publicar suas vagas.</p>',
                ['Acessar área da empresa', url_abs('empresa-login')],
            ));
        } else {
            Mailer::enviar($emp['email'], 'Cadastro não aprovado', Mailer::layout(
                'Cadastro não aprovado',
                '<p>Olá, ' . e($emp['responsavel']) . '!</p><p>O cadastro da <strong>' . e($emp['nome']) . '</strong> não foi aprovado.</p>'
                . '<p><strong>Motivo informado pela Coordenação:</strong><br>' . nl2br(e((string) $emp['motivo'])) . '</p><p>Você pode se cadastrar novamente com os dados corrigidos.</p>',
            ));
        }
    }

    // ---- vaga ----
    public static function vagaEnviada(array $vaga, array $emp): void
    {
        self::interna('vaga', (int) $vaga['id'], 'Nova vaga para aprovar: ' . $vaga['titulo'] . ' (' . $emp['nome'] . ')');
        self::paraAdmins('Nova vaga aguardando aprovação: ' . $vaga['titulo'], Mailer::layout(
            'Nova vaga enviada',
            '<p><strong>' . e($vaga['titulo']) . '</strong> · ' . e($vaga['tipo']) . ' · ' . e($vaga['area']) . '<br>Empresa: ' . e($emp['nome'])
            . '<br>No ar de ' . data_br($vaga['data_publicacao']) . ' até ' . data_br($vaga['data_limite']) . '</p>',
            ['Analisar no painel', url_abs('admin-aprovacoes')],
        ));
        Mailer::enviar($emp['email'], 'Vaga recebida: ' . $vaga['titulo'], Mailer::layout(
            'Vaga enviada para análise',
            '<p>Recebemos a vaga <strong>' . e($vaga['titulo']) . '</strong> (código ' . e($vaga['codigo']) . '). Assim que a Coordenação aprovar, avisaremos por e-mail com as datas em que ela ficará no ar.</p>',
        ));
    }

    /** Empresa editou vaga já aprovada/rejeitada: volta para análise. */
    public static function vagaReenviada(array $vaga, array $emp): void
    {
        self::interna('vaga', (int) $vaga['id'], 'Vaga editada aguarda nova aprovação: ' . $vaga['titulo'] . ' (' . $emp['nome'] . ')');
        self::paraAdmins('Vaga editada aguarda nova aprovação: ' . $vaga['titulo'], Mailer::layout(
            'Vaga editada',
            '<p>A empresa <strong>' . e($emp['nome']) . '</strong> alterou a vaga <strong>' . e($vaga['titulo']) . '</strong> (' . e($vaga['codigo']) . '). Ela saiu do ar e precisa de nova aprovação.</p>',
            ['Analisar no painel', url_abs('admin-aprovacoes')],
        ));
        Mailer::enviar($emp['email'], 'Vaga em nova análise: ' . $vaga['titulo'], Mailer::layout(
            'Vaga em nova análise',
            '<p>As alterações na vaga <strong>' . e($vaga['titulo']) . '</strong> foram recebidas. Ela só volta ao ar depois de aprovada pela Coordenação.</p>',
        ));
    }

    public static function vagaAvaliada(array $vaga, array $emp): void
    {
        if ($vaga['status'] === 'aprovada') {
            $hoje = date('Y-m-d');
            $quando = $vaga['data_publicacao'] <= $hoje ? 'já está no ar' : 'entra no ar em ' . data_br($vaga['data_publicacao']);
            Mailer::enviar($emp['email'], 'Vaga aprovada: ' . $vaga['titulo'], Mailer::layout(
                'Vaga aprovada',
                '<p>A vaga <strong>' . e($vaga['titulo']) . '</strong> foi <strong>aprovada</strong> e ' . $quando . '.</p>'
                . '<p>Ela ficará no ar <strong>até ' . data_br($vaga['data_limite']) . '</strong> e sai sozinha depois dessa data. Código da vaga: <strong>' . e($vaga['codigo']) . '</strong>.</p>',
                ['Ver vaga no site', url_abs('vaga', ['id' => $vaga['id']])],
            ));
        } else {
            Mailer::enviar($emp['email'], 'Vaga não aprovada: ' . $vaga['titulo'], Mailer::layout(
                'Vaga não aprovada',
                '<p>A vaga <strong>' . e($vaga['titulo']) . '</strong> não foi aprovada.</p><p><strong>Motivo:</strong><br>' . nl2br(e((string) $vaga['motivo'])) . '</p><p>Você pode corrigir e publicar novamente.</p>',
            ));
        }
    }

    public static function vagaVencendo(array $vaga, array $emp): void
    {
        Mailer::enviar($emp['email'], 'Sua vaga encerra em breve: ' . $vaga['titulo'], Mailer::layout(
            'Vaga perto do fim do prazo',
            '<p>A vaga <strong>' . e($vaga['titulo']) . '</strong> sai do ar em <strong>' . data_br($vaga['data_limite']) . '</strong>. Se ainda precisar de candidatos, publique uma nova vaga com novo prazo.</p>',
            ['Publicar vaga', url_abs('vaga-cadastro')],
        ));
    }

    public static function vagaEncerrada(array $vaga, array $emp): void
    {
        Mailer::enviar($emp['email'], 'Vaga encerrada: ' . $vaga['titulo'], Mailer::layout(
            'Vaga encerrada',
            '<p>A vaga <strong>' . e($vaga['titulo']) . '</strong> (código ' . e($vaga['codigo']) . ') saiu do ar.</p>',
        ));
    }
}
