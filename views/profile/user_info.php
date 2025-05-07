<?php
require_once __DIR__ . '/../../includes/auth_middleware.php';
require_once __DIR__ . '/../../controllers/ProfileController.php';

// Verify authentication
if (!isAuthenticated()) {
    header('Location: /views/auth/login.php');
    exit;
}

$username = $_SESSION['user_id'];
$profileController = new ProfileController();
$profileData = $profileController->getUserProfile($username);

// Handle form submission for profile update
$updateMessage = '';
$updateSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $userData = [
        'nombre' => $_POST['nombre'],
        'apellido' => $_POST['apellido'],
        'email' => $_POST['email']
    ];

    $result = $profileController->updateUserProfile($username, $userData);
    $updateSuccess = $result['success'];
    $updateMessage = $result['message'];

    if ($updateSuccess) {
        // Refresh profile data after update
        $profileData = $profileController->getUserProfile($username);
    }
}

// Handle form submission for password change
$passwordMessage = '';
$passwordSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    $result = $profileController->changePassword($username, $currentPassword, $newPassword, $confirmPassword);
    $passwordSuccess = $result['success'];
    $passwordMessage = $result['message'];
}

// Título de la página
$pageTitle = "Mi Perfil - Cat Store";

// Incluir navbar
$includeNavbar = true;

// Incluir CSS adicional
$extraCss = ['../../assets/css/profile.css'];

// Include header
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="container profile-container mt-5">
    <div class="profile-header">
        <h1 class="display-5 fw-bold text-primary">Mi Perfil</h1>
        <div class="profile-divider"></div>
    </div>

    <?php if (!empty($updateMessage)): ?>
        <div
            class="alert <?php echo $updateSuccess ? 'alert-success border-left-success' : 'alert-danger border-left-danger'; ?> alert-dismissible fade show shadow-sm animate__animated animate__fadeIn">
            <div class="d-flex align-items-center" style="display: flex; flex-direction: row; gap: 8px;">
                <i
                    class="bi <?php echo $updateSuccess ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger'; ?> me-3 fs-4"></i>
                <div style="display: flex; flex-direction: row; gap: 8px;">
                    <strong><?php echo $updateSuccess ? '¡Éxito!' : '¡Error!'; ?></strong>
                    <p class="mb-0"><?php echo $updateMessage; ?></p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($passwordMessage)): ?>
        <div
            class="alert <?php echo $passwordSuccess ? 'alert-success border-left-success' : 'alert-danger border-left-danger'; ?> alert-dismissible fade show shadow-sm animate__animated animate__fadeIn">
            <div class="d-flex align-items-center" style="display: flex; flex-direction: row; gap: 8px;">
                <i
                    class="bi <?php echo $passwordSuccess ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger'; ?> me-3 fs-4"></i>
                <div style="display: flex; flex-direction: row; gap: 8px;">
                    <strong><?php echo $passwordSuccess ? '¡Éxito!' : '¡Error!'; ?></strong>
                    <p class="mb-0"><?php echo $passwordMessage; ?></p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4" style="display: flex; flex-direction: column; gap: 20px;">
        <!-- User Information Card -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow profile-card h-100">
                <div class="card-header bg-gradient-primary text-white py-3">
                    <i class="bi bi-person-circle header-icon"></i>
                    <h5 class="card-title">Información Personal</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="">
                        <div class="mb-4">
                            <div class="form-label-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                <label for="username" class="form-label fw-semibold">Nombre de Usuario</label>
                            </div>
                            <div class="input-group">
                                <input type="text" class="form-control form-control-lg bg-light" id="username"
                                    value="<?php echo htmlspecialchars($profileData['user']['username']); ?>" readonly>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="form-label-group">
                                <span class="input-group-text bg-white"><i class="bi bi-type"></i></span>
                                <label for="nombre" class="form-label fw-semibold">Nombre</label>
                            </div>
                            <div class="input-group">
                                <input type="text" class="form-control form-control-lg" id="nombre" name="nombre"
                                    value="<?php echo htmlspecialchars($profileData['user']['nombre']); ?>" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="form-label-group">
                                <span class="input-group-text bg-white"><i class="bi bi-type-bold"></i></span>
                                <label for="apellido" class="form-label fw-semibold">Apellido</label>
                            </div>
                            <div class="input-group">
                                <input type="text" class="form-control form-control-lg" id="apellido" name="apellido"
                                    value="<?php echo htmlspecialchars($profileData['user']['apellido']); ?>" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="form-label-group">
                                <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                                <label for="email" class="form-label fw-semibold">Email</label>
                            </div>
                            <div class="input-group">
                                <input type="email" class="form-control form-control-lg" id="email" name="email"
                                    value="<?php echo htmlspecialchars($profileData['user']['email']); ?>" required>
                            </div>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary btn-lg shadow-sm"><i
                                class="bi bi-save me-2"></i>Actualizar
                            Información</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Change Password Card -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow profile-card h-100">
                <div class="card-header bg-gradient-secondary text-white py-3">
                    <i class="bi bi-shield-lock header-icon"></i>
                    <h5 class="card-title">Cambiar Contraseña</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="">
                        <div class="mb-4">
                            <div class="form-label-group">
                                <span class="input-group-text bg-white"><i class="bi bi-key"></i></span>
                                <label for="current_password" class="form-label fw-semibold">Contraseña Actual</label>
                            </div>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-lg" id="current_password"
                                    name="current_password" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="form-label-group">
                                <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                                <label for="new_password" class="form-label fw-semibold">Nueva Contraseña</label>
                            </div>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-lg" id="new_password"
                                    name="new_password" required>
                            </div>
                            <div class="form-text mt-2" style="margin-left: 12px;"><i class="bi bi-info-circle me-1"
                                    style="margin-right: 10px"></i>La
                                contraseña debe tener
                                al menos 6 caracteres.</div>
                        </div>
                        <div class="mb-4">
                            <div class="form-label-group">
                                <span class="input-group-text bg-white"><i class="bi bi-lock-fill"></i></span>
                                <label for="confirm_password" class="form-label fw-semibold">Confirmar
                                    Contraseña</label>
                            </div>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-lg" id="confirm_password"
                                    name="confirm_password" required>
                            </div>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-secondary btn-lg shadow-sm"><i
                                class="bi bi-check2-circle me-2"></i>Cambiar Contraseña</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Purchase Statistics Card -->
        <div class="col-12 mb-4">
            <div class="card shadow profile-card">
                <div class="card-header bg-gradient-info text-white py-3">
                    <i class="bi bi-graph-up header-icon"></i>
                    <h5 class="card-title">Estadísticas de Compra</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4 text-center">
                        <div class="col-md-3 mb-3">
                            <div class="stat-card p-4 border rounded h-100 shadow-sm">
                                <div class="stat-icon mb-3">
                                    <i class="bi bi-bag-check fs-1 text-primary"></i>
                                </div>
                                <h2 class="fw-bold"><?php echo $profileData['stats']['total_gatos'] ?: 0; ?></h2>
                                <p class="text-muted">Gatos Comprados</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card p-4 border rounded h-100 shadow-sm">
                                <div class="stat-icon mb-3">
                                    <i class="bi bi-currency-euro fs-1 text-success"></i>
                                </div>
                                <h2 class="fw-bold">
                                    <?php echo $profileData['stats']['gasto_total'] ? number_format($profileData['stats']['gasto_total'], 2) . ' €' : '0.00 €'; ?>
                                </h2>
                                <p class="text-muted">Gasto Total</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card p-4 border rounded h-100 shadow-sm">
                                <div class="stat-icon mb-3">
                                    <i class="bi bi-calendar-date fs-1 text-warning"></i>
                                </div>
                                <h2 class="fw-bold"><?php echo $profileData['stats']['primera_compra'] ?: 'N/A'; ?></h2>
                                <p class="text-muted">Primera Compra</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card p-4 border rounded h-100 shadow-sm">
                                <div class="stat-icon mb-3">
                                    <i class="bi bi-calendar-check fs-1 text-info"></i>
                                </div>
                                <h2 class="fw-bold"><?php echo $profileData['stats']['ultima_compra'] ?: 'N/A'; ?></h2>
                                <p class="text-muted">Última Compra</p>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <a href="/views/profile/purchase_history.php" class="btn btn-info btn-lg shadow-sm"><i
                                class="bi bi-clock-history me-2" style="margin-right: 10px"></i>Ver Historial de
                            Compras</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>