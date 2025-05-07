<?php

/**
 * Middleware de autenticación
 * Cat Store - Tienda de Gatos
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir configuración de la base de datos
require_once __DIR__ . '/../config/db_config.php';

/**
 * Verifica si el usuario está autenticado
 * @return bool True si está autenticado, False si no
 */
function isAuthenticated()
{
    // Comprobar si hay una sesión activa
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        return true;
    }

    // Comprobar si hay una cookie válida
    if (isset($_COOKIE['usuario']) && !empty($_COOKIE['usuario'])) {
        // Validar la cookie en la base de datos
        $pdo = connectDB();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuario WHERE username = ?");
        $stmt->execute([$_COOKIE['usuario']]);

        if ($stmt->fetchColumn() > 0) {
            // Usuario válido, establecer sesión
            $_SESSION['user_id'] = $_COOKIE['usuario'];
            return true;
        } else {
            // Cookie inválida, eliminarla
            deleteUserCookie('usuario');
        }
    }

    return false;
}

/**
 * Establece una cookie con la duración definida en la configuración
 * @param string $name Nombre de la cookie
 * @param string $value Valor de la cookie
 * @return bool True si se estableció correctamente
 */
function setUserCookie($name, $value)
{
    return setcookie(
        $name,
        $value,
        [
            'expires' => time() + COOKIE_DURATION,
            'path' => COOKIE_PATH,
            'domain' => COOKIE_DOMAIN,
            'secure' => COOKIE_SECURE,
            'httponly' => COOKIE_HTTPONLY,
            'samesite' => 'Strict'
        ]
    );
}

/**
 * Elimina una cookie
 * @param string $name Nombre de la cookie
 * @return bool True si se eliminó correctamente
 */
function deleteUserCookie($name)
{
    return setcookie(
        $name,
        '',
        [
            'expires' => time() - 3600,
            'path' => COOKIE_PATH,
            'domain' => COOKIE_DOMAIN,
            'secure' => COOKIE_SECURE,
            'httponly' => COOKIE_HTTPONLY,
            'samesite' => 'Strict'
        ]
    );
}

/**
 * Redirige a la página de login si el usuario no está autenticado
 * @param bool $isAdmin Indica si se requiere ser administrador
 * @return void
 */
function requireAuth($isAdmin = false)
{
    if (!isAuthenticated()) {
        header('Location: /views/auth/login.php');
        exit;
    }

    // Si se requiere ser administrador, verificar el rol
    if ($isAdmin) {
        $pdo = connectDB();
        $stmt = $pdo->prepare("SELECT username FROM usuario WHERE username = ? AND username = 'javial'");
        $stmt->execute([$_SESSION['user_id']]);

        if ($stmt->rowCount() === 0) {
            header('Location: /index.php?error=unauthorized');
            exit;
        }
    }
}

/**
 * Redirige a la página principal si el usuario ya está autenticado
 * @return void
 */
function redirectIfAuthenticated()
{
    if (isAuthenticated()) {
        header('Location: /views/store/index.php');
        exit;
    }
}
