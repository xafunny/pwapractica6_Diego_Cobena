<?php
/**
 * ---------------------------------------------------------------------
 * controllers/PedidosController.php
 * ---------------------------------------------------------------------
 *   pedidos/index     Cliente: sus pedidos | Personal: tablero por estado
 *   pedidos/ver       detalle de un pedido (el cliente solo los suyos)
 *   pedidos/estado    avanzar o cancelar un pedido (personal)  [POST JSON]
 *   pedidos/cancelar  el cliente cancela su pedido pendiente   [POST]
 *   pedidos/eliminar  eliminar un pedido (solo administrador)  [POST]
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class PedidosController extends Controller
{
    public function index(): void
    {
        $this->soloRoles();

        if (Auth::es(Auth::CLIENTE)) {
            $this->view('pedidos/mios', [
                'titulo'  => 'Mis pedidos',
                'pedidos' => Pedido::deCliente(Auth::id()),
            ]);
            return;
        }

        $estados = array_merge(['', 'todos'], array_keys(ESTADOS_PEDIDO));
        $pedido = (string) ($_GET['estado'] ?? '');
        $estado = in_array($pedido, $estados, true) ? $pedido : '';
        $this->view('pedidos/index', [
            'titulo'    => 'Pedidos',
            'pedidos'   => Pedido::listar($estado),
            'estado'    => $estado,
            'porEstado' => Pedido::conteoPorEstado(),
        ]);
    }

    public function ver(): void
    {
        $this->soloRoles();
        $p = Pedido::detalle((int) ($_GET['id'] ?? 0));
        if (!$p || (Auth::es(Auth::CLIENTE) && (int) $p['id_cliente'] !== Auth::id())) {
            flash('danger', 'Pedido no encontrado.');
            redirect('pedidos/index');
        }
        $this->view('pedidos/ver', [
            'titulo' => 'Pedido #' . $p['id'],
            'pedido' => $p,
            'lineas' => Pedido::lineas((int) $p['id']),
        ]);
    }

    public function estado(): void
    {
        $this->soloRoles(Auth::ADMIN, Auth::EMPLEADO);
        $this->soloPost();
        $id = post_int('id') ?? 0;
        $nuevo = post('estado');
        if (!isset(ESTADOS_PEDIDO[$nuevo])) {
            $this->json(['ok' => false, 'error' => 'Estado no válido.'], 400);
        }
        $error = Pedido::cambiarEstado($id, $nuevo, Auth::id());
        if ($error) $this->json(['ok' => false, 'error' => $error], 409);

        // Siguiente estado posible (para actualizar el botón en pantalla).
        $i = array_search($nuevo, Pedido::FLUJO, true);
        $siguiente = ($i !== false && $i < count(Pedido::FLUJO) - 1) ? Pedido::FLUJO[$i + 1] : null;
        $this->json(['ok' => true, 'estado' => $nuevo, 'badge' => badge_estado($nuevo), 'siguiente' => $siguiente]);
    }

    public function cancelar(): void
    {
        $this->soloRoles(Auth::CLIENTE);
        $this->soloPost();
        $p = Pedido::find(post_int('id') ?? 0);
        if (!$p || (int) $p['id_cliente'] !== Auth::id()) {
            redirect('pedidos/index');
        }
        if ($p['estado'] !== 'Pendiente') {
            flash('warning', 'El pedido ya se está preparando; comunícate con la heladería para cancelarlo.');
        } else {
            Pedido::cambiarEstado((int) $p['id'], 'Cancelado', null);
            flash('success', 'Pedido cancelado.');
        }
        redirect('pedidos/ver', ['id' => $p['id']]);
    }

    public function eliminar(): void
    {
        $this->soloRoles(Auth::ADMIN);
        $this->soloPost();
        $p = Pedido::find(post_int('id') ?? 0);
        if ($p) {
            // Si seguía activo, primero se cancela para devolver el stock.
            if (!in_array($p['estado'], ['Entregado', 'Cancelado'], true)) {
                Pedido::cambiarEstado((int) $p['id'], 'Cancelado', Auth::id());
            }
            Pedido::delete((int) $p['id']);
            flash('success', 'Pedido #' . $p['id'] . ' eliminado.');
        }
        redirect('pedidos/index', ['estado' => 'todos']);
    }
}
