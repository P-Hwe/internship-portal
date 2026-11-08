<?php
/** Credits — autoria do ESTAGIA+ e arquivos oficiais que substituem os provisórios quando forem colocados no projeto. */
class Credits
{
    public const DEMANDANTE = ['nome' => 'Prof. Robson Ferreira Lopes', 'papel' => 'Demandante e idealizador do projeto', 'sobre' => 'Engenheiro, sysadmin e docente do IFSP Câmpus Guarulhos, onde foi coordenador de TI.'];

    public const TEAM = [
        ['slug' => 'arthur', 'nome' => 'Arthur de Oliveira Mendes Sacramento', 'curto' => 'Arthur Sacramento', 'area' => 'Produto & automação', 'papel' => 'Desenvolvedor Full-Stack & IoT'],
        ['slug' => 'roberto', 'nome' => 'José Roberto Junior Alves Damasceno', 'curto' => 'José Roberto', 'area' => 'Experiências interativas', 'papel' => 'Desenvolvedor Web & Games'],
        ['slug' => 'pedro-miguel', 'nome' => 'Pedro Miguel Dias Oliveira', 'curto' => 'Pedro Miguel', 'area' => 'Infraestrutura & proteção', 'papel' => 'Especialista Cloud & Segurança'],
        ['slug' => 'pedro-henri', 'nome' => 'Pedro Henri Gois da Silva', 'curto' => 'Pedro Henri', 'area' => 'Dados & estrutura', 'papel' => 'Desenvolvedor Web & Análise'],
        ['slug' => 'rodrigo', 'nome' => 'Rodrigo Querino do Amaral', 'curto' => 'Rodrigo Querino', 'area' => 'Crescimento & experiência', 'papel' => 'Developer & Growth Marketing'],
    ];

    public const VALUES = ['Acessibilidade', 'Inovação', 'Ética', 'Responsabilidade Social', 'Colaboração', 'Credibilidade', 'Desenvolvimento Contínuo'];

    private static function firstFile(string $dir, string $name, array $exts): ?string
    {
        foreach ($exts as $ext) {
            $rel = "public/$dir/$name.$ext";
            if (is_file(ROOT_PATH . '/' . $rel)) return BASE_URL . $rel . '?v=' . filemtime(ROOT_PATH . '/' . $rel);
        }
        return null;
    }

    public static function photo(string $slug): ?string { return self::firstFile('img/equipe', $slug, ['webp', 'png', 'jpg']); }
    public static function officialLogo(): ?string      { return self::firstFile('img/marca', 'estagiamais-logo', ['svg', 'png', 'webp']); }
    public static function officialTerm(): ?string      { return self::firstFile('docs', 'termo-de-cooperacao', ['pdf']); }
}
