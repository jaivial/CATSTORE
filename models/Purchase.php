<?php

/**
 * Purchase Model
 * Handles operations related to user purchases
 */
class Purchase
{
    private $db;

    /**
     * Constructor - Initialize database connection
     */
    public function __construct($db = null)
    {
        if ($db) {
            $this->db = $db;
        } else {
            require_once __DIR__ . '/../config/db_config.php';
            $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

            if ($this->db->connect_error) {
                die("Connection failed: " . $this->db->connect_error);
            }
        }
    }

    /**
     * Get all purchases for a specific user
     * 
     * @param string $username The username of the user
     * @return array Array of purchases with animal details
     */
    public function getUserPurchases($username)
    {
        $purchases = [];

        $query = "SELECT c.fecha, a.id, a.nombre, a.tipo, a.color, a.sexo, a.precio, a.foto 
                  FROM compra c 
                  JOIN animal a ON c.id_animal = a.id 
                  WHERE c.username_usuario = ? 
                  ORDER BY c.fecha DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            // Format the date
            $date = new DateTime($row['fecha']);
            $row['fecha_formateada'] = $date->format('d/m/Y H:i');

            // Format animal sex
            $row['sexo_texto'] = $row['sexo'] ? 'Macho' : 'Hembra';

            $purchases[] = $row;
        }

        return $purchases;
    }

    /**
     * Record a new purchase
     * 
     * @param int $animalId The ID of the animal being purchased
     * @param string $username The username of the buyer
     * @return bool True if successful, false otherwise
     */
    public function recordPurchase($animalId, $username)
    {
        $query = "INSERT INTO compra (fecha, id_animal, username_usuario) VALUES (NOW(), ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("is", $animalId, $username);

        return $stmt->execute();
    }

    /**
     * Complete checkout process for all items in cart
     * 
     * @param string $username The username of the buyer
     * @return array Result with success status and message
     */
    public function checkout($username)
    {
        // Start transaction
        $this->db->begin_transaction();

        try {
            // Get all items in the user's cart
            $cartQuery = "SELECT id_animal FROM carrito WHERE username_usuario = ?";
            $cartStmt = $this->db->prepare($cartQuery);
            $cartStmt->bind_param("s", $username);
            $cartStmt->execute();
            $cartResult = $cartStmt->get_result();

            $purchaseCount = 0;

            // Record each purchase
            while ($item = $cartResult->fetch_assoc()) {
                $purchaseQuery = "INSERT INTO compra (fecha, id_animal, username_usuario) VALUES (NOW(), ?, ?)";
                $purchaseStmt = $this->db->prepare($purchaseQuery);
                $purchaseStmt->bind_param("is", $item['id_animal'], $username);
                $purchaseStmt->execute();
                $purchaseCount++;
            }

            // Clear the cart
            $clearCartQuery = "DELETE FROM carrito WHERE username_usuario = ?";
            $clearCartStmt = $this->db->prepare($clearCartQuery);
            $clearCartStmt->bind_param("s", $username);
            $clearCartStmt->execute();

            // Commit transaction
            $this->db->commit();

            return [
                'success' => true,
                'message' => "Compra completada con éxito. Has adquirido $purchaseCount gato(s).",
                'count' => $purchaseCount
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollback();

            return [
                'success' => false,
                'message' => "Error al procesar la compra: " . $e->getMessage()
            ];
        }
    }

    /**
     * Get purchase statistics for a user
     * 
     * @param string $username The username of the user
     * @return array Statistics including total spent, number of purchases, etc.
     */
    public function getUserPurchaseStats($username)
    {
        $stats = [
            'total_gatos' => 0,
            'gasto_total' => 0,
            'primera_compra' => null,
            'ultima_compra' => null
        ];

        // Get total cats and spending
        $query = "SELECT COUNT(*) as total_gatos, SUM(a.precio) as gasto_total 
                  FROM compra c 
                  JOIN animal a ON c.id_animal = a.id 
                  WHERE c.username_usuario = ?";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $stats['total_gatos'] = $row['total_gatos'];
            $stats['gasto_total'] = $row['gasto_total'];
        }

        // Get first and last purchase dates
        $dateQuery = "SELECT MIN(fecha) as primera_compra, MAX(fecha) as ultima_compra 
                      FROM compra 
                      WHERE username_usuario = ?";

        $dateStmt = $this->db->prepare($dateQuery);
        $dateStmt->bind_param("s", $username);
        $dateStmt->execute();
        $dateResult = $dateStmt->get_result();

        if ($dateRow = $dateResult->fetch_assoc()) {
            if ($dateRow['primera_compra']) {
                $firstDate = new DateTime($dateRow['primera_compra']);
                $stats['primera_compra'] = $firstDate->format('d/m/Y');
            }

            if ($dateRow['ultima_compra']) {
                $lastDate = new DateTime($dateRow['ultima_compra']);
                $stats['ultima_compra'] = $lastDate->format('d/m/Y');
            }
        }

        return $stats;
    }

    /**
     * Close the database connection
     */
    public function __destruct()
    {
        if ($this->db) {
            $this->db->close();
        }
    }
}
