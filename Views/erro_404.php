<?php $publico = ($modo ?? 'publico') === 'publico';
if ($publico) {
    require __DIR__ . '/header.php';
    $ativa = 'home';
    require __DIR__ . '/partials/nav.php';
}
require __DIR__ . '/partials/ilustracao_404.php';
if ($publico) {
    echo '</div>';
    require __DIR__ . '/footer.php';
}