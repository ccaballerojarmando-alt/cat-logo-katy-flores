-- Katy Flores: esquema inicial. Selecciona primero la base de datos en phpMyAdmin.
-- Compatible con MySQL / MariaDB y con bases creadas desde InfinityFree.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    usuario VARCHAR(60) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_usuario (usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categorias (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(80) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categorias_nombre (nombre),
    UNIQUE KEY uq_categorias_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tallas (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(30) NOT NULL,
    orden SMALLINT NOT NULL DEFAULT 0,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tallas_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS productos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    categoria_id INT UNSIGNED NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    slug VARCHAR(180) NOT NULL,
    precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    descripcion TEXT NOT NULL,
    estado ENUM('disponible','vendido') NOT NULL DEFAULT 'disponible',
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_productos_categoria (categoria_id),
    KEY idx_productos_estado (estado),
    KEY idx_productos_precio (precio),
    KEY idx_productos_nombre (nombre),
    CONSTRAINT fk_productos_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS producto_tallas (
    producto_id INT UNSIGNED NOT NULL,
    talla_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (producto_id, talla_id),
    KEY idx_producto_tallas_talla (talla_id),
    CONSTRAINT fk_producto_tallas_producto FOREIGN KEY (producto_id) REFERENCES productos(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_producto_tallas_talla FOREIGN KEY (talla_id) REFERENCES tallas(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS producto_imagenes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    producto_id INT UNSIGNED NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    texto_alternativo VARCHAR(180) NOT NULL DEFAULT '',
    orden TINYINT UNSIGNED NOT NULL DEFAULT 0,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_producto_imagenes_producto (producto_id),
    CONSTRAINT fk_producto_imagenes_producto FOREIGN KEY (producto_id) REFERENCES productos(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS configuracion (
    clave VARCHAR(80) NOT NULL,
    valor TEXT NOT NULL,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO categorias (nombre, slug) VALUES
('Canguros','canguros'),('Polos','polos'),('Poleras','poleras'),('Chamarras','chamarras'),
('Chompas','chompas'),('Sacos','sacos'),('Parkas','parkas');

INSERT IGNORE INTO tallas (nombre, orden) VALUES
('XS',1),('S',2),('M',3),('L',4),('XL',5),('XXL',6),('Talla única',7);

INSERT IGNORE INTO configuracion (clave, valor) VALUES
('nombre_tienda','Katy Flores'),
('eslogan','Prendas únicas, nuevas historias'),
('whatsapp',''),
('telefono',''),
('direccion',''),
('public_url',''),
('mensaje_inicio','Descubre prendas seleccionadas de nuestros fardos. Cada pieza tiene su propia historia.');

