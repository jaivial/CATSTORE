<?php

/**
 * Configuración de la conexión a la base de datos
 * Cat Store - Tienda de Gatos
 */

// Configuración de la base de datos
define('DB_HOST', '127.0.0.1'); // Usar IP en lugar de localhost
define('DB_USER', 'root'); // Cambiar por el usuario de la base de datos
define('DB_PASS', '');     // Cambiar por la contraseña de la base de datos
define('DB_NAME', 'gatos');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', '3306'); // Puerto de MySQL

// Configuración de cookies
define('COOKIE_DURATION', 60 * 60 * 24 * 7); // 1 semana en segundos
define('COOKIE_PATH', '/');
define('COOKIE_DOMAIN', '');
define('COOKIE_SECURE', false); // Cambiar a true en producción con HTTPS
define('COOKIE_HTTPONLY', true);

// Función para conectar a la base de datos
function connectDB()
{
    try {
        // Usar IP y puerto explícito en lugar de socket
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // En un entorno de producción, registrar el error en lugar de mostrarlo
        die('Error de conexión: ' . $e->getMessage());
    }
}
