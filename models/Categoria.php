<?php
/**
 * ---------------------------------------------------------------------
 * models/Categoria.php
 * ---------------------------------------------------------------------
 * Acceso a la tabla `categorias` del catálogo.
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class Categoria extends Model
{
    protected static string $tabla = 'categorias';

    /** Categorías con la cantidad de productos que tiene cada una. */
    public static function conConteo(): array
    {
        return self::query('SELECT c.*, COUNT(p.id) AS productos
                              FROM categorias c LEFT JOIN productos p ON p.id_categoria = c.id
                             GROUP BY c.id ORDER BY c.nombre')->fetchAll();
    }
}
