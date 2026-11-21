<?php
/**
 * views/tienda/index.php - Catálogo para el CLIENTE.
 * Cada producto tiene un selector de cantidad; los botones + / − y
 * "Agregar" actualizan el carrito en la sesión con fetch() (tienda.js)
 * sin recargar la página.
 * Variables: $productos, $categorias, $categoria (filtro), $enCarrito [id => cantidad]
 */
defined('APP') or exit('Acceso directo no permitido');
$scripts = ['tienda.js'];
?>
<div class="hp-hero mb-4">
  <div>
    <h1 class="hp-titulo">¿Qué se te antoja hoy?</h1>
    <p class="mb-0">Elige tus sabores, agrégalos al carrito y confirma tu pedido.</p>
  </div>
  <input type="search" class="form-control hp-buscar" id="buscarProducto" placeholder="Buscar sabor…" aria-label="Buscar producto">
</div>

<div class="d-flex flex-wrap gap-2 mb-4">
  <a href="<?= url('tienda/index') ?>" class="btn btn-sm <?= $categoria === 0 ? 'btn-primary' : 'btn-outline-primary' ?>">Todo</a>
  <?php foreach ($categorias as $c): ?>
    <a href="<?= url('tienda/index', ['categoria' => $c['id']]) ?>" class="btn btn-sm <?= $categoria === (int) $c['id'] ? 'btn-primary' : 'btn-outline-primary' ?>">
      <i class="bi bi-<?= e($c['icono']) ?>"></i> <?= e($c['nombre']) ?></a>
  <?php endforeach; ?>
</div>

<div class="row g-3" id="catalogo">
  <?php foreach ($productos as $p):
      $cant = $enCarrito[$p['id']] ?? 0;
      $agotado = (int) $p['stock'] === 0;
  ?>
    <div class="col-sm-6 col-lg-4 col-xl-3" data-buscar="<?= e(mb_strtolower($p['nombre'] . ' ' . $p['descripcion'] . ' ' . $p['categoria'])) ?>">
      <div class="card hp-card hp-producto h-100 <?= $agotado ? 'hp-agotado' : '' ?>" data-producto="<?= (int) $p['id'] ?>" data-stock="<?= (int) $p['stock'] ?>">
        <div class="hp-producto-icono"><i class="bi bi-<?= e($p['icono']) ?>"></i></div>
        <div class="card-body d-flex flex-column">
          <span class="hp-chip align-self-start mb-2"><?= e($p['categoria']) ?></span>
          <h2 class="h6 fw-bold mb-1"><?= e($p['nombre']) ?></h2>
          <p class="small text-body-secondary flex-grow-1"><?= e($p['descripcion']) ?></p>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="hp-precio"><?= dinero($p['precio']) ?></span>
            <small class="<?= $p['stock'] <= Producto::STOCK_BAJO ? 'text-danger fw-bold' : 'text-body-secondary' ?>">
              <?= $agotado ? 'Agotado' : ($p['stock'] <= Producto::STOCK_BAJO ? '¡Quedan ' . (int) $p['stock'] . '!' : 'Disponible') ?></small>
          </div>
          <?php if (!$agotado): ?>
            <!-- Se muestra uno u otro según si ya está en el carrito -->
            <button class="btn btn-primary w-100 hp-agregar <?= $cant ? 'd-none' : '' ?>"><i class="bi bi-cart-plus"></i> Agregar</button>
            <div class="input-group hp-cantidad <?= $cant ? '' : 'd-none' ?>">
              <button class="btn btn-outline-primary" data-delta="-1" aria-label="Quitar uno"><i class="bi bi-dash-lg"></i></button>
              <span class="form-control text-center fw-bold" data-cantidad><?= (int) $cant ?></span>
              <button class="btn btn-outline-primary" data-delta="1" aria-label="Agregar uno"><i class="bi bi-plus-lg"></i></button>
            </div>
          <?php else: ?>
            <button class="btn btn-secondary w-100" disabled>Agotado</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$productos): ?>
    <div class="col-12"><div class="hp-vacio"><i class="bi bi-snow"></i><p>No hay productos en esta categoría.</p></div></div>
  <?php endif; ?>
</div>
<p class="text-center text-body-secondary d-none mt-4" id="sinResultados">Ningún producto coincide con la búsqueda.</p>

<!-- Barra flotante con el total del carrito -->
<a href="<?= url('tienda/carrito') ?>" class="hp-barra-carrito <?= $enCarrito ? '' : 'd-none' ?>" id="barraCarrito">
  <span><i class="bi bi-cart3"></i> <span id="barraUnidades"><?= array_sum($enCarrito) ?></span> producto(s)</span>
  <span>Ver carrito <i class="bi bi-arrow-right"></i></span>
</a>

<script>window.HP_TIENDA = { url: <?= json_encode(url('tienda/poner')) ?> };</script>
