<?php
/**
 * ---------------------------------------------------------------------
 * core/helpers.php
 * ---------------------------------------------------------------------
 * Funciones de ayuda globales usadas en controladores y vistas:
 *   - e()            escapar HTML (previene XSS)
 *   - url()          construir enlaces internos (?r=controlador/accion)
 *   - redirect()     redirigir y terminar
 *   - flash()        mensajes de una sola vez entre peticiones
 *   - csrf_*()       protección contra CSRF en formularios y fetch()
 *   - dinero(), fecha() y datos de los estados de un pedido
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

/**
 * Estados de un pedido en el orden en que avanzan, con el color
 * (Bootstrap) y el icono con los que se muestran.
 */
const ESTADOS_PEDIDO = [
    'Pendiente'  => ['color' => 'warning',   'icono' => 'hourglass-split'],
    'Preparando' => ['color' => 'info',      'icono' => 'cup-hot'],
    'Listo'      => ['color' => 'primary',   'icono' => 'bag-check'],
    'Entregado'  => ['color' => 'success',   'icono' => 'check2-circle'],
    'Cancelado'  => ['color' => 'secondary', 'icono' => 'x-circle'],
];

/** Escapa texto para imprimirlo en HTML de forma segura. */
function e($valor): string
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Construye una URL interna.
 *   url('productos/index', ['id' => 3])  ->  index.php?r=productos/index&id=3
 */
function url(string $ruta = 'inicio/index', array $params = []): string
{
    $params = array_merge(['r' => $ruta], $params);
    return 'index.php?' . http_build_query($params);
}

/** Ruta pública de un recurso estático (css, js, img). */
function asset(string $ruta): string
{
    $archivo = __DIR__ . '/../assets/' . $ruta;
    // Se agrega la fecha de modificación para evitar caché vieja del navegador.
    $version = is_file($archivo) ? filemtime($archivo) : 1;
    return 'assets/' . $ruta . '?v=' . $version;
}

/** Redirige a otra ruta interna y detiene la ejecución. */
function redirect(string $ruta, array $params = []): never
{
    header('Location: ' . url($ruta, $params));
    exit;
}

/**
 * Guarda (con 2 argumentos) o lee y borra (sin argumentos) los mensajes
 * flash que se muestran una sola vez como alertas de Bootstrap.
 */
function flash(?string $tipo = null, ?string $mensaje = null): array
{
    if ($tipo !== null) {
        $_SESSION['flash'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
        return [];
    }
    $mensajes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $mensajes;
}

/** Token CSRF de la sesión (se crea una vez por sesión). */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** Campo oculto con el token CSRF para incluir en cada <form method="post">. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/**
 * Verifica el token CSRF recibido por POST (formularios) o por la
 * cabecera X-CSRF-Token (peticiones fetch desde JavaScript).
 */
function csrf_valido(): bool
{
    $recibido = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return is_string($recibido) && hash_equals(csrf_token(), $recibido);
}

/** Lee un campo POST como texto recortado. */
function post(string $campo, string $defecto = ''): string
{
    $v = $_POST[$campo] ?? $defecto;
    return is_string($v) ? trim($v) : $defecto;
}

/** Lee un campo POST como entero (o null si viene vacío). */
function post_int(string $campo): ?int
{
    $v = post($campo);
    return $v === '' ? null : (int) $v;
}

/** Lee un campo POST como número decimal (acepta coma o punto). */
function post_decimal(string $campo): ?float
{
    $v = str_replace(',', '.', post($campo));
    return is_numeric($v) ? round((float) $v, 2) : null;
}

/** Convierte una cadena vacía en null (para columnas opcionales). */
function nulo(string $v): ?string
{
    return $v === '' ? null : $v;
}

/** Formatea un valor como dinero: 1234.5 -> $1,234.50 */
function dinero($valor): string
{
    return '$' . number_format((float) $valor, 2);
}

/** Formatea 2025-03-04 => 04/03/2025 (y la hora si la tiene). */
function fecha(?string $valor, bool $conHora = false): string
{
    if (!$valor) return '';
    $t = strtotime($valor);
    return $conHora ? date('d/m/Y H:i', $t) : date('d/m/Y', $t);
}

/** Insignia de Bootstrap con el estado de un pedido. */
function badge_estado(string $estado): string
{
    $info = ESTADOS_PEDIDO[$estado] ?? ['color' => 'secondary', 'icono' => 'circle'];
    return '<span class="badge text-bg-' . $info['color'] . '"><i class="bi bi-' . $info['icono'] . '"></i> ' . e($estado) . '</span>';
}

/** Iniciales para el avatar (ej. "Diego Cobeña" -> "DC"). */
function iniciales(string $nombre, string $apellido = ''): string
{
    return mb_strtoupper(mb_substr($nombre, 0, 1) . mb_substr($apellido, 0, 1));
}

/** Nombre del mes en español (1..12). */
function nombre_mes(int $mes): string
{
    return ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto',
            'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'][$mes - 1];
}
