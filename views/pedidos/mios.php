<?php
/**
 * views/pedidos/mios.php - Historial de pedidos del CLIENTE.
 * Variables: $pedidos
 */
defined('APP') or exit('Acceso directo no permitido');
?>
<div class="hp-encabezado">
  <div>
    <h1 class="hp-titulo">Mis pedidos</h1>
    <p class="text-body-secondary mb-0">Revisa el estado de tus pedidos y tu historial de compras.</p>
  </div>
  <a href="<?= url('tienda/index') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nuevo pedido</a>
</div>

<div class="row g-3">
  <?php foreach ($pedidos as $p): ?>
    <div class="col-md-6 col-xl-4">
      <a href="<?= url('pedidos/ver', ['id' => $p['id']]) ?>" class="card hp-card hp-card-link h-100 text-decoration-none">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <span class="fw-bold">Pedido #<?= (int) $p['id'] ?></span>
            <?= badge_estado($p['estado']) ?>
          </div>
          <div class="small text-body-secondary mt-1"><?= fecha($p['fecha'], true) ?> · <?= e($p['tipo_entrega']) ?></div>
          <div class="d-flex justify-content-between align-items-end mt-3">
            <span class="small"><?= (int) $p['unidades'] ?> producto(s)</span>
            <span class="fs-5 fw-bold"><?= dinero($p['total']) ?></span>
          </div>
        </div>
      </a>
    </div>
  <?php endforeach; ?>
  <?php if (!$pedidos): ?>
    <div class="col-12">
      <div class="hp-vacio"><i class="bi bi-receipt"></i><p>Aún no has hecho pedidos.</p>
        <a href="<?= url('tienda/index') ?>" class="btn btn-primary btn-sm mt-2">Ver el catálogo</a></div>
    </div>
  <?php endif; ?>
</div>
