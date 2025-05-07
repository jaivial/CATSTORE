<?php

/**
 * Profile Controller
 * Handles operations related to user profiles
 */
class ProfileController
{
    private $userModel;
    private $purchaseModel;

    /**
     * Constructor - Initialize models
     */
    public function __construct()
    {
        require_once __DIR__ . '/../models/User.php';
        require_once __DIR__ . '/../models/Purchase.php';

        $this->userModel = new User();
        $this->purchaseModel = new Purchase();
    }

    /**
     * Display user profile information
     * 
     * @param string $username The username of the user
     * @return array User information
     */
    public function getUserProfile($username)
    {
        // Get user details from session or by username
        $user = null;

        if ($_SESSION['user_id'] === $username) {
            $user = $this->userModel->getCurrentUser();
        } else {
            // For admin purposes, could be extended to get other users
            // Currently will return error for non-current users
            return [
                'success' => false,
                'message' => 'No tienes permiso para ver este perfil'
            ];
        }

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Usuario no encontrado'
            ];
        }

        // Get purchase statistics
        $purchaseStats = $this->purchaseModel->getUserPurchaseStats($username);

        // Combine user info and purchase stats
        return [
            'success' => true,
            'user' => $user,
            'stats' => $purchaseStats
        ];
    }

    /**
     * Update user profile information
     * 
     * @param string $username Current username
     * @param array $userData Updated user data
     * @return array Result with success status and message
     */
    public function updateUserProfile($username, $userData)
    {
        // Verify the user is updating their own profile
        if ($_SESSION['user_id'] !== $username) {
            return [
                'success' => false,
                'message' => 'No tienes permiso para actualizar este perfil'
            ];
        }

        // Validate input data
        $errors = $this->validateUserData($userData);

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Error en los datos proporcionados',
                'errors' => $errors
            ];
        }

        // Update user information
        $result = $this->userModel->updateUser($userData);

        if ($result) {
            return [
                'success' => true,
                'message' => 'Perfil actualizado correctamente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al actualizar el perfil'
            ];
        }
    }

    /**
     * Change user password
     * 
     * @param string $username The username of the user
     * @param string $currentPassword Current password
     * @param string $newPassword New password
     * @param string $confirmPassword Confirmation of new password
     * @return array Result with success status and message
     */
    public function changePassword($username, $currentPassword, $newPassword, $confirmPassword)
    {
        // Verify the user is changing their own password
        if ($_SESSION['user_id'] !== $username) {
            return [
                'success' => false,
                'message' => 'No tienes permiso para cambiar esta contraseña'
            ];
        }

        // Validate passwords
        if ($newPassword !== $confirmPassword) {
            return [
                'success' => false,
                'message' => 'Las contraseñas nuevas no coinciden'
            ];
        }

        if (strlen($newPassword) < 6) {
            return [
                'success' => false,
                'message' => 'La contraseña debe tener al menos 6 caracteres'
            ];
        }

        // Verify current password by attempting login
        $loginResult = $this->userModel->login($username, $currentPassword);

        if (!$loginResult) {
            return [
                'success' => false,
                'message' => 'La contraseña actual es incorrecta'
            ];
        }

        // Update password using updateUser method
        $result = $this->userModel->updateUser(['contrasenya' => $newPassword]);

        if ($result) {
            return [
                'success' => true,
                'message' => 'Contraseña actualizada correctamente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al actualizar la contraseña'
            ];
        }
    }

    /**
     * Get purchase history for a user
     * 
     * @param string $username The username of the user
     * @return array Purchase history
     */
    public function getPurchaseHistory($username)
    {
        // Verify the user is viewing their own purchase history
        if ($_SESSION['user_id'] !== $username) {
            return [
                'success' => false,
                'message' => 'No tienes permiso para ver este historial'
            ];
        }

        // Use the Purchase model to get detailed purchase history
        $purchases = $this->purchaseModel->getUserPurchases($username);

        return [
            'success' => true,
            'purchases' => $purchases
        ];
    }

    /**
     * Validate user input data
     * 
     * @param array $userData User data to validate
     * @return array Array of validation errors
     */
    private function validateUserData($userData)
    {
        $errors = [];

        // Validate name
        if (isset($userData['nombre']) && (empty($userData['nombre']) || strlen($userData['nombre']) < 2)) {
            $errors['nombre'] = 'El nombre debe tener al menos 2 caracteres';
        }

        // Validate last name
        if (isset($userData['apellido']) && (empty($userData['apellido']) || strlen($userData['apellido']) < 2)) {
            $errors['apellido'] = 'El apellido debe tener al menos 2 caracteres';
        }

        // Validate email
        if (isset($userData['email']) && (empty($userData['email']) || !filter_var($userData['email'], FILTER_VALIDATE_EMAIL))) {
            $errors['email'] = 'El email no es válido';
        }

        return $errors;
    }
}
