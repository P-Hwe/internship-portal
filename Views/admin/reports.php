<?php
$cards = [
    ['vagas', 'Vagas', (int) ($vag['todas'] ?? 0), 'vagas cadastradas', 'briefcase', 'var(--chart-1)', 'Código, título, empresa, área, modalidade, remuneração, situação e datas de cada vaga.'],
    ['empresas', 'Empresas', (int) ($emp['total'] ?? 0), 'empresas cadastradas', 'building', 'var(--chart-2)', 'CNPJ, razão social, segmento, cidade, responsável, situação e quantidade de vagas.'],
    ['usuarios', 'Usuários das empresas', (int) $usu, 'pessoas com acesso', 'users', 'var(--chart-4)', 'Quem tem acesso à área de cada empresa, com cargo, situação e último acesso.'],
];
?>
<div class="dl-center">
  <?php foreach ($cards as [$tipo, $nomeCard, $qtd, $unidade, $ico, $tom, $desc]): ?>
  <form class="ui-card dl-card" method="get" action="<?= url('admin-exportar') ?>" data-dl-card style="--tone:<?= $tom ?>">
    <input type="hidden" name="tipo" value="<?= e($tipo) ?>">
    <div class="dl-card-head">
      <span class="dl-ico" aria-hidden="true"><?= icon($ico, 24) ?></span>
      <div><h2><?= e($nomeCard) ?></h2><span class="dl-count"><?= $qtd ?><small><?= e($unidade) ?></small></span></div>
    </div>
    <p><?= e($desc) ?></p>
    <details class="dl-opts">
      <summary>Período do arquivo <?= icon('chevron-right', 16) ?></summary>
      <div class="dl-opts-body">
        <label for="periodo-<?= e($tipo) ?>">Cadastrados</label>
        <select class="ui-select" id="periodo-<?= e($tipo) ?>" name="periodo">
          <?php foreach ($periodos as $chave => [$rotulo]): ?><option value="<?= e((string) $chave) ?>"><?= e($rotulo) ?></option><?php endforeach; ?>
        </select>
      </div>
    </details>
    <button class="ui-btn ui-btn-primary dl-btn" type="submit" data-dl>
      <span class="dl-idle"><?= icon('file', 16) ?> Baixar <?= e($tipo) ?>.csv</span>
      <span class="dl-busy"><span class="dl-spin" aria-hidden="true"></span> Gerando…</span>
      <span class="dl-fin"><?= icon('check-s', 16) ?> Pronto</span>
    </button>
    <p class="ui-sr" aria-live="polite" data-dl-live></p>
  </form>
  <?php endforeach; ?>
</div>
<p class="dl-note">Os arquivos são CSV separados por ponto e vírgula e abrem direto no Excel ou no Google Planilhas.</p>
