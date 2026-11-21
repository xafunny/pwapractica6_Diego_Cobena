<?php
/**
 * ---------------------------------------------------------------------
 * controllers/PerfilController.php   (todos los roles)
 * ---------------------------------------------------------------------
 *   perfil/index       ver y editar datos personales
 *   perfil/guardar     guardar datos personales     [POST]
 *   perfil/contrasena  cambiar contraseña           [POST]
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class PerfilController extends Controller
{
    public function index(): void
    {
        $this->soloRoles();
        $this->view('perfil/index', [
            'titulo' => 'Mi perfil',
            'u'      => Usuario::find(Auth::id()),
        ]);
    }

    public function guardar(): void
    {
        $this->soloRoles();
        $this->soloPost();
        $d = [
            'nombre'    => post('nombre'),
            'apellido'  => post('apellido'),
            'correo'    => mb_strtolower(post('correo')),
            'telefono'  => nulo(post('telefono')),
            'direccion' => nulo(post('direccion')),
        ];

        $err = null;
        if (mb_strlen($d['nombre']) < 2) $err = 'El nombre es obligatorio.';
        elseif (!filter_var($d['correo'], FILTER_VALIDATE_EMAIL)) $err = 'Correo no válido.';
        elseif (Usuario::correoExiste($d['correo'], Auth::id())) $err = 'Ese correo ya está en uso.';
        elseif ($d['telefono'] && !preg_match('/^\d{7,15}$/', $d['telefono'])) $err = 'El teléfono debe tener solo números (7 a 15 dígitos).';

        if ($err) {
            flash('danger', $err);
        } else {
            Usuario::update(Auth::id(), $d);
            Auth::refrescar($d);
            flash('success', 'Perfil actualizado.');
        }
        redirect('perfil/index');
    }

    public function contrasena(): void
    {
        $this->soloRoles();
        $this->soloPost();
        $u = Usuario::find(Auth::id());
        $nueva = $_POST['nueva'] ?? '';

        if (!password_verify($_POST['actual'] ?? '', $u['contrasena'])) {
            flash('danger', 'La contraseña actual no es correcta.');
        } elseif (strlen($nueva) < 8 || !preg_match('/[A-Za-z]/', $nueva) || !preg_match('/\d/', $nueva)) {
            flash('danger', 'La nueva contraseña debe tener al menos 8 caracteres, con letras y números.');
        } elseif ($nueva !== ($_POST['nueva2'] ?? '')) {
            flash('danger', 'Las contraseñas nuevas no coinciden.');
        } else {
            Usuario::update(Auth::id(), ['contrasena' => password_hash($nueva, PASSWORD_DEFAULT)]);
            flash('success', 'Contraseña actualizada.');
        }
        redirect('perfil/index');
    }
}
