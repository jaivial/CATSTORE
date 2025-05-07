<?php

/**
 * Modelo Animal para gestión de productos (gatos)
 * Cat Store - Tienda de Gatos
 */

require_once __DIR__ . '/../config/db_config.php';

class Animal
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
     * Obtiene todos los animales con opciones de filtrado y ordenación
     * @param string $orderBy Campo por el que ordenar
     * @param string $orderDir Dirección de ordenación (ASC o DESC)
     * @return array|bool Lista de animales o false si hay error
     */
    public function getAll($orderBy = 'id', $orderDir = 'ASC')
    {
        try {
            // Validar campos de ordenación permitidos
            $allowedFields = ['id', 'nombre', 'tipo', 'precio', 'fecha_anyadido'];
            $orderBy = in_array($orderBy, $allowedFields) ? $orderBy : 'id';

            // Validar dirección de ordenación
            $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

            $stmt = $this->db->prepare("SELECT * FROM animal ORDER BY {$orderBy} {$orderDir}");
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Obtiene un animal por su ID
     * @param int $id ID del animal
     * @return array|bool Datos del animal o false si no existe o hay error
     */
    public function getById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM animal WHERE id = ?");
            $stmt->execute([$id]);

            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Crea un nuevo animal
     * @param array $animalData Datos del animal
     * @return bool|int ID del animal creado o false si hay error
     */
    public function create($animalData)
    {
        try {
            // Preparar la consulta
            $stmt = $this->db->prepare(
                "INSERT INTO animal (nombre, tipo, color, sexo, precio, foto) 
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            // Ejecutar la consulta
            $result = $stmt->execute([
                $animalData['nombre'],
                $animalData['tipo'],
                $animalData['color'],
                $animalData['sexo'],
                $animalData['precio'],
                $animalData['foto'] ?? null
            ]);

            if ($result) {
                return $this->db->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Actualiza un animal existente
     * @param int $id ID del animal
     * @param array $animalData Datos del animal
     * @return bool True si se actualizó correctamente, False si no
     */
    public function update($id, $animalData)
    {
        try {
            // Construir la consulta dinámicamente
            $fields = [];
            $values = [];

            foreach ($animalData as $field => $value) {
                if ($field !== 'id') { // No permitir cambiar el ID
                    $fields[] = "$field = ?";
                    $values[] = $value;
                }
            }

            if (empty($fields)) {
                return false; // No hay campos para actualizar
            }

            // Añadir el ID para la condición WHERE
            $values[] = $id;

            $stmt = $this->db->prepare("UPDATE animal SET " . implode(', ', $fields) . " WHERE id = ?");
            return $stmt->execute($values);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Elimina un animal
     * @param int $id ID del animal
     * @return bool True si se eliminó correctamente, False si no
     */
    public function delete($id)
    {
        try {
            // Verificar si el animal está en algún carrito
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM carrito WHERE id_animal = ?");
            $stmt->execute([$id]);

            if ($stmt->fetchColumn() > 0) {
                // El animal está en algún carrito, no se puede eliminar
                return false;
            }

            // Verificar si el animal está en alguna compra
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM compra WHERE id_animal = ?");
            $stmt->execute([$id]);

            if ($stmt->fetchColumn() > 0) {
                // El animal está en alguna compra, no se puede eliminar
                return false;
            }

            // Eliminar el animal
            $stmt = $this->db->prepare("DELETE FROM animal WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Obtiene los animales con filtros
     * @param array $filters Filtros a aplicar
     * @param string $orderBy Campo por el que ordenar
     * @param string $orderDir Dirección de ordenación (ASC o DESC)
     * @return array|bool Lista de animales o false si hay error
     */
    public function getWithFilters($filters = [], $orderBy = 'id', $orderDir = 'ASC')
    {
        try {
            // Validar campos de ordenación permitidos
            $allowedFields = ['id', 'nombre', 'tipo', 'precio', 'fecha_anyadido'];
            $orderBy = in_array($orderBy, $allowedFields) ? $orderBy : 'id';

            // Validar dirección de ordenación
            $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

            // Construir la consulta con filtros
            $where = [];
            $values = [];

            if (!empty($filters)) {
                foreach ($filters as $field => $value) {
                    if ($field === 'tipo' && !empty($value)) {
                        $where[] = "tipo = ?";
                        $values[] = $value;
                    } elseif ($field === 'color' && !empty($value)) {
                        $where[] = "color = ?";
                        $values[] = $value;
                    } elseif ($field === 'sexo' && ($value === '0' || $value === '1')) {
                        $where[] = "sexo = ?";
                        $values[] = $value;
                    } elseif ($field === 'precio_min' && is_numeric($value)) {
                        $where[] = "precio >= ?";
                        $values[] = $value;
                    } elseif ($field === 'precio_max' && is_numeric($value)) {
                        $where[] = "precio <= ?";
                        $values[] = $value;
                    } elseif ($field === 'nombre' && !empty($value)) {
                        $where[] = "nombre LIKE ?";
                        $values[] = "%$value%";
                    }
                }
            }

            $whereClause = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";

            $stmt = $this->db->prepare("SELECT * FROM animal $whereClause ORDER BY {$orderBy} {$orderDir}");
            $stmt->execute($values);

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return false;
        }
    }
}
