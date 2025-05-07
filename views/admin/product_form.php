<?php
require_once __DIR__ . '/../../includes/auth_middleware.php';
require_once __DIR__ . '/../../controllers/AdminController.php';

// Verify admin authentication
$adminController = new AdminController();
if (!$adminController->isAdmin()) {
    header('Location: /views/store/index.php');
    exit;
}

// Check if we're editing an existing product
$isEditing = isset($_GET['id']) && is_numeric($_GET['id']);
$product = null;
$pageTitle = '';

if ($isEditing) {
    $result = $adminController->getProductById($_GET['id']);
    if ($result['success']) {
        $product = $result['data'];
        $pageTitle = "Editar Producto - Cat Store";
    } else {
        // Product not found, redirect to products list
        header('Location: /views/admin/products.php?error=1');
        exit;
    }
} else {
    $pageTitle = "Nuevo Producto - Cat Store";
}

// Additional CSS
$extraCss = ['/assets/css/admin.css'];

// Additional JS
$extraJs = ['/assets/js/admin.js'];

// Include navbar
$includeNavbar = true;

// Include header
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="container admin-container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo $isEditing ? 'Editar Producto' : 'Nuevo Producto'; ?></h1>
        <a href="/views/admin/products.php" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>
    </div>

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><?php echo $isEditing ? 'Editar Producto' : 'Nuevo Producto'; ?></h5>
        </div>
        <div class="card-body">
            <form id="product-form" method="post" action="/api/admin.php?action=<?php echo $isEditing ? 'update_product' : 'create_product'; ?>" enctype="multipart/form-data">
                <?php if ($isEditing): ?>
                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $isEditing ? htmlspecialchars($product['nombre']) : ''; ?>" required>
                            <div class="invalid-feedback" id="nombre-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo *</label>
                            <input type="text" class="form-control" id="tipo" name="tipo" value="<?php echo $isEditing ? htmlspecialchars($product['tipo']) : ''; ?>" required>
                            <div class="invalid-feedback" id="tipo-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="color" class="form-label">Color *</label>
                            <input type="text" class="form-control" id="color" name="color" value="<?php echo $isEditing ? htmlspecialchars($product['color']) : ''; ?>" required>
                            <div class="invalid-feedback" id="color-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="sexo" class="form-label">Sexo *</label>
                            <select class="form-control" id="sexo" name="sexo" required>
                                <option value="">Seleccionar...</option>
                                <option value="1" <?php echo $isEditing && $product['sexo'] == 1 ? 'selected' : ''; ?>>Macho</option>
                                <option value="0" <?php echo $isEditing && $product['sexo'] == 0 ? 'selected' : ''; ?>>Hembra</option>
                            </select>
                            <div class="invalid-feedback" id="sexo-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="precio" class="form-label">Precio (€) *</label>
                            <input type="number" class="form-control" id="precio" name="precio" step="0.01" min="0" value="<?php echo $isEditing ? number_format($product['precio'], 2, '.', '') : ''; ?>" required>
                            <div class="invalid-feedback" id="precio-error"></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="foto" class="form-label">Foto</label>
                            <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                            <div class="form-text">Formatos aceptados: JPG, PNG, GIF. Tamaño máximo: 2MB.</div>
                            <div class="invalid-feedback" id="foto-error"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Vista previa</label>
                            <div class="image-preview-container">
                                <img id="image-preview" src="<?php echo $isEditing && !empty($product['foto']) ? 'data:image/jpeg;base64,' . base64_encode($product['foto']) : '/assets/img/cat-placeholder.jpg'; ?>" alt="Vista previa" class="img-thumbnail">
                            </div>
                        </div>

                        <?php if ($isEditing): ?>
                            <div class="mb-3">
                                <label class="form-label">Fecha de creación</label>
                                <input type="text" class="form-control" value="<?php echo date('d/m/Y H:i', strtotime($product['fecha_anyadido'])); ?>" readonly>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-actions mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo $isEditing ? 'Actualizar' : 'Guardar'; ?>
                    </button>
                    <a href="/views/admin/products.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>