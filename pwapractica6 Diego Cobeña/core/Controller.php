<?php
/**
 * ---------------------------------------------------------------------
 * core/Controller.php
 * ---------------------------------------------------------------------
 * Clase base de los controladores. Ofrece:
 *   - view()       renderiza una vista dentro del layout (header/footer)
 *   - json()       responde JSON (para las peticiones fetch de JS)
 *   - soloRoles()  restringe la acción a ciertos roles
 *   - soloPost()   exige método POST + token CSRF válido
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

abstract class Controller
{
    /**
     * Renderiza views/{$vista}.php. Las claves de $datos se convierten en
     * variables dentro de la vista (extract). Con $layout = true se
     * incluyen el encabezado (navbar) y el pie de página.
     */
    protected function view(string $vista, array $datos = [], bool $layout = true): void
    {
        extract($datos);
        $titulo = $datos['titulo'] ?? APP_NAME;
        if ($layout) require __DIR__ . '/../views/layout/header.php';
        require __DIR__ . '/../views/' . $vista . '.php';
        if ($layout) require __DIR__ . '/../views/layout/footer.php';
    }

    /** Envía una respuesta JSON y termina. */
    protected function json(array $datos, int $codigo = 200): never
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** ¿La petición viene de fetch() (espera JSON)? */
    protected function esAjax(): bool
    {
        return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    }

    /**
     * Permite continuar solo si hay sesión y el rol es uno de los
     * indicados. Sin roles => basta con estar autenticado.
     */
    protected function soloRoles(int ...$roles): void
    {
        if (!Auth::check()) {
            if ($this->esAjax()) $this->json(['ok' => false, 'error' => 'Sesión expirada'], 401);
            flash('warning', 'Inicia sesión para continuar.');
            redirect('auth/login');
        }
        if ($roles && !Auth::es(...$roles)) {
            if ($this->esAjax()) $this->json(['ok' => false, 'error' => 'No autorizado'], 403);
            flash('danger', 'No tienes permiso para acceder a esa sección.');
            redirect('inicio/index');
        }
    }

    /** Exige POST con token CSRF válido (protege acciones que modifican datos). */
    protected function soloPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valido()) {
            if ($this->esAjax()) $this->json(['ok' => false, 'error' => 'Solicitud no válida'], 400);
            flash('danger', 'Solicitud no válida. Vuelve a intentarlo.');
            redirect('inicio/index');
        }
    }
}
