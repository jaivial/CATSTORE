<?php

/**
 * API simplificada del carrito
 * Cat Store - Tienda de Gatos
 */

// Configurar cabeceras para JSON y CORS
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Asegurar que siempre se devuelve un JSON válido, incluso ante errores fatales
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Si ha habido un error fatal, asegurar que se envíe un JSON válido
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error Fatal: ' . $error['message'],
            'file' => $error['file'],
            'line' => $error['line']
        ]);
    }
});

// Registro para depuración
$logFile = __DIR__ . '/../debug_cart_api.log';
try {
    file_put_contents(
        $logFile,
        date('Y-m-d H:i:s') . " - Solicitud recibida: " . $_SERVER['REQUEST_METHOD'] .
            " - Acción: " . (isset($_GET['action']) ? $_GET['action'] : 'none') .
            " - Usuario: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'sesión no iniciada') .
            " - URL: " . $_SERVER['REQUEST_URI'] .
            " - Referrer: " . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'directo') .
            " - Host: " . $_SERVER['HTTP_HOST'] .
            " - User Agent: " . $_SERVER['HTTP_USER_AGENT'] .
            " - Remote IP: " . $_SERVER['REMOTE_ADDR'] .
            "\n",
        FILE_APPEND
    );
} catch (Exception $e) {
    // No hacer nada si el log falla
}

// Manejar preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'CORS preflight accepted']);
    exit;
}

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Registrar información de la sesión
try {
    file_put_contents(
        $logFile,
        date('Y-m-d H:i:s') . " - Sesión iniciada - ID: " . session_id() .
            " - Usuario: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'no autenticado') .
            "\n",
        FILE_APPEND
    );
} catch (Exception $e) {
    // No hacer nada si el log falla
}

// Incluir archivos necesarios
require_once __DIR__ . '/../config/db_config.php';

// Verificar autenticación
function isUserAuthenticated()
{
    // Registrar información de autenticación
    global $logFile;

    // Comprobar si existe la variable de sesión user_id
    $sessionAuth = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

    // Comprobar si existe la cookie (respaldo)
    $cookieAuth = isset($_COOKIE['user_id']) && !empty($_COOKIE['user_id']);

    // Registrar toda la información de sesión y cookies
    file_put_contents(
        $logFile,
        date('Y-m-d H:i:s') . " - Datos de autenticación" .
            "\n - SESSION: " . json_encode($_SESSION) .
            "\n - COOKIES: " . json_encode($_COOKIE) .
            "\n - SESSION_ID: " . session_id() .
            "\n - SESSION_STATUS: " . session_status() .
            "\n",
        FILE_APPEND
    );

    // Registrar información de las verificaciones
    file_put_contents(
        $logFile,
        date('Y-m-d H:i:s') . " - Comprobación auth - Session: " .
            ($sessionAuth ? $_SESSION['user_id'] : 'no') .
            " - Cookie: " . ($cookieAuth ? $_COOKIE['user_id'] : 'no') . "\n",
        FILE_APPEND
    );

    // Si la autenticación por cookie está disponible pero la sesión no,
    // restaurar la sesión desde la cookie
    if ($cookieAuth && !$sessionAuth) {
        $_SESSION['user_id'] = $_COOKIE['user_id'];
        $sessionAuth = true;
        file_put_contents(
            $logFile,
            date('Y-m-d H:i:s') . " - Restaurada sesión desde cookie: " . $_COOKIE['user_id'] . "\n",
            FILE_APPEND
        );

        // Regenerar ID de sesión para mayor seguridad
        session_regenerate_id(true);
        file_put_contents(
            $logFile,
            date('Y-m-d H:i:s') . " - Regenerado ID de sesión: " . session_id() . "\n",
            FILE_APPEND
        );
    }

    return $sessionAuth || $cookieAuth; // Permitir autenticación por cualquier método
}

// Función para obtener los productos del carrito
function getCartItems()
{
    if (!isUserAuthenticated()) {
        return [
            'success' => false,
            'message' => 'Usuario no autenticado',
            'items' => [],
            'total' => 0
        ];
    }

    try {
        $db = connectDB();

        // Primero, obtener IDs y cantidad de cada animal en el carrito
        $countQuery = "SELECT id_animal, COUNT(*) AS cantidad
                      FROM carrito 
                      WHERE username_usuario = ? 
                      GROUP BY id_animal";

        $stmt = $db->prepare($countQuery);
        $stmt->execute([$_SESSION['user_id']]);
        $cartCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Si no hay elementos en el carrito, devolver array vacío
        if (empty($cartCounts)) {
            return [
                'success' => true,
                'items' => [],
                'total' => 0,
                'count' => 0
            ];
        }

        // Luego, obtener información detallada de cada animal
        $items = [];
        $total = 0;

        foreach ($cartCounts as $cartItem) {
            $animalQuery = "SELECT id, foto, nombre, tipo, sexo, color, precio
                          FROM animal 
                          WHERE id = ?";

            $stmt = $db->prepare($animalQuery);
            $stmt->execute([$cartItem['id_animal']]);
            $animal = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($animal) {
                // Añadir información adicional
                $animal['cantidad'] = $cartItem['cantidad'];
                $animal['username_usuario'] = $_SESSION['user_id'];

                // Procesar imagen si existe
                if (isset($animal['foto']) && !empty($animal['foto'])) {
                    $animal['foto'] = base64_encode($animal['foto']);
                }

                // Calcular subtotal
                $total += $animal['precio'] * $cartItem['cantidad'];

                // Añadir al array de items
                $items[] = $animal;
            }
        }

        // Registrar resultado para depuración
        $logFile = __DIR__ . '/../debug_cart.log';
        file_put_contents(
            $logFile,
            date('Y-m-d H:i:s') . " - getCartItems - " .
                "Items encontrados: " . count($items) . " - " .
                "Usuario: " . $_SESSION['user_id'] .
                "\n",
            FILE_APPEND
        );

        return [
            'success' => true,
            'items' => $items,
            'total' => $total,
            'count' => count($items)
        ];
    } catch (PDOException $e) {
        // Registrar el error para depuración
        $logFile = __DIR__ . '/../debug_cart.log';
        file_put_contents(
            $logFile,
            date('Y-m-d H:i:s') . " - getCartItems error - " .
                "Error: " . $e->getMessage() . " - " .
                "Usuario: " . $_SESSION['user_id'] .
                "\n",
            FILE_APPEND
        );

        return [
            'success' => false,
            'message' => 'Error en la base de datos: ' . $e->getMessage(),
            'items' => [],
            'total' => 0
        ];
    }
}

// Función para añadir producto al carrito
function addToCart($productId)
{
    if (!isUserAuthenticated()) {
        return [
            'success' => false,
            'message' => 'Usuario no autenticado'
        ];
    }

    try {
        $db = connectDB();

        // Verificar que el producto existe
        $stmt = $db->prepare("SELECT COUNT(*) FROM animal WHERE id = ?");
        $stmt->execute([$productId]);
        if ($stmt->fetchColumn() == 0) {
            return [
                'success' => false,
                'message' => 'El producto no existe'
            ];
        }

        // Añadir al carrito
        $stmt = $db->prepare("INSERT INTO carrito (id_animal, username_usuario) VALUES (?, ?)");
        $result = $stmt->execute([$productId, $_SESSION['user_id']]);

        if ($result) {
            // Contar items en el carrito
            $stmt = $db->prepare("SELECT COUNT(*) FROM carrito WHERE username_usuario = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $count = $stmt->fetchColumn();

            return [
                'success' => true,
                'message' => 'Producto añadido al carrito',
                'count' => $count
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al añadir al carrito'
            ];
        }
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error en la base de datos: ' . $e->getMessage()
        ];
    }
}

// Función para eliminar producto del carrito
function removeFromCart($productId, $username = null)
{
    if (!isUserAuthenticated()) {
        return [
            'success' => false,
            'message' => 'Usuario no autenticado'
        ];
    }

    try {
        $db = connectDB();

        // Usar el username proporcionado si está disponible, sino usar el de la sesión
        $userIdentifier = $username ?: $_SESSION['user_id'];

        // Eliminar del carrito (solo una unidad)
        $stmt = $db->prepare(
            "DELETE FROM carrito 
            WHERE id_animal = ? AND username_usuario = ? 
            LIMIT 1"
        );
        $result = $stmt->execute([$productId, $userIdentifier]);

        if ($result) {
            // Contar items en el carrito
            $stmt = $db->prepare("SELECT COUNT(*) FROM carrito WHERE username_usuario = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $count = $stmt->fetchColumn();

            return [
                'success' => true,
                'message' => 'Producto eliminado del carrito',
                'count' => $count
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al eliminar del carrito'
            ];
        }
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error en la base de datos: ' . $e->getMessage()
        ];
    }
}

// Función para vaciar el carrito
function clearCart()
{
    if (!isUserAuthenticated()) {
        return [
            'success' => false,
            'message' => 'Usuario no autenticado'
        ];
    }

    try {
        $db = connectDB();

        // Eliminar todos los productos del carrito del usuario
        $stmt = $db->prepare("DELETE FROM carrito WHERE username_usuario = ?");
        $result = $stmt->execute([$_SESSION['user_id']]);

        if ($result) {
            return [
                'success' => true,
                'message' => 'Carrito vaciado correctamente',
                'count' => 0
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al vaciar el carrito'
            ];
        }
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error en la base de datos: ' . $e->getMessage()
        ];
    }
}

// Función para finalizar la compra
function checkout()
{
    if (!isUserAuthenticated()) {
        return [
            'success' => false,
            'message' => 'Usuario no autenticado'
        ];
    }

    try {
        $db = connectDB();
        $db->beginTransaction();

        // Obtener productos en el carrito
        $stmt = $db->prepare("SELECT id_animal FROM carrito WHERE username_usuario = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $cartItems = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($cartItems)) {
            $db->rollBack();
            return [
                'success' => false,
                'message' => 'El carrito está vacío'
            ];
        }

        // Insertar en la tabla de compras
        $now = date('Y-m-d H:i:s');
        $stmt = $db->prepare("INSERT INTO compra (fecha, id_animal, username_usuario) VALUES (?, ?, ?)");

        foreach ($cartItems as $productId) {
            $result = $stmt->execute([$now, $productId, $_SESSION['user_id']]);
            if (!$result) {
                $db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Error al procesar la compra'
                ];
            }
        }

        // Vaciar el carrito
        $stmt = $db->prepare("DELETE FROM carrito WHERE username_usuario = ?");
        $result = $stmt->execute([$_SESSION['user_id']]);

        if (!$result) {
            $db->rollBack();
            return [
                'success' => false,
                'message' => 'Error al finalizar la compra'
            ];
        }

        $db->commit();
        return [
            'success' => true,
            'message' => 'Compra realizada con éxito',
            'count' => 0
        ];
    } catch (PDOException $e) {
        if (isset($db)) {
            $db->rollBack();
        }
        return [
            'success' => false,
            'message' => 'Error en la base de datos: ' . $e->getMessage()
        ];
    }
}

// Procesar la solicitud
$action = isset($_GET['action']) ? $_GET['action'] : '';
$result = ['success' => false, 'message' => 'Acción no especificada'];

try {
    switch ($action) {
        case 'get':
            $result = getCartItems();
            break;

        case 'add':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $productId = isset($data['id']) ? intval($data['id']) : 0;

                if ($productId > 0) {
                    $result = addToCart($productId);
                } else {
                    $result = ['success' => false, 'message' => 'ID de producto no válido'];
                }
            } else {
                $result = ['success' => false, 'message' => 'Método no permitido'];
            }
            break;

        case 'remove':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $productId = isset($data['id']) ? intval($data['id']) : 0;
                $username = isset($data['username']) ? $data['username'] : null;

                if ($productId > 0) {
                    $result = removeFromCart($productId, $username);
                } else {
                    $result = ['success' => false, 'message' => 'ID de producto no válido'];
                }
            } else {
                $result = ['success' => false, 'message' => 'Método no permitido'];
            }
            break;

        case 'clear':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = clearCart();
            } else {
                $result = ['success' => false, 'message' => 'Método no permitido'];
            }
            break;

        case 'checkout':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = checkout();
            } else {
                $result = ['success' => false, 'message' => 'Método no permitido'];
            }
            break;

        default:
            $result = ['success' => false, 'message' => 'Acción no válida'];
    }
} catch (Exception $e) {
    $result = [
        'success' => false,
        'message' => 'Error interno: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ];
}

// Registrar el resultado antes de enviarlo
try {
    file_put_contents(
        $logFile,
        date('Y-m-d H:i:s') . " - Respuesta: " . json_encode($result) . "\n",
        FILE_APPEND
    );
} catch (Exception $e) {
    // No hacer nada si el log falla
}

// Asegurar que result sea un array válido
if (!is_array($result)) {
    $result = ['success' => false, 'message' => 'Resultado inválido'];
}

// Comprobar y sanear datos para JSON
function sanitizeForJson($data)
{
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = sanitizeForJson($value);
            } elseif (is_string($value)) {
                // Caso especial para datos de imagen en base64
                if ($key === 'foto' && !empty($value)) {
                    // Verificar si ya es base64 válido
                    if (base64_decode($value, true) === false) {
                        // No es base64 válido, intentar codificar
                        try {
                            $data[$key] = base64_encode($value);
                        } catch (Exception $e) {
                            // Si falla, dejar en blanco para evitar errores
                            $data[$key] = '';
                        }
                    }
                    // Si ya es base64 válido, dejarlo como está
                    continue;
                }

                // Para otros strings, verificar codificación UTF-8
                if (!mb_check_encoding($value, 'UTF-8')) {
                    // Intentar detectar la codificación y convertir
                    try {
                        $data[$key] = mb_convert_encoding($value, 'UTF-8', 'auto');
                    } catch (Exception $e) {
                        // Si falla, usar un enfoque más básico
                        $data[$key] = utf8_encode($value);
                    }
                }
            } elseif (is_float($value)) {
                // Solo verificar NaN e infinitos en valores float
                if ($value === INF || $value === -INF) {
                    // Reemplazar valores infinitos
                    $data[$key] = "Infinity";
                } elseif (is_nan($value)) {
                    // Reemplazar NaN
                    $data[$key] = 0;
                }
            }
        }
    }
    return $data;
}

// Sanear resultado para asegurar compatibilidad JSON
try {
    $result = sanitizeForJson($result);

    // Verificar que se puede codificar a JSON
    $json = json_encode($result);

    if ($json === false) {
        // Obtener el error de json_encode
        $jsonError = json_last_error_msg();

        // Registrar el error
        file_put_contents(
            $logFile,
            date('Y-m-d H:i:s') . " - Error al codificar JSON: " . $jsonError . "\n" .
                "Datos: " . print_r($result, true) . "\n",
            FILE_APPEND
        );

        // Crear un resultado de error más simple
        $result = [
            'success' => false,
            'message' => 'Error al procesar datos: ' . $jsonError,
            'error_type' => 'json_encoding'
        ];

        // Intentar nuevamente con el resultado simple
        $json = json_encode($result);
    }

    // Devolver respuesta JSON
    header('Content-Type: application/json');
    echo $json;
} catch (Exception $e) {
    // En caso de error, enviar una respuesta de emergencia
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar la respuesta: ' . $e->getMessage(),
        'error_type' => 'exception'
    ]);

    // Registrar el error
    file_put_contents(
        $logFile,
        date('Y-m-d H:i:s') . " - Excepción al procesar respuesta: " . $e->getMessage() . "\n" .
            "Traza: " . $e->getTraceAsString() . "\n",
        FILE_APPEND
    );
}
exit;
