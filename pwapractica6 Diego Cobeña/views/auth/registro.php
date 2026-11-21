<?php
/**
 * views/auth/registro.php - Registro público de CLIENTES.
 * Variables: $errores (array), $d (datos del formulario)
 */
defined('APP') or exit('Acceso directo no permitido');
?>
<div class="row justify-content-center">
  <div class="col-lg-7 col-xl-6">
    <div class="card hp-card">
      <div class="card-body p-4 p-md-5">
        <a href="<?= url('auth/login') ?>" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Volver</a>
        <h1 class="h4 fw-bold mt-2">Crear cuenta</h1>
        <p class="text-body-secondary small mb-4">Regístrate para hacer pedidos en Heladería Polar.</p>

        <?php if ($errores): ?>
          <div class="alert alert-danger small">
            <ul class="mb-0 ps-3"><?php foreach ($errores as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
          </div>
        <?php endif; ?>

        <form method="post" action="<?= url('auth/registro') ?>" class="needs-validation" novalidate>
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="nombre">Nombre</label>
              <input class="form-control" id="nombre" name="nombre" value="<?= e($d['nombre']) ?>" required minlength="2" maxlength="50">
              <div class="invalid-feedback">Ingresa tu nombre.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="apellido">Apellido</label>
              <input class="form-control" id="apellido" name="apellido" value="<?= e($d['apellido']) ?>" required minlength="2" maxlength="50">
              <div class="invalid-feedback">Ingresa tu apellido.</div>
            </div>
            <div class="col-md-7">
              <label class="form-label" for="correo">Correo</label>
              <input type="email" class="form-control" id="correo" name="correo" value="<?= e($d['correo']) ?>" required maxlength="100">
              <div class="invalid-feedback">Correo no válido.</div>
            </div>
            <div class="col-md-5">
              <label class="form-label" for="telefono">Teléfono</label>
              <input class="form-control" id="telefono" name="telefono" value="<?= e($d['telefono']) ?>" required pattern="\d{7,15}" inputmode="numeric">
              <div class="invalid-feedback">Solo números (7 a 15 dígitos).</div>
            </div>
            <div class="col-12">
              <label class="form-label" for="direccion">Dirección <span class="text-body-secondary">(para entregas a domicilio)</span></label>
              <input class="form-control" id="direccion" name="direccion" value="<?= e($d['direccion']) ?>" maxlength="255">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="contrasena">Contraseña</label>
              <input type="password" class="form-control" id="contrasena" name="contrasena" required minlength="8"
                     pattern="(?=.*[A-Za-z])(?=.*\d).{8,}" autocomplete="new-password">
              <div class="form-text">Mínimo 8 caracteres, con letras y números.</div>
              <div class="invalid-feedback">Mínimo 8 caracteres, con letras y números.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="contrasena2">Repetir contraseña</label>
              <input type="password" class="form-control" id="contrasena2" name="contrasena2" required data-igual-a="contrasena" autocomplete="new-password">
              <div class="invalid-feedback">Las contraseñas no coinciden.</div>
            </div>
          </div>
          <button class="btn btn-primary w-100 py-2 fw-bold mt-4">Crear cuenta</button>
        </form>
      </div>
    </div>
  </div>
</div>
