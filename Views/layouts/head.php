<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
<script>var t='light';try{t=localStorage.getItem('vg-theme-choice')==='dark'?'dark':'light'}catch(e){}document.documentElement.setAttribute('data-theme',t)</script>
<link rel="icon" href="<?= e(BASE_URL) ?>public/img/ifsp/if.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Instrument+Sans:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="<?= e(BASE_URL) ?>public/css/app.css?v=<?= (int) @filemtime(ROOT_PATH . '/public/css/app.css') ?>">
<link rel="stylesheet" href="<?= e(BASE_URL) ?>public/css/views.css?v=<?= (int) @filemtime(ROOT_PATH . '/public/css/views.css') ?>">
