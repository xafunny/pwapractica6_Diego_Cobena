<?php
/**
 * ---------------------------------------------------------------------
 * config/config.local.example.php  (PLANTILLA)
 * ---------------------------------------------------------------------
 * Copia este archivo como "config.local.php" (en esta misma carpeta)
 * y reemplaza los valores con los datos que te da tu hosting.
 * Ejemplo para InfinityFree (panel > MySQL Databases):
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

define('DB_HOST', 'sqlXXX.infinityfree.com');   // "MySQL Hostname"
define('DB_PORT', 3306);
define('DB_NAME', 'if0_00000000_heladeria');     // "MySQL DB Name"
define('DB_USER', 'if0_00000000');              // "MySQL Username"
define('DB_PASS', 'tu_contrasena_del_hosting'); // "MySQL Password"

define('DEBUG', false); // En producción no se muestran errores internos
