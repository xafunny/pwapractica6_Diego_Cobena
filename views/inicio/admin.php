<?php
/**
 * views/inicio/admin.php - Panel del ADMINISTRADOR.
 * Variables: $ventasN, $ventasTotal, $porEstado, $stockBajo, $resumen (mes),
 *            $serie (ingresos/gastos 7 días), $masVendidos, $clientes, $activos
 */
defined('APP') or exit('Acceso directo no permitido');
$usarGraficas = true;
$scripts = ['panel.js'];
$enCurso = $porEstado['Pendiente'] + $porEstado['Preparando'] + $porEstado['Listo'];
?>
<div class="hp-encabezado">
  <div>
    <h1 class="hp-titulo">Panel de administración</h1>
    <p class="text-body-secondary mb-0"><?= nombre_mes((int) date('n')) ?> <?= date('Y') ?> · resumen del negocio</p>
  </div>
  <a href="<?= url('pedidos/index') ?>" class="btn btn-primary"><i class="bi bi-receipt"></i> Ver pedidos en curso</a>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-xl-3">
    <div class="card hp-card hp-stat h-100"><div class="card-body">
      <i class="bi bi-bag-check hp-stat-icono"></i>
      <div class="hp-stat-valor"><?= dinero($ventasTotal) ?></div>
      <div class="hp-stat-etiqueta">Ventas de hoy (<?= $ventasN ?>)</div>
    </div></div>
  </div>
  <div class="col-6 col-xl-3">
    <a href="<?= url('pedidos/index') ?>" class="card hp-card hp-card-link hp-stat h-100 text-decoration-none"><div class="card-body">
      <i class="bi bi-hourglass-split hp-stat-icono"></i>
      <div class="hp-stat-valor"><?= $enCurso ?></div>
      <div class="hp-stat-etiqueta">Pedidos en curso</div>
    </div></a>
  </div>
  <div class="col-6 col-xl-3">
    <a href="<?= url('finanzas/index') ?>" class="card hp-card hp-card-link hp-stat h-100 text-decoration-none"><div class="card-body">
      <i class="bi bi-cash-coin hp-stat-icono"></i>
      <div class="hp-stat-valor <?= $resumen['balance'] < 0 ? 'text-danger' : '' ?>"><?= dinero($resumen['balance']) ?></div>
      <div class="hp-stat-etiqueta">Balance del mes</div>
    </div></a>
  </div>
  <div class="col-6 col-xl-3">
    <a href="<?= url('clientes/index') ?>" class="card hp-card hp-card-link hp-stat h-100 text-decoration-none"><div class="card-body">
      <i class="bi bi-people hp-stat-icono"></i>
      <div class="hp-stat-valor"><?= $clientes ?></div>
      <div class="hp-stat-etiqueta">Clientes registrados</div>
    </div></a>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="card hp-card h-100">
      <div class="card-header fw-bold"><i class="bi bi-bar-chart"></i> Ingresos y gastos · últimos 7 días</div>
      <div class="card-body"><div class="hp-grafica"><canvas id="graficaFinanzas" aria-label="Gráfica de ingresos y gastos"></canvas></div></div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card hp-card h-100">
      <div class="card-header fw-bold"><i class="bi bi-trophy"></i> Más vendidos</div>
      <ul class="list-group list-group-flush">
        <?php foreach ($masVendidos as $i => $m): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><span class="hp-puesto"><?= $i + 1 ?></span> <?= e($m['nombre']) ?></span>
            <span class="badge text-bg-light"><?= (int) $m['unidades'] ?> u.</span>
          </li>
        <?php endforeach; ?>
        <?php if (!$masVendidos): ?><li class="list-group-item text-body-secondary">Aún no hay ventas entregadas.</li><?php endif; ?>
      </ul>
    </div>
  </div>
  <div class="col-12">
    <?php require __DIR__ . '/stock.php'; ?>
  </div>
</div>

<script>window.HP_SERIE = <?= json_encode($serie) ?>;</script>
