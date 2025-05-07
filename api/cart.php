<?php

/**
 * API del carrito
 * Cat Store - Tienda de Gatos
 */

require_once __DIR__ . '/../controllers/CartController.php';

// Manejo global de errores para evitar respuestas no JSON
set_exception_handler(function ($e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
    exit;
});
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "Error PHP: $errstr en $errfile:$errline"
    ]);
    exit;
});

// Verificar que la solicitud sea de tipo GET o POST
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit;
}

// Instanciar el controlador
$cartController = new CartController();

// Determinar la acción a realizar
$action = isset($_GET['action']) ? $_GET['action'] : '';
$result = [];

try {
    switch ($action) {
        case 'add':
            // Verificar que la solicitud sea de tipo POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $result = [
                    'success' => false,
                    'message' => 'Método no permitido para esta acción'
                ];
                break;
            }

            // Obtener datos de la solicitud
            $data = json_decode(file_get_contents('php://input'), true);
            $productId = isset($data['id']) ? intval($data['id']) : 0;

            if ($productId > 0) {
                $result = $cartController->addToCart($productId);
            } else {
                $result = [
                    'success' => false,
                    'message' => 'ID de producto no válido'
                ];
            }
            break;
        case 'remove':
            // Verificar que la solicitud sea de tipo POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $result = [
                    'success' => false,
                    'message' => 'Método no permitido para esta acción'
                ];
                break;
            }

            // Obtener datos de la solicitud
            $data = json_decode(file_get_contents('php://input'), true);
            $productId = isset($data['id']) ? intval($data['id']) : 0;

            if ($productId > 0) {
                $result = $cartController->removeFromCart($productId);
            } else {
                $result = [
                    'success' => false,
                    'message' => 'ID de producto no válido'
                ];
            }
            break;
        case 'clear':
            // Verificar que la solicitud sea de tipo POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $result = [
                    'success' => false,
                    'message' => 'Método no permitido para esta acción'
                ];
                break;
            }

            $result = $cartController->clearCart();
            break;
        case 'get':
            $result = $cartController->getCartContent();
            break;
        case 'count':
            $count = $cartController->getCartCount();
            $result = [
                'success' => true,
                'count' => $count
            ];
            break;
        case 'checkout':
            // Verificar que la solicitud sea de tipo POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $result = [
                    'success' => false,
                    'message' => 'Método no permitido para esta acción'
                ];
                break;
            }

            $result = $cartController->checkout();
            break;
        default:
            $result = [
                'success' => false,
                'message' => 'Acción no válida'
            ];
    }
} catch (Throwable $e) {
    $result = [
        'success' => false,
        'message' => 'Error inesperado: ' . $e->getMessage()
    ];
}

// Devolver respuesta JSON
header('Content-Type: application/json');
echo json_encode($result);
exit;
