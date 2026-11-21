/**
 * assets/js/panel.js  -  Gráfica del panel del administrador (Chart.js)
 * ---------------------------------------------------------------------
 * Dibuja barras de ingresos y gastos de los últimos 7 días a partir de
 * window.HP_SERIE, que la vista genera en PHP con json_encode().
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
  const canvas = document.getElementById('graficaFinanzas');
  const serie = window.HP_SERIE || [];
  if (!canvas || !window.Chart) return;

  new Chart(canvas, {
    type: 'bar',
    data: {
      labels: serie.map(d => d.dia),
      datasets: [
        { label: 'Ingresos', data: serie.map(d => d.ingresos), backgroundColor: '#14b8a6', borderRadius: 6 },
        { label: 'Gastos',   data: serie.map(d => d.gastos),   backgroundColor: '#f472b6', borderRadius: 6 },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'bottom' },
        tooltip: { callbacks: { label: c => `${c.dataset.label}: ${HP.dinero(c.parsed.y)}` } },
      },
      scales: {
        y: { beginAtZero: true, ticks: { callback: v => '$' + v } },
        x: { grid: { display: false } },
      },
    },
  });
});
