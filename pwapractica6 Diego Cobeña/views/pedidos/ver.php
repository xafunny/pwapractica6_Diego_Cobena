<?php
/**
 * views/pedidos/ver.php - Detalle de un pedido.
 *   - Línea de tiempo con el avance del estado.
 *   - Cliente: puede cancelar si sigue "Pendiente".
 *   - Personal: botones para avanzar o cancelar (pedidos.js).
 * Variables: $pedido, $lineas
 */
defined('APP') or exit('Acceso directo no permitido');
$p = $pedido;
$esCliente = Auth::es(Auth::CLIENTE);
if (!$esCliente) $scripts = ['pedidos.js'];
$posActual = array_search($p['estado'], Pedido::FLUJO, true);
$siguiente = $posActual !== false ? (Pedido::FLUJO[$posActual + 1] ?? null) : null;
?>
<a href="<?= url('pedidos/index') ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Volver</a>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="card hp-card">
      <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between gap-2">
          <div>
            <h1 class="hp-titulo mb-0">Pedido #<?= (int) $p['id'] ?></h1>
            <p class="text-body-secondary mb-0"><?= fecha($p['fecha'], true) ?></p>
          </div>
          <div id="estadoPedido"><?= badge_estado($p['estado']) ?></div>
        </div>

        <!-- Línea de tiempo del estado -->
        <?php if ($p['estado'] !== 'Cancelado'): ?>
          <ol class="hp-pasos my-4">
            <?php foreach (Pedido::FLUJO as $i => $paso): ?>
              <li class="<?= $i <= $posActual ? 'hecho' : '' ?>"><span><i class="bi bi-<?= ESTADOS_PEDIDO[$paso]['icono'] ?>"></i></span><?= $paso ?></li>
            <?php endforeach; ?>
          </ol>
        <?php else: ?>
          <div class="alert alert-secondary my-3"><i class="bi bi-x-circle"></i> Este pedido fue cancelado.</div>
        <?php endif; ?>

        <div class="table-responsive">
          <table class="table align-middle">
            <thead><tr><th>Producto</th><th class="text-center">Cant.</th><th class="text-end">Precio</th><th class="text-end">Subtotal</th></tr></thead>
            <tbody>
              <?php foreach ($lineas as $l): ?>
                <tr>
                  <td><?= e($l['nombre']) ?></td>
                  <td class="text-center"><?= (int) $l['cantidad'] ?></td>
                  <td class="text-end"><?= dinero($l['precio_unitario']) ?></td>
                  <td class="text-end"><?= dinero($l['subtotal']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot><tr><th colspan="3" class="text-end">Total</th><th class="text-end fs-5"><?= dinero($p['total']) ?></th></tr></tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card hp-card mb-3">
      <div class="card-header fw-bold"><i class="bi bi-truck"></i> Entrega</div>
      <div class="card-body">
        <p class="mb-1"><strong><?= e($p['tipo_entrega'] === 'Domicilio' ? 'A domicilio' : 'Retiro en el local') ?></strong></p>
        <?php if ($p['direccion']): ?><p class="mb-1"><i class="bi bi-geo-alt"></i> <?= e($p['direccion']) ?></p><?php endif; ?>
        <?php if ($p['notas']): ?><p class="mb-0 fst-italic">“<?= e($p['notas']) ?>”</p><?php endif; ?>
        <?php if (!$esCliente): ?>
          <hr>
          <p class="mb-1"><i class="bi bi-person"></i> <?= e($p['cliente']) ?></p>
          <?php if ($p['telefono']): ?><p class="mb-1"><i class="bi bi-telephone"></i> <?= e($p['telefono']) ?></p><?php endif; ?>
          <p class="mb-0 small text-body-secondary">Atendido por: <?= e($p['empleado']) ?: 'sin asignar' ?></p>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($esCliente && $p['estado'] === 'Pendiente'): ?>
      <form method="post" action="<?= url('pedidos/cancelar') ?>" data-confirmar="¿Cancelar el pedido #<?= (int) $p['id'] ?>?">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
        <button class="btn btn-outline-danger w-100"><i class="bi bi-x-circle"></i> Cancelar pedido</button>
      </form>
    <?php elseif (!$esCliente && !in_array($p['estado'], ['Entregado', 'Cancelado'], true)): ?>
      <!-- Mismo contenedor data-pedido que las tarjetas: pedidos.js recarga la página al cambiar -->
      <div class="d-flex gap-2" data-pedido="<?= (int) $p['id'] ?>" data-recargar>
        <?php if ($siguiente): ?>
          <button class="btn btn-primary flex-fill" data-estado="<?= $siguiente ?>"><i class="bi bi-arrow-right-circle"></i> Pasar a <?= $siguiente ?></button>
        <?php endif; ?>
        <button class="btn btn-outline-secondary" data-estado="Cancelado"><i class="bi bi-x-lg"></i> Cancelar</button>
      </div>
    <?php endif; ?>
  </div>
</div>
