<?php
/** Central de avisos: e-mail + notificação dentro do painel do administrador. */
class Notificador
{
    private static function emailsAdmin(): array
    {
        $l = array_column(Database::all('SELECT email FROM admins WHERE ativo = 1'), 'email');
        foreach (explode(',', (string) env('ADMIN_NOTIFY_EMAIL', '')) as $x) $l[] = $x;
        $l = array_map('mb_strtolower', array_filter(array_map('trim', $l), fn($x) => filter_var($x, FILTER_VALIDATE_EMAIL)));
        return array_values(array_unique($l));
    }
    private static function paraAdmins(string $assunto, string $html): void
    {
        foreach (self::emailsAdmin() as $e) Mailer::enviar($e, $assunto, $html);
    }
    public static function interna(string $tipo, int $ref, string $msg): void
    {
        Database::insert('notificacoes', ['tipo' => $tipo, 'referencia_id' => $ref, 'mensagem' => $msg, 'lida' => 0, 'criado_em' => Database::agora()]);
    }

    // ---- empresa ----
    public static function empresaCadastrada(array $emp): void
    {
        self::interna('empresa', (int) $emp['id'], 'Nova empresa para aprovar: ' . $emp['nome']);
        self::paraAdmins('Nova empresa aguardando aprovação: ' . $emp['nome'], Mailer::layout('Nova empresa cadastrada',
            '<p>A empresa <strong>' . e($emp['nome']) . '</strong> (CNPJ ' . e(Cnpj::formatar($emp['cnpj'])) . ') pediu cadastro e aguarda análise.</p>'
            . '<p>Responsável: ' . e($emp['responsavel']) . '<br>E-mail: ' . e($emp['email']) . '<br>Telefone: ' . e(fone_fmt($emp['telefone'])) . '</p>',
            ['Analisar no painel', url_abs('admin-aprovacoes')]));
        Mailer::enviar($emp['email'], 'Recebemos o cadastro da sua empresa', Mailer::layout('Cadastro recebido',
            '<p>Olá, ' . e($emp['responsavel']) . '!</p><p>Recebemos o cadastro da <strong>' . e($emp['nome']) . '</strong>. A Coordenação vai analisar os dados e o termo de cooperação. Você receberá um e-mail com o resultado.</p>'));
    }

    /** Avisa o novo administrador que a conta foi criada. A senha inicial é combinada com a Coordenação, nunca vai no e-mail. */
    public static function adminCriado(array $novo, array $criador): void
    {
        Mailer::enviar($novo['email'], 'Sua conta de administrador foi criada', Mailer::layout('Conta de administrador criada',
            '<p>Olá, ' . e(primeiro_nome($novo['nome'])) . '!</p><p><strong>' . e($criador['nome']) . '</strong> criou uma conta de administrador para você no portal de vagas de estágio.</p>'
            . '<p>Seu login é <strong>' . e($novo['email']) . '</strong>. Use a senha inicial informada pela Coordenação: ela vale <strong>só no primeiro acesso</strong>. Ao entrar, você vai criar a sua própria senha antes de usar o sistema.</p>',
            ['Fazer o primeiro acesso', url_abs('admin-login', ['email' => $novo['email']])]));
    }

    /** E-mail do "esqueci a senha": botão com o link e o código para digitar. */
    public static function senhaRedefinicao(string $perfil, array $u, string $token, string $codigo): void
    {
        $link = url_abs($perfil === 'admin' ? 'admin-login' : 'empresa-login', ['redefinir' => $token]);
        Mailer::enviar($u['email'], 'Redefinição de senha', Mailer::layout('Redefinir a sua senha',
            '<p>Olá, ' . e(primeiro_nome((string) ($u['responsavel'] ?? $u['nome']))) . '!</p><p>Recebemos um pedido para redefinir a senha da sua conta no portal de vagas de estágio.</p>'
            . '<p>Clique no botão abaixo ou, na tela de login, digite este código:</p><p style="font-size:28px;letter-spacing:6px;font-weight:bold;margin:10px 0">' . e($codigo) . '</p>'
            . '<p>O link e o código valem por <strong>' . SenhaReset::VALIDADE_MIN . ' minutos</strong> e só podem ser usados uma vez. Se não foi você, ignore este e-mail: a sua senha continua a mesma.</p>',
            ['Criar uma nova senha', $link]));
    }
    public static function senhaAlterada(string $perfil, array $u): void
    {
        Mailer::enviar($u['email'], 'Sua senha foi alterada', Mailer::layout('Senha alterada',
            '<p>Olá, ' . e(primeiro_nome((string) ($u['responsavel'] ?? $u['nome']))) . '!</p><p>A senha da sua conta foi alterada agora. Se não foi você, peça outra redefinição e avise a Coordenação.</p>',
            ['Entrar', url_abs($perfil === 'admin' ? 'admin-login' : 'empresa-login')]));
    }

    public static function empresaAvaliada(array $emp): void
    {
        if ($emp['status'] === Estado::E_BLOQUEADA) {
            Mailer::enviar($emp['email'], 'Conta bloqueada', Mailer::layout('Conta bloqueada',
                '<p>Olá, ' . e($emp['responsavel']) . '!</p><p>O acesso da <strong>' . e($emp['nome']) . '</strong> foi bloqueado pela Coordenação e as vagas deixaram de aparecer no site.</p>'
                . '<p><strong>Motivo:</strong><br>' . nl2br(e((string) $emp['motivo'])) . '</p><p>Para esclarecimentos, fale com a Coordenação.</p>'));
            return;
        }
        if ($emp['status'] === Estado::E_PENDENTE) return;
        if ($emp['status'] === 'aprovada') {
            Mailer::enviar($emp['email'], 'Cadastro aprovado: sua empresa já pode publicar vagas', Mailer::layout('Cadastro aprovado',
                '<p>Olá, ' . e($emp['responsavel']) . '!</p><p>O cadastro da <strong>' . e($emp['nome']) . '</strong> foi <strong>aprovado</strong>. Entre na área da empresa para publicar suas vagas.</p>',
                ['Acessar área da empresa', url_abs('empresa-login')]));
        } else {
            Mailer::enviar($emp['email'], 'Cadastro não aprovado', Mailer::layout('Cadastro não aprovado',
                '<p>Olá, ' . e($emp['responsavel']) . '!</p><p>O cadastro da <strong>' . e($emp['nome']) . '</strong> não foi aprovado.</p>'
                . '<p><strong>Motivo informado pela Coordenação:</strong><br>' . nl2br(e((string) $emp['motivo'])) . '</p><p>Você pode se cadastrar novamente com os dados corrigidos.</p>'));
        }
    }

    // ---- vaga ----
    public static function vagaEnviada(array $vaga, array $emp): void
    {
        self::interna('vaga', (int) $vaga['id'], 'Nova vaga para aprovar: ' . $vaga['titulo'] . ' (' . $emp['nome'] . ')');
        self::paraAdmins('Nova vaga aguardando aprovação: ' . $vaga['titulo'], Mailer::layout('Nova vaga enviada',
            '<p><strong>' . e($vaga['titulo']) . '</strong> · ' . e($vaga['tipo']) . ' · ' . e($vaga['area']) . '<br>Empresa: ' . e($emp['nome'])
            . '<br>No ar de ' . data_br($vaga['data_publicacao']) . ' até ' . data_br($vaga['data_limite']) . '</p>', ['Analisar no painel', url_abs('admin-aprovacoes')]));
        Mailer::enviar($emp['email'], 'Vaga recebida: ' . $vaga['titulo'], Mailer::layout('Vaga enviada para análise',
            '<p>Recebemos a vaga <strong>' . e($vaga['titulo']) . '</strong> (código ' . e($vaga['codigo']) . '). Assim que a Coordenação aprovar, avisaremos por e-mail com as datas em que ela ficará no ar.</p>'));
    }

    public static function vagaReenviada(array $vaga, array $emp): void
    {
        self::interna('vaga', (int) $vaga['id'], 'Vaga editada aguarda nova aprovação: ' . $vaga['titulo'] . ' (' . $emp['nome'] . ')');
        self::paraAdmins('Vaga editada aguarda nova aprovação: ' . $vaga['titulo'], Mailer::layout('Vaga editada',
            '<p>A empresa <strong>' . e($emp['nome']) . '</strong> alterou a vaga <strong>' . e($vaga['titulo']) . '</strong> (' . e($vaga['codigo']) . '). Ela saiu do ar e precisa de nova aprovação.</p>', ['Analisar no painel', url_abs('admin-aprovacoes')]));
        Mailer::enviar($emp['email'], 'Vaga em nova análise: ' . $vaga['titulo'], Mailer::layout('Vaga em nova análise',
            '<p>As alterações na vaga <strong>' . e($vaga['titulo']) . '</strong> foram recebidas. Ela só volta ao ar depois de aprovada pela Coordenação.</p>'));
    }

    public static function vagaAvaliada(array $vaga, array $emp): void
    {
        if ($vaga['status'] === 'encerrada') { self::vagaEncerrada($vaga, $emp, true); return; }
        if ($vaga['status'] === 'aprovada') {
            $hoje = date('Y-m-d');
            $quando = $vaga['data_publicacao'] <= $hoje ? 'já está no ar' : 'entra no ar em ' . data_br($vaga['data_publicacao']);
            Mailer::enviar($emp['email'], 'Vaga aprovada: ' . $vaga['titulo'], Mailer::layout('Vaga aprovada',
                '<p>A vaga <strong>' . e($vaga['titulo']) . '</strong> foi <strong>aprovada</strong> e ' . $quando . '.</p>'
                . '<p>Ela ficará no ar <strong>até ' . data_br($vaga['data_limite']) . '</strong> e sai sozinha depois dessa data. Código da vaga: <strong>' . e($vaga['codigo']) . '</strong>.</p>',
                ['Ver vaga no site', url_abs('vaga', ['id' => $vaga['id']])]));
        } else {
            Mailer::enviar($emp['email'], 'Vaga não aprovada: ' . $vaga['titulo'], Mailer::layout('Vaga não aprovada',
                '<p>A vaga <strong>' . e($vaga['titulo']) . '</strong> não foi aprovada.</p><p><strong>Motivo:</strong><br>' . nl2br(e((string) $vaga['motivo'])) . '</p><p>Você pode corrigir e publicar novamente.</p>'));
        }
    }

    public static function vagaVencendo(array $vaga, array $emp): void
    {
        Mailer::enviar($emp['email'], 'Sua vaga encerra em breve: ' . $vaga['titulo'], Mailer::layout('Vaga perto do fim do prazo',
            '<p>A vaga <strong>' . e($vaga['titulo']) . '</strong> sai do ar em <strong>' . data_br($vaga['data_limite']) . '</strong>. Se ainda precisar de candidatos, publique uma nova vaga com novo prazo.</p>', ['Publicar vaga', url_abs('vaga-cadastro')]));
    }

    public static function vagaEncerrada(array $vaga, array $emp, bool $pelaCoordenacao = false): void
    {
        Mailer::enviar($emp['email'], 'Vaga encerrada: ' . $vaga['titulo'], Mailer::layout('Vaga encerrada',
            '<p>A vaga <strong>' . e($vaga['titulo']) . '</strong> (código ' . e($vaga['codigo']) . ') saiu do ar' . ($pelaCoordenacao ? ' por decisão da Coordenação' : ' porque o prazo terminou') . '. Ela continua guardada no seu histórico.</p>'));
    }
}
