<?php

/**
 * Proxy simple para el carrito
 * Cat Store - Tienda de Gatos
 */

// Evitar que PHP muestre errores o advertencias en la salida
ini_set('display_errors', 0);
error_reporting(E_ERROR);

// Configurar cabeceras para JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Archivo para registro de depuración
$debugFile = __DIR__ . '/../debug_cart_proxy.log';

// Función para registrar mensajes de depuración
function logDebug($message)
{
    global $debugFile;
    try {
        file_put_contents(
            $debugFile,
            date('Y-m-d H:i:s') . " - " . $message . "\n",
            FILE_APPEND
        );
    } catch (Exception $e) {
        // Ignorar errores al escribir el log
    }
}

// Función para registrar información detallada de la sesión
function logSessionInfo()
{
    logDebug("---- Información de Sesión ----");
    logDebug("Session ID: " . session_id());
    logDebug("Session Status: " . session_status());
    logDebug("Usuario en sesión: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'No hay usuario en sesión'));
    logDebug("Cookies: " . json_encode($_COOKIE));
    logDebug("------------------------");
}

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Registrar información básica
logDebug("Proxy iniciado - IP: " . $_SERVER['REMOTE_ADDR'] . " - URI: " . $_SERVER['REQUEST_URI']);
logSessionInfo();

// Función para limpiar el buffer de salida
function clearOutputBuffer()
{
    while (ob_get_level()) {
        ob_end_clean();
    }
}

// Iniciar buffer de salida para capturar cualquier error o advertencia
ob_start();

// Registrar una función de cierre para capturar cualquier salida no esperada
register_shutdown_function(function () {
    // Obtener y borrar el buffer de salida
    $output = ob_get_clean();

    // Verificar si hay algún error no capturado
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // En caso de error fatal, registrar y devolver respuesta JSON
        logDebug("Error fatal: " . $error['message'] . " en " . $error['file'] . ":" . $error['line']);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error en el servidor: ' . $error['message'],
            'error' => true
        ]);
        exit;
    }

    // Si hay output inesperado y no es un JSON válido, retornar un JSON válido
    if (!empty($output)) {
        logDebug("Output capturado: " . substr($output, 0, 200) . (strlen($output) > 200 ? '...' : ''));

        // Intentar extraer JSON de la salida
        $jsonStart = strpos($output, '{');
        $jsonEnd = strrpos($output, '}');

        if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
            // Extraer la parte JSON
            $possibleJson = substr($output, $jsonStart, $jsonEnd - $jsonStart + 1);
            $jsonData = json_decode($possibleJson);

            if (json_last_error() === JSON_ERROR_NONE) {
                // Es un JSON válido dentro de otro contenido, enviarlo
                logDebug("JSON extraído con éxito de output mixto");
                header('Content-Type: application/json');
                echo $possibleJson;
                exit;
            }
        }

        // No es un JSON válido o no se pudo extraer, enviar respuesta de error
        logDebug("No se pudo extraer JSON válido del output");
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error en el servidor: Output inesperado',
            'debug_output' => mb_substr($output, 0, 200), // Incluir parte del output para depuración
            'error' => true
        ]);
        exit;
    }
});

try {
    // Incluir controlador del carrito
    try {
        // Verificar que el archivo existe
        if (!file_exists(__DIR__ . '/../controllers/CartController.php')) {
            throw new Exception('Archivo CartController.php no encontrado');
        }

        require_once __DIR__ . '/../controllers/CartController.php';

        // Verificar que la clase CartController existe
        if (!class_exists('CartController')) {
            throw new Exception('Clase CartController no definida');
        }
    } catch (Exception $e) {
        logDebug("Error al cargar CartController: " . $e->getMessage());
        clearOutputBuffer();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error al cargar el controlador: ' . $e->getMessage(),
            'error' => true
        ]);
        exit;
    }

    // Instanciar controlador
    $cartController = new CartController();

    // Obtener acción
    $action = isset($_GET['action']) ? $_GET['action'] : 'get';
    $result = [];

    // Registrar acción solicitada
    logDebug("Acción solicitada: " . $action . " - Método: " . $_SERVER['REQUEST_METHOD']);

    // Procesar acción
    switch ($action) {
        case 'get':
            $result = $cartController->getCartContent();
            break;
        case 'add':
            // Procesamiento para añadir al carrito
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $productId = isset($input['id']) ? intval($input['id']) : 0;

                logDebug("Añadiendo producto al carrito - ID: " . $productId);

                if ($productId > 0) {
                    $result = $cartController->addToCart($productId);
                } else {
                    $result = ['success' => false, 'message' => 'ID de producto no válido'];
                }
            } else {
                $result = ['success' => false, 'message' => 'Método no permitido'];
            }
            break;
        case 'remove':
            // Procesamiento para eliminar del carrito
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $productId = isset($input['id']) ? intval($input['id']) : 0;
                $username = isset($input['username']) ? $input['username'] : null;

                logDebug("Eliminando producto del carrito - ID: " . $productId . " - Username: " . ($username ?: 'no especificado'));

                if ($productId > 0) {
                    $result = $cartController->removeFromCart($productId, $username);
                } else {
                    $result = ['success' => false, 'message' => 'ID de producto no válido'];
                }
            } else {
                $result = ['success' => false, 'message' => 'Método no permitido'];
            }
            break;
        case 'clear':
            // Procesamiento para vaciar el carrito
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                logDebug("Vaciando carrito");
                $result = $cartController->clearCart();
            } else {
                $result = ['success' => false, 'message' => 'Método no permitido'];
            }
            break;
        case 'checkout':
            // Procesamiento para finalizar compra
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                logDebug("Finalizando compra");
                $result = $cartController->checkout();
            } else {
                $result = ['success' => false, 'message' => 'Método no permitido'];
            }
            break;
        default:
            $result = ['success' => false, 'message' => 'Acción no válida'];
    }

    // Asegurar que el resultado sea un array
    if (!is_array($result)) {
        logDebug("Resultado inválido del controlador (no es array)");
        $result = ['success' => false, 'message' => 'Respuesta inválida'];
    }

    // Registrar resultado (limitado para no llenar el log)
    logDebug("Resultado: " . json_encode(array_slice($result, 0, 3)));

    // Limpiar cualquier salida anterior
    clearOutputBuffer();

    // Asegurar estructura correcta del resultado
    if (!isset($result['success'])) {
        $result['success'] = true;
    }

    // Verificar si hay items y agregarlos si no existen
    if (!isset($result['items'])) {
        $result['items'] = [];
    }

    // Verificar si hay total y agregarlo si no existe
    if (!isset($result['total'])) {
        $result['total'] = 0;
    }

    // Enviar respuesta JSON
    header('Content-Type: application/json');
    echo json_encode($result);
    logDebug("Respuesta enviada correctamente");
    exit;
} catch (Exception $e) {
    // Registrar la excepción
    logDebug("Excepción: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());

    // Limpiar cualquier salida anterior
    clearOutputBuffer();

    // Capturar cualquier excepción y devolver respuesta JSON de error
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error en el servidor: ' . $e->getMessage(),
        'error' => true
    ]);
    exit;
}
