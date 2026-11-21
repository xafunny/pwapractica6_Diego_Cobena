<?php
/** views/errores/404.php - Página no encontrada. */
defined('APP') or exit('Acceso directo no permitido');
?>
<div class="text-center py-5">
  <div class="hp-titulo" style="font-size:6rem;color:var(--hp-rosa)">404</div>
  <p class="lead">Este sabor no existe… la página que buscas no está aquí.</p>
  <a href="<?= url('inicio/index') ?>" class="btn btn-primary"><i class="bi bi-house"></i> Volver al inicio</a>
</div>
