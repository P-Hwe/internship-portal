<?php

class EmpresaForm
{
    public static function senhaForte(string $s): bool
    {
        return strlen($s) >= 8 && preg_match('/[A-Za-z]/', $s) && preg_match('/\d/', $s);
    }

    public static function validar(array $d, array $files, string $modo, ?array $atual = null): array
    {
        $e = [];
        $d['site'] = Link::normalizar($d['site'] ?? '');
        $d['linkedin'] = Link::normalizar($d['linkedin'] ?? '');
        $len = fn($k) => mb_strlen($d[$k] ?? '');
        if ($modo === 'cadastro') {
            if (!Cnpj::valido($d['cnpj'] ?? '')) {
                $e['cnpj'] = 'CNPJ inválido. Confira os 14 caracteres (letras e números).';
            } elseif (Empresa::porCnpj($d['cnpj'])) {
                $e['cnpj'] = 'Este CNPJ já está cadastrado. Entre na área da empresa.';
            }
        }
        if ($len('razao_social') < 3 || $len('razao_social') > 150) {
            $e['razao_social'] = 'Informe a razão social.';
        }
        if ($len('nome') < 2 || $len('nome') > 120) {
            $e['nome'] = 'Informe o nome fantasia.';
        }
        if ($len('descricao') < 30 || $len('descricao') > 600) {
            $e['descricao'] = 'Descreva a empresa em 30 a 600 caracteres: é o que o estudante vê.';
        }
        if (!in_array($d['segmento'] ?? '', SEGMENTOS, true)) {
            $e['segmento'] = 'Escolha o segmento.';
        }
        if (!in_array($d['porte'] ?? '', PORTES, true)) {
            $e['porte'] = 'Escolha o porte.';
        }
        if ($d['site'] && !Link::valido($d['site'])) {
            $e['site'] = 'Endereço inválido. Exemplo: https://www.suaempresa.com.br';
        }
        if ($d['linkedin'] && !Link::valido($d['linkedin'], 'linkedin')) {
            $e['linkedin'] = 'Use o endereço do LinkedIn da empresa (linkedin.com/company/...).';
        }
        $d['link_principal'] = ($d['link_principal'] ?? 'site') === 'linkedin' ? 'linkedin' : 'site';
        if ($d['link_principal'] === 'linkedin' && !$d['linkedin']) {
            $d['link_principal'] = 'site';
        }
        if (strlen(preg_replace('/\D/', '', $d['cep'] ?? '')) !== 8) {
            $e['cep'] = 'CEP inválido. Informe os 8 números.';
        }
        foreach (['logradouro' => [3, 120], 'bairro' => [2, 60], 'cidade' => [2, 60]] as $c => [$min, $max]) {
            if ($len($c) < $min || $len($c) > $max) {
                $e[$c] = 'Preencha este campo corretamente.';
            }
        }
        $n = $len('numero');
        if ($n < 1 || $n > 10) {
            $e['numero'] = 'Informe o número (ou S/N).';
        }
        if ($len('complemento') > 60) {
            $e['complemento'] = 'Máximo de 60 caracteres.';
        }
        if (!in_array(strtoupper($d['uf'] ?? ''), UFS, true)) {
            $e['uf'] = 'Escolha o estado.';
        }
        if ($len('responsavel') < 3 || $len('responsavel') > 100) {
            $e['responsavel'] = 'Informe o nome do responsável.';
        }
        if ($len('cargo') < 2 || $len('cargo') > 60) {
            $e['cargo'] = 'Informe o cargo ou função.';
        }
        $f = strlen(preg_replace('/\D/', '', $d['telefone'] ?? ''));
        if ($f < 10 || $f > 11) {
            $e['telefone'] = 'Telefone incompleto.';
        }
        if (($d['celular'] ?? '') !== '') {
            $c = strlen(preg_replace('/\D/', '', $d['celular']));
            if ($c < 10 || $c > 11) {
                $e['celular'] = 'Celular incompleto.';
            }
        }
        if (($d['email_contato'] ?? '') !== '' && !filter_var($d['email_contato'], FILTER_VALIDATE_EMAIL)) {
            $e['email_contato'] = 'E-mail inválido.';
        }

        [$logo, $eLogo] = Upload::salvar($files['logo'] ?? null, ['image/jpeg', 'image/png'], ROOT_PATH . '/public/uploads/logos', false, 2);
        if ($eLogo) {
            $e['logo'] = $eLogo;
        } elseif ($logo) {
            $d['logo'] = 'public/uploads/logos/' . $logo;
        }

        if ($modo === 'cadastro') {
            if (!filter_var($d['email'] ?? '', FILTER_VALIDATE_EMAIL) || mb_strlen($d['email']) > 120) {
                $e['email'] = 'E-mail inválido.';
            } elseif (Empresa::porEmail($d['email'])) {
                $e['email'] = 'Este e-mail já tem cadastro. Entre na área da empresa.';
            }
            $s = $_POST['senha'] ?? '';
            if (!self::senhaForte($s)) {
                $e['senha'] = 'A senha precisa ter 8 ou mais caracteres, com letras e números.';
            } elseif ($s !== ($_POST['senha2'] ?? '')) {
                $e['senha2'] = 'As senhas não conferem.';
            }
            $d['senha'] = $s;
            [$arq, $eArq] = Upload::salvar($files['termo'] ?? null, ['application/pdf', 'image/jpeg', 'image/png'], ROOT_PATH . '/storage/termos', true);
            if ($eArq) {
                $e['termo'] = $eArq;
            } else {
                $d['termo_arquivo'] = $arq;
            }
            if (empty($d['aceite_termos'])) {
                $e['aceite_termos'] = 'É preciso aceitar os termos de uso.';
            }
            if (empty($d['aceite_privacidade'])) {
                $e['aceite_privacidade'] = 'É preciso confirmar a política de privacidade.';
            }
        }
        if ($e) {
            if (!empty($d['logo']) && (!$atual || $d['logo'] !== $atual['logo'])) {
                @unlink(ROOT_PATH . '/' . $d['logo']);
            }
            if (!empty($d['termo_arquivo'])) {
                @unlink(ROOT_PATH . '/storage/termos/' . $d['termo_arquivo']);
            }
        }
        return [$d, $e];
    }
}
