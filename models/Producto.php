<?php
/**
 * ---------------------------------------------------------------------
 * models/Producto.php
 * ---------------------------------------------------------------------
 * Acceso a la tabla `productos`. Incluye el catálogo para clientes
 * (solo activos) y las alertas de stock bajo.
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class Producto extends Model
{
    protected static string $tabla = 'productos';

    /** Stock a partir del cual se considera "bajo". */
    public const STOCK_BAJO = 5;

    /**
     * Listado con el nombre e icono de la categoría.
     *   $soloActivos -> true para el catálogo del cliente
     *   $categoria   -> filtra por id de categoría
     *   $buscar      -> texto en nombre o descripción
     */
    public static function listar(bool $soloActivos = false, int $categoria = 0, string $buscar = ''): array
    {
        $sql = 'SELECT p.*, c.nombre AS categoria, c.icono
                  FROM productos p JOIN categorias c ON c.id = p.id_categoria WHERE 1=1';
        $params = [];
        if ($soloActivos) $sql .= ' AND p.activo = 1';
        if ($categoria) {
            $sql .= ' AND p.id_categoria = ?';
            $params[] = $categoria;
        }
        if ($buscar !== '') {
            $sql .= ' AND (p.nombre LIKE ? OR p.descripcion LIKE ?)';
            array_push($params, "%$buscar%", "%$buscar%");
        }
        return self::query($sql . ' ORDER BY c.nombre, p.nombre', $params)->fetchAll();
    }

    /** Productos activos con stock bajo o agotado. */
    public static function stockBajo(): array
    {
        return self::query('SELECT * FROM productos WHERE activo = 1 AND stock <= ? ORDER BY stock, nombre', [self::STOCK_BAJO])->fetchAll();
    }

    /** Productos más vendidos (pedidos entregados). */
    public static function masVendidos(int $limite = 5): array
    {
        $sql = "SELECT pr.nombre, SUM(d.cantidad) AS unidades, SUM(d.subtotal) AS total
                  FROM detalle_pedido d
                  JOIN pedidos p    ON p.id = d.id_pedido AND p.estado = 'Entregado'
                  JOIN productos pr ON pr.id = d.id_producto
                 GROUP BY pr.id ORDER BY unidades DESC LIMIT " . (int) $limite;
        return self::query($sql)->fetchAll();
    }

    /** ¿El producto aparece en algún pedido? (entonces no se borra, se desactiva). */
    public static function enPedidos(int $id): bool
    {
        return (bool) self::query('SELECT 1 FROM detalle_pedido WHERE id_producto = ? LIMIT 1', [$id])->fetch();
    }
}
