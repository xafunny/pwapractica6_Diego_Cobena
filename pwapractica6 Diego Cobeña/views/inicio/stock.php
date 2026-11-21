<?php
/**
 * views/inicio/stock.php - Alerta de productos con stock bajo (parcial).
 * Variable: $stockBajo
 */
defined('APP') or exit('Acceso directo no permitido');
?>
<div class="card hp-card">
  <div class="card-header fw-bold d-flex justify-content-between">
    <span><i class="bi bi-exclamation-triangle text-warning"></i> Stock bajo</span>
    <?php if (Auth::es(Auth::ADMIN)): ?><a href="<?= url('productos/index') ?>" class="small">Ir a productos</a><?php endif; ?>
  </div>
  <div class="card-body">
    <?php if ($stockBajo): ?>
      <div class="d-flex flex-wrap gap-2">
        <?php foreach ($stockBajo as $s): ?>
          <span class="hp-chip <?= (int) $s['stock'] === 0 ? 'hp-chip-peligro' : 'hp-chip-aviso' ?>">
            <?= e($s['nombre']) ?>: <strong><?= (int) $s['stock'] === 0 ? 'agotado' : (int) $s['stock'] . ' u.' ?></strong>
          </span>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="mb-0 text-body-secondary"><i class="bi bi-check2-circle text-success"></i> Todo el inventario está en buen nivel.</p>
    <?php endif; ?>
  </div>
</div>
