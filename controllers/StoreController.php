<?php

/**
 * Controlador de la tienda
 * Cat Store - Tienda de Gatos
 */

require_once __DIR__ . '/../models/Animal.php';

class StoreController
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
     * Obtiene todos los animales con opciones de ordenación
     * @param string $orderBy Campo por el que ordenar
     * @param string $orderDir Dirección de ordenación (ASC o DESC)
     * @return array Resultado con los animales y metadatos
     */
    public function getAllAnimals($orderBy = 'id', $orderDir = 'ASC')
    {
        $animals = $this->animalModel->getAll($orderBy, $orderDir);

        return [
            'success' => ($animals !== false),
            'data' => $animals ?: [],
            'count' => is_array($animals) ? count($animals) : 0,
            'orderBy' => $orderBy,
            'orderDir' => $orderDir
        ];
    }

    /**
     * Obtiene animales con filtros
     * @param array $filters Filtros a aplicar
     * @param string $orderBy Campo por el que ordenar
     * @param string $orderDir Dirección de ordenación (ASC o DESC)
     * @return array Resultado con los animales filtrados y metadatos
     */
    public function getFilteredAnimals($filters = [], $orderBy = 'id', $orderDir = 'ASC')
    {
        $animals = $this->animalModel->getWithFilters($filters, $orderBy, $orderDir);

        return [
            'success' => ($animals !== false),
            'data' => $animals ?: [],
            'count' => is_array($animals) ? count($animals) : 0,
            'filters' => $filters,
            'orderBy' => $orderBy,
            'orderDir' => $orderDir
        ];
    }

    /**
     * Obtiene un animal por su ID
     * @param int $id ID del animal
     * @return array Resultado con los datos del animal
     */
    public function getAnimalById($id)
    {
        $animal = $this->animalModel->getById($id);

        return [
            'success' => ($animal !== false),
            'data' => $animal ?: null
        ];
    }

    /**
     * Procesa los parámetros de filtro y ordenación de la solicitud
     * @return array Parámetros procesados
     */
    public function processRequestParams()
    {
        // Obtener parámetros de ordenación
        $orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'fecha_anyadido';
        $orderDir = isset($_GET['orderDir']) ? $_GET['orderDir'] : 'DESC';

        // Obtener parámetros de filtro
        $filters = [];

        if (isset($_GET['tipo']) && !empty($_GET['tipo'])) {
            $filters['tipo'] = $_GET['tipo'];
        }

        if (isset($_GET['color']) && !empty($_GET['color'])) {
            $filters['color'] = $_GET['color'];
        }

        if (isset($_GET['sexo']) && ($_GET['sexo'] === '0' || $_GET['sexo'] === '1')) {
            $filters['sexo'] = $_GET['sexo'];
        }

        if (isset($_GET['precio_min']) && is_numeric($_GET['precio_min'])) {
            $filters['precio_min'] = $_GET['precio_min'];
        }

        if (isset($_GET['precio_max']) && is_numeric($_GET['precio_max'])) {
            $filters['precio_max'] = $_GET['precio_max'];
        }

        if (isset($_GET['nombre']) && !empty($_GET['nombre'])) {
            $filters['nombre'] = $_GET['nombre'];
        }

        return [
            'orderBy' => $orderBy,
            'orderDir' => $orderDir,
            'filters' => $filters
        ];
    }
}

// Instanciar el controlador si se accede directamente al archivo
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    $storeController = new StoreController();

    // Procesar parámetros de la solicitud
    $params = $storeController->processRequestParams();

    // Determinar la acción a realizar
    $action = isset($_GET['action']) ? $_GET['action'] : 'list';
    $result = [];

    switch ($action) {
        case 'list':
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
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $result = $storeController->getAnimalById($id);
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
