<?php

/**
 * Script para probar la conexión a la base de datos
 */

// Incluir configuración de la base de datos
require_once __DIR__ . '/config/db_config.php';

echo "<h1>Probando conexión a la base de datos</h1>";

try {
    // Intentar conectar a la base de datos
    $conn = connectDB();
    echo "<p style='color: green; font-weight: bold;'>✅ Conexión exitosa a la base de datos</p>";

    // Probar una consulta simple
    $stmt = $conn->query("SELECT * FROM usuario WHERE username = 'javial'");
    $user = $stmt->fetch();

    if ($user) {
        echo "<p>Usuario administrador encontrado:</p>";
        echo "<ul>";
        echo "<li>Username: " . htmlspecialchars($user['username']) . "</li>";
        echo "<li>Nombre: " . htmlspecialchars($user['nombre']) . "</li>";
        echo "<li>Apellido: " . htmlspecialchars($user['apellido']) . "</li>";
        echo "<li>Email: " . htmlspecialchars($user['email']) . "</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ Usuario administrador no encontrado</p>";
    }

    // Mostrar todas las tablas
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<p>Tablas en la base de datos:</p>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . htmlspecialchars($table) . "</li>";
    }
    echo "</ul>";
} catch (PDOException $e) {
    echo "<p style='color: red; font-weight: bold;'>❌ Error de conexión: " . $e->getMessage() . "</p>";

    // Mostrar información adicional para depuración
    echo "<h2>Información de depuración:</h2>";
    echo "<ul>";
    echo "<li>Host: " . DB_HOST . "</li>";
    echo "<li>Puerto: " . (defined('DB_PORT') ? DB_PORT : 'No definido') . "</li>";
    echo "<li>Usuario: " . DB_USER . "</li>";
    echo "<li>Base de datos: " . DB_NAME . "</li>";
    echo "<li>PHP Version: " . phpversion() . "</li>";
    echo "<li>PDO Drivers: " . implode(', ', PDO::getAvailableDrivers()) . "</li>";
    echo "</ul>";

    // Sugerencias para solucionar el problema
    echo "<h2>Sugerencias:</h2>";
    echo "<ul>";
    echo "<li>Verifica que MySQL esté en ejecución</li>";
    echo "<li>Comprueba que el usuario y contraseña sean correctos</li>";
    echo "<li>Asegúrate de que la base de datos 'gatos' exista</li>";
    echo "<li>Prueba cambiando 'localhost' por '127.0.0.1' en la configuración</li>";
    echo "<li>Si usas XAMPP/MAMP, verifica el puerto de MySQL (normalmente 3306 o 8889)</li>";
    echo "</ul>";
}
