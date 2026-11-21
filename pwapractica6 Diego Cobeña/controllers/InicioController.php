<?php
/**
 * ---------------------------------------------------------------------
 * controllers/InicioController.php
 * ---------------------------------------------------------------------
 * Panel principal según el rol:
 *   - Administrador: ventas, pedidos, finanzas del mes, stock bajo
 *   - Empleado: pedidos por atender y stock bajo
 *   - Cliente: se le lleva directamente a la tienda
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class InicioController extends Controller
{
    public function index(): void
    {
        $this->soloRoles();

        if (Auth::es(Auth::CLIENTE)) {
            redirect('tienda/index');
        }

        [$ventasN, $ventasTotal] = Pedido::ventasHoy();
        $datos = [
            'titulo'     => 'Panel',
            'porEstado'  => Pedido::conteoPorEstado(),
            'stockBajo'  => Producto::stockBajo(),
            'activos'    => Pedido::listar(),
            'ventasN'    => $ventasN,
            'ventasTotal'=> $ventasTotal,
        ];

        if (Auth::es(Auth::ADMIN)) {
            $datos += [
                'resumen'     => Finanza::resumen(date('Y-m')),
                'serie'       => Finanza::porDia(7),
                'masVendidos' => Producto::masVendidos(),
                'clientes'    => Usuario::totalClientes(),
            ];
            $this->view('inicio/admin', $datos);
            return;
        }

        $this->view('inicio/empleado', $datos);
    }
}
