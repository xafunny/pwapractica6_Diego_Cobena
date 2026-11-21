<?php
/**
 * ---------------------------------------------------------------------
 * models/Carrito.php
 * ---------------------------------------------------------------------
 * Carrito de compras del cliente guardado en la SESIÓN de PHP:
 *   $_SESSION['carrito'] = [idProducto => cantidad, ...]
 * No usa tabla propia: al confirmar se convierte en un pedido.
 * Los precios y el stock siempre se leen de la base de datos, nunca
 * del navegador, para que no se puedan manipular.
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class Carrito
{
    /** Cantidad máxima de un mismo producto por pedido. */
    public const MAX_POR_PRODUCTO = 20;

    /** Contenido crudo: [idProducto => cantidad]. */
    public static function items(): array
    {
        return $_SESSION['carrito'] ?? [];
    }

    /**
     * Fija la cantidad de un producto (0 = quitarlo). La cantidad se
     * limita al stock disponible. Devuelve la cantidad que quedó.
     */
    public static function poner(array $producto, int $cantidad): int
    {
        $id = (int) $producto['id'];
        $cantidad = max(0, min($cantidad, (int) $producto['stock'], self::MAX_POR_PRODUCTO));
        if ($cantidad === 0) {
            unset($_SESSION['carrito'][$id]);
        } else {
            $_SESSION['carrito'][$id] = $cantidad;
        }
        return $cantidad;
    }

    /** Vacía el carrito. */
    public static function vaciar(): void
    {
        unset($_SESSION['carrito']);
    }

    /** Número total de unidades (para el contador del navbar). */
    public static function unidades(): int
    {
        return array_sum(self::items());
    }

    /**
     * Detalle del carrito con datos actuales de la BD:
     *   ['lineas' => [...], 'total' => float]
     * Los productos que ya no existen o se desactivaron se quitan.
     */
    public static function detalle(): array
    {
        $lineas = [];
        $total = 0.0;
        foreach (self::items() as $id => $cantidad) {
            $p = Producto::find((int) $id);
            if (!$p || !$p['activo']) {
                unset($_SESSION['carrito'][$id]);
                continue;
            }
            $subtotal = round($p['precio'] * $cantidad, 2);
            $total += $subtotal;
            $lineas[] = $p + ['cantidad' => $cantidad, 'subtotal' => $subtotal];
        }
        return ['lineas' => $lineas, 'total' => round($total, 2)];
    }
}
