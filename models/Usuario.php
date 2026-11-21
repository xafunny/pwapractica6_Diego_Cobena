<?php
/**
 * ---------------------------------------------------------------------
 * models/Usuario.php
 * ---------------------------------------------------------------------
 * Acceso a la tabla `usuarios` (administradores, empleados y clientes).
 * Las contraseñas se guardan con password_hash() y se validan con
 * password_verify().
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class Usuario extends Model
{
    protected static string $tabla = 'usuarios';

    /** Busca por correo incluyendo el nombre del rol (para el login). */
    public static function porCorreo(string $correo): ?array
    {
        $sql = 'SELECT u.*, r.nombre AS rol FROM usuarios u JOIN roles r ON r.id = u.id_rol WHERE u.correo = ?';
        return self::query($sql, [$correo])->fetch() ?: null;
    }

    /** ¿Ya existe ese correo? (opcionalmente ignorando un id al editar). */
    public static function correoExiste(string $correo, int $exceptoId = 0): bool
    {
        return (bool) self::query('SELECT 1 FROM usuarios WHERE correo = ? AND id <> ?', [$correo, $exceptoId])->fetch();
    }

    /** Personal del local (administradores y empleados). */
    public static function personal(): array
    {
        $sql = 'SELECT u.*, r.nombre AS rol,
                       (SELECT COUNT(*) FROM pedidos p WHERE p.id_empleado = u.id) AS pedidos_atendidos
                  FROM usuarios u JOIN roles r ON r.id = u.id_rol
                 WHERE u.id_rol IN (?, ?) ORDER BY u.id_rol, u.nombre';
        return self::query($sql, [Auth::ADMIN, Auth::EMPLEADO])->fetchAll();
    }

    /**
     * Clientes con resumen de compras: nº de pedidos entregados,
     * total gastado y fecha del último pedido.
     */
    public static function clientes(string $buscar = ''): array
    {
        $sql = "SELECT u.*,
                       COUNT(p.id) AS pedidos,
                       COALESCE(SUM(CASE WHEN p.estado = 'Entregado' THEN p.total END), 0) AS gastado,
                       MAX(p.fecha) AS ultimo_pedido
                  FROM usuarios u
                  LEFT JOIN pedidos p ON p.id_cliente = u.id
                 WHERE u.id_rol = ?";
        $params = [Auth::CLIENTE];
        if ($buscar !== '') {
            $sql .= ' AND (u.nombre LIKE ? OR u.apellido LIKE ? OR u.correo LIKE ? OR u.telefono LIKE ?)';
            array_push($params, "%$buscar%", "%$buscar%", "%$buscar%", "%$buscar%");
        }
        return self::query($sql . ' GROUP BY u.id ORDER BY u.nombre', $params)->fetchAll();
    }

    /** Cantidad de clientes registrados. */
    public static function totalClientes(): int
    {
        return (int) self::query('SELECT COUNT(*) FROM usuarios WHERE id_rol = ?', [Auth::CLIENTE])->fetchColumn();
    }

    /** ¿El usuario tiene pedidos? (los clientes con pedidos solo se desactivan). */
    public static function tienePedidos(int $id): bool
    {
        return (bool) self::query('SELECT 1 FROM pedidos WHERE id_cliente = ? LIMIT 1', [$id])->fetch();
    }
}
