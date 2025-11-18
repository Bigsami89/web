-- Crear base de datos
CREATE DATABASE IF NOT EXISTS aeropuerto
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_spanish_ci;

USE aeropuerto;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    contrasena VARCHAR(100) NOT NULL,
    nombre_completo VARCHAR(100) NOT NULL,
    tipo ENUM('admin','huesped') NOT NULL DEFAULT 'huesped'
) ENGINE=InnoDB;

-- Tabla de categorías de habitaciones
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- Tabla de habitaciones
CREATE TABLE IF NOT EXISTS habitaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion_corta VARCHAR(255) NOT NULL,
    descripcion_larga TEXT,
    precio_noche DECIMAL(10,2) NOT NULL,
    disponibles INT NOT NULL DEFAULT 0,
    imagen VARCHAR(100) NOT NULL,
    CONSTRAINT fk_habitaciones_categorias
      FOREIGN KEY (id_categoria)
      REFERENCES categorias(id)
      ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabla de imágenes de habitaciones (permite múltiples imágenes por habitación)
CREATE TABLE IF NOT EXISTS habitacion_imagenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_habitacion INT NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_imagenes_habitaciones
      FOREIGN KEY (id_habitacion)
      REFERENCES habitaciones(id)
      ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabla de reservaciones
CREATE TABLE IF NOT EXISTS reservaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    fecha DATETIME NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_reservaciones_usuarios
      FOREIGN KEY (id_usuario)
      REFERENCES usuarios(id)
      ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabla de detalle de reservaciones
CREATE TABLE IF NOT EXISTS reservacion_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_reservacion INT NOT NULL,
    id_habitacion INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_detalle_reservaciones
      FOREIGN KEY (id_reservacion)
      REFERENCES reservaciones(id)
      ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_detalle_habitaciones
      FOREIGN KEY (id_habitacion)
      REFERENCES habitaciones(id)
      ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Usuario administrador (claves de acceso para superusuario)
-- usuario: admin
-- contraseña: admin123
INSERT INTO usuarios (usuario, contrasena, nombre_completo, tipo)
VALUES ('admin', 'admin123', 'Administrador del sistema', 'admin')
ON DUPLICATE KEY UPDATE usuario = usuario;

-- Usuario huésped de ejemplo
-- usuario: invitado
-- contraseña: invitado123
INSERT INTO usuarios (usuario, contrasena, nombre_completo, tipo)
VALUES ('invitado', 'invitado123', 'Huésped de ejemplo', 'huesped')
ON DUPLICATE KEY UPDATE usuario = usuario;

-- Categorías de ejemplo
INSERT INTO categorias (nombre) VALUES
('Económica'),
('Negocios'),
('Suite')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Habitaciones de ejemplo
INSERT INTO habitaciones (id_categoria, nombre, descripcion_corta, descripcion_larga,
                          precio_noche, disponibles, imagen)
VALUES
(1, 'Habitación sencilla', 'Cama individual, WiFi, TV básica',
 'Habitación económica cercana al aeropuerto con cama individual, WiFi gratuito y TV por cable.',
 800.00, 10, 'sencilla.jpg'),
(2, 'Habitación ejecutiva', 'Cama matrimonial, escritorio, WiFi rápido',
 'Habitación ideal para viajes de negocios, con escritorio amplio, silla ergonómica y WiFi de alta velocidad.',
 1500.00, 8, 'ejecutiva.jpg'),
(3, 'Suite aeropuerto', 'Sala, cama king, vista a pista',
 'Suite amplia con sala de estar, cama king size, minibar y vista a la pista de aterrizaje.',
 2500.00, 5, 'suite.jpg');

-- Migrar imágenes existentes a la nueva tabla
INSERT INTO habitacion_imagenes (id_habitacion, nombre_archivo)
SELECT id, imagen FROM habitaciones WHERE imagen IS NOT NULL AND imagen != '';
