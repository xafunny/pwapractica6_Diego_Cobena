<?php
/**
 * =====================================================================
 *  HELADERÍA POLAR - Sistema de gestión de una heladería
 *  index.php  ->  "Front controller": punto de entrada ÚNICO de la app
 * =====================================================================
 *  Flujo de cada petición:
 *    1. Se inicia la sesión (con cookies seguras).
 *    2. Se cargan configuración, núcleo (core/) y modelos (models/).
 *    3. El Router lee ?r=controlador/accion y ejecuta el método
 *       correspondiente del controlador.
 *
 *  Arquitectura (pseudo-framework MVC propio, sin frameworks PHP):
 *    config/       configuración (BD, nombre de la app)
 *    core/         Database (PDO), Router, Controller, Model, Auth, helpers
 *    models/       acceso a datos por tabla
 *    controllers/  lógica de cada módulo y control de roles
 *    views/        HTML + Bootstrap 5 (layout, módulos, parciales)
 *    assets/       CSS, JavaScript e imágenes
 * =====================================================================
 */

// Constante que habilita la carga de los archivos internos.
// Si alguien abre directamente core/Auth.php en el navegador, se detiene.
define('APP', true);

// Cookies de sesión: solo HTTP (no accesibles desde JS) y SameSite=Lax.
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();

require __DIR__ . '/config/config.php';

// Mostrar errores solo en modo depuración.
ini_set('display_errors', DEBUG ? '1' : '0');
error_reporting(E_ALL);

// Núcleo del pseudo-framework.
require __DIR__ . '/core/helpers.php';
require __DIR__ . '/core/Database.php';
require __DIR__ . '/core/Auth.php';
require __DIR__ . '/core/Model.php';
require __DIR__ . '/core/Controller.php';
require __DIR__ . '/core/Router.php';

// Modelos: se cargan todos los archivos de la carpeta models/.
foreach (glob(__DIR__ . '/models/*.php') as $modelo) {
    require_once $modelo;
}

// Despachar la ruta solicitada.
Router::dispatch(is_string($_GET['r'] ?? null) ? $_GET['r'] : '');
