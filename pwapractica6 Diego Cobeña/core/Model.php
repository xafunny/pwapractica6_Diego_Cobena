<?php
/**
 * ---------------------------------------------------------------------
 * core/Model.php
 * ---------------------------------------------------------------------
 * Clase base de todos los modelos (pseudo-framework propio).
 * Cada modelo indica su tabla en $tabla y hereda operaciones CRUD
 * genéricas: buscar por id, listar, insertar, actualizar y eliminar.
 * Las consultas específicas (JOIN, filtros) se escriben en cada modelo.
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

abstract class Model
{
    /** Nombre de la tabla en la base de datos (lo define cada hijo). */
    protected static string $tabla;

    /** Conexión PDO compartida. */
    protected static function db(): PDO
    {
        return Database::get();
    }

    /** Ejecuta una consulta preparada y devuelve el PDOStatement. */
    protected static function query(string $sql, array $params = []): PDOStatement
    {
        $st = self::db()->prepare($sql);
        $st->execute($params);
        return $st;
    }

    /** Busca un registro por su id. */
    public static function find(int $id): ?array
    {
        $fila = self::query('SELECT * FROM ' . static::$tabla . ' WHERE id = ?', [$id])->fetch();
        return $fila ?: null;
    }

    /** Lista todos los registros de la tabla. */
    public static function all(string $orden = 'id'): array
    {
        return self::query('SELECT * FROM ' . static::$tabla . ' ORDER BY ' . $orden)->fetchAll();
    }

    /**
     * Inserta un registro. $datos = ['columna' => valor, ...]
     * Devuelve el id generado.
     */
    public static function create(array $datos): int
    {
        $cols = array_keys($datos);
        $sql = 'INSERT INTO ' . static::$tabla . ' (' . implode(', ', $cols) . ') VALUES ('
             . implode(', ', array_fill(0, count($cols), '?')) . ')';
        self::query($sql, array_values($datos));
        return (int) self::db()->lastInsertId();
    }

    /** Actualiza las columnas indicadas del registro con ese id. */
    public static function update(int $id, array $datos): void
    {
        $set = implode(', ', array_map(fn($c) => "$c = ?", array_keys($datos)));
        $params = array_values($datos);
        $params[] = $id;
        self::query('UPDATE ' . static::$tabla . " SET $set WHERE id = ?", $params);
    }

    /** Elimina el registro con ese id. */
    public static function delete(int $id): void
    {
        self::query('DELETE FROM ' . static::$tabla . ' WHERE id = ?', [$id]);
    }
}
