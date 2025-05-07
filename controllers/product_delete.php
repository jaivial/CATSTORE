<?php
require_once __DIR__ . '/../includes/auth_middleware.php';
require_once __DIR__ . '/../controllers/AdminController.php';

// Verificar permisos de administrador
$adminController = new AdminController();
if (!$adminController->isAdmin()) {
    // Redirigir si no tiene permisos
    header('Location: /views/store/index.php');
    exit;
}

// Obtener ID del producto (aceptar tanto GET como POST)
$productId = 0;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $productId = intval($_GET['id']);
} elseif (isset($_POST['id']) && is_numeric($_POST['id'])) {
    $productId = intval($_POST['id']);
}

// Verificar si se proporcionó un ID válido
if ($productId <= 0) {
    // Redirigir con mensaje de error
    header('Location: /views/admin/products.php?error=1');
    exit;
}

// Procesar la eliminación del producto
$result = $adminController->deleteProduct($productId);

if ($result['success']) {
    // Redirigir con mensaje de éxito
    header('Location: /views/admin/products.php?success=1');
} else {
    // Redirigir con mensaje de error
    header('Location: /views/admin/products.php?error=1');
}
exit;
