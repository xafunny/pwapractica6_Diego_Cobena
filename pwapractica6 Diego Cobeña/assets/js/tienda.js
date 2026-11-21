/**
 * assets/js/tienda.js  -  Catálogo y carrito (Cliente)
 * ---------------------------------------------------------------------
 * Funciona en dos páginas:
 *   - Catálogo (tienda/index): botón "Agregar" y controles + / −.
 *   - Carrito  (tienda/carrito): + / − por línea, subtotal y total.
 * Cada cambio se envía a tienda/poner con fetch(); el servidor limita la
 * cantidad al stock y devuelve los totales recalculados, que se pintan
 * en pantalla (contador del navbar incluido).
 * Usa window.HP_TIENDA = { url } definido en la vista.
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
  const cfg = window.HP_TIENDA;
  if (!cfg) return;

  /** Pide al servidor fijar la cantidad y actualiza la pantalla. */
  async function poner(contenedor, cantidad) {
    const botones = contenedor.querySelectorAll('button');
    botones.forEach(b => { b.disabled = true; });
    try {
      const r = await HP.api(cfg.url, { producto: contenedor.dataset.producto, cantidad });
      pintar(contenedor, r);
      if (r.aviso) HP.toast(r.aviso, 'warning');
      return r;
    } catch (err) {
      HP.toast(err.message, 'danger');
    } finally {
      botones.forEach(b => { b.disabled = false; });
    }
  }

  /** Refleja la respuesta del servidor en la tarjeta / línea y los totales. */
  function pintar(contenedor, r) {
    const span = contenedor.querySelector('[data-cantidad]');
    if (span) span.textContent = r.cantidad;

    // Catálogo: alternar entre botón "Agregar" y controles de cantidad.
    contenedor.querySelector('.hp-agregar')?.classList.toggle('d-none', r.cantidad > 0);
    if (contenedor.querySelector('.hp-agregar')) {
      contenedor.querySelector('.hp-cantidad').classList.toggle('d-none', r.cantidad === 0);
    }

    // Carrito: subtotal de la línea, quitar la línea en 0 y total general.
    const subtotal = contenedor.querySelector('[data-subtotal]');
    if (subtotal) subtotal.textContent = HP.dinero(r.subtotal);
    if (contenedor.tagName === 'LI' && r.cantidad === 0) {
      contenedor.classList.add('hp-salida');
      setTimeout(() => {
        contenedor.remove();
        if (!document.querySelector('#listaCarrito li')) location.reload(); // muestra "carrito vacío"
      }, 250);
    }
    const total = document.getElementById('totalCarrito');
    if (total) total.textContent = HP.dinero(r.total);

    // Contador del navbar y barra flotante del catálogo.
    const contador = document.getElementById('contadorCarrito');
    if (contador) {
      contador.textContent = r.unidades;
      contador.classList.toggle('d-none', r.unidades === 0);
      contador.classList.remove('hp-rebote');
      void contador.offsetWidth; // reinicia la animación
      contador.classList.add('hp-rebote');
    }
    const barra = document.getElementById('barraCarrito');
    if (barra) {
      barra.classList.toggle('d-none', r.unidades === 0);
      document.getElementById('barraUnidades').textContent = r.unidades;
    }
  }

  // Botón "Agregar" del catálogo.
  document.querySelectorAll('.hp-agregar').forEach(btn => {
    btn.addEventListener('click', async () => {
      const tarjeta = btn.closest('[data-producto]');
      const r = await poner(tarjeta, 1);
      if (r && r.cantidad > 0) HP.toast('Agregado al carrito');
    });
  });

  // Botones + / − (catálogo y carrito).
  document.querySelectorAll('[data-delta]').forEach(btn => {
    btn.addEventListener('click', () => {
      const cont = btn.closest('[data-producto]');
      const actual = parseInt(cont.querySelector('[data-cantidad]').textContent, 10) || 0;
      poner(cont, Math.max(0, actual + parseInt(btn.dataset.delta, 10)));
    });
  });

  // Búsqueda instantánea en el catálogo.
  const buscador = document.getElementById('buscarProducto');
  buscador?.addEventListener('input', () => {
    const q = buscador.value.trim().toLowerCase();
    let visibles = 0;
    document.querySelectorAll('#catalogo [data-buscar]').forEach(col => {
      const ok = col.dataset.buscar.includes(q);
      col.classList.toggle('d-none', !ok);
      if (ok) visibles++;
    });
    document.getElementById('sinResultados').classList.toggle('d-none', visibles > 0);
  });

  // Carrito: la dirección solo se pide para entregas a domicilio.
  const direccion = document.getElementById('direccion');
  document.querySelectorAll('input[name="tipo_entrega"]').forEach(radio => {
    radio.addEventListener('change', () => {
      const domicilio = document.getElementById('entregaDomicilio').checked;
      document.getElementById('grupoDireccion').classList.toggle('d-none', !domicilio);
      direccion.required = domicilio;
      if (domicilio) direccion.focus();
    });
  });
});
