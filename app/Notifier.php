<?php
class Notifier
{
    private static function adminEmails(): array
    {
        $list = array_column(Database::all('SELECT email FROM admins WHERE ativo = 1'), 'email');
        foreach (explode(',', (string) env('ADMIN_NOTIFY_EMAIL', '')) as $extra) $list[] = $extra;
        $list = array_map('mb_strtolower', array_filter(array_map('trim', $list), fn($x) => filter_var($x, FILTER_VALIDATE_EMAIL)));
        return array_values(array_unique($list));
    }

    private static function notifyAdmins(string $subject, string $html): void
    {
        foreach (self::adminEmails() as $email) Mailer::enviar($email, $subject, $html);
    }

    public static function interna(string $type, int $refId, string $message): void
    {
        Database::insert('notificacoes', ['tipo' => $type, 'referencia_id' => $refId, 'mensagem' => $message, 'lida' => 0, 'criado_em' => Database::now()]);
    }

    public static function empresaCadastrada(array $company): void
    {
        self::interna('empresa', (int) $company['id'], 'Nova empresa para aprovar: ' . $company['nome']);
        self::notifyAdmins('Nova empresa aguardando aprovação: ' . $company['nome'], Mailer::layout('Nova empresa cadastrada',
            '<p>A empresa <strong>' . e($company['nome']) . '</strong> (CNPJ ' . e(Cnpj::format($company['cnpj'])) . ') pediu cadastro e aguarda análise.</p>'
            . '<p>Responsável: ' . e($company['responsavel']) . '<br>E-mail: ' . e($company['email']) . '<br>Telefone: ' . e(fone_fmt($company['telefone'])) . '</p>',
            ['Analisar no painel', url_abs('admin-aprovacoes')]));
        Mailer::enviar($company['email'], 'Recebemos o cadastro da sua empresa', Mailer::layout('Cadastro recebido',
            '<p>Olá, ' . e($company['responsavel']) . '!</p><p>Recebemos o cadastro da <strong>' . e($company['nome']) . '</strong>. A Coordenação vai analisar os dados e o termo de cooperação. Você receberá um e-mail com o resultado.</p>'));
    }

    public static function adminCriado(array $newAdmin, array $creator): void
    {
        Mailer::enviar($newAdmin['email'], 'Sua conta de administrador foi criada', Mailer::layout('Conta de administrador criada',
            '<p>Olá, ' . e(primeiro_nome($newAdmin['nome'])) . '!</p><p><strong>' . e($creator['nome']) . '</strong> criou uma conta de administrador para você no portal de vagas de estágio.</p>'
            . '<p>Seu login é <strong>' . e($newAdmin['email']) . '</strong>. Use a senha inicial informada pela Coordenação: ela vale <strong>só no primeiro acesso</strong>. Ao entrar, você vai criar a sua própria senha antes de usar o sistema.</p>',
            ['Fazer o primeiro acesso', url_abs('admin-login', ['email' => $newAdmin['email']])]));
    }

    public static function senhaRedefinicao(string $profile, array $user, string $token, string $code): void
    {
        $link = url_abs($profile === 'admin' ? 'admin-login' : 'empresa-login', ['redefinir' => $token]);
        Mailer::enviar($user['email'], 'Redefinição de senha', Mailer::layout('Redefinir a sua senha',
            '<p>Olá, ' . e(primeiro_nome((string) ($user['responsavel'] ?? $user['nome']))) . '!</p><p>Recebemos um pedido para redefinir a senha da sua conta no portal de vagas de estágio.</p>'
            . '<p>Clique no botão abaixo ou, na tela de login, digite este código:</p><p style="font-size:28px;letter-spacing:6px;font-weight:bold;margin:10px 0">' . e($code) . '</p>'
            . '<p>O link e o código valem por <strong>' . PasswordReset::VALIDADE_MIN . ' minutos</strong> e só podem ser usados uma vez. Se não foi você, ignore este e-mail: a sua senha continua a mesma.</p>',
            ['Criar uma nova senha', $link]));
    }

    public static function senhaAlterada(string $profile, array $user): void
    {
        Mailer::enviar($user['email'], 'Sua senha foi alterada', Mailer::layout('Senha alterada',
            '<p>Olá, ' . e(primeiro_nome((string) ($user['responsavel'] ?? $user['nome']))) . '!</p><p>A senha da sua conta foi alterada agora. Se não foi você, peça outra redefinição e avise a Coordenação.</p>',
            ['Entrar', url_abs($profile === 'admin' ? 'admin-login' : 'empresa-login')]));
    }

    public static function empresaAvaliada(array $company): void
    {
        if ($company['status'] === Status::E_BLOQUEADA) {
            Mailer::enviar($company['email'], 'Conta bloqueada', Mailer::layout('Conta bloqueada',
                '<p>Olá, ' . e($company['responsavel']) . '!</p><p>O acesso da <strong>' . e($company['nome']) . '</strong> foi bloqueado pela Coordenação e as vagas deixaram de aparecer no site.</p>'
                . '<p><strong>Motivo:</strong><br>' . nl2br(e((string) $company['motivo'])) . '</p><p>Para esclarecimentos, fale com a Coordenação.</p>'));
            return;
        }
        if ($company['status'] === Status::E_PENDENTE) return;
        if ($company['status'] === 'aprovada') {
            Mailer::enviar($company['email'], 'Cadastro aprovado: sua empresa já pode publicar vagas', Mailer::layout('Cadastro aprovado',
                '<p>Olá, ' . e($company['responsavel']) . '!</p><p>O cadastro da <strong>' . e($company['nome']) . '</strong> foi <strong>aprovado</strong>. Entre na área da empresa para publicar suas vagas.</p>',
                ['Acessar área da empresa', url_abs('empresa-login')]));
        } else {
            Mailer::enviar($company['email'], 'Cadastro não aprovado', Mailer::layout('Cadastro não aprovado',
                '<p>Olá, ' . e($company['responsavel']) . '!</p><p>O cadastro da <strong>' . e($company['nome']) . '</strong> não foi aprovado.</p>'
                . '<p><strong>Motivo informado pela Coordenação:</strong><br>' . nl2br(e((string) $company['motivo'])) . '</p><p>Você pode se cadastrar novamente com os dados corrigidos.</p>'));
        }
    }

    public static function vagaEnviada(array $job, array $company): void
    {
        self::interna('vaga', (int) $job['id'], 'Nova vaga para aprovar: ' . $job['titulo'] . ' (' . $company['nome'] . ')');
        self::notifyAdmins('Nova vaga aguardando aprovação: ' . $job['titulo'], Mailer::layout('Nova vaga enviada',
            '<p><strong>' . e($job['titulo']) . '</strong> · ' . e($job['tipo']) . ' · ' . e($job['area']) . '<br>Empresa: ' . e($company['nome'])
            . '<br>No ar de ' . data_br($job['data_publicacao']) . ' até ' . data_br($job['data_limite']) . '</p>',
            ['Analisar no painel', url_abs('admin-aprovacoes')]));
        Mailer::enviar($company['email'], 'Vaga recebida: ' . $job['titulo'], Mailer::layout('Vaga enviada para análise',
            '<p>Recebemos a vaga <strong>' . e($job['titulo']) . '</strong> (código ' . e($job['codigo']) . '). Assim que a Coordenação aprovar, avisaremos por e-mail com as datas em que ela ficará no ar.</p>'));
    }

    public static function vagaReenviada(array $job, array $company): void
    {
        self::interna('vaga', (int) $job['id'], 'Vaga editada aguarda nova aprovação: ' . $job['titulo'] . ' (' . $company['nome'] . ')');
        self::notifyAdmins('Vaga editada aguarda nova aprovação: ' . $job['titulo'], Mailer::layout('Vaga editada',
            '<p>A empresa <strong>' . e($company['nome']) . '</strong> alterou a vaga <strong>' . e($job['titulo']) . '</strong> (' . e($job['codigo']) . '). Ela saiu do ar e precisa de nova aprovação.</p>',
            ['Analisar no painel', url_abs('admin-aprovacoes')]));
        Mailer::enviar($company['email'], 'Vaga em nova análise: ' . $job['titulo'], Mailer::layout('Vaga em nova análise',
            '<p>As alterações na vaga <strong>' . e($job['titulo']) . '</strong> foram recebidas. Ela só volta ao ar depois de aprovada pela Coordenação.</p>'));
    }

    public static function vagaAvaliada(array $job, array $company): void
    {
        if ($job['status'] === 'encerrada') { self::vagaEncerrada($job, $company, true); return; }
        if ($job['status'] === 'aprovada') {
            $today = date('Y-m-d');
            $when  = $job['data_publicacao'] <= $today ? 'já está no ar' : 'entra no ar em ' . data_br($job['data_publicacao']);
            Mailer::enviar($company['email'], 'Vaga aprovada: ' . $job['titulo'], Mailer::layout('Vaga aprovada',
                '<p>A vaga <strong>' . e($job['titulo']) . '</strong> foi <strong>aprovada</strong> e ' . $when . '.</p>'
                . '<p>Ela ficará no ar <strong>até ' . data_br($job['data_limite']) . '</strong> e sai sozinha depois dessa data. Código da vaga: <strong>' . e($job['codigo']) . '</strong>.</p>',
                ['Ver vaga no site', url_abs('vaga', ['id' => $job['id']])]));
        } else {
            Mailer::enviar($company['email'], 'Vaga não aprovada: ' . $job['titulo'], Mailer::layout('Vaga não aprovada',
                '<p>A vaga <strong>' . e($job['titulo']) . '</strong> não foi aprovada.</p><p><strong>Motivo:</strong><br>' . nl2br(e((string) $job['motivo'])) . '</p><p>Você pode corrigir e publicar novamente.</p>'));
        }
    }

    public static function vagaVencendo(array $job, array $company): void
    {
        Mailer::enviar($company['email'], 'Sua vaga encerra em breve: ' . $job['titulo'], Mailer::layout('Vaga perto do fim do prazo',
            '<p>A vaga <strong>' . e($job['titulo']) . '</strong> sai do ar em <strong>' . data_br($job['data_limite']) . '</strong>. Se ainda precisar de candidatos, publique uma nova vaga com novo prazo.</p>',
            ['Publicar vaga', url_abs('vaga-cadastro')]));
    }

    public static function vagaEncerrada(array $job, array $company, bool $byCoordination = false): void
    {
        Mailer::enviar($company['email'], 'Vaga encerrada: ' . $job['titulo'], Mailer::layout('Vaga encerrada',
            '<p>A vaga <strong>' . e($job['titulo']) . '</strong> (código ' . e($job['codigo']) . ') saiu do ar'
            . ($byCoordination ? ' por decisão da Coordenação' : ' porque o prazo terminou')
            . '. Ela continua guardada no seu histórico.</p>'));
    }
}
