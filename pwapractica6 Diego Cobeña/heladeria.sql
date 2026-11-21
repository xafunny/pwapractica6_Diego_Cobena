-- =====================================================================
--  HELADERÍA POLAR - Sistema de gestión de una heladería
--  Base de datos MySQL / MariaDB  (importar desde phpMyAdmin)
-- ---------------------------------------------------------------------
--  Proyecto final - Programación Web Avanzada (Ejercicio 37)
--
--  Cambios respecto a la base de datos sugerida en el enunciado:
--   * Clientes, empleados y administradores en una sola tabla
--     `usuarios` relacionada con `roles` (un solo inicio de sesión).
--   * Un pedido puede tener VARIOS productos: tabla `pedidos` (cabecera)
--     + `detalle_pedido` (líneas), en lugar de un producto por pedido.
--   * `categorias` para organizar el catálogo de helados.
--   * `finanzas` guarda movimientos (ingresos y gastos) con su concepto;
--     el balance se calcula sumando, en vez de guardarse a mano.
--   * Llaves foráneas reales, nombres sin tildes y DECIMAL para dinero.
--
--  USO LOCAL (XAMPP / Laragon):
--    1. En phpMyAdmin crear la base de datos `heladeria` (utf8mb4_unicode_ci)
--    2. Seleccionarla y en la pestaña "Importar" subir este archivo.
--
--  Usuarios de prueba (contraseña entre paréntesis):
--    admin@heladeria.com     (Admin123*)     -> Administrador
--    empleado@heladeria.com  (Empleado123*)  -> Empleado
--    cliente@heladeria.com   (Cliente123*)   -> Cliente
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS finanzas;
DROP TABLE IF EXISTS detalle_pedido;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS roles;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- ROLES
-- ---------------------------------------------------------------------
CREATE TABLE roles (
  id     INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (id, nombre) VALUES
  (1, 'Administrador'),
  (2, 'Empleado'),
  (3, 'Cliente');

-- ---------------------------------------------------------------------
-- USUARIOS: administradores, empleados y clientes.
--   contrasena guarda el HASH de password_hash() (bcrypt)
-- ---------------------------------------------------------------------
CREATE TABLE usuarios (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  nombre     VARCHAR(50)  NOT NULL,
  apellido   VARCHAR(50)  NOT NULL DEFAULT '',
  correo     VARCHAR(100) NOT NULL UNIQUE,
  contrasena VARCHAR(255) NOT NULL,
  id_rol     INT          NOT NULL,
  telefono   VARCHAR(15)  NULL,
  direccion  VARCHAR(255) NULL,
  activo     TINYINT(1)   NOT NULL DEFAULT 1,
  creado_en  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_usuarios_roles FOREIGN KEY (id_rol) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- CATEGORIAS del catálogo (icono = nombre de Bootstrap Icons)
-- ---------------------------------------------------------------------
CREATE TABLE categorias (
  id     INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(40) NOT NULL UNIQUE,
  icono  VARCHAR(30) NOT NULL DEFAULT 'snow'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- PRODUCTOS: helados, paletas, postres y bebidas
--   activo = 0 oculta el producto del catálogo sin borrarlo
-- ---------------------------------------------------------------------
CREATE TABLE productos (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  id_categoria INT           NOT NULL,
  nombre       VARCHAR(60)   NOT NULL,
  descripcion  VARCHAR(255)  NOT NULL DEFAULT '',
  precio       DECIMAL(8,2)  NOT NULL,
  stock        INT           NOT NULL DEFAULT 0,
  activo       TINYINT(1)    NOT NULL DEFAULT 1,
  CONSTRAINT fk_productos_categoria FOREIGN KEY (id_categoria) REFERENCES categorias(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- PEDIDOS (cabecera): quién pidió, cómo se entrega y en qué estado está
-- ---------------------------------------------------------------------
CREATE TABLE pedidos (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente   INT           NOT NULL,
  id_empleado  INT           NULL,
  fecha        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  tipo_entrega ENUM('Retiro','Domicilio') NOT NULL DEFAULT 'Retiro',
  direccion    VARCHAR(255)  NULL,
  notas        VARCHAR(255)  NULL,
  total        DECIMAL(10,2) NOT NULL DEFAULT 0,
  estado       ENUM('Pendiente','Preparando','Listo','Entregado','Cancelado') NOT NULL DEFAULT 'Pendiente',
  INDEX idx_pedidos_estado (estado),
  CONSTRAINT fk_pedidos_cliente  FOREIGN KEY (id_cliente)  REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_pedidos_empleado FOREIGN KEY (id_empleado) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- DETALLE_PEDIDO: productos de cada pedido. Se guarda el precio del
-- momento de la compra para que el historial no cambie si sube el precio.
-- ---------------------------------------------------------------------
CREATE TABLE detalle_pedido (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  id_pedido       INT          NOT NULL,
  id_producto     INT          NOT NULL,
  cantidad        INT          NOT NULL,
  precio_unitario DECIMAL(8,2) NOT NULL,
  subtotal        DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_detalle_pedido   FOREIGN KEY (id_pedido)   REFERENCES pedidos(id)   ON DELETE CASCADE,
  CONSTRAINT fk_detalle_producto FOREIGN KEY (id_producto) REFERENCES productos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- FINANZAS: movimientos de dinero. Al entregar un pedido se registra
-- su ingreso automáticamente (id_pedido). Los gastos se registran a mano.
-- ---------------------------------------------------------------------
CREATE TABLE finanzas (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  fecha      DATE          NOT NULL,
  tipo       ENUM('Ingreso','Gasto') NOT NULL,
  concepto   VARCHAR(120)  NOT NULL,
  monto      DECIMAL(10,2) NOT NULL,
  id_pedido  INT           NULL UNIQUE,
  creado_por INT           NULL,
  INDEX idx_finanzas_fecha (fecha),
  CONSTRAINT fk_finanzas_pedido  FOREIGN KEY (id_pedido)  REFERENCES pedidos(id)  ON DELETE SET NULL,
  CONSTRAINT fk_finanzas_usuario FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- DATOS DE PRUEBA
-- =====================================================================
INSERT INTO usuarios (id, nombre, apellido, correo, contrasena, id_rol, telefono, direccion) VALUES
  (1, 'Admin',     'Polar',    'admin@heladeria.com',    '$2y$12$0ooTPvwmT0j6bB7wL6.lI.eiqSNa2eRxNvX7dQwr1hHd8DTpiTSLu', 1, '0980000001', NULL),
  (2, 'Sofía',     'Mendoza',  'empleado@heladeria.com', '$2y$12$JcMI/bDzIfNrNg3nFVDuRe6ZHq7T9M5EFXX1xyJDi9C8y20uTf1UW', 2, '0980000002', NULL),
  (3, 'Andrés',    'Pérez',    'cliente@heladeria.com',  '$2y$12$CUGw1D/sh3tW1A9WbmCZ7.JNmdKJ9crmSveJAhaBb9uoDHu6c5Tg6', 3, '0980000003', 'Av. 9 de Octubre 120 y Malecón'),
  (4, 'Valentina', 'Ríos',     'valentina@correo.com',   '$2y$12$CUGw1D/sh3tW1A9WbmCZ7.JNmdKJ9crmSveJAhaBb9uoDHu6c5Tg6', 3, '0980000004', 'Cdla. Kennedy Norte, Mz. 5'),
  (5, 'Mateo',     'Salazar',  'mateo@correo.com',       '$2y$12$CUGw1D/sh3tW1A9WbmCZ7.JNmdKJ9crmSveJAhaBb9uoDHu6c5Tg6', 3, '0980000005', NULL);

INSERT INTO categorias (id, nombre, icono) VALUES
  (1, 'Helados',  'snow'),
  (2, 'Paletas',  'thermometer-snow'),
  (3, 'Postres',  'cake2'),
  (4, 'Bebidas',  'cup-straw');

INSERT INTO productos (id, id_categoria, nombre, descripcion, precio, stock) VALUES
  (1,  1, 'Vainilla clásica',        'Helado cremoso de vainilla natural (2 bolas)',        2.50, 40),
  (2,  1, 'Chocolate intenso',       'Chocolate amargo 70% con trozos de brownie',          2.75, 35),
  (3,  1, 'Fresa con crema',         'Fresas frescas con base de crema',                    2.50, 30),
  (4,  1, 'Mora silvestre',          'Sorbete de mora, sin lácteos',                        2.25, 25),
  (5,  1, 'Pistacho',                'Pistacho tostado con un toque de sal',                3.25, 15),
  (6,  2, 'Paleta de maracuyá',      'Paleta de fruta natural',                             1.50, 50),
  (7,  2, 'Paleta de coco',          'Coco con leche condensada',                           1.75, 3),
  (8,  3, 'Banana split',            'Tres sabores, banana, crema y salsa de chocolate',    5.50, 20),
  (9,  3, 'Copa brownie',            'Brownie tibio con helado de vainilla',                4.75, 18),
  (10, 4, 'Milkshake de chocolate',  'Batido espeso con crema chantilly',                   3.75, 22),
  (11, 4, 'Frappé de café',          'Café frío con helado de vainilla',                    3.50, 0);

-- Pedidos de ejemplo (fechas relativas a hoy)
INSERT INTO pedidos (id, id_cliente, id_empleado, fecha, tipo_entrega, direccion, notas, total, estado) VALUES
  (1, 3, 2,    NOW() - INTERVAL 3 DAY,     'Retiro',    NULL,                         NULL,              10.00, 'Entregado'),
  (2, 4, 2,    NOW() - INTERVAL 1 DAY,     'Domicilio', 'Cdla. Kennedy Norte, Mz. 5', 'Tocar el timbre', 11.00, 'Entregado'),
  (3, 3, NULL, NOW() - INTERVAL 40 MINUTE, 'Retiro',    NULL,                         NULL,               7.75, 'Pendiente'),
  (4, 5, 2,    NOW() - INTERVAL 20 MINUTE, 'Domicilio', 'Urdesa Central, calle 3',    'Sin chispas',      5.50, 'Preparando');

INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, subtotal) VALUES
  (1, 1,  1, 2.50, 2.50),  (1, 2, 1, 2.75, 2.75),  (1, 9, 1, 4.75, 4.75),
  (2, 8,  2, 5.50, 11.00),
  (3, 3,  1, 2.50, 2.50),  (3, 10, 1, 3.75, 3.75), (3, 6, 1, 1.50, 1.50),
  (4, 8,  1, 5.50, 5.50);

INSERT INTO finanzas (fecha, tipo, concepto, monto, id_pedido, creado_por) VALUES
  (CURDATE() - INTERVAL 10 DAY, 'Gasto',   'Compra de leche y crema',  45.00, NULL, 1),
  (CURDATE() - INTERVAL 6 DAY,  'Gasto',   'Frutas para sorbetes',     22.50, NULL, 1),
  (CURDATE() - INTERVAL 3 DAY,  'Ingreso', 'Pedido #1',                10.00, 1,    2),
  (CURDATE() - INTERVAL 2 DAY,  'Ingreso', 'Ventas en mostrador',     120.00, NULL, 1),
  (CURDATE() - INTERVAL 1 DAY,  'Ingreso', 'Pedido #2',                11.00, 2,    2),
  (CURDATE(),                   'Gasto',   'Pago de luz',              30.00, NULL, 1);
