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

        // Registrar intento de conexión
        file_put_contents(
            __DIR__ . '/../debug_cart.log',
            date('Y-m-d H:i:s') . " - connectDB - " .
                "Intentando conectar a: " . DB_HOST . ":" . DB_PORT . " - " . DB_NAME .
                "\n",
            FILE_APPEND
        );

        // Establecer timeout de conexión para evitar bloqueos prolongados
        $options[PDO::ATTR_TIMEOUT] = 5; // 5 segundos de timeout

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

        // Verificar que la conexión funciona con una consulta simple
        $pdo->query('SELECT 1');

        // Registrar conexión exitosa
        file_put_contents(
            __DIR__ . '/../debug_cart.log',
            date('Y-m-d H:i:s') . " - connectDB - " .
                "Conexión exitosa a la base de datos" .
                "\n",
            FILE_APPEND
        );

        return $pdo;
    } catch (PDOException $e) {
        // Registrar error de conexión
        file_put_contents(
            __DIR__ . '/../debug_cart.log',
            date('Y-m-d H:i:s') . " - connectDB - " .
                "Error de conexión: " . $e->getMessage() .
                "\n",
            FILE_APPEND
        );

        // Mensajes amigables según el error
        $errorCode = $e->getCode();
        $errorMessage = 'Error de conexión a la base de datos';

        if ($errorCode == 2002) {
            $errorMessage = 'No se pudo conectar al servidor de base de datos. Verifique que MySQL está en ejecución.';
        } elseif ($errorCode == 1045) {
            $errorMessage = 'Credenciales incorrectas para la base de datos.';
        } elseif ($errorCode == 1049) {
            $errorMessage = 'Base de datos "' . DB_NAME . '" no existe.';
        } elseif ($errorCode == 2003) {
            $errorMessage = 'El servidor de base de datos está rechazando la conexión.';
        }

        // En un entorno de producción, no mostrar detalles técnicos
        if (
            strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false ||
            in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'])
        ) {
            // En entorno local, mostrar detalles del error
            die($errorMessage . ' (' . $e->getMessage() . ')');
        } else {
            // En producción, mostrar mensaje genérico
            die($errorMessage);
        }
    }
}