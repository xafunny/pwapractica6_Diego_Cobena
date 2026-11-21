<?php
/**
 * views/personal/index.php - Cuentas del personal (ADMINISTRADOR).
 * Variables: $personal (con pedidos_atendidos)
 */
defined('APP') or exit('Acceso directo no permitido');
$scripts = ['interruptores.js'];
?>
<div class="hp-encabezado">
  <div>
    <h1 class="hp-titulo">Personal</h1>
    <p class="text-body-secondary mb-0">Administradores y empleados que atienden los pedidos.</p>
  </div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPersonal"><i class="bi bi-person-plus"></i> Nueva cuenta</button>
</div>

<div class="row g-3">
  <?php foreach ($personal as $u):
      $esYo = (int) $u['id'] === Auth::id();
      $json = json_encode(array_intersect_key($u, array_flip(['id', 'nombre', 'apellido', 'correo', 'telefono', 'id_rol'])));
  ?>
    <div class="col-md-6 col-xl-4">
      <div class="card hp-card h-100 <?= $u['activo'] ? '' : 'opacity-50' ?>">
        <div class="card-body d-flex gap-3">
          <span class="hp-avatar hp-avatar-lg"><?= e(iniciales($u['nombre'], $u['apellido'])) ?></span>
          <div class="flex-grow-1">
            <div class="fw-bold"><?= e($u['nombre'] . ' ' . $u['apellido']) ?><?= $esYo ? ' <small class="text-body-secondary">(tú)</small>' : '' ?></div>
            <span class="badge <?= (int) $u['id_rol'] === Auth::ADMIN ? 'text-bg-primary' : 'text-bg-info' ?>"><?= e($u['rol']) ?></span>
            <div class="small text-body-secondary mt-2"><?= e($u['correo']) ?><br><?= e($u['telefono']) ?></div>
            <div class="small mt-1"><?= (int) $u['pedidos_atendidos'] ?> pedido(s) atendidos</div>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
          <div class="form-check form-switch m-0">
            <input class="form-check-input" type="checkbox" role="switch" id="act<?= (int) $u['id'] ?>"
                   data-interruptor="<?= e(url('personal/estado')) ?>" data-id="<?= (int) $u['id'] ?>" <?= $u['activo'] ? 'checked' : '' ?> <?= $esYo ? 'disabled' : '' ?>>
            <label class="form-check-label small" for="act<?= (int) $u['id'] ?>">Activo</label>
          </div>
          <div class="d-flex gap-1">
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalPersonal" data-editar='<?= e($json) ?>' aria-label="Editar"><i class="bi bi-pencil"></i></button>
            <?php if (!$esYo): ?>
              <form method="post" action="<?= url('personal/eliminar') ?>" data-confirmar="¿Eliminar la cuenta de <?= e($u['nombre']) ?>?">
                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                <button class="btn btn-sm btn-outline-danger" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- ============== MODAL PERSONAL ============== -->
<div class="modal fade" id="modalPersonal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content needs-validation" method="post" action="<?= url('personal/guardar') ?>" novalidate
          data-titulo-nuevo="Nueva cuenta" data-titulo-editar="Editar cuenta">
      <div class="modal-header">
        <h2 class="modal-title h5">Nueva cuenta</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <?= csrf_field() ?>
        <input type="hidden" name="id">
        <div class="row g-3">
          <div class="col-6"><label class="form-label">Nombre</label><input class="form-control" name="nombre" required minlength="2" maxlength="50"><div class="invalid-feedback">Obligatorio.</div></div>
          <div class="col-6"><label class="form-label">Apellido</label><input class="form-control" name="apellido" maxlength="50"></div>
          <div class="col-md-7"><label class="form-label">Correo</label><input type="email" class="form-control" name="correo" required maxlength="100"><div class="invalid-feedback">Correo no válido.</div></div>
          <div class="col-md-5"><label class="form-label">Teléfono</label><input class="form-control" name="telefono" pattern="\d{7,15}" inputmode="numeric"><div class="invalid-feedback">Solo números.</div></div>
          <div class="col-md-5">
            <label class="form-label">Rol</label>
            <select class="form-select" name="id_rol" required>
              <option value="<?= Auth::EMPLEADO ?>">Empleado</option>
              <option value="<?= Auth::ADMIN ?>">Administrador</option>
            </select>
          </div>
          <div class="col-md-7">
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
