<?php

/**
 * API del perfil de usuario
 * Cat Store - Tienda de Gatos
 */

require_once __DIR__ . '/../controllers/ProfileController.php';
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

// Verificar autenticación
if (!isAuthenticated()) {
    header('HTTP/1.1 401 Unauthorized');
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no autenticado'
    ]);
    exit;
}

// Instanciar el controlador
$profileController = new ProfileController();
$username = $_SESSION['user_id'];

// Determinar la acción a realizar
$action = isset($_GET['action']) ? $_GET['action'] : '';
$result = [];

switch ($action) {
    case 'get_profile':
        $result = $profileController->getUserProfile($username);
        break;

    case 'update_profile':
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

        if (empty($data)) {
            $result = [
                'success' => false,
                'message' => 'No se recibieron datos'
            ];
            break;
        }

        $userData = [
            'nombre' => isset($data['nombre']) ? trim($data['nombre']) : '',
            'apellido' => isset($data['apellido']) ? trim($data['apellido']) : '',
            'email' => isset($data['email']) ? trim($data['email']) : ''
        ];

        $result = $profileController->updateUserProfile($username, $userData);
        break;

    case 'change_password':
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

        if (empty($data)) {
            $result = [
                'success' => false,
                'message' => 'No se recibieron datos'
            ];
            break;
        }

        $currentPassword = isset($data['current_password']) ? $data['current_password'] : '';
        $newPassword = isset($data['new_password']) ? $data['new_password'] : '';
        $confirmPassword = isset($data['confirm_password']) ? $data['confirm_password'] : '';

        $result = $profileController->changePassword($username, $currentPassword, $newPassword, $confirmPassword);
        break;

    case 'get_purchase_history':
        $result = $profileController->getPurchaseHistory($username);
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
