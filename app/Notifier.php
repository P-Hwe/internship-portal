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

    public static function firstAccessBody(string $email, string $temporaryPassword): string
    {
        return '<p>Seu login é <strong>' . e($email) . '</strong> e a sua senha provisória é:</p>'
            . '<p style="font-family:Consolas,monospace;font-size:22px;letter-spacing:2px;font-weight:bold;background:#f3f5f4;border:1px dashed #9aa5a0;padding:10px 14px;display:inline-block">' . e($temporaryPassword) . '</p>'
            . '<p>Só você recebeu esta senha: quem criou a sua conta não tem acesso a ela. Use o botão abaixo para criar a sua própria senha agora. O link vale por '
            . PasswordReset::FIRST_ACCESS_HOURS . ' horas e funciona uma única vez. Se preferir, entre com a senha provisória: o sistema vai pedir a nova senha antes de liberar o acesso.</p>';
    }

    public static function adminCriado(array $newAdmin, array $creator, string $temporaryPassword, string $token): void
    {
        Mailer::enviar($newAdmin['email'], 'Sua conta de administrador foi criada', Mailer::layout('Conta de administrador criada',
            '<p>Olá, ' . e(primeiro_nome($newAdmin['nome'])) . '!</p><p><strong>' . e($creator['nome']) . '</strong> criou uma conta de administrador para você no portal de vagas de estágio.</p>'
            . self::firstAccessBody($newAdmin['email'], $temporaryPassword),
            ['Criar minha senha', url_abs('admin-login', ['redefinir' => $token])]));
    }

    public static function rootCriado(array $admin, string $temporaryPassword, string $token): bool
    {
        return Mailer::enviar($admin['email'], 'Acesso de administrador principal do portal de vagas', Mailer::layout('Bem-vindo ao portal de vagas',
            '<p>Olá!</p><p>O portal de vagas de estágio do IFSP Guarulhos foi instalado e você é o <strong>administrador principal</strong>: aprova empresas e vagas e cria as contas da equipe da Coordenação.</p>'
            . self::firstAccessBody($admin['email'], $temporaryPassword),
            ['Criar minha senha', url_abs('admin-login', ['redefinir' => $token])]));
    }

    public static function adminStatus(array $admin, string $event, array $actor): void
    {
        $texts = [
            'desativado' => ['Sua conta de administrador foi desativada', 'Conta desativada', 'desativou a sua conta de administrador. Você não consegue mais entrar no painel até ela ser reativada.'],
            'reativado'  => ['Sua conta de administrador foi reativada', 'Conta reativada', 'reativou a sua conta de administrador. Você já pode entrar no painel com a sua senha.'],
            'excluido'   => ['Sua conta de administrador foi excluída', 'Conta excluída', 'excluiu a sua conta de administrador. O acesso ao painel foi encerrado.'],
        ];
        if (!isset($texts[$event])) return;
        [$subject, $title, $text] = $texts[$event];
        Mailer::enviar($admin['email'], $subject, Mailer::layout($title,
            '<p>Olá, ' . e(primeiro_nome($admin['nome'])) . '!</p><p><strong>' . e($actor['nome']) . '</strong> ' . $text . '</p><p>Em caso de dúvida, fale com a Coordenação.</p>',
            $event === 'reativado' ? ['Entrar no painel', url_abs('admin-login')] : null));
    }

    public static function parceriaEncerrada(int $companyId, array $former): void
    {
        $html = '<p>A parceria da empresa <strong>' . e($former['nome']) . '</strong> com o portal de vagas de estágio do IFSP Guarulhos foi encerrada a pedido da própria empresa.</p>'
            . '<p>As vagas saíram do mural e os dados de cadastro, contatos, usuários e arquivos foram apagados do portal. O CNPJ ficou livre: se quiser voltar, basta fazer um novo cadastro.</p>';
        foreach ($former['emails'] as $email) Mailer::enviar($email, 'Parceria encerrada no portal de vagas do IFSP Guarulhos', Mailer::layout('Parceria encerrada', $html, ['Ver o mural de vagas', url_abs('home')]));
        $message = 'A empresa ' . $former['nome'] . ' encerrou a parceria e removeu os seus dados do portal';
        self::interna('empresa', $companyId, mb_substr($message, 0, 200));
        self::notifyAdmins($message, Mailer::layout('Parceria encerrada por uma empresa',
            '<p>A empresa <strong>' . e($former['nome']) . '</strong> encerrou a parceria pela área da empresa.</p><p>' . ($former['vagas'] ? (int) $former['vagas'] . ' vaga(s) saíram do mural. ' : '')
            . 'Os dados de cadastro e contato foram apagados do portal (LGPD); fica só o registro do encerramento no histórico.</p>',
            ['Ver no painel', url_abs('admin-empresa', ['id' => $companyId])]));
    }

    public static function membroCriado(array $member, array $company, string $creatorName, string $temporaryPassword, string $token): void
    {
        Mailer::enviar($member['email'], 'Você foi adicionado à área da empresa ' . $company['nome'], Mailer::layout('Acesso à área da empresa',
            '<p>Olá, ' . e(primeiro_nome($member['nome'])) . '!</p><p><strong>' . e($creatorName) . '</strong> adicionou você à área da empresa <strong>' . e($company['nome'])
            . '</strong> no portal de vagas de estágio do IFSP Guarulhos, com o cargo <strong>' . e(CompanyMember::roleLabel($member['papel'])) . '</strong>: ' . e(CompanyMember::ROLE_HINTS[$member['papel']] ?? '') . '.</p>'
            . self::firstAccessBody($member['email'], $temporaryPassword),
            ['Criar minha senha', url_abs('empresa-login', ['redefinir' => $token])]));
    }

    public static function membroCoordenacao(array $member, array $company, string $actorName, string $event): void
    {
        $role = CompanyMember::roleLabel($member['papel']);
        $message = $event === 'permissao'
            ? 'Cargo alterado na empresa ' . $company['nome'] . ': ' . $member['nome'] . ' agora é ' . $role
            : 'Novo usuário na empresa ' . $company['nome'] . ': ' . $member['nome'] . ' (' . $role . ')';
        self::interna('membro', (int) $company['id'], mb_substr($message, 0, 200));
        self::notifyAdmins($message, Mailer::layout($event === 'permissao' ? 'Cargo alterado em uma empresa' : 'Novo usuário em uma empresa',
            '<p><strong>' . e($actorName) . '</strong> ' . ($event === 'permissao' ? 'mudou o cargo de' : 'adicionou') . ' <strong>' . e($member['nome'])
            . '</strong> (' . e($member['email']) . ') na empresa <strong>' . e($company['nome']) . '</strong>.</p><p>Cargo: <strong>' . e($role) . '</strong>.</p>',
            ['Ver empresa no painel', url_abs('admin-empresa', ['id' => $company['id']])]));
    }

    public static function membroStatus(array $member, array $company, string $event, string $actorLabel, ?string $reason = null): void
    {
        $texts = [
            'desativado' => ['Seu acesso à área da empresa foi desativado', 'Acesso desativado', 'desativou o seu acesso à área da empresa'],
            'reativado'  => ['Seu acesso à área da empresa foi reativado', 'Acesso reativado', 'reativou o seu acesso à área da empresa'],
            'excluido'   => ['Seu acesso à área da empresa foi removido', 'Acesso removido', 'removeu o seu acesso à área da empresa'],
            'permissao'  => ['Seu cargo na área da empresa mudou', 'Cargo alterado', 'mudou o seu cargo na área da empresa'],
        ];
        if (!isset($texts[$event])) return;
        [$subject, $title, $text] = $texts[$event];
        $extra = $event === 'permissao' ? '<p>Novo cargo: <strong>' . e(CompanyMember::roleLabel($member['papel'])) . '</strong> (' . e(CompanyMember::ROLE_HINTS[$member['papel']] ?? '') . ').</p>' : '';
        if ($reason) $extra .= '<p><strong>Motivo:</strong><br>' . nl2br(e($reason)) . '</p>';
        Mailer::enviar($member['email'], $subject, Mailer::layout($title,
            '<p>Olá, ' . e(primeiro_nome($member['nome'])) . '!</p><p><strong>' . e($actorLabel) . '</strong> ' . $text . ' <strong>' . e($company['nome']) . '</strong>.</p>' . $extra,
            in_array($event, ['reativado', 'permissao'], true) ? ['Entrar na área da empresa', url_abs('empresa-login')] : null));
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
