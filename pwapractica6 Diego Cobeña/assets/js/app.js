/**
 * =====================================================================
 * assets/js/app.js  -  JavaScript común a todas las páginas
 * =====================================================================
 * Objeto global HP con utilidades:
 *   HP.api(url, datos)   POST con fetch() + token CSRF; devuelve el JSON
 *   HP.toast(msg, tipo)  notificación flotante de Bootstrap
 *   HP.confirmar(msg)    ventana de confirmación (Promise<boolean>)
 *   HP.dinero(n)         formatea 3.5 -> "$3.50"
 *
 * Comportamientos activados con atributos data-*:
 *   form.needs-validation   validación de Bootstrap antes de enviar
 *   [data-igual-a="id"]     el campo debe coincidir con otro (contraseñas)
 *   [data-toggle-pass="id"] mostrar / ocultar contraseña
 *   form[data-confirmar]    pide confirmación antes de enviar
 *   [data-editar='{json}']  llena el formulario del modal para editar
 * =====================================================================
 */
'use strict';

const HP = (() => {
  /** Token CSRF publicado por el servidor en <meta name="csrf-token">. */
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

  /** Envía datos por POST y devuelve el JSON; lanza Error si falla. */
  async function api(url, datos = {}) {
    const cuerpo = new FormData();
    Object.entries(datos).forEach(([k, v]) => { if (v !== null && v !== undefined) cuerpo.append(k, v); });
    let respuesta;
    try {
      respuesta = await fetch(url, {
        method: 'POST',
        body: cuerpo,
        headers: { 'Accept': 'application/json', 'X-CSRF-Token': csrf },
        credentials: 'same-origin',
      });
    } catch {
      throw new Error('Sin conexión con el servidor.');
    }
    const json = await respuesta.json().catch(() => ({}));
    if (!respuesta.ok || !json.ok) {
      if (respuesta.status === 401) setTimeout(() => location.reload(), 1200); // sesión expirada
      throw new Error(json.error || 'Ocurrió un error inesperado.');
    }
    return json;
  }

  /** Notificación (toast) en la esquina inferior derecha. */
  function toast(mensaje, tipo = 'success') {
    const iconos = { success: 'check-circle', danger: 'exclamation-triangle', info: 'info-circle', warning: 'exclamation-circle' };
    const el = document.createElement('div');
    el.className = `toast align-items-center text-bg-${tipo} border-0`;
    el.setAttribute('role', 'status');
    el.innerHTML = `<div class="d-flex"><div class="toast-body"><i class="bi bi-${iconos[tipo] || 'info-circle'} me-1"></i></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button></div>`;
    el.querySelector('.toast-body').append(mensaje); // texto plano, sin HTML
    document.getElementById('toasts').append(el);
    const t = new bootstrap.Toast(el, { delay: 3000 });
    el.addEventListener('hidden.bs.toast', () => el.remove());
    t.show();
  }

  /** Ventana de confirmación con estilo Bootstrap. */
  function confirmar(mensaje) {
    return new Promise(resolver => {
      const el = document.createElement('div');
      el.className = 'modal fade';
      el.tabIndex = -1;
      el.innerHTML = `<div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content">
          <div class="modal-body py-4 text-center"><i class="bi bi-question-circle fs-2 d-block mb-2 text-primary"></i><p class="mb-0"></p></div>
          <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">No</button>
            <button type="button" class="btn btn-primary" data-ok>Sí, continuar</button>
          </div></div></div>`;
      el.querySelector('p').textContent = mensaje;
      document.body.append(el);
      const modal = new bootstrap.Modal(el);
      let aceptado = false;
      el.querySelector('[data-ok]').addEventListener('click', () => { aceptado = true; modal.hide(); });
      el.addEventListener('hidden.bs.modal', () => { el.remove(); resolver(aceptado); });
      modal.show();
    });
  }

  const formatoDinero = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });
  const dinero = n => formatoDinero.format(n);

  return { api, toast, confirmar, dinero };
})();

document.addEventListener('DOMContentLoaded', () => {

  /* ---------- Validación de formularios (Bootstrap) ---------- */
  document.querySelectorAll('form.needs-validation').forEach(form => {
    form.addEventListener('submit', e => {
      form.querySelectorAll('[data-igual-a]').forEach(campo => {
        const otro = document.getElementById(campo.dataset.igualA);
        campo.setCustomValidity(otro && campo.value !== otro.value ? 'No coincide' : '');
      });
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
        form.querySelector(':invalid')?.focus();
      }
      form.classList.add('was-validated');
    });
  });

  /* ---------- Mostrar / ocultar contraseña ---------- */
  document.querySelectorAll('[data-toggle-pass]').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = document.getElementById(btn.dataset.togglePass);
      const mostrar = input.type === 'password';
      input.type = mostrar ? 'text' : 'password';
      btn.innerHTML = `<i class="bi bi-eye${mostrar ? '-slash' : ''}"></i>`;
    });
  });

  /* ---------- Confirmación antes de enviar ---------- */
  document.querySelectorAll('form[data-confirmar]').forEach(form => {
    form.addEventListener('submit', async e => {
      if (form.dataset.confirmado) return;
      e.preventDefault();
      if (await HP.confirmar(form.dataset.confirmar)) {
        form.dataset.confirmado = '1';
        form.submit();
      }
    });
  });

  /* ---------- Modales de crear / editar ----------
   * Si el botón que abre el modal trae data-editar='{json}', se llenan
   * los campos por su "name"; si no, el formulario se limpia. */
  document.querySelectorAll('.modal').forEach(modal => {
    const form = modal.querySelector('form[data-titulo-nuevo]');
    if (!form) return;
    modal.addEventListener('show.bs.modal', e => {
      const datos = e.relatedTarget?.dataset.editar ? JSON.parse(e.relatedTarget.dataset.editar) : null;
      const editando = !!datos;

      form.classList.remove('was-validated');
      form.querySelectorAll('input, select, textarea').forEach(campo => {
        if (campo.name === 'csrf') return;
        if (campo.tagName === 'SELECT') campo.selectedIndex = 0;
        else if (campo.type !== 'checkbox' && campo.type !== 'radio') campo.value = campo.defaultValue;
      });

      if (editando) {
        Object.entries(datos).forEach(([nombre, valor]) => {
          const campo = form.elements[nombre];
          if (campo) campo.value = valor ?? '';
        });
      } else {
        form.querySelectorAll('[data-hoy]').forEach(c => { c.value = new Date().toLocaleDateString('en-CA'); });
      }

      form.querySelectorAll('[data-requerido-al-crear]').forEach(c => { c.required = !editando; });
      form.querySelectorAll('[data-solo-edicion]').forEach(c => c.classList.toggle('d-none', !editando));
      modal.querySelector('.modal-title').textContent = editando ? form.dataset.tituloEditar : form.dataset.tituloNuevo;
    });
    modal.addEventListener('shown.bs.modal', () => {
      const visibles = [...form.querySelectorAll('input:not([type=hidden]), textarea')].filter(el => el.offsetParent !== null);
      (visibles[0] || form.querySelector('select'))?.focus();
    });
  });

  /* ---------- Las alertas flash se cierran solas ---------- */
  document.querySelectorAll('.hp-flash').forEach(alerta => {
    setTimeout(() => bootstrap.Alert.getOrCreateInstance(alerta).close(), 5000);
  });
});
