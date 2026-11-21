<?php
/**
 * views/pedidos/tarjeta.php - Tarjeta de un pedido activo (parcial).
 * Se incluye desde el tablero de pedidos y desde el panel del empleado.
 * Los botones usan assets/js/pedidos.js para cambiar el estado sin recargar.
 * Variable: $p (pedido con cliente, unidades, total, estado...)
 */
defined('APP') or exit('Acceso directo no permitido');

$i = array_search($p['estado'], Pedido::FLUJO, true);
$siguiente = Pedido::FLUJO[$i + 1] ?? null;
$minutos = (int) floor((time() - strtotime($p['fecha'])) / 60);
?>
<div class="card hp-card hp-pedido mb-3" data-pedido="<?= (int) $p['id'] ?>">
  <div class="card-body p-3">
    <div class="d-flex justify-content-between align-items-start">
      <a href="<?= url('pedidos/ver', ['id' => $p['id']]) ?>" class="fw-bold text-decoration-none">#<?= (int) $p['id'] ?></a>
      <!-- En rojo si el pedido lleva más de 30 minutos pendiente -->
      <small class="<?= $minutos > 30 && $p['estado'] === 'Pendiente' ? 'text-danger fw-bold' : 'text-body-secondary' ?>">
        <i class="bi bi-clock"></i> <?= $minutos < 60 ? "hace $minutos min" : fecha($p['fecha'], true) ?></small>
    </div>
    <div class="fw-semibold mt-1"><?= e($p['cliente']) ?></div>
    <div class="small text-body-secondary">
      <?= (int) $p['unidades'] ?> producto(s) · <strong class="text-body"><?= dinero($p['total']) ?></strong>
    </div>
    <div class="small mt-1">
      <?= $p['tipo_entrega'] === 'Domicilio' ? '<i class="bi bi-bicycle"></i> Domicilio' : '<i class="bi bi-shop"></i> Retiro en local' ?>
    </div>
    <?php if ($p['notas']): ?><div class="small fst-italic mt-1">“<?= e($p['notas']) ?>”</div><?php endif; ?>
    <div class="d-flex gap-2 mt-3">
      <?php if ($siguiente): ?>
        <button class="btn btn-sm btn-primary flex-fill" data-estado="<?= $siguiente ?>">
          <i class="bi bi-arrow-right-circle"></i> <?= $siguiente ?>
        </button>
      <?php endif; ?>
      <button class="btn btn-sm btn-outline-secondary" data-estado="Cancelado" title="Cancelar pedido" aria-label="Cancelar pedido"><i class="bi bi-x-lg"></i></button>
    </div>
  </div>
</div>
