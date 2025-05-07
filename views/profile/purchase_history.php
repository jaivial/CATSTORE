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
$purchaseData = $profileController->getPurchaseHistory($username);

// Título de la página
$pageTitle = "Historial de Compras - Cat Store";

// Incluir navbar
$includeNavbar = true;

// Incluir CSS adicional
$extraCss = ['../../assets/css/purchase-history.css'];

// Include header
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="container purchase-history-container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Historial de Compras</h1>
        <a href="/views/profile/user_info.php" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Volver al Perfil
        </a>
    </div>

    <?php if (empty($purchaseData['purchases'])): ?>
        <div class="alert alert-info alert-dismissible fade show shadow-sm border-left-info animate__animated animate__fadeIn">
            <div class="d-flex align-items-center">
                <i class="bi bi-info-circle-fill text-info me-3 fs-4"></i>
                <div>
                    <strong>Información</strong>
                    <p class="mb-0">No has realizado ninguna compra todavía.</p>
                </div>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="/views/store/index.php" class="btn btn-primary">Ir a la Tienda</a>
        </div>
    <?php else: ?>
        <div class="card shadow" style="margin-top: 20px !important;">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Tus Compras</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Foto</th>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Sexo</th>
                                <th>Precio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($purchaseData['purchases'] as $purchase): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($purchase['fecha_formateada']); ?></td>
                                    <td>
                                        <?php if ($purchase['foto']): ?>
                                            <img src="data:image/jpeg;base64,<?php echo base64_encode($purchase['foto']); ?>"
                                                alt="<?php echo htmlspecialchars($purchase['nombre']); ?>" class="img-thumbnail"
                                                style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php else: ?>
                                            <img src="/assets/img/cat-placeholder.jpg" alt="Sin imagen" class="img-thumbnail"
                                                style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($purchase['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($purchase['tipo']); ?></td>
                                    <td><?php echo htmlspecialchars($purchase['sexo_texto']); ?></td>
                                    <td><?php echo number_format($purchase['precio'], 2); ?> €</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex flex-column gap-3 justify-content-center align-items-center">
                    <span>Total de compras: <?php echo count($purchaseData['purchases']); ?></span>
                    <a href="/views/store/index.php" class="btn btn-primary">Seguir Comprando</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>