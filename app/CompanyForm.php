<?php
class CompanyForm
{
    public static function passwordStrong(string $s): bool
    {
        return strlen($s) >= 8 && preg_match('/[A-Za-z]/', $s) && preg_match('/\d/', $s);
    }

    /** @deprecated Use passwordStrong() */
    public static function senhaForte(string $s): bool { return self::passwordStrong($s); }

    public static function validate(array $d, array $files, string $mode, ?array $current = null): array
    {
        $errors = [];
        $d['site']     = Link::normalize($d['site'] ?? '');
        $d['linkedin'] = Link::normalize($d['linkedin'] ?? '');
        $len = fn($k) => mb_strlen($d[$k] ?? '');

        if ($mode === 'cadastro') {
            if (!Cnpj::isValid($d['cnpj'] ?? ''))  $errors['cnpj'] = 'CNPJ inválido. Confira os 14 caracteres (letras e números).';
            elseif (Company::porCnpj($d['cnpj']))  $errors['cnpj'] = 'Este CNPJ já está cadastrado. Entre na área da empresa.';
        }
        if ($len('razao_social') < 3 || $len('razao_social') > 150) $errors['razao_social'] = 'Informe a razão social.';
        if ($len('nome') < 2 || $len('nome') > 120)   $errors['nome'] = 'Informe o nome fantasia.';
        if ($len('descricao') < 30 || $len('descricao') > 600) $errors['descricao'] = 'Descreva a empresa em 30 a 600 caracteres: é o que o estudante vê.';
        if (!in_array($d['segmento'] ?? '', SEGMENTS, true))      $errors['segmento'] = 'Escolha o segmento.';
        if (!in_array($d['porte'] ?? '', COMPANY_SIZES, true))    $errors['porte']    = 'Escolha o porte.';
        if ($d['site']     && !Link::isValid($d['site']))                 $errors['site']     = 'Endereço inválido. Exemplo: https://www.suaempresa.com.br';
        if ($d['linkedin'] && !Link::isValid($d['linkedin'], 'linkedin')) $errors['linkedin'] = 'Use o endereço do LinkedIn da empresa (linkedin.com/company/...).';

        $d['link_principal'] = ($d['link_principal'] ?? 'site') === 'linkedin' ? 'linkedin' : 'site';
        if ($d['link_principal'] === 'linkedin' && !$d['linkedin']) $d['link_principal'] = 'site';

        if (strlen(preg_replace('/\D/', '', $d['cep'] ?? '')) !== 8) $errors['cep'] = 'CEP inválido. Informe os 8 números.';
        foreach (['logradouro' => [3, 120], 'bairro' => [2, 60], 'cidade' => [2, 60]] as $field => [$min, $max]) {
            if ($len($field) < $min || $len($field) > $max) $errors[$field] = 'Preencha este campo corretamente.';
        }
        $numLen = $len('numero');
        if ($numLen < 1 || $numLen > 10) $errors['numero'] = 'Informe o número (ou S/N).';
        if ($len('complemento') > 60)    $errors['complemento'] = 'Máximo de 60 caracteres.';
        if (!in_array(strtoupper($d['uf'] ?? ''), UFS, true)) $errors['uf'] = 'Escolha o estado.';
        if ($len('responsavel') < 3 || $len('responsavel') > 100) $errors['responsavel'] = 'Informe o nome do responsável.';
        if ($len('cargo') < 2 || $len('cargo') > 60)              $errors['cargo']       = 'Informe o cargo ou função.';
        $phoneLen = strlen(preg_replace('/\D/', '', $d['telefone'] ?? ''));
        if ($phoneLen < 10 || $phoneLen > 11) $errors['telefone'] = 'Telefone incompleto.';
        if (($d['celular'] ?? '') !== '') {
            $cellLen = strlen(preg_replace('/\D/', '', $d['celular']));
            if ($cellLen < 10 || $cellLen > 11) $errors['celular'] = 'Celular incompleto.';
        }
        if (($d['email_contato'] ?? '') !== '' && !filter_var($d['email_contato'], FILTER_VALIDATE_EMAIL)) $errors['email_contato'] = 'E-mail inválido.';

        $companyPrefix = isset($current['id']) ? 'company_' . (int) $current['id'] . '_logo' : 'logo';
        [$logo, $logoError] = Upload::save($files['logo'] ?? null, ['image/jpeg', 'image/png'], ROOT_PATH . '/public/uploads/logos', false, 2, $companyPrefix);
        if ($logoError) $errors['logo'] = $logoError; elseif ($logo) $d['logo'] = 'public/uploads/logos/' . $logo;

        $bannerPrefix = isset($current['id']) ? 'company_' . (int) $current['id'] . '_banner' : 'banner';
        [$banner, $bannerError] = Upload::save($files['banner'] ?? null, ['image/jpeg', 'image/png'], ROOT_PATH . '/public/uploads/capas', false, UPLOAD_MAX_MB, $bannerPrefix);
        if ($bannerError) $errors['banner'] = $bannerError; elseif ($banner) $d['banner'] = 'public/uploads/capas/' . $banner;

        if ($mode === 'cadastro') {
            if (!filter_var($d['email'] ?? '', FILTER_VALIDATE_EMAIL) || mb_strlen($d['email']) > 120) $errors['email'] = 'E-mail inválido.';
            elseif (Company::porEmail($d['email'])) $errors['email'] = 'Este e-mail já tem cadastro. Entre na área da empresa.';
            $pwd = $_POST['senha'] ?? '';
            if (!self::passwordStrong($pwd)) $errors['senha'] = 'A senha precisa ter 8 ou mais caracteres, com letras e números.';
            elseif ($pwd !== ($_POST['senha2'] ?? '')) $errors['senha2'] = 'As senhas não conferem.';
            $d['senha'] = $pwd;
            [$termFile, $termError] = Upload::save($files['termo'] ?? null, ['application/pdf', 'image/jpeg', 'image/png'], ROOT_PATH . '/storage/termos', true);
            if ($termError) $errors['termo'] = $termError; else $d['termo_arquivo'] = $termFile;
            if (empty($d['aceite_termos']))      $errors['aceite_termos']      = 'É preciso aceitar os termos de uso.';
            if (empty($d['aceite_privacidade'])) $errors['aceite_privacidade'] = 'É preciso confirmar a política de privacidade.';
        }

        if ($errors) {
            if (!empty($d['logo'])   && (!$current || $d['logo']   !== $current['logo']))            @unlink(ROOT_PATH . '/' . $d['logo']);
            if (!empty($d['banner']) && (!$current || $d['banner'] !== ($current['banner'] ?? null))) @unlink(ROOT_PATH . '/' . $d['banner']);
            if (!empty($d['termo_arquivo'])) @unlink(ROOT_PATH . '/storage/termos/' . $d['termo_arquivo']);
        }

        return [$d, $errors];
    }

    /** @deprecated Use validate() */
    public static function validar(array $d, array $files, string $mode, ?array $current = null): array { return self::validate($d, $files, $mode, $current); }
}
