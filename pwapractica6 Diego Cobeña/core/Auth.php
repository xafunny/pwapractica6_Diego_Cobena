<?php
/**
 * ---------------------------------------------------------------------
 * core/Auth.php
 * ---------------------------------------------------------------------
 * Manejo de la sesión del usuario autenticado y de los ROLES.
 *
 * Al iniciar sesión se guarda en $_SESSION['usuario'] un arreglo con
 * id, nombre, apellido, correo, id_rol y rol (nombre del rol).
 * Las constantes ADMIN, EMPLEADO y CLIENTE coinciden con los id de la
 * tabla `roles` de la base de datos.
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class Auth
{
    public const ADMIN    = 1;
    public const EMPLEADO = 2;
    public const CLIENTE  = 3;

    /** Guarda al usuario en la sesión después de validar su contraseña. */
    public static function login(array $usuario): void
    {
        // Se regenera el id de sesión para evitar fijación de sesión.
        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id'       => (int) $usuario['id'],
            'nombre'   => $usuario['nombre'],
            'apellido' => $usuario['apellido'],
            'correo'   => $usuario['correo'],
            'id_rol'   => (int) $usuario['id_rol'],
            'rol'      => $usuario['rol'],
        ];
    }

    /** Cierra la sesión por completo. */
    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    /** ¿Hay un usuario autenticado? */
    public static function check(): bool
    {
        return isset($_SESSION['usuario']);
    }

    /** Datos del usuario autenticado (o null). */
    public static function user(): ?array
    {
        return $_SESSION['usuario'] ?? null;
    }

    /** Id del usuario autenticado. */
    public static function id(): ?int
    {
        return $_SESSION['usuario']['id'] ?? null;
    }

    /** Id del rol del usuario autenticado. */
    public static function rol(): ?int
    {
        return $_SESSION['usuario']['id_rol'] ?? null;
    }

    /** ¿El usuario tiene alguno de los roles indicados? */
    public static function es(int ...$roles): bool
    {
        return self::check() && in_array(self::rol(), $roles, true);
    }

    /** Actualiza nombre/apellido/correo en sesión tras editar el perfil. */
    public static function refrescar(array $datos): void
    {
        foreach (['nombre', 'apellido', 'correo'] as $k) {
            if (isset($datos[$k])) $_SESSION['usuario'][$k] = $datos[$k];
        }
    }
}
