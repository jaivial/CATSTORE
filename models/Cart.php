<?php

/**
 * Modelo Cart para gestión del carrito de compra
 * Cat Store - Tienda de Gatos
 */

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../includes/auth_middleware.php';

class Cart
{
    private $db;

    /**
     * Constructor de la clase
     */
    public function __construct()
    {
        $this->db = connectDB();
    }

    /**
     * Añade un producto al carrito
     * @param int $productId ID del producto
     * @return bool|array True si se añadió correctamente, array con error si no
     */
    public function addToCart($productId)
    {
        // Verificar autenticación
        if (!isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Usuario no autenticado'
            ];
        }

        try {
            // Verificar si el producto existe
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM animal WHERE id = ?");
            $stmt->execute([$productId]);

            if ($stmt->fetchColumn() === 0) {
                return [
                    'success' => false,
                    'message' => 'El producto no existe'
                ];
            }

            // Añadir al carrito
            $stmt = $this->db->prepare("INSERT INTO carrito (id_animal, username_usuario) VALUES (?, ?)");
            $result = $stmt->execute([$productId, $_SESSION['user_id']]);

            if ($result) {
                // Obtener número de elementos en el carrito
                $count = $this->getCartCount();

                return [
                    'success' => true,
                    'message' => 'Producto añadido al carrito',
                    'count' => $count
                ];
            }

            return [
                'success' => false,
                'message' => 'Error al añadir al carrito'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error en la base de datos'
            ];
        }
    }

    /**
     * Elimina un producto del carrito
     * @param int $productId ID del producto
     * @param string|null $username Nombre de usuario opcional 
     * @return bool|array True si se eliminó correctamente, array con error si no
     */
    public function removeFromCart($productId, $username = null)
    {
        // Verificar autenticación
        if (!isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Usuario no autenticado'
            ];
        }

        try {
            // Usar el username proporcionado si está disponible, sino usar el de la sesión
            $userIdentifier = $username ?: $_SESSION['user_id'];

            // Eliminar del carrito (solo una unidad)
            $stmt = $this->db->prepare(
                "DELETE FROM carrito 
                WHERE id_animal = ? AND username_usuario = ? 
                LIMIT 1"
            );
            $result = $stmt->execute([$productId, $userIdentifier]);

            if ($result) {
                // Obtener número de elementos en el carrito
                $count = $this->getCartCount();

                return [
                    'success' => true,
                    'message' => 'Producto eliminado del carrito',
                    'count' => $count
                ];
            }

            return [
                'success' => false,
                'message' => 'Error al eliminar del carrito'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error en la base de datos'
            ];
        }
    }

    /**
     * Vacía el carrito
     * @return bool|array True si se vació correctamente, array con error si no
     */
    public function clearCart()
    {
        // Verificar autenticación
        if (!isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Usuario no autenticado'
            ];
        }

        try {
            // Eliminar todos los elementos del carrito
            $stmt = $this->db->prepare("DELETE FROM carrito WHERE username_usuario = ?");
            $result = $stmt->execute([$_SESSION['user_id']]);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Carrito vaciado correctamente',
                    'count' => 0
                ];
            }

            return [
                'success' => false,
                'message' => 'Error al vaciar el carrito'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error en la base de datos'
            ];
        }
    }

    /**
     * Obtiene el contenido del carrito
     * @return array|bool Contenido del carrito o false si hay error
     */
    public function getCartContent()
    {
        // Asegurar que la sesión está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Verificar autenticación
        if (!isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Usuario no autenticado',
                'items' => [],
                'total' => 0
            ];
        }

        try {
            // Verificar conexión a la base de datos
            if (!$this->db) {
                return [
                    'success' => false,
                    'message' => 'Error de conexión a la base de datos',
                    'items' => [],
                    'total' => 0
                ];
            }

            // Obtener conteo de elementos del carrito primero
            $stmt = $this->db->prepare(
                "SELECT id_animal, COUNT(*) AS repetitions 
                FROM carrito 
                WHERE username_usuario = ? 
                GROUP BY id_animal"
            );
            $stmt->execute([$_SESSION['user_id']]);
            $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Si no hay elementos en el carrito, devolver array vacío
            if (empty($cartItems)) {
                return [
                    'success' => true,
                    'items' => [],
                    'total' => 0
                ];
            }

            // Obtener información de los animales
            $items = [];
            $total = 0;

            foreach ($cartItems as $cartItem) {
                // Obtener datos del animal
                $stmt = $this->db->prepare(
                    "SELECT id, foto, nombre, tipo, sexo, color, precio 
                    FROM animal 
                    WHERE id = ?"
                );
                $stmt->execute([$cartItem['id_animal']]);
                $animal = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($animal) {
                    // Añadir username_usuario y repetitions
                    $animal['username_usuario'] = $_SESSION['user_id'];
                    $animal['repetitions'] = $cartItem['repetitions'];

                    // Convertir datos binarios de imagen a base64
                    if (isset($animal['foto']) && !empty($animal['foto'])) {
                        $animal['foto'] = base64_encode($animal['foto']);
                    }

                    // Calcular subtotal
                    $total += $animal['precio'] * $cartItem['repetitions'];

                    // Añadir a los items
                    $items[] = $animal;
                }
            }

            // Registrar los items encontrados para depuración
            file_put_contents(
                __DIR__ . '/../debug_cart.log',
                date('Y-m-d H:i:s') . " - Cart::getCartContent - " .
                    "Items encontrados: " . count($items) . " - " .
                    "Usuario: " . $_SESSION['user_id'] .
                    "\n",
                FILE_APPEND
            );

            return [
                'success' => true,
                'items' => $items,
                'total' => $total
            ];
        } catch (PDOException $e) {
            // Registrar el error para depuración
            file_put_contents(
                __DIR__ . '/../debug_cart.log',
                date('Y-m-d H:i:s') . " - Cart::getCartContent - " .
                    "Error PDO: " . $e->getMessage() . " - " .
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
        } catch (Exception $e) {
            // Registrar el error para depuración
            file_put_contents(
                __DIR__ . '/../debug_cart.log',
                date('Y-m-d H:i:s') . " - Cart::getCartContent - " .
                    "Error general: " . $e->getMessage() . " - " .
                    "Usuario: " . $_SESSION['user_id'] .
                    "\n",
                FILE_APPEND
            );

            return [
                'success' => false,
                'message' => 'Error inesperado: ' . $e->getMessage(),
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
        // Verificar autenticación
        if (!isAuthenticated()) {
            return 0;
        }

        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM carrito WHERE username_usuario = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Realiza la compra de los elementos del carrito
     * @return array Resultado de la operación
     */
    public function checkout()
    {
        // Verificar autenticación
        if (!isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Usuario no autenticado'
            ];
        }

        try {
            // Verificar que hay elementos en el carrito
            $cartCount = $this->getCartCount();
            if ($cartCount === 0) {
                return [
                    'success' => false,
                    'message' => 'El carrito está vacío'
                ];
            }

            // Iniciar transacción
            $this->db->beginTransaction();

            // Insertar elementos del carrito en la tabla de compras
            $stmt = $this->db->prepare(
                "INSERT INTO compra (fecha, id_animal, username_usuario) 
                SELECT CURRENT_DATE, id_animal, username_usuario 
                FROM carrito 
                WHERE username_usuario = ?"
            );
            $result = $stmt->execute([$_SESSION['user_id']]);

            if (!$result) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Error al procesar la compra'
                ];
            }

            // Vaciar el carrito
            $stmt = $this->db->prepare("DELETE FROM carrito WHERE username_usuario = ?");
            $result = $stmt->execute([$_SESSION['user_id']]);

            if (!$result) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Error al vaciar el carrito'
                ];
            }

            // Confirmar transacción
            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Compra realizada correctamente'
            ];
        } catch (PDOException $e) {
            // Revertir transacción en caso de error
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            return [
                'success' => false,
                'message' => 'Error en la base de datos'
            ];
        }
    }
}
