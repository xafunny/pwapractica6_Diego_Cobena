<?php
/**
 * views/pedidos/index.php - Pedidos para el PERSONAL (admin y empleado).
 *   - Sin filtro: tablero con columnas Pendiente / Preparando / Listo.
 *     Los botones mueven la tarjeta de columna sin recargar (pedidos.js).
 *   - Con filtro (Entregado, Cancelado, Todos): tabla.
 * Variables: $pedidos, $estado (filtro), $porEstado (conteos)
 */
defined('APP') or exit('Acceso directo no permitido');
$scripts = ['pedidos.js'];
$activos = ['Pendiente', 'Preparando', 'Listo'];
$filtros = ['' => 'En curso', 'Entregado' => 'Entregados', 'Cancelado' => 'Cancelados', 'todos' => 'Todos'];
?>
<div class="hp-encabezado">
  <div>
    <h1 class="hp-titulo">Pedidos</h1>
    <p class="text-body-secondary mb-0">Avanza cada pedido a medida que se prepara y se entrega.</p>
  </div>
  <ul class="nav nav-pills hp-pills">
    <?php foreach ($filtros as $k => $txt): ?>
      <li class="nav-item">
        <a class="nav-link <?= $estado === $k ? 'active' : '' ?>" href="<?= url('pedidos/index', array_filter(['estado' => $k])) ?>"><?= $txt ?>
          <?php if (isset($porEstado[$k])): ?><span class="badge rounded-pill text-bg-light"><?= (int) $porEstado[$k] ?></span><?php endif; ?></a>
      </li>
    <?php endforeach; ?>
  </ul>
</div>

<?php if ($estado === ''): ?>
  <!-- ================= TABLERO ================= -->
  <div class="row g-3">
    <?php foreach ($activos as $col): $info = ESTADOS_PEDIDO[$col]; ?>
      <div class="col-md-4">
        <div class="hp-columna" data-columna="<?= $col ?>">
          <div class="hp-columna-titulo">
            <span><i class="bi bi-<?= $info['icono'] ?> text-<?= $info['color'] ?>"></i> <?= $col ?></span>
            <span class="badge rounded-pill text-bg-<?= $info['color'] ?>" data-contador><?= (int) $porEstado[$col] ?></span>
          </div>
          <div data-lista>
            <?php foreach ($pedidos as $p): if ($p['estado'] !== $col) continue; ?>
              <?php require __DIR__ . '/tarjeta.php'; ?>
            <?php endforeach; ?>
          </div>
          <p class="hp-vacio-col small text-body-secondary text-center py-3 mb-0">Sin pedidos</p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <!-- ================= TABLA ================= -->
  <div class="card hp-card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead><tr><th>#</th><th>Fecha</th><th>Cliente</th><th class="d-none d-md-table-cell">Entrega</th><th class="text-end">Total</th><th>Estado</th><th class="d-none d-lg-table-cell">Atendió</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($pedidos as $p): ?>
          <tr>
            <td class="fw-bold">#<?= (int) $p['id'] ?></td>
            <td class="text-nowrap"><?= fecha($p['fecha'], true) ?></td>
            <td><?= e($p['cliente']) ?></td>
            <td class="d-none d-md-table-cell"><?= e($p['tipo_entrega']) ?></td>
            <td class="text-end fw-semibold"><?= dinero($p['total']) ?></td>
            <td><?= badge_estado($p['estado']) ?></td>
            <td class="d-none d-lg-table-cell small"><?= e($p['empleado']) ?: '—' ?></td>
            <td class="text-end text-nowrap">
              <a class="btn btn-sm btn-outline-secondary" href="<?= url('pedidos/ver', ['id' => $p['id']]) ?>" aria-label="Ver pedido"><i class="bi bi-eye"></i></a>
              <?php if (Auth::es(Auth::ADMIN)): ?>
                <form method="post" action="<?= url('pedidos/eliminar') ?>" class="d-inline" data-confirmar="¿Eliminar el pedido #<?= (int) $p['id'] ?>? Esta acción no se puede deshacer.">
                  <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger" aria-label="Eliminar pedido"><i class="bi bi-trash"></i></button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$pedidos): ?><tr><td colspan="8" class="text-center text-body-secondary py-4">No hay pedidos.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>
