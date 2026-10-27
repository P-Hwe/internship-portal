<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" class="no-js" lang="pt-br" dir="ltr">


<head>
        <base href="<?= BASE_URL ?>" />
        <link rel="stylesheet"
                href="<?= BASE_URL ?>public/css/ifsp.css?v=<?= @filemtime(ROOT_PATH . '/public/css/ifsp.css') ?>"
                type="text/css" />
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <title><?= htmlspecialchars($titulo ?? "Vagas de Estágio") ?> - IFSP Guarulhos</title>
        <link href="<?= BASE_URL ?>images/if.png" rel="shortcut icon" type="image/x-icon" />
        <script type="application/json" class="joomla-script-options new">
                {
                        "csrf.token": "b6b5ba187a333f7b3462578be868db42",
                        "system.paths": {
                                "root": "",
                                "base": ""
                        },
                        "rl_tabs": {
                                "use_hash": 1,
                                "reload_iframes": 0,
                                "init_timeout": 0,
                                "urlscroll": 0
                        }
                }
        </script>
        <script src="https://gru.ifsp.edu.br/media/jui/js/jquery.min.js?906785bf1ac1a55068e89dc581424ac8"
                type="text/javascript"></script>
        <script src="https://gru.ifsp.edu.br/media/jui/js/jquery-noconflict.js?906785bf1ac1a55068e89dc581424ac8"
                type="text/javascript"></script>
        <script src="https://gru.ifsp.edu.br/media/jui/js/jquery-migrate.min.js?906785bf1ac1a55068e89dc581424ac8"
                type="text/javascript"></script>
        <script src="https://gru.ifsp.edu.br/components/com_k2/js/k2.js?v2.6.7&amp;sitepath=/" type="text/javascript">
        </script>
        <script src="https://gru.ifsp.edu.br/media/jui/js/bootstrap.min.js?906785bf1ac1a55068e89dc581424ac8"
                type="text/javascript"></script>
        <script src="https://gru.ifsp.edu.br/media/tabs/js/script.min.js?v=8.4.0" type="text/javascript"></script>
        <script src="https://gru.ifsp.edu.br/media/system/js/mootools-more.js?906785bf1ac1a55068e89dc581424ac8"
                type="text/javascript"></script>
        <script src="https://gru.ifsp.edu.br/media/system/js/core.js" type="text/javascript"></script>
        <script type="application/ld+json">
                {
                        "@context": "http:\/\/schema.org",
                        "@type": "Organization",
                        "name": "Instituto Federal de Educa\u00e7\u00e3o, Ci\u00eancia e Tecnologia de S\u00e3o Paulo - C\u00e2mpus Guarulhos",
                        "url": "http:\/\/ifspguarulhos.edu.br",
                        "logo": "https:\/\/gru.ifsp.edu.br\/images\/logos\/Guarulhos-02.jpg",
                        "telephone": "+551123044250",
                        "sameAs": ["https:\/\/www.facebook.com\/ifspguarulhos", "https:\/\/twitter.com\/ifspguarulhos",
                                "https:\/\/www.youtube.com\/channel\/UCj8XSt0SxLObeBBUaPTxjwQ"
                        ]
                }
        </script>

        <meta content="width=device-width, initial-scale=1.0" name="viewport">

        <script type="text/javascript">
                var _paq = window._paq || [];
                _paq.push(['trackPageView']);
                _paq.push(['enableLinkTracking']);
                (function() {
                        var u = "https://dadosportais.ifsp.edu.br/";
                        _paq.push(['setTrackerUrl', u + 'matomo.php']);
                        _paq.push(['setSiteId', '53']);
                        var d = document,
                                g = d.createElement('script'),
                                s = d.getElementsByTagName('script')[0];
                        g.type = 'text/javascript';
                        g.async = true;
                        g.defer = true;
                        g.src = u + 'matomo.js';
                        s.parentNode.insertBefore(g, s);
                })();
        </script>
        <noscript>
                <p><img src="https://dadosportais.ifsp.edu.br/matomo.php?idsite=53&amp;rec=1" style="border:0;" alt="" /></p>
        </noscript>
        <link rel="stylesheet" href="<?= BASE_URL ?>public/css/vagas.css" type="text/css" />
</head>

<body>
        <a class="hide" id="topo" href="#accessibility">Ir direto para menu de acessibilidade.</a>
        <noscript>
                <div class="error minor-font">
                        Seu navegador de internet está sem suporte à JavaScript. Por esse motivo algumas funcionalidades do site
                        podem não estar acessíveis.
                </div>
        </noscript>
        <div id="barra-brasil">
                <div id="wrapper-barra-brasil">
                        <div class=brasil-flag><a href=https://gov.br/ class=link-barra>Brasil</a></div>
                        <nav>
                                <ul id=lista-barra-brasil class=list>
                                        <li><a href=# id=menu-icon></a></li>
                                        <li class=list-item><a
                                                        href=http://www.gov.br/economia/pt-br/canais_atendimento/ouvidoria/simplifique
                                                        class=link-barra>Simplifique!</a></li>
                                        <li class=list-item><a href=https://www.gov.br/secom/pt-br/acesso-a-informacao/comunicabr/
                                                        class=link-barra>Comunica BR</a></li>
                                        <li class=list-item><a href=https://www.gov.br/pt-br/participacao-social/
                                                        class=link-barra>Participe</a></li>
                                        <li class=list-item><a href=http://www.gov.br/acessoainformacao/ class=link-barra>Acesso à
                                                        informação</a></li>
                                        <li class=list-item><a href=http://www.planalto.gov.br/legislacao class=link-barra>Legislação</a>
                                        </li>
                                        <li class="list-item last last-item"><a href=https://gov.br/pt-br/canais-do-executivo-federal
                                                        class=link-barra>Canais</a></li>
                                </ul>
                        </nav> <span id=brasil-vlibras><a class=logo-vlibras id=logovlibras href=#></a><span class=link-vlibras><img
                                                src="https://barra.brasil.gov.br/imagens/vlibras.gif" class=barralazy width=132
                                                height=116>&nbsp;<br>O conteúdo desse portal pode ser acessível em Libras usando o <a
                                                href=http://www.vlibras.gov.br />VLibras</a></span></span>
                </div>
        </div>
        <div class="layout">
                <header>
                        <div class="container">
                                <div class="row-fluid accessibility-language-actions-container">
                                        <div class="span6 accessibility-container">
                                                <ul id="accessibility">
                                                        <li>
                                                                <a accesskey="1" href="#content" id="link-conteudo">
                                                                        Ir para o conte&uacute;do
                                                                        <span>1</span>
                                                                </a>
                                                        </li>
                                                        <li>
                                                                <a accesskey="2" href="#navigation" id="link-navegacao">
                                                                        Ir para o menu
                                                                        <span>2</span>
                                                                </a>
                                                        </li>
                                                        <li>
                                                                <a accesskey="3" href="#portal-searchbox" id="link-buscar">
                                                                        Ir para a busca
                                                                        <span>3</span>
                                                                </a>
                                                        </li>
                                                        <li>
                                                                <a accesskey="4" href="#footer" id="link-rodape">
                                                                        Ir para o rodap&eacute;
                                                                        <span>4</span>
                                                                </a>
                                                        </li>
                                                </ul>
                                        </div>
                                        <div class="span6 language-and-actions-container">
                                                <h2 class="hide">Opções de acessibilidade</h2>

                                                <ul class="pull-right" id="portal-siteactions">
                                                        <li class="item-140"><a href="https://gru.ifsp.edu.br/acessibilidade.html"
                                                                        accesskey="5">Acessibilidade</a></li>
                                                        <li class="item-141 toggle-contraste"><a class="toggle-contraste" href="#"
                                                                        accesskey="6">Alto contraste</a></li>
                                                        <li class="item-142"><a href="https://gru.ifsp.edu.br/mapa-do-site.html" accesskey="7">Mapa
                                                                        do site</a></li>
                                                </ul>

                                        </div>
                                </div>
                                <div class="row-fluid">
                                        <div id="logo" class="span8 small">
                                                <a href="https://gru.ifsp.edu.br/" title="Instituto Federal de São Paulo">
                                                        <span class="portal-title-1">Campus Guarulhos</span>
                                                        <h1 class="portal-title corto">Instituto Federal de São Paulo</h1>
                                                        <span class="portal-description">Ministério da Educação</span>
                                                </a>
                                        </div>
                                        <div class="span4">
                                                <div id="portal-searchbox" class="row">
                                                        <h2 class="hidden">Buscar no portal</h2>
                                                        <form action="https://gru.ifsp.edu.br/component/content/" method="post" class="pull-right">
                                                                <fieldset>
                                                                        <legend class="hide">Busca</legend>
                                                                        <h2 class="hidden">Buscar no portal</h2>
                                                                        <div class="input-append">
                                                                                <label for="portal-searchbox-field" class="hide">Busca: </label>
                                                                                <input type="text" id="portal-searchbox-field" class="searchField"
                                                                                        placeholder="Buscar no portal" title="Buscar no portal" name="searchword">
                                                                                <button type="submit" class="btn searchButton"><span
                                                                                                class="hide">Buscar</span><i class="icon-search"></i></button>
                                                                        </div>
                                                                        <input type="hidden" name="task" value="search" />
                                                                        <input type="hidden" name="option" value="com_search" />
                                                                        <input type="hidden" name="Itemid" value="" />
                                                                </fieldset>
                                                        </form>
                                                </div>
                                                <div id="social-icons" class="row">
                                                        <h2 class="hidden">Redes Sociais</h2>

                                                        <ul class="pull-right">
                                                                <li class="portalredes-item item-403"><a href="http://telegram.me/ifspguarulhos_bot"
                                                                                target="_blank" rel="noopener noreferrer"><i
                                                                                        class="icon-telegram-sign"><span>Telegram</span></i></a></li>
                                                                <li class="portalredes-item item-138"><a href="https://www.facebook.com/ifspguarulhos"
                                                                                target="_blank" rel="noopener noreferrer"><i
                                                                                        class="icon-facebook-sign"><span>Facebook</span></i></a></li>
                                                                <li class="portalredes-item item-257"><a href="https://www.instagram.com/ifspgru/"><i
                                                                                        class="icon-instagram"><span>Instagram</span></i></a></li>
                                                                <li class="portalredes-item item-136"><a href="https://twitter.com/ifspguarulhos"
                                                                                target="_blank" rel="noopener noreferrer"><i
                                                                                        class="icon-twitter-sign"><span>Twitter</span></i></a></li>
                                                                <li class="portalredes-item item-137"><a
                                                                                href="https://www.youtube.com/channel/UCj8XSt0SxLObeBBUaPTxjwQ" target="_blank"
                                                                                rel="noopener noreferrer"><i class="icon-youtube"><span>YouTube</span></i></a>
                                                                </li>
                                                                <li class="portalredes-item item-226"><a
                                                                                href="https://gru.ifsp.edu.br/ultimas-noticias.feed?type=rss" target="_blank"
                                                                                rel="noopener noreferrer"><i class="icon-rss-sign"><span>RSS</span></i></a></li>
                                                        </ul>
                                                </div>

                                        </div>
                                </div>
                        </div>
                        <div class="sobre">
                                <div class="container">
                                        <nav class="menu-servicos pull-right">
                                                <h2 class="hide">Serviços</h2>

                                                <ul>
                                                        <li class="item-875"><a href="http://www.ifsp.edu.br/acessoainformacao/" target="_blank"
                                                                        rel="noopener noreferrer">Acesso à informação</a></li>
                                                        <li class="item-132"><a href="https://gru.ifsp.edu.br/contato.html">Contato</a></li>
                                                        <li class="item-943"><a
                                                                        href="https://gru.ifsp.edu.br/localiza%C3%83%C2%A7%C3%83%C2%A3o.html">Localização</a>
                                                        </li>
                                                        <li class="item-876"><a href="https://www.ifsp.edu.br/ouvidoria" target="_blank"
                                                                        rel="noopener noreferrer">Ouvidoria</a></li>
                                                        <li class="item-133"><a href="https://gru.ifsp.edu.br/servicos-e-sistemas.html">Serviços e
                                                                        sistemas</a></li>
                                                </ul>
                                                <span class="hide">Fim do menu de serviços</span>
                                        </nav>

                                </div>
                        </div>
                </header>
                <main>
                        <div class="container">
                                <div class="row-fluid">
                                        <section>

                                                <div class="rastro-navegacao row-flutuante">
                                                        <a href="https://gru.ifsp.edu.br/" class="pathway">Página inicial</a>
                                                </div>
                                        </section>
                                </div>

                                <div class="row-fluid">
                                        <div id="navigation" class="span3">
                                                <a href="#" class="visible-phone visible-tablet mainmenu-toggle btn"><i
                                                                class="icon-list"></i>&nbsp;Menu</a>
                                                <section id="navigation-section">
                                                        <span class="hide">Início do menu principal</span>
                                                        <nav class="assuntos span9">
                                                                <h2>O Campus <i class="icon-chevron-up visible-phone visible-tablet pull-right"></i>
                                                                </h2>

                                                                <ul>
                                                                        <li class="item-365 parent"><a
                                                                                        href="https://gru.ifsp.edu.br/instituicao.html">Instituição</a></li>
                                                                        <li class="item-913 parent"><a
                                                                                        href="https://gru.ifsp.edu.br/documentos_institucionais.html">Documentos
                                                                                        Institucionais</a></li>
                                                                        <li class="item-333"><a href="https://gru.ifsp.edu.br/setores.html">Setores e
                                                                                        Coordenadores</a></li>
                                                                        <li class="item-884"><a
                                                                                        href="https://gru.ifsp.edu.br/hor%C3%83%C2%A1rio-de-atendimento.html">
                                                                                        Horário de Atendimento</a></li>
                                                                        <li class="item-357 parent"><a
                                                                                        href="https://gru.ifsp.edu.br/conselho-de-campus.html">CONCAM</a></li>
                                                                        <li class="item-534"><a
                                                                                        href="https://www.ifsp.edu.br/component/content/article/9-assuntos/reitoria/310"
                                                                                        target="_blank">NEABI</a></li>
                                                                        <li class="item-912"><a href="https://www.ifsp.edu.br/nugs">NUGS</a></li>
                                                                        <li class="item-881"><a href="https://ifsp.edu.br/estudeaqui">Estude aqui!</a></li>
                                                                        <li class="item-882"><a href="https://ifsp.edu.br/trabalhe-conosco">Trabalhe
                                                                                        conosco</a></li>
                                                                        <li class="item-361"><a href="https://gru.ifsp.edu.br/pdi.html">Plano de
                                                                                        Desenvolvimento Institucional (PDI)</a></li>
                                                                </ul>
                                                        </nav>
                                                        <nav class="sobre span9">
                                                                <h2>Cursos <i class="icon-chevron-up visible-phone visible-tablet pull-right"></i></h2>

                                                                <ul>
                                                                        <li class="item-374 parent"><a
                                                                                        href="https://gru.ifsp.edu.br/tecnicos.html">Técnicos</a></li>
                                                                        <li class="item-667 parent"><a
                                                                                        href="https://gru.ifsp.edu.br/ensino-medio-integrado-ao-tecnico.html">Técnico
                                                                                        Integrado ao Ensino Médio</a></li>
                                                                        <li class="item-375 parent"><a
                                                                                        href="https://gru.ifsp.edu.br/superiores.html">Superiores</a></li>
                                                                        <li class="item-376 parent"><a
                                                                                        href="https://gru.ifsp.edu.br/pos-graduacao.html">Pós-graduação</a></li>
                                                                </ul>
                                                        </nav>
                                                        <nav class="sobre span9">
                                                                <h2>Ensino <i class="icon-chevron-up visible-phone visible-tablet pull-right"></i></h2>

                                                                <ul>
                                                                        <li class="item-893"><a
                                                                                        href="https://gru.ifsp.edu.br/cae-coordenadoria-de-apoio-ao-ensino.html">Coordenadoria
                                                                                        de Apoio ao Ensino</a></li>
                                                                        <li class="item-887"><a
                                                                                        href="https://itq.ifsp.edu.br/index.php/agenda-veiculos">Agendamento de
                                                                                        veículos coletivos</a></li>
                                                                        <li class="item-359"><a href="https://gru.ifsp.edu.br/apoio-ao-aluno.html">Apoio ao
                                                                                        Aluno</a></li>
                                                                        <li class="item-355"><a
                                                                                        href="https://gru.ifsp.edu.br/biblioteca.html">Biblioteca</a></li>
                                                                        <li class="item-794"><a
                                                                                        href="https://gru.ifsp.edu.br/docentes-hor%C3%83%C2%A1rios.html">Corpo
                                                                                        Docente</a></li>
                                                                        <li class="item-879"><a href="https://www.gov.br/mj/pt-br/escolasegura">Escola
                                                                                        Segura</a></li>
                                                                        <li class="item-957"><a
                                                                                        href="https://gru.ifsp.edu.br/est%C3%83%C2%A1gio-docente-obrigat%C3%83%C2%B3rio-no-campus.html">Estágio
                                                                                        Docente Obrigatório no Campus</a></li>
                                                                        <li class="item-369"><a
                                                                                        href="https://enquetes.ifsp.edu.br/index.php/254111/lang-pt-BR"
                                                                                        target="_blank">Ex-alunos</a></li>
                                                                        <li class="item-836"><a
                                                                                        href="https://gru.ifsp.edu.br/gr%C3%83%C2%AAmio-estudantil.html">Grêmio
                                                                                        Estudantil </a></li>
                                                                        <li class="item-960"><a
                                                                                        href="https://www.ifsp.edu.br/images/reitoria/AssessoriaComunicacao/GuiadoEstudante/GuiaEstudante.pdf">Guia
                                                                                        do Estudante</a></li>
                                                                        <li class="item-484"><a href="https://gru.ifsp.edu.br/ppp.html">Projeto
                                                                                        Político-Pedagógico (PPP)</a></li>
                                                                </ul>
                                                        </nav>
                                                        <nav class="assuntos span9">
                                                                <h2>Pesquisa <i class="icon-chevron-up visible-phone visible-tablet pull-right"></i>
                                                                </h2>

                                                                <ul>
                                                                        <li class="item-785"><a
                                                                                        href="https://gru.ifsp.edu.br/coordenadoria-de-pesquisa,-inova%C3%83%C2%A7%C3%83%C2%A3o-e-p%C3%83%C2%B3s-gradua%C3%83%C2%A7%C3%83%C2%A3o.html">Coordenadoria
                                                                                        de Pesquisa, Inovação e Pós-Graduação</a></li>
                                                                        <li class="item-811"><a
                                                                                        href="https://gru.ifsp.edu.br/acesso-%C3%83%C2%A0-bases-cient%C3%83%C2%ADficas.html">Acesso
                                                                                        às bases científicas</a></li>
                                                                        <li class="item-789"><a
                                                                                        href="https://gru.ifsp.edu.br/comit%C3%83%C2%AA-de-inicia%C3%83%C2%A7%C3%83%C2%A3o-cient%C3%83%C2%ADfica.html">Comitê
                                                                                        de Iniciação Científica</a></li>
                                                                        <li class="item-790"><a href="https://gru.ifsp.edu.br/compesq.html">COMPESQ</a></li>
                                                                        <li class="item-872"><a href="https://gru.ifsp.edu.br/gerse.html">GERSE</a></li>
                                                                        <li class="item-791"><a
                                                                                        href="https://gru.ifsp.edu.br/grupos-de-pesquisa.html">Grupos de
                                                                                        Pesquisa</a></li>
                                                                        <li class="item-865"><a href="https://labifmakerguarulhos.my.canva.site/#page-1">Lab
                                                                                        IFMaker</a></li>
                                                                        <li class="item-886"><a href="https://integra.ifsp.edu.br/">Integra IFSP</a></li>
                                                                        <li class="item-871"><a href="https://gru.ifsp.edu.br/oficinas-4-0.html">Oficinas
                                                                                        4.0</a></li>
                                                                        <li class="item-787 parent"><a
                                                                                        href="https://gru.ifsp.edu.br/pibifsp.html">PIBIFSP</a></li>
                                                                        <li class="item-788"><a href="https://gru.ifsp.edu.br/pivict.html">PIVICT</a></li>
                                                                </ul>
                                                        </nav>
                                                        <nav class="assuntos span9">
                                                                <h2>Extensão <i class="icon-chevron-up visible-phone visible-tablet pull-right"></i>
                                                                </h2>

                                                                <ul>
                                                                        <li class="item-366"><a
                                                                                        href="https://gru.ifsp.edu.br/coordenadoria.html">Coordenadoria</a></li>
                                                                        <li class="item-847 parent"><a
                                                                                        href="https://gru.ifsp.edu.br/cursos-de-extens%C3%83%C2%A3o.html">Cursos de
                                                                                        Extensão</a></li>
                                                                        <li class="item-368 parent"><a
                                                                                        href="https://gru.ifsp.edu.br/estagios.html">Estágios</a></li>
                                                                        <li class="item-845"><a href="https://manuais.ifsp.edu.br/shelves/extensao"
                                                                                        target="_blank">Manuais</a></li>
                                                                        <li class="item-959 parent"><a
                                                                                        href="https://gru.ifsp.edu.br/coordenadoria-de-rela%C3%83%C2%A7%C3%83%C2%B5es-internacionais-guarulhos.html">Arinter
                                                                                        Campus Guarulhos</a></li>
                                                                        <li class="item-354"><a href="https://gru.ifsp.edu.br/cursos-fic.html">Editais
                                                                                        anteriores</a></li>
                                                                        <li class="item-950"><a
                                                                                        href="https://gru.ifsp.edu.br/programa-partiu-if.html">Programa Partiu
                                                                                        IF</a></li>
                                                                        <li class="item-980"><a
                                                                                        href="https://mini.gru.ifsp.edu.br/login/index.php">CEX-Minicurso</a></li>
                                                                </ul>
                                                        </nav>
                                                        <nav class="sobre span9">
                                                                <h2>Direção Geral <i
                                                                                class="icon-chevron-up visible-phone visible-tablet pull-right"></i></h2>

                                                                <ul>
                                                                        <li class="item-953 active deeper parent"><a
                                                                                        href="https://gru.ifsp.edu.br/drg.html">Direção Geral do Campus (DRG)</a>
                                                                                <ul>
                                                                                        <li class="item-854"><a
                                                                                                        href="https://gru.ifsp.edu.br/drg/agenda-do-diretor.html">Agenda do
                                                                                                        Diretor</a></li>
                                                                                </ul>
                                                                        </li>
                                                                        <li class="item-952 deeper parent"><a
                                                                                        href="https://gru.ifsp.edu.br/cdi.html">Coordenadoria de Apoio à Direção</a>
                                                                                <ul>
                                                                                        <li class="item-942"><a
                                                                                                        href="https://gru.ifsp.edu.br/cdi/emiss%C3%83%C2%A3o-de-portarias.html">Emissão
                                                                                                        de portarias</a></li>
                                                                                </ul>
                                                                        </li>
                                                                </ul>
                                                        </nav>
                                                        <nav class="sobre span9">
                                                                <h2>Administração <i
                                                                                class="icon-chevron-up visible-phone visible-tablet pull-right"></i></h2>
                                                                <!-- visible-phone visible-tablet -->

                                                                <ul>
                                                                        <li class="item-954"><a
                                                                                        href="https://gru.ifsp.edu.br/diretoria-adjunta-de-administra%C3%83%C2%A7%C3%83%C2%A3o.html">Diretoria
                                                                                        Adjunta de Administração</a></li>
                                                                        <li class="item-955"><a
                                                                                        href="https://gru.ifsp.edu.br/coordenadoria-de-manuten%C3%83%C2%A7%C3%83%C2%A3o,-almoxarifado-e-patrim%C3%83%C2%B4nio.html">Coordenadoria
                                                                                        de Almoxarifado, Manutenção e Patrimônio</a></li>
                                                                        <li class="item-866"><a
                                                                                        href="https://gru.ifsp.edu.br/coordenadoria-de-contabilidade-e-finan%C3%83%C2%A7as.html">Coordenadoria
                                                                                        de Contabilidade e Finanças</a></li>
                                                                        <li class="item-864"><a
                                                                                        href="https://gru.ifsp.edu.br/licita%C3%83%C2%A7%C3%83%C2%B5es-e-contratos.html">Coordenadoria
                                                                                        de Licitações e Contratos</a></li>
                                                                        <li class="item-951"><a href="https://manuais.ifsp.edu.br/">Manuais</a></li>
                                                                </ul>
                                                        </nav>
                                                        <nav class="assuntos span9">
                                                                <h2>CGP <i class="icon-chevron-up visible-phone visible-tablet pull-right"></i></h2>

                                                                <ul>
                                                                        <li class="item-835"><a
                                                                                        href="https://gru.ifsp.edu.br/cgp-coordenadoria-de-gest%C3%83%C2%A3o-de-pessoas.html">Coordenadoria
                                                                                        de Gestão de Pessoas</a></li>
                                                                        <li class="item-972"><a
                                                                                        href="https://docs.google.com/forms/d/e/1FAIpQLSeqyCYf4zTeIpBwNP10jKuZ2q7iziNp891WaadHlmcCxX_Fyg/viewform">Manifestação
                                                                                        de interesse em mudança de setor</a></li>
                                                                        <li class="item-828"><a
                                                                                        href="https://gru.ifsp.edu.br/processo-seletivo-professor-substituto.html">Processo
                                                                                        Seletivo - Professor Substituto</a></li>
                                                                        <li class="item-956"><a
                                                                                        href="https://gru.ifsp.edu.br/programa-de-gest%C3%83%C2%A3o-e-desempenho.html">Programa
                                                                                        de Gestão e Desempenho (PGD)</a></li>
                                                                </ul>
                                                        </nav>
                                                        <nav class="assuntos span9">
                                                                <h2>CPA <i class="icon-chevron-up visible-phone visible-tablet pull-right"></i></h2>

                                                                <ul>
                                                                        <li class="item-528"><a
                                                                                        href="https://gru.ifsp.edu.br/comissao-propria-de-avaliacao.html">Comissão
                                                                                        Própria de Avaliação</a></li>
                                                                        <li class="item-529"><a href="https://gru.ifsp.edu.br/atas-e-memorandos.html">Atas e
                                                                                        Memorandos</a></li>
                                                                        <li class="item-530"><a
                                                                                        href="https://gru.ifsp.edu.br/portarias-de-nomeacao.html">Portarias de
                                                                                        Nomeação</a></li>
                                                                        <li class="item-531"><a
                                                                                        href="https://gru.ifsp.edu.br/relatorios-de-avaliacao.html">Relatórios de
                                                                                        Avaliação</a></li>
                                                                        <li class="item-532"><a
                                                                                        href="https://gru.ifsp.edu.br/relatorios-de-acoes.html">Relatórios de
                                                                                        Ações</a></li>
                                                                        <li class="item-965"><a href="https://gru.ifsp.edu.br/hallowcpa.html">HallowCPA</a>
                                                                        </li>
                                                                </ul>
                                                        </nav>
                                                        <nav class="assuntos span9">
                                                                <h2>CSP <i class="icon-chevron-up visible-phone visible-tablet pull-right"></i></h2>

                                                                <ul>
                                                                        <li class="item-522"><a
                                                                                        href="https://gru.ifsp.edu.br/coordenadoria-sociopedagogica.html">Coordenadoria
                                                                                        Sociopedagógica</a></li>
                                                                        <li class="item-523"><a
                                                                                        href="https://gru.ifsp.edu.br/assistencia-estudantil.html">Assistência
                                                                                        Estudantil</a></li>
                                                                        <li class="item-524"><a href="https://gru.ifsp.edu.br/bolsa-de-ensino.html">Bolsa de
                                                                                        Ensino</a></li>
                                                                        <li class="item-525"><a href="https://gru.ifsp.edu.br/combate-a-evasao.html">Combate
                                                                                        à Evasão</a></li>
                                                                        <li class="item-704"><a
                                                                                        href="http://cspcursos.ifspguarulhos.edu.br/?pag=programacao"
                                                                                        onclick="window.open(this.href,'targetWindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,');return false;">Cursos</a>
                                                                        </li>
                                                                        <li class="item-705"><a href="https://gru.ifsp.edu.br/eventoscsp.html"
                                                                                        onclick="window.open(this.href,'targetWindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes');return false;">Eventos</a>
                                                                        </li>
                                                                        <li class="item-526"><a
                                                                                        href="https://gru.ifsp.edu.br/formacao-continuada.html">Formação
                                                                                        Continuada</a></li>
                                                                </ul>
                                                        </nav>
                                                        <nav class="assuntos span9">
                                                                <h2>NAPNE <i class="icon-chevron-up visible-phone visible-tablet pull-right"></i></h2>

                                                                <ul>
                                                                        <li class="item-855"><a
                                                                                        href="https://gru.ifsp.edu.br/apresenta%C3%83%C2%A7%C3%83%C2%A3o.html">Apresentação</a>
                                                                        </li>
                                                                        <li class="item-859"><a
                                                                                        href="https://gru.ifsp.edu.br/calend%C3%83%C2%A1rio.html">Calendário</a>
                                                                        </li>
                                                                        <li class="item-856"><a href="https://gru.ifsp.edu.br/equipe.html">Equipe</a></li>
                                                                        <li class="item-861"><a
                                                                                        href="https://gru.ifsp.edu.br/forma%C3%83%C2%A7%C3%83%C2%A3o.html">Formação</a>
                                                                        </li>
                                                                        <li class="item-858"><a
                                                                                        href="https://gru.ifsp.edu.br/legisla%C3%83%C2%A7%C3%83%C2%A3o.html">Legislação</a>
                                                                        </li>
                                                                        <li class="item-860"><a href="https://gru.ifsp.edu.br/metas.html">Metas</a></li>
                                                                        <li class="item-857"><a
                                                                                        href="https://gru.ifsp.edu.br/p%C3%83%C2%BAblico-alvo.html">Público alvo</a>
                                                                        </li>
                                                                        <li class="item-862"><a
                                                                                        href="https://gru.ifsp.edu.br/publica%C3%83%C2%A7%C3%83%C2%B5es.html">Publicações</a>
                                                                        </li>
                                                                </ul>
                                                        </nav>
                                                        <nav class="sobre span9">
                                                                <h2>Secretaria <i class="icon-chevron-up visible-phone visible-tablet pull-right"></i>
                                                                </h2>
                                                                <ul>
                                                                        <li class="item-846"><a
                                                                                        href="https://gru.ifsp.edu.br/atendimento-presencial.html">Atendimento
                                                                                        Presencial</a></li>
                                                                        <li class="item-565"><a
                                                                                        href="https://gru.ifsp.edu.br/component/content/article/112-uncategorized/secretaria/2312-encceja-veja-como-solicitar-seu-certificado.html">Certificação
                                                                                        ENCCEJA</a></li>
                                                                        <li class="item-848"><a href="https://gru.ifsp.edu.br/passe-escolar.html">Passe
                                                                                        escolar</a></li>
                                                                        <li class="item-825"><a
                                                                                        href="https://gru.ifsp.edu.br/comunicados.html">Procedimentos</a></li>
                                                                        <li class="item-827"><a href="https://gru.ifsp.edu.br/websecretaria.html">Web
                                                                                        Secretaria</a></li>
                                                                </ul>
                                                        </nav>
                                                        <nav class="sobre span9">
                                                                <h2>Semanas Temáticas <i
                                                                                class="icon-chevron-up visible-phone visible-tablet pull-right"></i></h2>
                                                                <!-- visible-phone visible-tablet -->

                                                                <ul>
                                                                        <li class="item-873"><a href="https://revista.gru.ifsp.edu.br/exatecca">EXATECCA</a>
                                                                        </li>
                                                                        <li class="item-874"><a href="https://revista.gru.ifsp.edu.br/">Semana da Matemática
                                                                                        (SEMAT)</a></li>
                                                                </ul>
                                                        </nav>
                                                        <nav class="sobre span9">
                                                                <h2>Acesso à Informação <i
                                                                                class="icon-chevron-up visible-phone visible-tablet pull-right"></i></h2>

                                                                <ul>
                                                                        <li class="item-112"><a
                                                                                        href="https://gru.ifsp.edu.br/institucional.html">Institucional</a></li>
                                                                        <li class="item-113"><a href="https://gru.ifsp.edu.br/acoes-e-programas.html">Ações
                                                                                        e Programas</a></li>
                                                                        <li class="item-114"><a href="https://ifsp.edu.br/auditorias">Auditoria Interna</a>
                                                                        </li>
                                                                        <li class="item-115"><a
                                                                                        href="https://ifsp.edu.br/convenios-e-transferencias">Convênios e
                                                                                        Transferências</a></li>
                                                                        <li class="item-119"><a
                                                                                        href="https://ifsp.edu.br/informacoes-classificadas">Informações
                                                                                        classificadas</a></li>
                                                                        <li class="item-117"><a href="https://ifsp.edu.br/licitacoes-e-contratos">Licitações
                                                                                        e Contratos</a></li>
                                                                        <li class="item-880"><a
                                                                                        href="https://www.ifsp.edu.br/component/content/article/81/">Ouvidoria</a>
                                                                        </li>
                                                                        <li class="item-894"><a href="https://ifsp.edu.br/participacao-social">Participação
                                                                                        Social</a></li>
                                                                        <li class="item-910"><a
                                                                                        href="https://www.ifsp.edu.br/perguntas-frequentes2">Perguntas
                                                                                        Frequentes</a></li>
                                                                        <li class="item-124"><a
                                                                                        href="https://www.ifsp.edu.br/protecao-de-dados-pessoais">Proteção de Dados
                                                                                        Pessoais</a></li>
                                                                        <li class="item-116"><a href="https://ifsp.edu.br/receitas-e-despesas">Receitas e
                                                                                        Despesas</a></li>
                                                                        <li class="item-120"><a
                                                                                        href="https://ifsp.edu.br/servico-de-informacao-ao-cidadao-sic">Serviço de
                                                                                        Informação ao Cidadão (SIC)</a></li>
                                                                        <li class="item-118"><a href="https://ifsp.edu.br/servidores">Servidores</a></li>
                                                                        <li class="item-911"><a
                                                                                        href="https://www.ifsp.edu.br/sustentabilidade">Sustentabilidade</a></li>
                                                                </ul>
                                                        </nav>

                                                        <span class="hide">Fim do menu principal</span>
                                                </section>
                                        </div>
                                        <div id="content" class="span9 internas">
                                                <section id="content-section">
                                                        <span class="hide">Início do conteúdo da página</span>
