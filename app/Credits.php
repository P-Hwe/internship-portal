<?php
/** Credits — autoria do ESTAGIA+ e arquivos oficiais que substituem os provisórios quando forem colocados no projeto. */
class Credits
{
    public const DEMANDANTE = ['nome' => 'Prof. Robson Ferreira Lopes', 'papel' => 'Demandante e idealizador do projeto', 'sobre' => 'Engenheiro, sysadmin e docente do IFSP Câmpus Guarulhos, onde foi coordenador de TI.'];

    /** Projeto de extensão do qual o ESTAGIA+ fez parte (cadastro no SUAP do IFSP). */
    public const EXTENSION = [
        'nome'  => 'Projeto de Captação de Demandas oriundas da comunidade externa para ações de consultoria, capacitação e desenvolvimento de sistemas para internet pelos estudantes do curso de Tecnologia em Análise e Desenvolvimento de Sistemas',
        'curso' => 'Tecnologia em Análise e Desenvolvimento de Sistemas',
        'fatos' => [
            ['Modalidade', 'Projeto de Curricularização da Extensão'],
            ['Edital', 'nº 25/2025 · PRX: Programa de Curricularização da Extensão'],
            ['Execução', '13/10/2025 a 11/12/2026'],
            ['Coordenação', 'Prof. Robson Ferreira Lopes'],
            ['Área', 'Ciência da Computação · Tecnologia e Produção'],
            ['Câmpus', 'IFSP Guarulhos'],
        ],
        'resumo' => 'O projeto organiza estudantes de Análise e Desenvolvimento de Sistemas em startups acadêmicas que atendem demandas da comunidade externa, como pequenos comércios, associações, profissionais liberais e iniciativas de economia solidária que não têm condições de contratar consultoria em tecnologia. Durante 15 meses, as equipes atuam como consultorias acadêmicas, usando métodos ágeis (SCRUM e XP) e práticas DevOps para entregar soluções digitais sob medida, com entregas em sprints e uma apresentação pública dos resultados no câmpus.',
        'objetivo' => 'Ampliar a presença digital de quem atua na comunidade, oferecendo consultoria, capacitação e desenvolvimento de sistemas com entregas incrementais, e dar aos estudantes a vivência prática de engenharia de software em problemas reais.',
        'objetivos' => [
            'Formar startups acadêmicas de 3 a 6 estudantes, com papéis de Product Owner, Scrum Master e time de desenvolvimento.',
            'Mapear demandas reais da comunidade e levantar requisitos com cada parceiro.',
            'Desenvolver as soluções em sprints, validando cada entrega com o parceiro.',
            'Capacitar os parceiros para usar o que foi desenvolvido.',
            'Documentar e divulgar os resultados em relatório final e cerimônia de encerramento.',
        ],
        'fases' => [
            ['Formação das startups', 'Início', 'Equipes, papéis e uma página de apresentação de cada startup.'],
            ['Captação dos parceiros', 'Até o 3º mês', 'Escolha das organizações atendidas e formalização da parceria.'],
            ['Diagnóstico e planejamento', '3º ao 5º mês', 'Entrevistas, requisitos, épicos, histórias de usuário e Product Backlog.'],
            ['Desenvolvimento incremental', '5º ao 14º mês', 'Sprints de 2 a 4 semanas, testes, integração contínua e oficinas com o parceiro.'],
            ['Encerramento', '15º mês', 'Sistema publicado, apresentação pública, lições aprendidas e relatório final.'],
        ],
        'ods' => [
            ['4', 'Educação de qualidade'],
            ['8', 'Trabalho decente e crescimento econômico'],
            ['9', 'Indústria, inovação e infraestrutura'],
            ['10', 'Redução das desigualdades'],
        ],
    ];

    /** Página de estágios do site oficial do câmpus, para onde o botão Voltar leva. */
    public const CAMPUS_INTERNSHIPS_URL = 'https://gru.ifsp.edu.br/estagios.html';

    public const TEAM = [
        ['slug' => 'arthur', 'nome' => 'Arthur de Oliveira Mendes Sacramento', 'curto' => 'Arthur Sacramento', 'area' => 'Produto & automação', 'papel' => 'Desenvolvedor Full-Stack & IoT'],
        ['slug' => 'roberto', 'nome' => 'José Roberto Junior Alves Damasceno', 'curto' => 'José Roberto', 'area' => 'Experiências interativas', 'papel' => 'Desenvolvedor Web & Games'],
        ['slug' => 'pedro-miguel', 'nome' => 'Pedro Miguel Dias Oliveira', 'curto' => 'Pedro Miguel', 'area' => 'Infraestrutura & proteção', 'papel' => 'Especialista Cloud & Segurança'],
        ['slug' => 'pedro-henri', 'nome' => 'Pedro Henri Gois da Silva', 'curto' => 'Pedro Henri', 'area' => 'Dados & estrutura', 'papel' => 'Desenvolvedor Web & Análise'],
        ['slug' => 'rodrigo', 'nome' => 'Rodrigo Querino do Amaral', 'curto' => 'Rodrigo Querino', 'area' => 'Crescimento & experiência', 'papel' => 'Developer & Growth Marketing'],
    ];

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
