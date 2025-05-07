<?php

/**
 * Controlador del carrito
 * Cat Store - Tienda de Gatos
 */

require_once __DIR__ . '/../models/Cart.php';

class CartController
{
    private $cartModel;

    /**
     * Constructor de la clase
     */
    public function __construct()
    {
        $this->cartModel = new Cart();
    }

    /**
     * Añade un producto al carrito
     * @param int $productId ID del producto
     * @return array Resultado de la operación
     */
    public function addToCart($productId)
    {
        return $this->cartModel->addToCart($productId);
    }

    /**
     * Elimina un producto del carrito
     * @param int $productId ID del producto
     * @param string|null $username Nombre de usuario opcional
     * @return array Resultado de la operación
     */
    public function removeFromCart($productId, $username = null)
    {
        return $this->cartModel->removeFromCart($productId, $username);
    }

    /**
     * Vacía el carrito
     * @return array Resultado de la operación
     */
    public function clearCart()
    {
        return $this->cartModel->clearCart();
    }

    /**
     * Sanitizar datos para JSON para evitar problemas de serialización
     * @param mixed $data Datos a sanitizar
     * @return mixed Sanitizado
     */
    private function sanitizeForJson($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = $this->sanitizeForJson($value);
                } elseif (is_string($value)) {
                    // Solo procesar strings que no son imágenes codificadas en base64
                    if ($key === 'foto') {
                        // No procesar datos de imagen, dejarlo tal cual
                        continue;
                    }

                    try {
                        // Verificar si es una cadena UTF-8 válida
                        if (!mb_check_encoding($value, 'UTF-8')) {
                            // Si no es UTF-8, primero intentar con iconv
                            $converted = @iconv('ISO-8859-1', 'UTF-8', $value);
                            if ($converted !== false) {
                                $data[$key] = $converted;
                            } else {
                                // Si iconv falla, usar utf8_encode como último recurso
                                $data[$key] = utf8_encode($value);
                            }
                        }
                    } catch (Exception $e) {
                        // En caso de error, usar una versión segura de la cadena
                        $data[$key] = utf8_encode($value);
                    }
                } elseif (is_float($value)) {
                    // Manejar valores especiales de float
                    if ($value === INF || $value === -INF) {
                        $data[$key] = "Infinity";
                    } elseif (is_nan($value)) {
                        $data[$key] = 0;
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Obtiene el contenido del carrito
     * @return array Contenido del carrito
     */
    public function getCartContent()
    {
        try {
            // Asegurar que la sesión está iniciada
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Registrar información de depuración
            file_put_contents(
                __DIR__ . '/../debug_cart.log',
                date('Y-m-d H:i:s') . " - CartController::getCartContent - " .
                    "Usuario: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'No hay sesión') .
                    "\n",
                FILE_APPEND
            );

            $result = $this->cartModel->getCartContent();

            // Sanear los datos para asegurar compatibilidad JSON
            $result = $this->sanitizeForJson($result);

            // Registrar el resultado
            file_put_contents(
                __DIR__ . '/../debug_cart.log',
                date('Y-m-d H:i:s') . " - CartController::getCartContent - " .
                    "Resultado: " . json_encode($result) .
                    "\n",
                FILE_APPEND
            );

            // Asegurar que el resultado tenga la estructura correcta
            if (!is_array($result)) {
                $result = [
                    'success' => false,
                    'message' => 'Respuesta inválida del modelo',
                    'items' => [],
                    'total' => 0
                ];
            }

            if (!isset($result['success'])) {
                $result['success'] = true;
            }

            if (!isset($result['items'])) {
                $result['items'] = [];
            }

            if (!isset($result['total'])) {
                $result['total'] = 0;
            }

            return $result;
        } catch (Exception $e) {
            // Registrar la excepción
            file_put_contents(
                __DIR__ . '/../debug_cart.log',
                date('Y-m-d H:i:s') . " - CartController::getCartContent - " .
                    "Excepción: " . $e->getMessage() .
                    "\n",
                FILE_APPEND
            );

            return [
                'success' => false,
                'message' => 'Error en el controlador: ' . $e->getMessage(),
                'items' => [],
                'total' => 0
            ];
        }
    }

    /**
     * Obtiene el número de elementos en el carrito
     * @return int Número de elementos en el carrito
     */
    public function getCartCount()
    {
        return $this->cartModel->getCartCount();
    }

    /**
     * Realiza la compra de los elementos del carrito
     * @return array Resultado de la operación
     */
    public function checkout()
    {
        return $this->cartModel->checkout();
    }
}

// Instanciar el controlador si se accede directamente al archivo
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    $cartController = new CartController();

    // Determinar la acción a realizar
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    $result = [];

    switch ($action) {
        case 'add':
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
            // Obtener datos de la solicitud
            $data = json_decode(file_get_contents('php://input'), true);
            $productId = isset($data['id']) ? intval($data['id']) : 0;
            $username = isset($data['username']) ? $data['username'] : null;

            if ($productId > 0) {
                $result = $cartController->removeFromCart($productId, $username);
            } else {
                $result = [
                    'success' => false,
                    'message' => 'ID de producto no válido'
                ];
            }
            break;
        case 'clear':
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
            $result = $cartController->checkout();
            break;
        default:
            $result = [
                'success' => false,
                'message' => 'Acción no válida'
            ];
    }

    // Devolver respuesta JSON
    header('Content-Type: application/json');

    // Sanitizar los datos para evitar problemas con JSON
    function sanitizeForJsonOutput($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = sanitizeForJsonOutput($value);
                } elseif (is_string($value)) {
                    // No procesar datos de imagen
                    if ($key === 'foto') {
                        continue;
                    }

                    // Solo procesar strings normales
                    try {
                        // Verificar si es una cadena UTF-8 válida
                        if (!mb_check_encoding($value, 'UTF-8')) {
                            // Si no es UTF-8, primero intentar con iconv
                            $converted = @iconv('ISO-8859-1', 'UTF-8', $value);
                            if ($converted !== false) {
                                $data[$key] = $converted;
                            } else {
                                // Si iconv falla, usar utf8_encode como último recurso
                                $data[$key] = utf8_encode($value);
                            }
                        }
                    } catch (Exception $e) {
                        // En caso de error, usar una versión segura
                        $data[$key] = utf8_encode($value);
                    }
                } elseif (is_float($value)) {
                    // Manejar valores especiales de float
                    if ($value === INF || $value === -INF) {
                        $data[$key] = "Infinity";
                    } elseif (is_nan($value)) {
                        $data[$key] = 0;
                    }
                }
            }
        }
        return $data;
    }

    // Asegurar que el resultado sea codificable en JSON
    $result = sanitizeForJsonOutput($result);
    echo json_encode($result);
    exit;
}
