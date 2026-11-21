<?php
/**
 * views/clientes/ver.php - Ficha de un cliente con su historial de pedidos.
 * Variables: $cliente, $pedidos
 */
defined('APP') or exit('Acceso directo no permitido');
$c = $cliente;
$gastado = array_sum(array_map(fn($p) => $p['estado'] === 'Entregado' ? (float) $p['total'] : 0, $pedidos));
?>
<a href="<?= url('clientes/index') ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Clientes</a>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="card hp-card">
      <div class="card-body text-center">
        <span class="hp-avatar hp-avatar-xl mb-2"><?= e(iniciales($c['nombre'], $c['apellido'])) ?></span>
        <h1 class="h4 fw-bold mb-0"><?= e($c['nombre'] . ' ' . $c['apellido']) ?></h1>
        <p class="text-body-secondary small">Cliente desde <?= fecha($c['creado_en']) ?></p>
        <?php if (!$c['activo']): ?><span class="badge text-bg-secondary mb-2">Cuenta desactivada</span><?php endif; ?>
        <ul class="list-unstyled text-start small mb-0">
          <li class="mb-1"><i class="bi bi-envelope"></i> <?= e($c['correo']) ?></li>
          <li class="mb-1"><i class="bi bi-telephone"></i> <?= e($c['telefono']) ?: '—' ?></li>
          <li><i class="bi bi-geo-alt"></i> <?= e($c['direccion']) ?: 'Sin dirección' ?></li>
        </ul>
      </div>
      <div class="card-footer d-flex justify-content-around text-center">
        <div><div class="fw-bold fs-5"><?= count($pedidos) ?></div><small class="text-body-secondary">Pedidos</small></div>
        <div><div class="fw-bold fs-5"><?= dinero($gastado) ?></div><small class="text-body-secondary">Compras</small></div>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card hp-card">
      <div class="card-header fw-bold"><i class="bi bi-receipt"></i> Historial de pedidos</div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th>#</th><th>Fecha</th><th>Entrega</th><th class="text-end">Total</th><th>Estado</th></tr></thead>
          <tbody>
            <?php foreach ($pedidos as $p): ?>
              <tr>
                <td><a href="<?= url('pedidos/ver', ['id' => $p['id']]) ?>" class="fw-bold">#<?= (int) $p['id'] ?></a></td>
                <td><?= fecha($p['fecha'], true) ?></td>
                <td><?= e($p['tipo_entrega']) ?></td>
                <td class="text-end"><?= dinero($p['total']) ?></td>
                <td><?= badge_estado($p['estado']) ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$pedidos): ?><tr><td colspan="5" class="text-center text-body-secondary py-4">Sin pedidos todavía.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
