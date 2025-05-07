<?php

/**
 * API de administración
 * Cat Store - Tienda de Gatos
 */

require_once __DIR__ . '/../controllers/AdminController.php';
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
$adminController = new AdminController();

// Verificar permisos de administrador
if (!$adminController->isAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'No tienes permisos de administrador'
    ]);
    exit;
}

// Determinar la acción a realizar
$action = isset($_GET['action']) ? $_GET['action'] : '';
$result = [];

switch ($action) {
    case 'get_products':
        // Obtener parámetros de ordenación
        $orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'id';
        $orderDir = isset($_GET['orderDir']) ? $_GET['orderDir'] : 'ASC';

        $result = $adminController->getAllProducts($orderBy, $orderDir);
        break;

    case 'get_product':
        // Obtener ID del producto
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id > 0) {
            $result = $adminController->getProductById($id);
        } else {
            $result = [
                'success' => false,
                'message' => 'ID de producto no válido'
            ];
        }
        break;

    case 'create_product':
        // Verificar que la solicitud sea de tipo POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $result = [
                'success' => false,
                'message' => 'Método no permitido para esta acción'
            ];
            break;
        }

        // Obtener datos del formulario
        $productData = [
            'nombre' => isset($_POST['nombre']) ? trim($_POST['nombre']) : '',
            'tipo' => isset($_POST['tipo']) ? trim($_POST['tipo']) : '',
            'color' => isset($_POST['color']) ? trim($_POST['color']) : '',
            'sexo' => isset($_POST['sexo']) ? $_POST['sexo'] : '',
            'precio' => isset($_POST['precio']) ? $_POST['precio'] : ''
        ];

        // Procesar imagen si existe
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $imageData = file_get_contents($_FILES['foto']['tmp_name']);
            $productData['foto'] = $imageData;
        }

        $result = $adminController->createProduct($productData);
        break;

    case 'update_product':
        // Verificar que la solicitud sea de tipo POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $result = [
                'success' => false,
                'message' => 'Método no permitido para esta acción'
            ];
            break;
        }

        // Obtener ID del producto
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if ($id <= 0) {
            $result = [
                'success' => false,
                'message' => 'ID de producto no válido'
            ];
            break;
        }

        // Obtener datos del formulario
        $productData = [];

        if (isset($_POST['nombre'])) {
            $productData['nombre'] = trim($_POST['nombre']);
        }

        if (isset($_POST['tipo'])) {
            $productData['tipo'] = trim($_POST['tipo']);
        }

        if (isset($_POST['color'])) {
            $productData['color'] = trim($_POST['color']);
        }

        if (isset($_POST['sexo'])) {
            $productData['sexo'] = $_POST['sexo'];
        }

        if (isset($_POST['precio'])) {
            $productData['precio'] = $_POST['precio'];
        }

        // Procesar imagen si existe
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $imageData = file_get_contents($_FILES['foto']['tmp_name']);
            $productData['foto'] = $imageData;
        }

        $result = $adminController->updateProduct($id, $productData);
        break;

    case 'delete_product':
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
        $id = isset($data['id']) ? intval($data['id']) : 0;

        if ($id > 0) {
            $result = $adminController->deleteProduct($id);
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
