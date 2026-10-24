<?php
/**
 * @var array $empPend
 * @var array $vagasPend
 */
?>
<?php $ret = 'admin-aprovacoes';
$total = count($empPend) + count($vagasPend); ?>
<?php if (!$total): echo Ui::vazio('Tudo em dia', 'Não há empresas nem vagas aguardando análise.');
endif; ?>
<?php if ($empPend): ?><section class="ui-section">
    <h2>Empresas aguardando aprovação <?= Ui::badge((string) count($empPend), 'warn') ?></h2>
    <div class="ui-grid">
      <?php foreach ($empPend as $e): ?>
        <article class="ui-comp ui-job">
          <div class="ui-comp-head"><?= Ui::logo($e, 52) ?><div>
              <h3><a href="<?= url('admin-empresa', ['id' => $e['id']]) ?>"><?= e($e['nome']) ?></a></h3>
              <p><?= e(Empresa::segmentoTxt($e)) ?></p>
            </div>
          </div>
          <ul class="ui-job-meta">
            <li><?= icon('building', 15) ?> CNPJ <?= e(Cnpj::formatar($e['cnpj'])) ?></li>
            <li><?= icon('pin', 15) ?> <?= e($e['cidade'] ? $e['cidade'] . '/' . $e['uf'] : $e['endereco']) ?></li>
            <li><?= icon('user', 15) ?> <?= e($e['responsavel']) ?> · <?= e($e['email']) ?></li>
            <li><?= icon('clock', 15) ?> Cadastrada em <?= e(date('d/m/Y H:i', strtotime($e['criado_em']))) ?></li>
          </ul>
          <div class="ui-job-foot"><a class="ui-btn ui-btn-sm"
              href="<?= url('admin-empresa', ['id' => $e['id']]) ?>"><?= icon('eye', 15) ?>
              Abrir</a><?php if ($e['termo_arquivo']): ?><a class="ui-btn ui-btn-sm"
                href="<?= url('admin-termo', ['id' => $e['id']]) ?>" target="_blank"
                rel="noopener"><?= icon('file', 15) ?> Termo</a><?php endif; ?>
            <?= Ui::acoesEmpresaAdmin($e, 'admin-aprovacoes') ?></div>
        </article>
      <?php endforeach; ?>
    </div>
  </section><?php endif; ?>
<?php if ($vagasPend): ?><section class="ui-section">
    <h2>Vagas aguardando aprovação <?= Ui::badge((string) count($vagasPend), 'warn') ?></h2>
    <div class="ui-grid">
      <?php foreach ($vagasPend as $v) {
          $ctx = 'admin';
          require __DIR__ . '/partials/vaga_card_app.php';
      } ?></div>
  </section><?php endif; ?>
?>
