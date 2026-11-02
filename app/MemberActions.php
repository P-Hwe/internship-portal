<?php
class MemberActions
{
    public static function create(array $company, array $actor, array $input): array
    {
        $name = trim((string) ($input['nome'] ?? ''));
        $email = mb_strtolower(trim((string) ($input['email'] ?? '')));
        $role = (string) ($input['papel'] ?? CompanyMember::MEMBER);
        $errors = [];
        if (mb_strlen($name) < 3 || mb_strlen($name) > 100) $errors['nome'] = 'Informe o nome completo.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 120) $errors['email'] = 'E-mail inválido.';
        elseif (CompanyMember::emailTaken($email) || Company::porEmail($email)) $errors['email'] = 'Este e-mail já está em uso no portal.';
        if (!in_array($role, [CompanyMember::MEMBER, CompanyMember::MANAGER], true)) $errors['papel'] = 'Escolha a permissão.';
        elseif ($role === CompanyMember::MANAGER && !CompanyMember::isPrincipal($actor)) $errors['papel'] = 'Só o responsável principal pode dar a permissão de administrador da empresa.';
        if ($errors) return [null, $errors];

        [$id, $temporaryPassword] = CompanyMember::create((int) $company['id'], $name, $email, $role, (int) $actor['id']);
        $member = CompanyMember::find($id);
        AuditLog::registrar('empresa', (int) $company['id'], 'usuario_criado', null, $role, 'empresa', (int) $company['id'], $name);
        Notifier::membroCriado($member, $company, $actor['nome'], $temporaryPassword, PasswordReset::issueFirstAccess('empresa', $id));
        Notifier::membroCoordenacao($member, $company, $actor['nome'], 'criado');
        return [$member, []];
    }

    public static function changeRole(array $company, array $actor, array $target, string $role): ?string
    {
        if (!CompanyMember::isPrincipal($actor)) return 'Só o responsável principal pode mudar permissões.';
        if (CompanyMember::isPrincipal($target)) return 'A permissão do responsável principal não pode ser alterada.';
        if (!in_array($role, [CompanyMember::MEMBER, CompanyMember::MANAGER], true)) return 'Permissão inválida.';
        if ($target['papel'] === $role) return null;
        CompanyMember::setRole((int) $target['id'], $role);
        $target = CompanyMember::find((int) $target['id']);
        AuditLog::registrar('empresa', (int) $company['id'], 'usuario_permissao', null, $role, 'empresa', (int) $company['id'], $target['nome']);
        Notifier::membroStatus($target, $company, 'permissao', $actor['nome']);
        Notifier::membroCoordenacao($target, $company, $actor['nome'], 'permissao');
        return null;
    }

    public static function changeStatus(array $company, array $target, string $action, string $actorType, ?array $actor, string $actorLabel, ?string $reason = null): ?string
    {
        if (!in_array($action, ['desativar', 'ativar', 'excluir'], true)) return 'Ação inválida.';
        if (CompanyMember::isPrincipal($target)) return 'O responsável principal não pode ser desativado nem excluído por aqui.';
        if ($actorType === 'empresa' && $actor && (int) $actor['id'] === (int) $target['id']) return 'Você não pode alterar o seu próprio acesso.';
        if ($actorType === 'empresa' && $action === 'ativar' && $target['desativado_por'] === 'admin') {
            return 'Este usuário foi desativado pela Coordenação. Só ela pode reativá-lo.';
        }
        $companyId = (int) $company['id'];
        $actorId = $actorType === 'admin' ? (int) ($actor['id'] ?? 0) : $companyId;
        if ($action === 'excluir') {
            CompanyMember::softDelete((int) $target['id']);
            AuditLog::registrar('empresa', $companyId, 'usuario_excluido', null, null, $actorType, $actorId, $target['nome']);
            Notifier::membroStatus($target, $company, 'excluido', $actorLabel, $reason);
            return null;
        }
        $active = $action === 'ativar';
        CompanyMember::setActive((int) $target['id'], $active, $actorType);
        AuditLog::registrar('empresa', $companyId, $active ? 'usuario_reativado' : 'usuario_desativado', null, null, $actorType, $actorId, $target['nome']);
        Notifier::membroStatus($target, $company, $active ? 'reativado' : 'desativado', $actorLabel, $reason);
        return null;
    }
}
