<?php
/**
 * views/perfil/index.php - Datos personales y cambio de contraseña.
 * Variables: $u (usuario completo)
 */
defined('APP') or exit('Acceso directo no permitido');
?>
<div class="hp-encabezado">
  <div class="d-flex align-items-center gap-3">
    <span class="hp-avatar hp-avatar-xl"><?= e(iniciales($u['nombre'], $u['apellido'])) ?></span>
    <div>
      <h1 class="hp-titulo mb-0"><?= e($u['nombre'] . ' ' . $u['apellido']) ?></h1>
      <p class="text-body-secondary mb-0"><?= e(Auth::user()['rol']) ?> · desde <?= fecha($u['creado_en']) ?></p>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="card hp-card">
      <div class="card-header fw-bold"><i class="bi bi-person-vcard"></i> Mis datos</div>
      <div class="card-body">
        <form method="post" action="<?= url('perfil/guardar') ?>" class="needs-validation" novalidate>
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Nombre</label>
              <input class="form-control" name="nombre" value="<?= e($u['nombre']) ?>" required minlength="2" maxlength="50"><div class="invalid-feedback">Obligatorio.</div></div>
            <div class="col-md-6"><label class="form-label">Apellido</label>
              <input class="form-control" name="apellido" value="<?= e($u['apellido']) ?>" maxlength="50"></div>
            <div class="col-md-7"><label class="form-label">Correo</label>
              <input type="email" class="form-control" name="correo" value="<?= e($u['correo']) ?>" required maxlength="100"><div class="invalid-feedback">Correo no válido.</div></div>
            <div class="col-md-5"><label class="form-label">Teléfono</label>
              <input class="form-control" name="telefono" value="<?= e($u['telefono']) ?>" pattern="\d{7,15}" inputmode="numeric"><div class="invalid-feedback">Solo números.</div></div>
            <div class="col-12"><label class="form-label">Dirección</label>
              <input class="form-control" name="direccion" value="<?= e($u['direccion']) ?>" maxlength="255"></div>
          </div>
          <button class="btn btn-primary mt-4"><i class="bi bi-save"></i> Guardar cambios</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card hp-card">
      <div class="card-header fw-bold"><i class="bi bi-shield-lock"></i> Cambiar contraseña</div>
      <div class="card-body">
        <form method="post" action="<?= url('perfil/contrasena') ?>" class="needs-validation" novalidate>
          <?= csrf_field() ?>
          <div class="mb-3"><label class="form-label">Contraseña actual</label>
            <input type="password" class="form-control" name="actual" required autocomplete="current-password"><div class="invalid-feedback">Obligatoria.</div></div>
          <div class="mb-3"><label class="form-label">Nueva contraseña</label>
            <input type="password" class="form-control" name="nueva" id="nuevaClave" required minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d).{8,}" autocomplete="new-password">
            <div class="invalid-feedback">Mínimo 8 caracteres, con letras y números.</div></div>
          <div class="mb-3"><label class="form-label">Repetir nueva contraseña</label>
            <input type="password" class="form-control" name="nueva2" required data-igual-a="nuevaClave" autocomplete="new-password"><div class="invalid-feedback">No coincide.</div></div>
          <button class="btn btn-outline-primary"><i class="bi bi-key"></i> Actualizar contraseña</button>
        </form>
      </div>
    </div>
  </div>
</div>
