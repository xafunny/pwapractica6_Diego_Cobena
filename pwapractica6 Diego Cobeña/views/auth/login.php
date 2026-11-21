<?php
/**
 * views/auth/login.php - Formulario de inicio de sesión.
 * Variables: $error (string), $correo (string)
 */
defined('APP') or exit('Acceso directo no permitido');
?>
<div class="hp-auth">
  <div class="hp-auth-portada d-none d-lg-flex">
    <div>
      <img src="<?= asset('img/logo.svg') ?>" alt="" width="84" height="84" class="mb-3">
      <h1 class="hp-titulo display-4">Heladería <span>Polar</span></h1>
      <p class="lead text-body-secondary">Pide tus helados favoritos en línea y recógelos en el local o recíbelos en casa.</p>
      <div class="d-flex flex-wrap gap-2 mt-4">
        <span class="hp-chip"><i class="bi bi-snow"></i> Helados artesanales</span>
        <span class="hp-chip"><i class="bi bi-bicycle"></i> Entrega a domicilio</span>
        <span class="hp-chip"><i class="bi bi-clock-history"></i> Sigue tu pedido</span>
      </div>
    </div>
  </div>

  <div class="card hp-card hp-auth-caja">
    <div class="card-body p-4 p-md-5">
      <div class="text-center d-lg-none mb-3"><img src="<?= asset('img/logo.svg') ?>" alt="" width="56" height="56"></div>
      <h2 class="h4 fw-bold mb-1">Iniciar sesión</h2>
      <p class="text-body-secondary small mb-4">Ingresa con tu correo y contraseña.</p>

      <?php if ($error): ?>
        <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-triangle"></i> <?= e($error) ?></div>
      <?php endif; ?>

      <form method="post" action="<?= url('auth/login') ?>" class="needs-validation" novalidate>
        <?= csrf_field() ?>
        <div class="mb-3">
          <label for="correo" class="form-label">Correo electrónico</label>
          <input type="email" class="form-control" id="correo" name="correo" value="<?= e($correo) ?>" required autofocus autocomplete="email">
          <div class="invalid-feedback">Ingresa un correo válido.</div>
        </div>
        <div class="mb-4">
          <label for="contrasena" class="form-label">Contraseña</label>
          <div class="input-group">
            <input type="password" class="form-control" id="contrasena" name="contrasena" required autocomplete="current-password">
            <button class="btn btn-outline-secondary" type="button" data-toggle-pass="contrasena" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
            <div class="invalid-feedback">Ingresa tu contraseña.</div>
          </div>
        </div>
        <button class="btn btn-primary w-100 py-2 fw-bold">Entrar</button>
      </form>

      <p class="text-center small mt-4 mb-0">¿Primera vez aquí? <a href="<?= url('auth/registro') ?>" class="fw-bold">Crea tu cuenta</a></p>
    </div>
  </div>
</div>
