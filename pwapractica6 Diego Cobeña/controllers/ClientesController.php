<?php
/**
 * ---------------------------------------------------------------------
 * controllers/ClientesController.php   (ADMINISTRADOR y EMPLEADO)
 * ---------------------------------------------------------------------
 *   clientes/index     listado con resumen de compras
 *   clientes/ver       ficha del cliente con su historial de pedidos
 *   clientes/guardar   crear / editar cliente                [POST]
 *   clientes/estado    activar / desactivar                  [POST JSON]
 *   clientes/eliminar  eliminar (solo administrador)         [POST]
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class ClientesController extends Controller
{
    public function index(): void
    {
        $this->soloRoles(Auth::ADMIN, Auth::EMPLEADO);
        $buscar = trim((string) ($_GET['q'] ?? ''));
        $this->view('clientes/index', [
            'titulo'   => 'Clientes',
            'clientes' => Usuario::clientes($buscar),
            'buscar'   => $buscar,
        ]);
    }

    public function ver(): void
    {
        $this->soloRoles(Auth::ADMIN, Auth::EMPLEADO);
        $c = $this->cliente((int) ($_GET['id'] ?? 0));
        $this->view('clientes/ver', [
            'titulo'  => $c['nombre'] . ' ' . $c['apellido'],
            'cliente' => $c,
            'pedidos' => Pedido::deCliente((int) $c['id']),
        ]);
    }

    public function guardar(): void
    {
        $this->soloRoles(Auth::ADMIN, Auth::EMPLEADO);
        $this->soloPost();
        $id = post_int('id') ?? 0;
        if ($id) $this->cliente($id);

        $d = [
            'nombre'    => post('nombre'),
            'apellido'  => post('apellido'),
            'correo'    => mb_strtolower(post('correo')),
            'telefono'  => post('telefono'),
            'direccion' => nulo(post('direccion')),
        ];
        $clave = $_POST['contrasena'] ?? '';

        $err = null;
        if (mb_strlen($d['nombre']) < 2) $err = 'El nombre es obligatorio.';
        elseif (!filter_var($d['correo'], FILTER_VALIDATE_EMAIL)) $err = 'Correo no válido.';
        elseif (Usuario::correoExiste($d['correo'], $id)) $err = 'Ese correo ya está registrado.';
        elseif (!preg_match('/^\d{7,15}$/', $d['telefono'])) $err = 'El teléfono debe tener solo números (7 a 15 dígitos).';
        elseif (!$id && strlen($clave) < 8) $err = 'La contraseña debe tener al menos 8 caracteres.';
        elseif ($id && $clave !== '' && strlen($clave) < 8) $err = 'La nueva contraseña debe tener al menos 8 caracteres.';
        if ($err) {
            flash('danger', $err);
            redirect('clientes/index');
        }

        if ($clave !== '') $d['contrasena'] = password_hash($clave, PASSWORD_DEFAULT);
        if ($id) {
            Usuario::update($id, $d);
            flash('success', 'Cliente actualizado.');
        } else {
            Usuario::create($d + ['id_rol' => Auth::CLIENTE]);
            flash('success', 'Cliente registrado.');
        }
        redirect('clientes/index');
    }

    public function estado(): void
    {
        $this->soloRoles(Auth::ADMIN, Auth::EMPLEADO);
        $this->soloPost();
        $c = $this->cliente(post_int('id') ?? 0);
        $nuevo = $c['activo'] ? 0 : 1;
        Usuario::update((int) $c['id'], ['activo' => $nuevo]);
        $this->json(['ok' => true, 'activo' => $nuevo]);
    }

    public function eliminar(): void
    {
        $this->soloRoles(Auth::ADMIN);
        $this->soloPost();
        $c = $this->cliente(post_int('id') ?? 0);
        if (Usuario::tienePedidos((int) $c['id'])) {
            flash('danger', 'El cliente tiene pedidos registrados; desactívalo en lugar de eliminarlo.');
        } else {
            Usuario::delete((int) $c['id']);
            flash('success', 'Cliente eliminado.');
        }
        redirect('clientes/index');
    }

    /** Devuelve el usuario si existe y es cliente; si no, corta la petición. */
    private function cliente(int $id): array
    {
        $c = Usuario::find($id);
        if (!$c || (int) $c['id_rol'] !== Auth::CLIENTE) {
            if ($this->esAjax()) $this->json(['ok' => false, 'error' => 'Cliente no encontrado.'], 404);
            flash('danger', 'Cliente no encontrado.');
            redirect('clientes/index');
        }
        return $c;
    }
}
