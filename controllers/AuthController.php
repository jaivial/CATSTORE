<?php

/**
 * Controlador de autenticación
 * Cat Store - Tienda de Gatos
 */

require_once __DIR__ . '/../models/User.php';

class AuthController
{
    private $userModel;

    /**
     * Constructor de la clase
     */
    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Procesa el formulario de inicio de sesión
     * @return array Resultado del proceso (success, message, redirect)
     */
    public function processLogin()
    {
        // Verificar si se envió el formulario
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [
                'success' => false,
                'message' => 'Método no permitido',
                'redirect' => null
            ];
        }

        // Validar datos de entrada
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';

        if (empty($username) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Por favor, complete todos los campos',
                'redirect' => null
            ];
        }

        // Intentar iniciar sesión
        $result = $this->userModel->login($username, $password);

        if ($result) {
            return [
                'success' => true,
                'message' => 'Inicio de sesión exitoso',
                'redirect' => '/views/store/index.php',
                'user' => $result
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Nombre de usuario o contraseña incorrectos',
                'redirect' => null
            ];
        }
    }

    /**
     * Procesa el formulario de registro
     * @return array Resultado del proceso (success, message, redirect)
     */
    public function processRegister()
    {
        // Verificar si se envió el formulario
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [
                'success' => false,
                'message' => 'Método no permitido',
                'redirect' => null
            ];
        }

        // Validar datos de entrada
        $userData = [
            'username' => isset($_POST['username']) ? trim($_POST['username']) : '',
            'password' => isset($_POST['password']) ? trim($_POST['password']) : '',
            'nombre' => isset($_POST['nombre']) ? trim($_POST['nombre']) : '',
            'apellido' => isset($_POST['apellido']) ? trim($_POST['apellido']) : '',
            'email' => isset($_POST['email']) ? trim($_POST['email']) : ''
        ];

        // Verificar campos obligatorios
        foreach ($userData as $field => $value) {
            if (empty($value)) {
                return [
                    'success' => false,
                    'message' => 'Por favor, complete todos los campos',
                    'redirect' => null
                ];
            }
        }

        // Validar formato de email
        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'El formato del email no es válido',
                'redirect' => null
            ];
        }

        // Intentar registrar al usuario
        $result = $this->userModel->register($userData);

        if ($result === true) {
            return [
                'success' => true,
                'message' => 'Registro exitoso',
                'redirect' => '/views/store/index.php'
            ];
        } else {
            return [
                'success' => false,
                'message' => $result, // Mensaje de error del modelo
                'redirect' => null
            ];
        }
    }

    /**
     * Procesa el cierre de sesión
     * @return array Resultado del proceso (success, message, redirect)
     */
    public function processLogout()
    {
        $this->userModel->logout();

        return [
            'success' => true,
            'message' => 'Sesión cerrada correctamente',
            'redirect' => '/views/auth/login.php'
        ];
    }

    /**
     * Verifica si el usuario está autenticado
     * @return bool True si está autenticado, False si no
     */
    public function isLoggedIn()
    {
        return isAuthenticated();
    }

    /**
     * Obtiene los datos del usuario actual
     * @return array|bool Datos del usuario o false si no hay usuario autenticado
     */
    public function getCurrentUser()
    {
        return $this->userModel->getCurrentUser();
    }
}

// Instanciar el controlador si se accede directamente al archivo
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    $authController = new AuthController();

    // Procesar la acción solicitada
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    $result = [];

    switch ($action) {
        case 'login':
            $result = $authController->processLogin();
            break;
        case 'register':
            $result = $authController->processRegister();
            break;
        case 'logout':
            $result = $authController->processLogout();
            break;
        default:
            $result = [
                'success' => false,
                'message' => 'Acción no válida',
                'redirect' => null
            ];
    }

    // Devolver respuesta JSON
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}
