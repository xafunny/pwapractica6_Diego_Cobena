<?php
/**
 * ---------------------------------------------------------------------
 * models/Pedido.php
 * ---------------------------------------------------------------------
 * Acceso a `pedidos` y `detalle_pedido`.
 *
 * Reglas de negocio:
 *  - Crear un pedido descuenta el stock (transacción con FOR UPDATE
 *    para que dos clientes no compren la última unidad a la vez).
 *  - Cancelar un pedido devuelve el stock.
 *  - Al pasar a "Entregado" se registra el ingreso en `finanzas`.
 *  - Los estados avanzan en orden: Pendiente -> Preparando -> Listo
 *    -> Entregado. Se puede cancelar mientras no esté entregado.
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class Pedido extends Model
{
    protected static string $tabla = 'pedidos';

    /** Orden en que avanzan los estados. */
    public const FLUJO = ['Pendiente', 'Preparando', 'Listo', 'Entregado'];

    /**
     * Crea el pedido a partir de las líneas del carrito.
     * Devuelve [idPedido, null] o [null, mensajeDeError].
     */
    public static function crear(int $cliente, array $items, string $tipoEntrega, ?string $direccion, ?string $notas): array
    {
        $db = self::db();
        $db->beginTransaction();
        try {
            $total = 0.0;
            $lineas = [];
            foreach ($items as $idProducto => $cantidad) {
                // Bloquea la fila del producto hasta terminar la transacción.
                $p = self::query('SELECT * FROM productos WHERE id = ? FOR UPDATE', [(int) $idProducto])->fetch();
                if (!$p || !$p['activo']) {
                    $db->rollBack();
                    return [null, 'Un producto del carrito ya no está disponible.'];
                }
                if ($p['stock'] < $cantidad) {
                    $db->rollBack();
                    return [null, "Solo quedan {$p['stock']} unidades de «{$p['nombre']}»."];
                }
                $subtotal = round($p['precio'] * $cantidad, 2);
                $total += $subtotal;
                $lineas[] = [(int) $p['id'], $cantidad, $p['precio'], $subtotal];
            }

            self::query('INSERT INTO pedidos (id_cliente, tipo_entrega, direccion, notas, total) VALUES (?, ?, ?, ?, ?)',
                [$cliente, $tipoEntrega, $direccion, $notas, round($total, 2)]);
            $idPedido = (int) $db->lastInsertId();

            foreach ($lineas as [$idProducto, $cantidad, $precio, $subtotal]) {
                self::query('INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)',
                    [$idPedido, $idProducto, $cantidad, $precio, $subtotal]);
                self::query('UPDATE productos SET stock = stock - ? WHERE id = ?', [$cantidad, $idProducto]);
            }
            $db->commit();
            return [$idPedido, null];
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Cambia el estado aplicando las reglas de negocio (stock y finanzas).
     * Devuelve null si todo salió bien o el mensaje de error.
     */
    public static function cambiarEstado(int $id, string $nuevo, ?int $empleado): ?string
    {
        $db = self::db();
        $db->beginTransaction();
        try {
            $p = self::query('SELECT * FROM pedidos WHERE id = ? FOR UPDATE', [$id])->fetch();
            if (!$p) {
                $db->rollBack();
                return 'Pedido no encontrado.';
            }
            $actual = $p['estado'];
            $permitido = $nuevo === 'Cancelado'
                ? !in_array($actual, ['Entregado', 'Cancelado'], true)
                : array_search($nuevo, self::FLUJO, true) === array_search($actual, self::FLUJO, true) + 1;
            if (!$permitido) {
                $db->rollBack();
                return "No se puede pasar de «{$actual}» a «{$nuevo}».";
            }

            self::query('UPDATE pedidos SET estado = ?, id_empleado = COALESCE(id_empleado, ?) WHERE id = ?', [$nuevo, $empleado, $id]);

            if ($nuevo === 'Cancelado') {
                // Devolver al inventario lo que se había descontado.
                self::query('UPDATE productos pr JOIN detalle_pedido d ON d.id_producto = pr.id
                                SET pr.stock = pr.stock + d.cantidad WHERE d.id_pedido = ?', [$id]);
            }
            if ($nuevo === 'Entregado') {
                self::query("INSERT INTO finanzas (fecha, tipo, concepto, monto, id_pedido, creado_por)
                             VALUES (CURDATE(), 'Ingreso', ?, ?, ?, ?)", ['Pedido #' . $id, $p['total'], $id, $empleado]);
            }
            $db->commit();
            return null;
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /** SELECT común con datos del cliente y número de productos. */
    private const BASE = "SELECT p.*,
               CONCAT(c.nombre, ' ', c.apellido) AS cliente, c.telefono,
               CONCAT(e.nombre, ' ', e.apellido) AS empleado,
               (SELECT SUM(d.cantidad) FROM detalle_pedido d WHERE d.id_pedido = p.id) AS unidades
          FROM pedidos p
          JOIN usuarios c      ON c.id = p.id_cliente
          LEFT JOIN usuarios e ON e.id = p.id_empleado";

    /** Pedidos para el personal, filtrando por estado ('' = activos). */
    public static function listar(string $estado = ''): array
    {
        if ($estado === 'todos') {
            return self::query(self::BASE . ' ORDER BY p.fecha DESC LIMIT 300')->fetchAll();
        }
        if ($estado === '') {
            return self::query(self::BASE . " WHERE p.estado IN ('Pendiente','Preparando','Listo') ORDER BY p.fecha")->fetchAll();
        }
        return self::query(self::BASE . ' WHERE p.estado = ? ORDER BY p.fecha DESC LIMIT 300', [$estado])->fetchAll();
    }

    /** Pedidos de un cliente (más recientes primero). */
    public static function deCliente(int $cliente, int $limite = 100): array
    {
        return self::query(self::BASE . ' WHERE p.id_cliente = ? ORDER BY p.fecha DESC LIMIT ' . (int) $limite, [$cliente])->fetchAll();
    }

    /** Un pedido con sus datos completos. */
    public static function detalle(int $id): ?array
    {
        return self::query(self::BASE . ' WHERE p.id = ?', [$id])->fetch() ?: null;
    }

    /** Productos (líneas) de un pedido. */
    public static function lineas(int $id): array
    {
        return self::query('SELECT d.*, pr.nombre FROM detalle_pedido d JOIN productos pr ON pr.id = d.id_producto
                             WHERE d.id_pedido = ? ORDER BY d.id', [$id])->fetchAll();
    }

    /** Cantidad de pedidos por estado: ['Pendiente' => 3, ...]. */
    public static function conteoPorEstado(): array
    {
        $filas = self::query('SELECT estado, COUNT(*) total FROM pedidos GROUP BY estado')->fetchAll();
        return array_column($filas, 'total', 'estado') + array_fill_keys(array_keys(ESTADOS_PEDIDO), 0);
    }

    /** Ventas (pedidos entregados) de hoy: [cantidad, total]. */
    public static function ventasHoy(): array
    {
        $f = self::query("SELECT COUNT(*) n, COALESCE(SUM(total),0) t FROM pedidos
                           WHERE estado = 'Entregado' AND DATE(fecha) = CURDATE()")->fetch();
        return [(int) $f['n'], (float) $f['t']];
    }
}
