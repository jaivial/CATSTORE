<?php

/**
 * Cabecera común
 * Cat Store - Tienda de Gatos
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir configuración de la base de datos si no está incluida
if (!function_exists('connectDB')) {
    require_once __DIR__ . '/../config/db_config.php';
}

// Incluir middleware de autenticación si no está incluido
if (!function_exists('isAuthenticated')) {
    require_once __DIR__ . '/../includes/auth_middleware.php';
}

// Título por defecto
$pageTitle = isset($pageTitle) ? $pageTitle : 'Cat Store - Tienda de Gatos';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/cart.css">
    <?php if (isset($extraCss) && is_array($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body>
    <?php if (isset($includeNavbar) && $includeNavbar): ?>
        <?php include_once __DIR__ . '/navbar.php'; ?>
    <?php endif; ?>