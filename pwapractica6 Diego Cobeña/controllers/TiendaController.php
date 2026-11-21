<?php
/**
 * ---------------------------------------------------------------------
 * controllers/TiendaController.php     (solo CLIENTE)
 * ---------------------------------------------------------------------
 * Catálogo, carrito y confirmación del pedido.
 *   tienda/index      catálogo con filtro por categoría y búsqueda
 *   tienda/carrito    ver el carrito y el formulario de entrega
 *   tienda/poner      fija la cantidad de un producto  [POST JSON]
 *   tienda/confirmar  convierte el carrito en un pedido [POST]
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class TiendaController extends Controller
{
    public function index(): void
    {
        $this->soloRoles(Auth::CLIENTE);
        $categoria = (int) ($_GET['categoria'] ?? 0);
        $this->view('tienda/index', [
            'titulo'     => 'Nuestros helados',
            'productos'  => Producto::listar(true, $categoria),
            'categorias' => Categoria::all('nombre'),
            'categoria'  => $categoria,
            'enCarrito'  => Carrito::items(),
        ]);
    }

    public function carrito(): void
    {
        $this->soloRoles(Auth::CLIENTE);
        $this->view('tienda/carrito', [
            'titulo'  => 'Mi carrito',
            'carrito' => Carrito::detalle(),
            'cliente' => Usuario::find(Auth::id()),
        ]);
    }

    public function poner(): void
    {
        $this->soloRoles(Auth::CLIENTE);
        $this->soloPost();
        $p = Producto::find(post_int('producto') ?? 0);
        if (!$p || !$p['activo']) {
            $this->json(['ok' => false, 'error' => 'Producto no disponible.'], 404);
        }
        $pedida = max(0, post_int('cantidad') ?? 0);
        $quedo = Carrito::poner($p, $pedida);
        $detalle = Carrito::detalle();

        $aviso = null;
        if ($quedo < $pedida) {
            if ((int) $p['stock'] === 0) {
                $aviso = 'Este producto está agotado.';
            } elseif ($p['stock'] < Carrito::MAX_POR_PRODUCTO) {
                $aviso = "Solo hay {$p['stock']} unidades disponibles.";
            } else {
                $aviso = 'Máximo ' . Carrito::MAX_POR_PRODUCTO . ' unidades por producto.';
            }
        }
        $this->json([
            'ok'       => true,
            'cantidad' => $quedo,
            'subtotal' => round($p['precio'] * $quedo, 2),
            'total'    => $detalle['total'],
            'unidades' => Carrito::unidades(),
            'aviso'    => $aviso,
        ]);
    }

    public function confirmar(): void
    {
        $this->soloRoles(Auth::CLIENTE);
        $this->soloPost();

        $items = Carrito::items();
        $tipo = post('tipo_entrega');
        $direccion = nulo(post('direccion'));
        $notas = nulo(mb_substr(post('notas'), 0, 255));

        if (!$items) {
            flash('warning', 'Tu carrito está vacío.');
            redirect('tienda/index');
        }
        if (!in_array($tipo, ['Retiro', 'Domicilio'], true)) {
            flash('danger', 'Elige cómo quieres recibir tu pedido.');
            redirect('tienda/carrito');
        }
        if ($tipo === 'Domicilio' && mb_strlen((string) $direccion) < 5) {
            flash('danger', 'Escribe la dirección de entrega.');
            redirect('tienda/carrito');
        }

        [$id, $error] = Pedido::crear(Auth::id(), $items, $tipo, $tipo === 'Domicilio' ? $direccion : null, $notas);
        if ($error) {
            flash('danger', $error);
            redirect('tienda/carrito');
        }
        Carrito::vaciar();
        flash('success', "¡Pedido #$id recibido! Te avisaremos cuando esté listo.");
        redirect('pedidos/ver', ['id' => $id]);
    }
}
