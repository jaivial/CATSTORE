-- Script SQL para insertar datos de gatos en la tabla animal
-- Usando rutas absolutas para las imágenes

USE gatos;

-- Configurar variables necesarias para cargar archivos
SET GLOBAL max_allowed_packet = 16777216; -- 16MB
-- Verificar la ruta permitida para cargar archivos
-- SHOW VARIABLES LIKE 'secure_file_priv';

-- Desactivar restricciones de clave foránea temporalmente
SET FOREIGN_KEY_CHECKS = 0;

-- Vaciar tabla animal si es necesario
-- TRUNCATE TABLE animal;

-- Insertar registros de gatos
-- Nota: Reemplaza '/ruta/completa/del/proyecto' con la ruta absoluta de tu proyecto
-- Por ejemplo, en MacOS podría ser algo como '/Users/usuario/Documents/PROYECTOS/PROYECTOS_PUBLICADOS/FLORIDA-CATS'

INSERT INTO animal (nombre, tipo, color, sexo, precio, foto)
VALUES ('Michi', 'Munchkin', 'Gris', 1, 344.55, LOAD_FILE('/Users/usuario/Documents/PROYECTOS/PROYECTOS_PUBLICADOS/FLORIDA-CATS/img/gato1.jpg'));

INSERT INTO animal (nombre, tipo, color, sexo, precio, foto)
VALUES ('Whiskas', 'Munchkin', 'Gris y blanco', 0, 500, LOAD_FILE('/Users/usuario/Documents/PROYECTOS/PROYECTOS_PUBLICADOS/FLORIDA-CATS/img/gato2.jpg'));

INSERT INTO animal (nombre, tipo, color, sexo, precio, foto)
VALUES ('Michi', 'ShortHair', 'Marron y blanco', 1, 700.50, LOAD_FILE('/Users/usuario/Documents/PROYECTOS/PROYECTOS_PUBLICADOS/FLORIDA-CATS/img/gato3.jpg'));

INSERT INTO animal (nombre, tipo, color, sexo, precio, foto)
VALUES ('Lila', 'British', 'Gris', 1, 420.50, LOAD_FILE('/Users/usuario/Documents/PROYECTOS/PROYECTOS_PUBLICADOS/FLORIDA-CATS/img/gato4.jpg'));

INSERT INTO animal (nombre, tipo, color, sexo, precio, foto)
VALUES ('Garfield Jr.', 'OrangeStray', 'Naranja', 0, 999, LOAD_FILE('/Users/usuario/Documents/PROYECTOS/PROYECTOS_PUBLICADOS/FLORIDA-CATS/img/gato5.jpg'));

INSERT INTO animal (nombre, tipo, color, sexo, precio, foto)
VALUES ('Pepe', 'Munchkin', 'Negro y blanco', 1, 3450, LOAD_FILE('/Users/usuario/Documents/PROYECTOS/PROYECTOS_PUBLICADOS/FLORIDA-CATS/img/gato6.jpg'));

INSERT INTO animal (nombre, tipo, color, sexo, precio, foto)
VALUES ('Lucas', 'British', 'Gris', 1, 700.50, LOAD_FILE('/Users/usuario/Documents/PROYECTOS/PROYECTOS_PUBLICADOS/FLORIDA-CATS/img/gato7.jpg'));

INSERT INTO animal (nombre, tipo, color, sexo, precio, foto)
VALUES ('Toni', 'Stray', 'Gris', 1, 202, LOAD_FILE('/Users/usuario/Documents/PROYECTOS/PROYECTOS_PUBLICADOS/FLORIDA-CATS/img/gato8.jpg'));

INSERT INTO animal (nombre, tipo, color, sexo, precio, foto)
VALUES ('Viqui', 'Argentina', 'Negro y blanco', 0, 5, LOAD_FILE('/Users/usuario/Documents/PROYECTOS/PROYECTOS_PUBLICADOS/FLORIDA-CATS/img/gato9.jpg'));

INSERT INTO animal (nombre, tipo, color, sexo, precio, foto)
VALUES ('Ramses', 'Egiptshorthair', 'Blanco', 1, 700.50, LOAD_FILE('/Users/usuario/Documents/PROYECTOS/PROYECTOS_PUBLICADOS/FLORIDA-CATS/img/gato10.jpg'));

-- Reactivar restricciones de clave foránea
SET FOREIGN_KEY_CHECKS = 1; 