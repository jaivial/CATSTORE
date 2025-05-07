-- Script SQL para insertar datos de muestra
-- Cat Store - Tienda de Gatos

-- Usar la base de datos
USE gatos;

-- Configurar variables necesarias para cargar archivos
SET GLOBAL max_allowed_packet = 16777216; -- 16MB

-- Desactivar restricciones de clave foránea temporalmente
SET FOREIGN_KEY_CHECKS = 0;

-- Insertar registros de gatos
-- Nota: Las imágenes se cargarán desde el directorio assets/img/cats
-- Estos inserts son de ejemplo. En un entorno real, se debe ajustar la ruta de las imágenes

-- Insertar gatos de ejemplo sin imágenes (se usará un placeholder)
INSERT INTO animal (nombre, tipo, color, sexo, precio) 
VALUES ('Michi', 'Munchkin', 'Gris', 1, 344.55);

INSERT INTO animal (nombre, tipo, color, sexo, precio) 
VALUES ('Whiskas', 'Munchkin', 'Gris y blanco', 0, 500);

INSERT INTO animal (nombre, tipo, color, sexo, precio) 
VALUES ('Luna', 'ShortHair', 'Marron y blanco', 1, 700.50);

INSERT INTO animal (nombre, tipo, color, sexo, precio) 
VALUES ('Lila', 'British', 'Gris', 1, 420.50);

INSERT INTO animal (nombre, tipo, color, sexo, precio) 
VALUES ('Garfield', 'OrangeStray', 'Naranja', 0, 999);

INSERT INTO animal (nombre, tipo, color, sexo, precio) 
VALUES ('Pepe', 'Munchkin', 'Negro y blanco', 1, 345);

INSERT INTO animal (nombre, tipo, color, sexo, precio) 
VALUES ('Lucas', 'British', 'Gris', 1, 700.50);

INSERT INTO animal (nombre, tipo, color, sexo, precio) 
VALUES ('Toni', 'Stray', 'Gris', 1, 202);

INSERT INTO animal (nombre, tipo, color, sexo, precio) 
VALUES ('Viqui', 'Argentina', 'Negro y blanco', 0, 550);

INSERT INTO animal (nombre, tipo, color, sexo, precio) 
VALUES ('Ramses', 'Egiptshorthair', 'Blanco', 1, 700.50);

-- Reactivar restricciones de clave foránea
SET FOREIGN_KEY_CHECKS = 1; 