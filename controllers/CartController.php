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
     * @return array Resultado de la operación
     */
    public function removeFromCart($productId)
    {
        return $this->cartModel->removeFromCart($productId);
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
     * Obtiene el contenido del carrito
     * @return array Contenido del carrito
     */
    public function getCartContent()
    {
        return $this->cartModel->getCartContent();
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
    echo json_encode($result);
    exit;
}
