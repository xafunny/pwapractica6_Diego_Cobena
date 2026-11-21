<?php
/**
 * views/clientes/index.php - Gestión de clientes (ADMINISTRADOR y EMPLEADO).
 * Variables: $clientes (con pedidos, gastado, ultimo_pedido), $buscar
 */
defined('APP') or exit('Acceso directo no permitido');
$scripts = ['interruptores.js'];
?>
<div class="hp-encabezado">
  <div>
    <h1 class="hp-titulo">Clientes</h1>
    <p class="text-body-secondary mb-0"><?= count($clientes) ?> cliente(s) registrados.</p>
  </div>
  <div class="d-flex gap-2">
    <form class="d-flex gap-2" method="get">
      <input type="hidden" name="r" value="clientes/index">
      <input type="search" name="q" class="form-control" value="<?= e($buscar) ?>" placeholder="Nombre, correo o teléfono" aria-label="Buscar cliente">
      <button class="btn btn-outline-secondary" aria-label="Buscar"><i class="bi bi-search"></i></button>
    </form>
    <button class="btn btn-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#modalCliente"><i class="bi bi-person-plus"></i> Nuevo</button>
  </div>
</div>

<div class="card hp-card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Cliente</th><th class="d-none d-md-table-cell">Teléfono</th><th class="text-center">Pedidos</th><th class="text-end">Compras</th><th class="d-none d-lg-table-cell">Último pedido</th><th>Activo</th><th class="text-end">Acciones</th></tr></thead>
      <tbody>
      <?php foreach ($clientes as $c):
          $json = json_encode(array_intersect_key($c, array_flip(['id', 'nombre', 'apellido', 'correo', 'telefono', 'direccion'])));
      ?>
        <tr>
          <td>
            <div class="d-flex align-items-center gap-2">
              <span class="hp-avatar"><?= e(iniciales($c['nombre'], $c['apellido'])) ?></span>
              <div class="lh-sm"><a href="<?= url('clientes/ver', ['id' => $c['id']]) ?>" class="fw-bold text-decoration-none"><?= e($c['nombre'] . ' ' . $c['apellido']) ?></a><br>
                <small class="text-body-secondary"><?= e($c['correo']) ?></small></div>
            </div>
          </td>
          <td class="d-none d-md-table-cell"><?= e($c['telefono']) ?></td>
          <td class="text-center"><?= (int) $c['pedidos'] ?></td>
          <td class="text-end fw-semibold"><?= dinero($c['gastado']) ?></td>
          <td class="d-none d-lg-table-cell small"><?= $c['ultimo_pedido'] ? fecha($c['ultimo_pedido']) : '—' ?></td>
          <td>
            <div class="form-check form-switch m-0">
              <input class="form-check-input" type="checkbox" role="switch" aria-label="Activo"
                     data-interruptor="<?= e(url('clientes/estado')) ?>" data-id="<?= (int) $c['id'] ?>" <?= $c['activo'] ? 'checked' : '' ?>>
            </div>
          </td>
          <td class="text-end text-nowrap">
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalCliente" data-editar='<?= e($json) ?>' aria-label="Editar"><i class="bi bi-pencil"></i></button>
            <?php if (Auth::es(Auth::ADMIN) && !(int) $c['pedidos']): ?>
              <form method="post" action="<?= url('clientes/eliminar') ?>" class="d-inline" data-confirmar="¿Eliminar a <?= e($c['nombre']) ?>?">
                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                <button class="btn btn-sm btn-outline-danger" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$clientes): ?><tr><td colspan="7" class="text-center text-body-secondary py-4">No se encontraron clientes.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ============== MODAL CLIENTE ============== -->
<div class="modal fade" id="modalCliente" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content needs-validation" method="post" action="<?= url('clientes/guardar') ?>" novalidate
          data-titulo-nuevo="Nuevo cliente" data-titulo-editar="Editar cliente">
      <div class="modal-header">
        <h2 class="modal-title h5">Nuevo cliente</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <?= csrf_field() ?>
        <input type="hidden" name="id">
        <div class="row g-3">
          <div class="col-6"><label class="form-label">Nombre</label><input class="form-control" name="nombre" required minlength="2" maxlength="50"><div class="invalid-feedback">Obligatorio.</div></div>
          <div class="col-6"><label class="form-label">Apellido</label><input class="form-control" name="apellido" maxlength="50"></div>
          <div class="col-md-7"><label class="form-label">Correo</label><input type="email" class="form-control" name="correo" required maxlength="100"><div class="invalid-feedback">Correo no válido.</div></div>
          <div class="col-md-5"><label class="form-label">Teléfono</label><input class="form-control" name="telefono" required pattern="\d{7,15}" inputmode="numeric"><div class="invalid-feedback">Solo números.</div></div>
          <div class="col-12"><label class="form-label">Dirección</label><input class="form-control" name="direccion" maxlength="255"></div>
          <div class="col-12">
            <label class="form-label">Contraseña <small class="text-body-secondary" data-solo-edicion>(vacío = no cambiar)</small></label>
            <input type="password" class="form-control" name="contrasena" minlength="8" data-requerido-al-crear autocomplete="new-password">
            <div class="invalid-feedback">Mínimo 8 caracteres.</div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>
