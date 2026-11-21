<?php
/**
 * views/inicio/empleado.php - Panel del EMPLEADO: pedidos por atender.
 * Variables: $activos (pedidos en curso), $porEstado, $stockBajo, $ventasN, $ventasTotal
 */
defined('APP') or exit('Acceso directo no permitido');
$scripts = ['pedidos.js'];
?>
<div class="hp-encabezado">
  <div>
    <h1 class="hp-titulo">Hola, <?= e(Auth::user()['nombre']) ?></h1>
    <p class="text-body-secondary mb-0">Hoy se entregaron <?= $ventasN ?> pedido(s) por <?= dinero($ventasTotal) ?>.</p>
  </div>
  <a href="<?= url('pedidos/index') ?>" class="btn btn-primary"><i class="bi bi-kanban"></i> Tablero de pedidos</a>
</div>

<div class="row g-3 mb-4">
  <?php foreach (['Pendiente', 'Preparando', 'Listo'] as $e): $info = ESTADOS_PEDIDO[$e]; ?>
    <div class="col-4">
      <div class="card hp-card hp-stat h-100"><div class="card-body">
        <i class="bi bi-<?= $info['icono'] ?> hp-stat-icono"></i>
        <div class="hp-stat-valor"><?= (int) $porEstado[$e] ?></div>
        <div class="hp-stat-etiqueta"><?= $e ?></div>
      </div></div>
    </div>
  <?php endforeach; ?>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <h2 class="h5 fw-bold mb-3">Pedidos por atender</h2>
    <div class="row g-3">
      <?php foreach ($activos as $p): ?>
        <div class="col-md-6"><?php require __DIR__ . '/../pedidos/tarjeta.php'; ?></div>
      <?php endforeach; ?>
      <?php if (!$activos): ?>
        <div class="col-12"><div class="hp-vacio"><i class="bi bi-emoji-smile"></i><p>No hay pedidos pendientes.</p></div></div>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-4">
    <?php require __DIR__ . '/stock.php'; ?>
  </div>
</div>
