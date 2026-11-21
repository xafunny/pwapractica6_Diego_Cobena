<?php
/**
 * ---------------------------------------------------------------------
 * views/layout/header.php
 * ---------------------------------------------------------------------
 * Encabezado común: <head>, librerías (Bootstrap 5, iconos, fuentes),
 * barra de navegación según el ROL y mensajes flash.
 * Variables: $titulo (string)
 * ---------------------------------------------------------------------
 */
defined('APP') or exit('Acceso directo no permitido');

$rutaActual = is_string($_GET['r'] ?? null) ? $_GET['r'] : '';
$usuario = Auth::user();

/** Devuelve 'active' si la ruta actual pertenece al módulo indicado. */
$activo = fn(string $modulo) => str_starts_with($rutaActual, $modulo . '/') ? 'active' : '';

// Menú según el rol: [módulo, ruta, icono, texto]
$menu = [];
if (Auth::es(Auth::ADMIN)) {
    $menu = [
        ['inicio', 'inicio/index', 'speedometer2', 'Panel'],
        ['pedidos', 'pedidos/index', 'receipt', 'Pedidos'],
        ['productos', 'productos/index', 'box-seam', 'Productos'],
        ['clientes', 'clientes/index', 'people', 'Clientes'],
        ['finanzas', 'finanzas/index', 'cash-coin', 'Finanzas'],
        ['personal', 'personal/index', 'person-badge', 'Personal'],
    ];
} elseif (Auth::es(Auth::EMPLEADO)) {
    $menu = [
        ['inicio', 'inicio/index', 'speedometer2', 'Panel'],
        ['pedidos', 'pedidos/index', 'receipt', 'Pedidos'],
        ['clientes', 'clientes/index', 'people', 'Clientes'],
    ];
} elseif (Auth::es(Auth::CLIENTE)) {
    $menu = [
        ['tienda', 'tienda/index', 'shop', 'Tienda'],
        ['pedidos', 'pedidos/index', 'receipt', 'Mis pedidos'],
    ];
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Heladería Polar - pedidos y gestión de la heladería">
  <!-- Token CSRF que usa assets/js/app.js en las peticiones fetch() -->
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
  <title><?= e($titulo) ?> · <?= e(APP_NAME) ?></title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600&family=Nunito:wght@400;600;700&display=swap">
  <link rel="stylesheet" href="<?= asset('css/styles.css') ?>">
  <link rel="icon" href="<?= asset('img/logo.svg') ?>" type="image/svg+xml">
</head>
<body>

<?php if ($usuario): ?>
<nav class="navbar navbar-expand-lg hp-navbar sticky-top">
  <div class="container">
    <a class="navbar-brand hp-marca" href="<?= url('inicio/index') ?>">
      <img src="<?= asset('img/logo.svg') ?>" alt="" width="34" height="34"> Heladería <span>Polar</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
            aria-controls="menuPrincipal" aria-expanded="false" aria-label="Abrir menú">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menuPrincipal">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php foreach ($menu as [$mod, $ruta, $icono, $texto]): ?>
          <li class="nav-item">
            <a class="nav-link <?= $activo($mod) ?>" href="<?= url($ruta) ?>"><i class="bi bi-<?= $icono ?>"></i> <?= $texto ?></a>
          </li>
        <?php endforeach; ?>
      </ul>

      <?php if (Auth::es(Auth::CLIENTE)): $unidades = Carrito::unidades(); ?>
        <a href="<?= url('tienda/carrito') ?>" class="btn hp-btn-carrito me-lg-3 mb-2 mb-lg-0" aria-label="Ver carrito">
          <i class="bi bi-cart3"></i> Carrito
          <span class="badge rounded-pill text-bg-light ms-1 <?= $unidades ? '' : 'd-none' ?>" id="contadorCarrito"><?= $unidades ?></span>
        </a>
      <?php endif; ?>

      <div class="dropdown">
        <button class="btn btn-link nav-link dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
          <span class="hp-avatar"><?= e(iniciales($usuario['nombre'], $usuario['apellido'])) ?></span>
          <span class="text-start lh-sm">
            <span class="d-block fw-bold"><?= e($usuario['nombre']) ?></span>
            <small class="text-body-secondary"><?= e($usuario['rol']) ?></small>
          </span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
          <li><a class="dropdown-item" href="<?= url('perfil/index') ?>"><i class="bi bi-person-gear"></i> Mi perfil</a></li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <form method="post" action="<?= url('auth/salir') ?>">
              <?= csrf_field() ?>
              <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</button>
            </form>
          </li>
        </ul>
      </div>
    </div>
  </div>
</nav>
<?php endif; ?>

<main class="container py-4 hp-main">
  <?php foreach (flash() as $f): ?>
    <div class="alert alert-<?= e($f['tipo']) ?> alert-dismissible fade show hp-flash" role="alert">
      <?= e($f['mensaje']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  <?php endforeach; ?>
