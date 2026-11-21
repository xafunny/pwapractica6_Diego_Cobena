/**
 * assets/js/interruptores.js  -  Interruptores de activar / desactivar
 * ---------------------------------------------------------------------
 * Para cualquier <input type="checkbox" data-interruptor="url" data-id="n">:
 * al cambiarlo se envía el id a esa URL con fetch() y se muestra el
 * resultado. Si el servidor responde con error, se revierte el cambio.
 * Se usa en Productos (mostrar en tienda), Clientes y Personal.
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-interruptor]').forEach(sw => {
    sw.addEventListener('change', async () => {
      sw.disabled = true;
      try {
        const r = await HP.api(sw.dataset.interruptor, { id: sw.dataset.id });
        sw.checked = !!r.activo;
        sw.closest('tr, .card')?.classList.toggle('opacity-50', !r.activo);
        HP.toast(r.activo ? 'Activado.' : 'Desactivado.', r.activo ? 'success' : 'warning');
      } catch (err) {
        sw.checked = !sw.checked;
        HP.toast(err.message, 'danger');
      } finally {
        sw.disabled = false;
      }
    });
  });
});
