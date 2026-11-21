<?php
/**
 * ---------------------------------------------------------------------
 * controllers/PersonalController.php    (solo ADMINISTRADOR)
 * ---------------------------------------------------------------------
 * Gestión de las cuentas del personal (empleados y administradores).
 *   personal/index     listado
 *   personal/guardar   crear / editar          [POST]
 *   personal/estado    activar / desactivar    [POST JSON]
 *   personal/eliminar  eliminar                [POST]
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class PersonalController extends Controller
{
    public function index(): void
    {
        $this->soloRoles(Auth::ADMIN);
        $this->view('personal/index', [
            'titulo'   => 'Personal',
            'personal' => Usuario::personal(),
        ]);
    }

    public function guardar(): void
    {
        $this->soloRoles(Auth::ADMIN);
        $this->soloPost();
        $id = post_int('id') ?? 0;
        if ($id) $this->miembro($id);

        $d = [
            'nombre'   => post('nombre'),
            'apellido' => post('apellido'),
            'correo'   => mb_strtolower(post('correo')),
            'telefono' => nulo(post('telefono')),
            'id_rol'   => post_int('id_rol'),
        ];
        $clave = $_POST['contrasena'] ?? '';

        $err = null;
        if (mb_strlen($d['nombre']) < 2) $err = 'El nombre es obligatorio.';
        elseif (!filter_var($d['correo'], FILTER_VALIDATE_EMAIL)) $err = 'Correo no válido.';
        elseif (Usuario::correoExiste($d['correo'], $id)) $err = 'Ese correo ya está registrado.';
        elseif (!in_array($d['id_rol'], [Auth::ADMIN, Auth::EMPLEADO], true)) $err = 'Rol no válido.';
        elseif ($id === Auth::id() && $d['id_rol'] !== Auth::ADMIN) $err = 'No puedes quitarte el rol de administrador.';
        elseif (!$id && strlen($clave) < 8) $err = 'La contraseña debe tener al menos 8 caracteres.';
        elseif ($id && $clave !== '' && strlen($clave) < 8) $err = 'La nueva contraseña debe tener al menos 8 caracteres.';
        if ($err) {
            flash('danger', $err);
            redirect('personal/index');
        }

        if ($clave !== '') $d['contrasena'] = password_hash($clave, PASSWORD_DEFAULT);
        if ($id) {
            Usuario::update($id, $d);
            if ($id === Auth::id()) Auth::refrescar($d);
            flash('success', 'Datos actualizados.');
        } else {
            Usuario::create($d);
            flash('success', 'Cuenta creada.');
        }
        redirect('personal/index');
    }

    public function estado(): void
    {
        $this->soloRoles(Auth::ADMIN);
        $this->soloPost();
        $u = $this->miembro(post_int('id') ?? 0);
        if ((int) $u['id'] === Auth::id()) $this->json(['ok' => false, 'error' => 'No puedes desactivar tu propia cuenta.'], 400);
        $nuevo = $u['activo'] ? 0 : 1;
        Usuario::update((int) $u['id'], ['activo' => $nuevo]);
        $this->json(['ok' => true, 'activo' => $nuevo]);
    }

    public function eliminar(): void
    {
        $this->soloRoles(Auth::ADMIN);
        $this->soloPost();
        $u = $this->miembro(post_int('id') ?? 0);
        if ((int) $u['id'] === Auth::id()) {
            flash('danger', 'No puedes eliminar tu propia cuenta.');
        } else {
            Usuario::delete((int) $u['id']); // sus pedidos atendidos quedan sin empleado asignado
            flash('success', 'Cuenta eliminada.');
        }
        redirect('personal/index');
    }

    /** Devuelve el usuario si es administrador o empleado. */
    private function miembro(int $id): array
    {
        $u = Usuario::find($id);
        if (!$u || !in_array((int) $u['id_rol'], [Auth::ADMIN, Auth::EMPLEADO], true)) {
            if ($this->esAjax()) $this->json(['ok' => false, 'error' => 'Usuario no encontrado.'], 404);
            flash('danger', 'Usuario no encontrado.');
            redirect('personal/index');
        }
        return $u;
    }
}
