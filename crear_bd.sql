CREATE DATABASE IF NOT EXISTS gatos;

USE gatos;

CREATE TABLE IF NOT EXISTS usuario (
  username VARCHAR(100) NOT NULL PRIMARY KEY, 
  contrasenya VARCHAR(100) NOT NULL, 
  nombre VARCHAR(100) NOT NULL, 
  apellido VARCHAR(200) NOT NULL, 
  email VARCHAR(200) NOT NULL
); 

CREATE TABLE IF NOT EXISTS animal (
  id INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT, 
  nombre VARCHAR(50) NOT NULL, 
  tipo VARCHAR(50) NOT NULL, 
  color VARCHAR(20) NOT NULL, 
  sexo BOOLEAN NOT NULL, 
  precio DECIMAL(10,2) NOT NULL, 
  foto LONGBLOB NULL, 
  fecha_anyadido TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
); 

CREATE TABLE IF NOT EXISTS carrito (
  id_animal INT(11) NOT NULL,
  username_usuario VARCHAR(50) NOT NULL,
  FOREIGN KEY (id_animal) REFERENCES animal (id),
  FOREIGN KEY (username_usuario) REFERENCES usuario(username)
);

CREATE TABLE IF NOT EXISTS compra (
  fecha DATETIME NOT NULL,
  id_animal INT(11) NOT NULL,
  username_usuario VARCHAR(100) NOT NULL,
  FOREIGN KEY (id_animal) REFERENCES animal(id),
  FOREIGN KEY (username_usuario) REFERENCES usuario(username)
);

-- Insertar un usuario administrador predeterminado
INSERT INTO usuario (username, contrasenya, nombre, apellido, email)
VALUES ('javial', '12', 'Javier', 'Administrador', 'admin@floridacats.com'); 