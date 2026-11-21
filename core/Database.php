<?php
/**
 * ---------------------------------------------------------------------
 * core/Database.php
 * ---------------------------------------------------------------------
 * Conexión única (patrón Singleton) a MySQL usando PDO.
 *
 * - Toda la app obtiene la conexión con Database::get().
 * - Se usan SIEMPRE consultas preparadas (prepare + execute) para
 *   evitar inyección SQL.
 * - Los resultados se devuelven como arreglos asociativos.
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class Database
{
    /** @var PDO|null Instancia compartida de la conexión */
    private static ?PDO $pdo = null;

    /**
     * Devuelve la conexión PDO. La crea la primera vez que se llama.
     */
    public static function get(): PDO
    {
        if (self::$pdo === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT
                 . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            try {
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // errores como excepciones
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // filas como arreglos asociativos
                    PDO::ATTR_EMULATE_PREPARES   => false,                  // preparadas reales en MySQL
                ]);
                // Sincroniza la zona horaria de MySQL con la de PHP (NOW(), CURDATE()).
                self::$pdo->exec("SET time_zone = '" . date('P') . "'");
            } catch (PDOException $e) {
                http_response_code(500);
                $detalle = DEBUG ? '<br><small>' . htmlspecialchars($e->getMessage()) . '</small>' : '';
                exit('<h3 style="font-family:sans-serif">No se pudo conectar a la base de datos.'
                    . ' Revise config/config.php e importe heladeria.sql.' . $detalle . '</h3>');
            }
        }
        return self::$pdo;
    }
}
