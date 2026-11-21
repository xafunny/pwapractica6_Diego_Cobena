<?php
/**
 * ---------------------------------------------------------------------
 * config/config.php
 * ---------------------------------------------------------------------
 * Configuración general de la aplicación: conexión a la base de datos,
 * nombre de la app, zona horaria y modo de depuración.
 *
 * Para el HOSTING (InfinityFree, etc.) NO edites este archivo: crea
 * config/config.local.php copiando config.local.example.php y pon allí
 * los datos de la base de datos del hosting. Ese archivo se carga
 * primero y está excluido de GitHub (.gitignore), así las contraseñas
 * reales nunca se publican en el repositorio.
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

// 1) Si existe configuración local (hosting / equipo propio) se carga primero.
if (is_file(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

// 2) Valores por defecto (XAMPP / Laragon). Solo se definen si
//    config.local.php no los definió antes.
defined('DB_HOST') or define('DB_HOST', 'localhost');
defined('DB_PORT') or define('DB_PORT', 3306);
defined('DB_NAME') or define('DB_NAME', 'heladeria');
defined('DB_USER') or define('DB_USER', 'root');
defined('DB_PASS') or define('DB_PASS', '');

defined('APP_NAME') or define('APP_NAME', 'Heladería Polar');

// En producción conviene ponerlo en false para no mostrar errores internos.
defined('DEBUG') or define('DEBUG', true);

// Zona horaria usada para fechas de clases, reservas y entrenamientos.
date_default_timezone_set(defined('ZONA_HORARIA') ? ZONA_HORARIA : 'America/Guayaquil');
