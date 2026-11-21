<?php
/**
 * ---------------------------------------------------------------------
 * core/Router.php
 * ---------------------------------------------------------------------
 * Enrutador muy simple. Toda petición entra por index.php con el
 * parámetro r=controlador/accion, por ejemplo:
 *
 *    index.php?r=clases/index   ->  ClasesController::index()
 *    index.php?r=reservas/crear ->  ReservasController::crear()
 *
 * Solo se pueden ejecutar métodos PÚBLICOS declarados en el propio
 * controlador (no los heredados de Controller), lo que evita que se
 * invoquen métodos internos desde la URL.
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class Router
{
    public static function dispatch(string $ruta): void
    {
        // Ruta por defecto: si no hay sesión, al login; si hay, al inicio.
        if ($ruta === '') {
            $ruta = Auth::check() ? 'inicio/index' : 'auth/login';
        }

        // Solo letras minúsculas en controlador y acción (seguridad).
        if (!preg_match('#^([a-z]+)/([a-z]+)$#', $ruta, $m)) {
            self::noEncontrado();
        }
        [, $nombre, $accion] = $m;

        $clase   = ucfirst($nombre) . 'Controller';
        $archivo = __DIR__ . '/../controllers/' . $clase . '.php';
        if (!is_file($archivo)) self::noEncontrado();
        require_once $archivo;

        if (!method_exists($clase, $accion)) self::noEncontrado();
        $ref = new ReflectionMethod($clase, $accion);
        if (!$ref->isPublic() || $ref->getDeclaringClass()->getName() !== $clase) {
            self::noEncontrado();
        }

        (new $clase())->$accion();
    }

    /** Página 404. */
    private static function noEncontrado(): never
    {
        http_response_code(404);
        $titulo = 'Página no encontrada';
        require __DIR__ . '/../views/layout/header.php';
        require __DIR__ . '/../views/errores/404.php';
        require __DIR__ . '/../views/layout/footer.php';
        exit;
    }
}
