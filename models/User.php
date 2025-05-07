<?php

/**
 * Modelo User para gestión de usuarios
 * Cat Store - Tienda de Gatos
 */

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../includes/auth_middleware.php';

class User
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
     * Verifica las credenciales del usuario
     * @param string $username Nombre de usuario
     * @param string $password Contraseña
     * @return bool|array False si las credenciales son incorrectas, array con datos del usuario si son correctas
     */
    public function login($username, $password)
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuario WHERE username = ? AND contrasenya = ?");
            $stmt->execute([$username, $password]);

            if ($stmt->fetchColumn() > 0) {
                // Credenciales correctas, obtener datos del usuario
                $stmt = $this->db->prepare("SELECT username, nombre, apellido, email FROM usuario WHERE username = ?");
                $stmt->execute([$username]);
                $userData = $stmt->fetch();

                // Establecer sesión y cookie
                $_SESSION['user_id'] = $username;
                setUserCookie('usuario', $username);

                return $userData;
            }

            return false;
        } catch (PDOException $e) {
            // En producción, registrar el error en lugar de mostrarlo
            return false;
        }
    }

    /**
     * Registra un nuevo usuario
     * @param array $userData Datos del usuario (username, password, nombre, apellido, email)
     * @return bool|string True si se registró correctamente, string con mensaje de error si no
     */
    public function register($userData)
    {
        try {
            // Verificar si el usuario o email ya existen
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuario WHERE username = ? OR email = ?");
            $stmt->execute([$userData['username'], $userData['email']]);

            if ($stmt->fetchColumn() > 0) {
                return "El nombre de usuario o email ya están registrados";
            }

            // Insertar nuevo usuario
            $stmt = $this->db->prepare("INSERT INTO usuario (username, contrasenya, nombre, apellido, email) VALUES (?, ?, ?, ?, ?)");
            $result = $stmt->execute([
                $userData['username'],
                $userData['password'],
                $userData['nombre'],
                $userData['apellido'],
                $userData['email']
            ]);

            if ($result) {
                // Establecer sesión y cookie
                $_SESSION['user_id'] = $userData['username'];
                setUserCookie('usuario', $userData['username']);
                return true;
            }

            return "Error al registrar el usuario";
        } catch (PDOException $e) {
            // En producción, registrar el error en lugar de mostrarlo
            return "Error en la base de datos: " . $e->getMessage();
        }
    }

    /**
     * Cierra la sesión del usuario
     * @return void
     */
    public function logout()
    {
        // Eliminar sesión
        session_unset();
        session_destroy();

        // Eliminar cookie
        deleteUserCookie('usuario');
    }

    /**
     * Obtiene los datos del usuario actual
     * @return array|bool Datos del usuario o false si no hay usuario autenticado
     */
    public function getCurrentUser()
    {
        if (!isAuthenticated()) {
            return false;
        }

        try {
            $stmt = $this->db->prepare("SELECT username, nombre, apellido, email FROM usuario WHERE username = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Actualiza los datos del usuario
     * @param array $userData Datos del usuario a actualizar
     * @return bool True si se actualizó correctamente, False si no
     */
    public function updateUser($userData)
    {
        if (!isAuthenticated()) {
            return false;
        }

        try {
            // Si se actualiza el email, verificar que no exista ya
            if (isset($userData['email'])) {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuario WHERE email = ? AND username != ?");
                $stmt->execute([$userData['email'], $_SESSION['user_id']]);

                if ($stmt->fetchColumn() > 0) {
                    return false; // El email ya está en uso
                }
            }

            // Construir la consulta dinámicamente
            $fields = [];
            $values = [];

            foreach ($userData as $field => $value) {
                if ($field !== 'username') { // No permitir cambiar el username
                    $fields[] = "$field = ?";
                    $values[] = $value;
                }
            }

            if (empty($fields)) {
                return false; // No hay campos para actualizar
            }

            // Añadir el username para la condición WHERE
            $values[] = $_SESSION['user_id'];

            $stmt = $this->db->prepare("UPDATE usuario SET " . implode(', ', $fields) . " WHERE username = ?");
            return $stmt->execute($values);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Obtiene el historial de compras del usuario
     * @return array|bool Historial de compras o false si hay error
     */
    public function getPurchaseHistory()
    {
        if (!isAuthenticated()) {
            return false;
        }

        try {
            $stmt = $this->db->prepare(
                "SELECT compra.fecha, animal.foto, animal.nombre, animal.tipo, animal.sexo, animal.precio, COUNT(*) AS repetitions 
                FROM animal 
                INNER JOIN compra ON compra.id_animal = animal.id AND compra.username_usuario = ? 
                GROUP BY compra.fecha, animal.foto, animal.nombre, animal.tipo, animal.sexo, animal.precio"
            );
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return false;
        }
    }
}
