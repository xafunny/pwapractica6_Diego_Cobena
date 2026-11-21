<?php
/**
 * views/tienda/carrito.php - Carrito y confirmación del pedido.
 * Las cantidades se cambian con fetch() (tienda.js) y el total se
 * recalcula en el servidor. El formulario final confirma el pedido.
 * Variables: $carrito ['lineas', 'total'], $cliente (datos del cliente para la dirección)
 */
defined('APP') or exit('Acceso directo no permitido');
$scripts = ['tienda.js'];
$lineas = $carrito['lineas'];
?>
<div class="hp-encabezado">
  <div>
    <h1 class="hp-titulo">Mi carrito</h1>
    <p class="text-body-secondary mb-0">Revisa tu pedido antes de confirmarlo.</p>
  </div>
  <a href="<?= url('tienda/index') ?>" class="btn btn-outline-primary"><i class="bi bi-arrow-left"></i> Seguir comprando</a>
</div>

<?php if (!$lineas): ?>
  <div class="hp-vacio"><i class="bi bi-cart-x"></i><p>Tu carrito está vacío.</p>
    <a href="<?= url('tienda/index') ?>" class="btn btn-primary btn-sm mt-2">Ver el catálogo</a></div>
<?php else: ?>
<div class="row g-4">
  <div class="col-lg-7">
    <div class="card hp-card">
      <ul class="list-group list-group-flush" id="listaCarrito">
        <?php foreach ($lineas as $l): ?>
          <li class="list-group-item py-3" data-producto="<?= (int) $l['id'] ?>" data-stock="<?= (int) $l['stock'] ?>">
            <div class="d-flex justify-content-between align-items-center gap-3">
              <div>
                <div class="fw-bold"><?= e($l['nombre']) ?></div>
                <small class="text-body-secondary"><?= dinero($l['precio']) ?> c/u</small>
              </div>
              <div class="d-flex align-items-center gap-3">
                <div class="input-group input-group-sm hp-cantidad" style="width:120px">
                  <button class="btn btn-outline-primary" data-delta="-1" aria-label="Quitar uno"><i class="bi bi-dash-lg"></i></button>
                  <span class="form-control text-center fw-bold" data-cantidad><?= (int) $l['cantidad'] ?></span>
                  <button class="btn btn-outline-primary" data-delta="1" aria-label="Agregar uno"><i class="bi bi-plus-lg"></i></button>
                </div>
                <span class="fw-bold text-end" style="min-width:70px" data-subtotal><?= dinero($l['subtotal']) ?></span>
              </div>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class="card-footer d-flex justify-content-between align-items-center">
        <span class="fw-bold">Total</span>
        <span class="hp-precio fs-4" id="totalCarrito"><?= dinero($carrito['total']) ?></span>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <form class="card hp-card needs-validation" method="post" action="<?= url('tienda/confirmar') ?>" novalidate id="formConfirmar">
      <div class="card-header fw-bold"><i class="bi bi-truck"></i> ¿Cómo lo recibes?</div>
      <div class="card-body">
        <?= csrf_field() ?>
        <div class="btn-group w-100 mb-3" role="group" aria-label="Tipo de entrega">
          <input type="radio" class="btn-check" name="tipo_entrega" id="entregaRetiro" value="Retiro" checked>
          <label class="btn btn-outline-primary" for="entregaRetiro"><i class="bi bi-shop"></i> Retiro en local</label>
          <input type="radio" class="btn-check" name="tipo_entrega" id="entregaDomicilio" value="Domicilio">
          <label class="btn btn-outline-primary" for="entregaDomicilio"><i class="bi bi-bicycle"></i> A domicilio</label>
        </div>
        <div class="mb-3 d-none" id="grupoDireccion">
          <label class="form-label" for="direccion">Dirección de entrega</label>
          <input class="form-control" id="direccion" name="direccion" maxlength="255" minlength="5" value="<?= e($cliente['direccion']) ?>">
          <div class="invalid-feedback">Escribe la dirección de entrega.</div>
        </div>
        <div class="mb-3">
          <label class="form-label" for="notas">Notas <span class="text-body-secondary">(opcional)</span></label>
          <input class="form-control" id="notas" name="notas" maxlength="255" placeholder="Ej.: sin chispas, tocar el timbre…">
        </div>
        <button class="btn btn-primary w-100 py-2 fw-bold"><i class="bi bi-check2-circle"></i> Confirmar pedido</button>
        <p class="small text-body-secondary text-center mt-2 mb-0">Pagas al recibir tu pedido.</p>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<script>window.HP_TIENDA = { url: <?= json_encode(url('tienda/poner')) ?> };</script>
