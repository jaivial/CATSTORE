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

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/img/favicon/favicon-16x16.png">
    <link rel="manifest" href="/assets/img/favicon/site.webmanifest">
    <link rel="shortcut icon" href="/assets/img/favicon/favicon.ico">

    <!-- Meta tags para compartir en redes sociales -->
    <meta property="og:title" content="<?php echo $pageTitle; ?>">
    <meta property="og:description" content="Cat Store - Tu tienda de gatos de confianza. Encuentra tu compañero felino perfecto.">
    <meta property="og:image" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]"; ?>/assets/img/social/cat-store-social.png">
    <meta property="og:url" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
    <meta property="og:type" content="website">

    <!-- Meta tags para WhatsApp -->
    <meta property="og:site_name" content="Cat Store">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $pageTitle; ?>">
    <meta name="twitter:description" content="Cat Store - Tu tienda de gatos de confianza. Encuentra tu compañero felino perfecto.">
    <meta name="twitter:image" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]"; ?>/assets/img/social/cat-store-social.png">

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