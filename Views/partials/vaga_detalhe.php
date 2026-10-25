<?php
?>
<?php require __DIR__ . '/../../public/css/vaga_detalhe.css'; ?>
<div class="ui-grid-2">
  <div class="ui-card">
    <h2>Informações</h2>
    <dl class="ui-kv">
      <dt>Código</dt>
      <dd><span class="ui-code"><?= e($v['codigo']) ?></span> <small class="ui-hint">versão
          <?= (int) $v['versao'] ?></small></dd>
      <dt>Tipo</dt>
      <dd><?= e($v['tipo']) ?></dd>
      <dt>Área</dt>
      <dd><?= e($v['area']) ?></dd>
      <dt>Modalidade</dt>
      <dd><?= e($v['modalidade']) ?></dd>
      <dt>Local</dt>
      <dd><?= e($v['local']) ?></dd>
      <dt>Remuneração</dt>
      <dd><?= e($v['bolsa_txt']) ?></dd>
      <dt>Carga horária</dt>
      <dd><?= e($v['carga_txt'] ?: 'Não informada') ?></dd>
      <dt>Período</dt>
      <dd><?= data_br($v['data_publicacao']) ?> a <?= data_br($v['data_limite']) ?> <small
          class="ui-hint">(<?= e(dias_txt($v['dias'])) ?>)</small></dd>
    </dl>
  </div>
  <div class="ui-card">
    <h2>Candidatura e links</h2>
    <dl class="ui-kv">
      <dt>Processo seletivo</dt>
      <dd><?= e(Link::PLATAFORMAS[$v['link_plataforma']] ?? 'Por e-mail') ?></dd>
      <dt>Link de candidatura</dt>
      <dd><?= $v['link_candidatura'] ? '<a href="' . e($v['link_candidatura']) . '" target="_blank" rel="noopener noreferrer nofollow">' . e(Link::dominio($v['link_candidatura'])) . '</a>' : '-' ?>
      </dd>
      <dt>E-mail</dt>
      <dd><?= e($v['contato'] ?: '-') ?></dd>
      <dt>Site</dt>
      <dd><?= $v['link_site'] ? '<a href="' . e($v['link_site']) . '" target="_blank" rel="noopener noreferrer nofollow">' . e(Link::dominio($v['link_site'])) . '</a>' : '-' ?>
      </dd>
      <dt>LinkedIn</dt>
      <dd><?= $v['link_linkedin'] ? '<a href="' . e($v['link_linkedin']) . '" target="_blank" rel="noopener noreferrer nofollow">' . e(Link::dominio($v['link_linkedin'])) . '</a>' : '-' ?>
      </dd>
    </dl>
  </div>
</div>
<div class="ui-card ui-card-margin-top">
  <h2>Descrição</h2>
  <p><?= nl2br(e($v['descricao'])) ?></p>
</div>
<div class="ui-grid-2 ui-grid-2-margin-top">
  <div class="ui-card">
    <h2>Requisitos obrigatórios</h2><?php if ($v['requisitos']): ?><ul class="ui-list">
        <?php foreach ($v['requisitos'] as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul><?php else: ?><p
        class="ui-sub">Não informado.</p><?php endif; ?>
    <?php if ($v['requisitos_desejaveis']): ?><h2>Desejáveis</h2>
      <ul class="ui-list"><?php foreach ($v['requisitos_desejaveis'] as $x): ?><li><?= e($x) ?></li>
        <?php endforeach; ?></ul><?php endif; ?>
  </div>
  <div class="ui-card">
    <h2>Benefícios</h2><?php if ($v['beneficios']): ?><ul class="ui-pills">
        <?php foreach ($v['beneficios'] as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul><?php else: ?><p
        class="ui-sub">Não informado.</p><?php endif; ?>
    <?php if ($v['atividades']): ?><h2>Atividades</h2>
      <ul class="ui-list"><?php foreach ($v['atividades'] as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>
  </div>
</div>
