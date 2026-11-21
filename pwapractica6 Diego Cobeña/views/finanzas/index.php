<?php
/**
 * views/finanzas/index.php - Ingresos, gastos y balance del mes (ADMINISTRADOR).
 * Variables: $mes ('YYYY-MM'), $movimientos, $resumen ['ingresos','gastos','balance']
 */
defined('APP') or exit('Acceso directo no permitido');
[$anio, $m] = array_map('intval', explode('-', $mes));
$anterior = date('Y-m', mktime(0, 0, 0, $m - 1, 1, $anio));
$siguiente = date('Y-m', mktime(0, 0, 0, $m + 1, 1, $anio));
$esActual = $mes === date('Y-m');
?>
<div class="hp-encabezado">
  <div>
    <h1 class="hp-titulo">Finanzas</h1>
    <div class="d-flex align-items-center gap-2">
      <a href="<?= url('finanzas/index', ['mes' => $anterior]) ?>" class="btn btn-sm btn-outline-secondary" aria-label="Mes anterior"><i class="bi bi-chevron-left"></i></a>
      <span class="fw-bold"><?= nombre_mes($m) ?> <?= $anio ?></span>
      <?php if (!$esActual): ?>
        <a href="<?= url('finanzas/index', ['mes' => $siguiente]) ?>" class="btn btn-sm btn-outline-secondary" aria-label="Mes siguiente"><i class="bi bi-chevron-right"></i></a>
      <?php endif; ?>
    </div>
  </div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMovimiento"><i class="bi bi-plus-lg"></i> Registrar movimiento</button>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4"><div class="card hp-card h-100"><div class="card-body">
    <div class="hp-stat-etiqueta">Ingresos</div><div class="hp-stat-valor text-success"><?= dinero($resumen['ingresos']) ?></div>
  </div></div></div>
  <div class="col-md-4"><div class="card hp-card h-100"><div class="card-body">
    <div class="hp-stat-etiqueta">Gastos</div><div class="hp-stat-valor text-danger"><?= dinero($resumen['gastos']) ?></div>
  </div></div></div>
  <div class="col-md-4"><div class="card hp-card h-100 hp-balance"><div class="card-body">
    <div class="hp-stat-etiqueta">Balance</div><div class="hp-stat-valor"><?= dinero($resumen['balance']) ?></div>
  </div></div></div>
</div>

<div class="card hp-card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Fecha</th><th>Concepto</th><th>Tipo</th><th class="text-end">Monto</th><th class="d-none d-md-table-cell">Registró</th><th class="text-end"></th></tr></thead>
      <tbody>
      <?php foreach ($movimientos as $f): $auto = (bool) $f['id_pedido']; ?>
        <tr>
          <td class="text-nowrap"><?= fecha($f['fecha']) ?></td>
          <td>
            <?= e($f['concepto']) ?>
            <?php if ($auto): ?><a href="<?= url('pedidos/ver', ['id' => $f['id_pedido']]) ?>" class="badge text-bg-light text-decoration-none">automático</a><?php endif; ?>
          </td>
          <td><span class="badge <?= $f['tipo'] === 'Ingreso' ? 'text-bg-success' : 'text-bg-danger' ?>"><?= e($f['tipo']) ?></span></td>
          <td class="text-end fw-semibold <?= $f['tipo'] === 'Gasto' ? 'text-danger' : '' ?>"><?= ($f['tipo'] === 'Gasto' ? '−' : '') . dinero($f['monto']) ?></td>
          <td class="d-none d-md-table-cell small"><?= e($f['registrado_por']) ?></td>
          <td class="text-end text-nowrap">
            <?php if (!$auto): ?>
              <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalMovimiento"
                      data-editar='<?= e(json_encode(array_intersect_key($f, array_flip(['id', 'fecha', 'tipo', 'concepto', 'monto'])))) ?>' aria-label="Editar"><i class="bi bi-pencil"></i></button>
              <form method="post" action="<?= url('finanzas/eliminar') ?>" class="d-inline" data-confirmar="¿Eliminar «<?= e($f['concepto']) ?>»?">
                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                <button class="btn btn-sm btn-outline-danger" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$movimientos): ?><tr><td colspan="6" class="text-center text-body-secondary py-4">Sin movimientos en este mes.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ============== MODAL MOVIMIENTO ============== -->
<div class="modal fade" id="modalMovimiento" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content needs-validation" method="post" action="<?= url('finanzas/guardar') ?>" novalidate
          data-titulo-nuevo="Registrar movimiento" data-titulo-editar="Editar movimiento">
      <div class="modal-header">
        <h2 class="modal-title h5">Registrar movimiento</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <?= csrf_field() ?>
        <input type="hidden" name="id">
        <div class="row g-3">
          <div class="col-6">
            <label class="form-label">Tipo</label>
            <select class="form-select" name="tipo" required><option>Gasto</option><option>Ingreso</option></select>
          </div>
          <div class="col-6">
            <label class="form-label">Fecha</label>
            <input type="date" class="form-control" name="fecha" required max="<?= date('Y-m-d') ?>" data-hoy>
            <div class="invalid-feedback">Fecha no válida.</div>
          </div>
          <div class="col-8">
            <label class="form-label">Concepto</label>
            <input class="form-control" name="concepto" required maxlength="120" list="conceptos">
            <datalist id="conceptos"><option>Compra de insumos</option><option>Pago de luz</option><option>Pago de agua</option><option>Sueldos</option><option>Ventas en mostrador</option></datalist>
            <div class="invalid-feedback">Obligatorio.</div>
          </div>
          <div class="col-4">
            <label class="form-label">Monto ($)</label>
            <input type="number" class="form-control" name="monto" required min="0.01" step="0.01">
            <div class="invalid-feedback">Mayor a 0.</div>
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
