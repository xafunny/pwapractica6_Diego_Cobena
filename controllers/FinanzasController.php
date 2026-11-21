<?php
/**
 * ---------------------------------------------------------------------
 * controllers/FinanzasController.php    (solo ADMINISTRADOR)
 * ---------------------------------------------------------------------
 * Ingresos y gastos del local, por mes.
 *   finanzas/index     resumen del mes (?mes=YYYY-MM) + movimientos
 *   finanzas/guardar   registrar / editar un movimiento   [POST]
 *   finanzas/eliminar  eliminar un movimiento manual      [POST]
 * Los ingresos de pedidos entregados se registran solos y no se
 * pueden editar ni borrar desde aquí.
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class FinanzasController extends Controller
{
    public function index(): void
    {
        $this->soloRoles(Auth::ADMIN);
        $mes = is_string($_GET['mes'] ?? null) && preg_match('/^\d{4}-\d{2}$/', $_GET['mes']) ? $_GET['mes'] : date('Y-m');
        $this->view('finanzas/index', [
            'titulo'      => 'Finanzas',
            'mes'         => $mes,
            'movimientos' => Finanza::delMes($mes),
            'resumen'     => Finanza::resumen($mes),
        ]);
    }

    public function guardar(): void
    {
        $this->soloRoles(Auth::ADMIN);
        $this->soloPost();
        $id = post_int('id') ?? 0;
        $d = [
            'fecha'    => post('fecha'),
            'tipo'     => post('tipo'),
            'concepto' => post('concepto'),
            'monto'    => post_decimal('monto'),
        ];
        $mes = substr($d['fecha'], 0, 7);

        $err = null;
        if (!strtotime($d['fecha']) || $d['fecha'] > date('Y-m-d')) $err = 'Ingresa una fecha válida (no futura).';
        elseif (!in_array($d['tipo'], ['Ingreso', 'Gasto'], true)) $err = 'Selecciona si es ingreso o gasto.';
        elseif ($d['concepto'] === '' || mb_strlen($d['concepto']) > 120) $err = 'Escribe el concepto (máx. 120 caracteres).';
        elseif ($d['monto'] === null || $d['monto'] <= 0) $err = 'El monto debe ser mayor a 0.';
        if ($err) {
            flash('danger', $err);
            redirect('finanzas/index');
        }

        if ($id) {
            $m = Finanza::find($id);
            if ($m && !$m['id_pedido']) {
                Finanza::update($id, $d);
                flash('success', 'Movimiento actualizado.');
            }
        } else {
            Finanza::create($d + ['creado_por' => Auth::id()]);
            flash('success', $d['tipo'] . ' registrado.');
        }
        redirect('finanzas/index', ['mes' => $mes]);
    }

    public function eliminar(): void
    {
        $this->soloRoles(Auth::ADMIN);
        $this->soloPost();
        $m = Finanza::find(post_int('id') ?? 0);
        if ($m && $m['id_pedido']) {
            flash('danger', 'Este ingreso viene de un pedido entregado y no se puede eliminar.');
        } elseif ($m) {
            Finanza::delete((int) $m['id']);
            flash('success', 'Movimiento eliminado.');
        }
        redirect('finanzas/index', ['mes' => substr($m['fecha'] ?? date('Y-m'), 0, 7)]);
    }
}
