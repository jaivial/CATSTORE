<?php

/**
 * Archivo principal
 * Cat Store - Tienda de Gatos
 */

require_once __DIR__ . '/includes/auth_middleware.php';

// Redirigir según el estado de autenticación
if (isAuthenticated()) {
    // Si está autenticado, redirigir a la tienda
    header('Location: /views/store/index.php');
} else {
    // Si no está autenticado, redirigir al login
    header('Location: /views/auth/login.php');
}

exit;
