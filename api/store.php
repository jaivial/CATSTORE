<?php

/**
 * API de la tienda
 * Cat Store - Tienda de Gatos
 */

require_once __DIR__ . '/../controllers/StoreController.php';
require_once __DIR__ . '/../includes/auth_middleware.php';

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
$storeController = new StoreController();

// Determinar la acción a realizar
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$result = [];

switch ($action) {
    case 'list':
        // Procesar parámetros de la solicitud
        $params = $storeController->processRequestParams();

        // Obtener productos según los filtros
        if (!empty($params['filters'])) {
            $result = $storeController->getFilteredAnimals(
                $params['filters'],
                $params['orderBy'],
                $params['orderDir']
            );
        } else {
            $result = $storeController->getAllAnimals(
                $params['orderBy'],
                $params['orderDir']
            );
        }
        break;

    case 'detail':
        // Obtener ID del producto
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id > 0) {
            $result = $storeController->getAnimalById($id);
        } else {
            $result = [
                'success' => false,
                'message' => 'ID de producto no válido'
            ];
        }
        break;

    default:
        $result = [
            'success' => false,
            'message' => 'Acción no válida'
        ];
}

// Devolver respuesta JSON
header('Content-Type: application/json');
echo json_encode($result);
exit;
