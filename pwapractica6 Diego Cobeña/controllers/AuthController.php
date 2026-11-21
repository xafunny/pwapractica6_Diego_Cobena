<?php
/**
 * ---------------------------------------------------------------------
 * controllers/AuthController.php
 * ---------------------------------------------------------------------
 * Inicio de sesión, registro de clientes y cierre de sesión.
 *   auth/login     GET muestra el formulario, POST valida credenciales
 *   auth/registro  GET formulario, POST crea un usuario con rol Cliente
 *   auth/salir     POST cierra la sesión
 * Los empleados y administradores los crea el Administrador.
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class AuthController extends Controller
{
    /** Intentos fallidos permitidos antes de bloquear temporalmente. */
    private const MAX_INTENTOS = 5;
    private const BLOQUEO_SEG  = 60;

    public function login(): void
    {
        if (Auth::check()) redirect('inicio/index');

        $error = '';
        $correo = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $correo = mb_strtolower(post('correo'));
            $clave  = $_POST['contrasena'] ?? '';
            $intentos = $_SESSION['intentos_login'] ?? ['n' => 0, 't' => 0];

            if (!csrf_valido()) {
                $error = 'La sesión expiró, vuelve a intentarlo.';
            } elseif ($intentos['n'] >= self::MAX_INTENTOS && time() - $intentos['t'] < self::BLOQUEO_SEG) {
                $error = 'Demasiados intentos. Espera un minuto e inténtalo de nuevo.';
            } elseif ($correo === '' || $clave === '') {
                $error = 'Completa todos los campos.';
            } else {
                $u = Usuario::porCorreo($correo);
                if (!$u || !password_verify($clave, $u['contrasena'])) {
                    // Mensaje genérico: no revela si el correo existe.
                    $_SESSION['intentos_login'] = ['n' => $intentos['n'] + 1, 't' => time()];
                    $error = 'Correo o contraseña incorrectos.';
                } elseif (!$u['activo']) {
                    $error = 'Tu cuenta está desactivada. Comunícate con la heladería.';
                } else {
                    unset($_SESSION['intentos_login']);
                    Auth::login($u);
                    flash('success', '¡Hola, ' . $u['nombre'] . '!');
                    redirect('inicio/index');
                }
            }
        }

        $this->view('auth/login', ['titulo' => 'Iniciar sesión', 'error' => $error, 'correo' => $correo]);
    }

    public function registro(): void
    {
        if (Auth::check()) redirect('inicio/index');

        $errores = [];
        $d = ['nombre' => '', 'apellido' => '', 'correo' => '', 'telefono' => '', 'direccion' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($d as $k => $_) $d[$k] = post($k);
            $d['correo'] = mb_strtolower($d['correo']);
            $clave  = $_POST['contrasena']  ?? '';
            $clave2 = $_POST['contrasena2'] ?? '';

            // Validaciones del lado del servidor (JS también valida en el navegador).
            if (!csrf_valido()) $errores[] = 'La sesión expiró, vuelve a intentarlo.';
            if (mb_strlen($d['nombre']) < 2)   $errores[] = 'Ingresa tu nombre.';
            if (mb_strlen($d['apellido']) < 2) $errores[] = 'Ingresa tu apellido.';
            if (!filter_var($d['correo'], FILTER_VALIDATE_EMAIL)) $errores[] = 'El correo no es válido.';
            elseif (Usuario::correoExiste($d['correo'])) $errores[] = 'Ese correo ya está registrado.';
            if (!preg_match('/^\d{7,15}$/', $d['telefono'])) $errores[] = 'El teléfono debe tener solo números (7 a 15 dígitos).';
            if (strlen($clave) < 8 || !preg_match('/[A-Za-z]/', $clave) || !preg_match('/\d/', $clave)) {
                $errores[] = 'La contraseña debe tener al menos 8 caracteres, con letras y números.';
            }
            if ($clave !== $clave2) $errores[] = 'Las contraseñas no coinciden.';

            if (!$errores) {
                Usuario::create([
                    'nombre'     => $d['nombre'],
                    'apellido'   => $d['apellido'],
                    'correo'     => $d['correo'],
                    'contrasena' => password_hash($clave, PASSWORD_DEFAULT),
                    'id_rol'     => Auth::CLIENTE,
                    'telefono'   => $d['telefono'],
                    'direccion'  => nulo($d['direccion']),
                ]);
                Auth::login(Usuario::porCorreo($d['correo'])); // inicia sesión automáticamente
                flash('success', '¡Cuenta creada! Ya puedes hacer tu primer pedido.');
                redirect('tienda/index');
            }
        }

        $this->view('auth/registro', ['titulo' => 'Crear cuenta', 'errores' => $errores, 'd' => $d]);
    }

    public function salir(): void
    {
        $this->soloPost();
        Auth::logout();
        session_start(); // nueva sesión solo para el mensaje flash
        flash('info', 'Sesión cerrada correctamente.');
        redirect('auth/login');
    }
}
