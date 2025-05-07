<?php

/**
 * Controlador de administración
 * Cat Store - Tienda de Gatos
 */

require_once __DIR__ . '/../models/Animal.php';
require_once __DIR__ . '/../includes/auth_middleware.php';

class AdminController
{
    private $animalModel;

    /**
     * Constructor de la clase
     */
    public function __construct()
    {
        $this->animalModel = new Animal();
    }

    /**
     * Verifica si el usuario actual es administrador
     * @return bool True si es administrador, False si no
     */
    public function isAdmin()
    {
        if (!isAuthenticated()) {
            return false;
        }

        return $_SESSION['user_id'] === 'javial';
    }

    /**
     * Obtiene todos los productos (gatos)
     * @param string $orderBy Campo por el que ordenar
     * @param string $orderDir Dirección de ordenación (ASC o DESC)
     * @return array Resultado con los productos y metadatos
     */
    public function getAllProducts($orderBy = 'id', $orderDir = 'ASC')
    {
        // Verificar permisos de administrador
        if (!$this->isAdmin()) {
            return [
                'success' => false,
                'message' => 'No tienes permisos de administrador',
                'data' => [],
                'count' => 0
            ];
        }

        $products = $this->animalModel->getAll($orderBy, $orderDir);

        return [
            'success' => ($products !== false),
            'data' => $products ?: [],
            'count' => is_array($products) ? count($products) : 0,
            'orderBy' => $orderBy,
            'orderDir' => $orderDir
        ];
    }

    /**
     * Obtiene productos filtrados
     * @param array $filters Filtros a aplicar (nombre, tipo, sexo, etc.)
     * @param string $orderBy Campo por el que ordenar
     * @param string $orderDir Dirección de ordenación (ASC o DESC)
     * @return array Resultado con los productos filtrados y metadatos
     */
    public function getFilteredProducts($filters = [], $orderBy = 'id', $orderDir = 'ASC')
    {
        // Verificar permisos de administrador
        if (!$this->isAdmin()) {
            return [
                'success' => false,
                'message' => 'No tienes permisos de administrador',
                'data' => [],
                'count' => 0
            ];
        }

        $products = $this->animalModel->getWithFilters($filters, $orderBy, $orderDir);

        return [
            'success' => ($products !== false),
            'data' => $products ?: [],
            'count' => is_array($products) ? count($products) : 0,
            'filters' => $filters,
            'orderBy' => $orderBy,
            'orderDir' => $orderDir
        ];
    }

    /**
     * Obtiene un producto por su ID
     * @param int $id ID del producto
     * @return array Resultado con los datos del producto
     */
    public function getProductById($id)
    {
        // Verificar permisos de administrador
        if (!$this->isAdmin()) {
            return [
                'success' => false,
                'message' => 'No tienes permisos de administrador',
                'data' => null
            ];
        }

        $product = $this->animalModel->getById($id);

        return [
            'success' => ($product !== false),
            'data' => $product ?: null
        ];
    }

    /**
     * Crea un nuevo producto
     * @param array $productData Datos del producto
     * @return array Resultado de la operación
     */
    public function createProduct($productData)
    {
        // Verificar permisos de administrador
        if (!$this->isAdmin()) {
            return [
                'success' => false,
                'message' => 'No tienes permisos de administrador'
            ];
        }

        // Validar datos de entrada
        $errors = $this->validateProductData($productData);

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Error en los datos proporcionados',
                'errors' => $errors
            ];
        }

        // Procesar imagen si existe
        if (isset($productData['foto']) && !empty($productData['foto'])) {
            // La imagen ya debe estar en formato base64 o como un objeto de archivo
            // Aquí se podría procesar según sea necesario
        }

        // Crear producto
        $result = $this->animalModel->create($productData);

        if ($result) {
            return [
                'success' => true,
                'message' => 'Producto creado correctamente',
                'id' => $result
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al crear el producto'
            ];
        }
    }

    /**
     * Actualiza un producto existente
     * @param int $id ID del producto
     * @param array $productData Datos del producto
     * @return array Resultado de la operación
     */
    public function updateProduct($id, $productData)
    {
        // Verificar permisos de administrador
        if (!$this->isAdmin()) {
            return [
                'success' => false,
                'message' => 'No tienes permisos de administrador'
            ];
        }

        // Validar datos de entrada
        $errors = $this->validateProductData($productData, true);

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Error en los datos proporcionados',
                'errors' => $errors
            ];
        }

        // Procesar imagen si existe
        if (isset($productData['foto']) && !empty($productData['foto'])) {
            // La imagen ya debe estar en formato base64 o como un objeto de archivo
            // Aquí se podría procesar según sea necesario
        }

        // Actualizar producto
        $result = $this->animalModel->update($id, $productData);

        if ($result) {
            return [
                'success' => true,
                'message' => 'Producto actualizado correctamente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al actualizar el producto'
            ];
        }
    }

    /**
     * Elimina un producto
     * @param int $id ID del producto
     * @return array Resultado de la operación
     */
    public function deleteProduct($id)
    {
        // Verificar permisos de administrador
        if (!$this->isAdmin()) {
            return [
                'success' => false,
                'message' => 'No tienes permisos de administrador'
            ];
        }

        // Eliminar producto
        $result = $this->animalModel->delete($id);

        if ($result) {
            return [
                'success' => true,
                'message' => 'Producto eliminado correctamente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al eliminar el producto. Puede que esté en un carrito o haya sido comprado.'
            ];
        }
    }

    /**
     * Valida los datos de un producto
     * @param array $productData Datos del producto
     * @param bool $isUpdate Indica si es una actualización (algunos campos pueden ser opcionales)
     * @return array Array de errores
     */
    private function validateProductData($productData, $isUpdate = false)
    {
        $errors = [];

        // En una actualización, algunos campos pueden no estar presentes
        if (!$isUpdate) {
            // Campos requeridos para creación
            $requiredFields = ['nombre', 'tipo', 'color', 'sexo', 'precio'];

            foreach ($requiredFields as $field) {
                if (!isset($productData[$field]) || empty($productData[$field])) {
                    $errors[$field] = "El campo $field es obligatorio";
                }
            }
        }

        // Validar nombre si está presente
        if (isset($productData['nombre']) && empty($productData['nombre'])) {
            $errors['nombre'] = 'El nombre es obligatorio';
        }

        // Validar tipo si está presente
        if (isset($productData['tipo']) && empty($productData['tipo'])) {
            $errors['tipo'] = 'El tipo es obligatorio';
        }

        // Validar color si está presente
        if (isset($productData['color']) && empty($productData['color'])) {
            $errors['color'] = 'El color es obligatorio';
        }

        // Validar sexo si está presente
        if (isset($productData['sexo']) && !in_array($productData['sexo'], [0, 1, '0', '1'])) {
            $errors['sexo'] = 'El sexo debe ser 0 (hembra) o 1 (macho)';
        }

        // Validar precio si está presente
        if (isset($productData['precio'])) {
            if (!is_numeric($productData['precio']) || $productData['precio'] <= 0) {
                $errors['precio'] = 'El precio debe ser un número mayor que 0';
            }
        }

        return $errors;
    }
}
