/**
 * assets/js/pedidos.js  -  Cambiar el estado de los pedidos (Personal)
 * ---------------------------------------------------------------------
 * Cada pedido está en un contenedor [data-pedido="id"] con botones
 * [data-estado="Preparando|Listo|Entregado|Cancelado"].
 * Al pulsar se llama a pedidos/estado con fetch() y:
 *   - En el tablero: la tarjeta pasa a la columna del nuevo estado
 *     (o desaparece si se entregó / canceló) y se actualizan contadores.
 *   - En el detalle ([data-recargar]): se recarga la página.
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {

  document.querySelectorAll('[data-pedido]').forEach(contenedor => {
    contenedor.addEventListener('click', async e => {
      const btn = e.target.closest('button[data-estado]');
      if (!btn) return;
      const nuevo = btn.dataset.estado;
      const id = contenedor.dataset.pedido;

      if (nuevo === 'Cancelado' && !(await HP.confirmar(`¿Cancelar el pedido #${id}? Se devolverá el stock.`))) return;

      const botones = contenedor.querySelectorAll('button');
      botones.forEach(b => { b.disabled = true; });
      try {
        const r = await HP.api('index.php?r=pedidos/estado', { id, estado: nuevo });
        HP.toast(`Pedido #${id}: ${r.estado}`, nuevo === 'Cancelado' ? 'warning' : 'success');

        if (contenedor.hasAttribute('data-recargar')) {
          setTimeout(() => location.reload(), 500);
          return;
        }
        moverTarjeta(contenedor, r);
      } catch (err) {
        HP.toast(err.message, 'danger');
        botones.forEach(b => { b.disabled = false; });
      }
    });
  });

  /**
   * Coloca la tarjeta según su nuevo estado:
   *   - Entregado / Cancelado: se quita de la pantalla.
   *   - En el tablero: pasa a la columna del nuevo estado.
   *   - En el panel del empleado (sin columnas): se queda en su lugar.
   */
  function moverTarjeta(tarjeta, r) {
    const terminado = !r.siguiente;
    const destino = document.querySelector(`[data-columna="${r.estado}"] [data-lista]`);
    tarjeta.classList.add('hp-salida');
    setTimeout(() => {
      if (terminado) {
        (tarjeta.closest('.col-md-6') || tarjeta).remove();
      } else {
        // El botón principal pasa al siguiente estado del flujo.
        const principal = tarjeta.querySelector('button.btn-primary');
        principal.dataset.estado = r.siguiente;
        principal.innerHTML = `<i class="bi bi-arrow-right-circle"></i> ${r.siguiente}`;
        tarjeta.querySelectorAll('button').forEach(b => { b.disabled = false; });
        tarjeta.classList.remove('hp-salida');
        if (destino) destino.prepend(tarjeta);
      }
      actualizarContadores();
    }, 250);
  }

  /** Recalcula el número de tarjetas de cada columna del tablero. */
  function actualizarContadores() {
    document.querySelectorAll('[data-columna]').forEach(col => {
      const n = col.querySelectorAll('[data-pedido]').length;
      col.querySelector('[data-contador]').textContent = n;
    });
  }
});
