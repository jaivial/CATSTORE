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
$extraCss = ['/assets/css/admin.css', '/assets/css/product-form.css'];

// Additional JS
$extraJs = ['/assets/js/admin.js'];

// Include navbar
$includeNavbar = true;

// Include header
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="product-form-container">
    <div class="product-form-header">
        <h1 class="product-form-title"><?php echo $isEditing ? 'Editar Producto' : 'Nuevo Producto'; ?></h1>
        <a href="/views/admin/products.php" class="product-form-back-btn">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>
    </div>

    <div class="product-form-card">
        <div class="product-form-card-header">
            <h5 class="product-form-card-title"><?php echo $isEditing ? 'Editar Producto' : 'Nuevo Producto'; ?></h5>
        </div>
        <div class="product-form-card-body">
            <form id="product-form" method="post"
                action="/api/admin.php?action=<?php echo $isEditing ? 'update_product' : 'create_product'; ?>"
                enctype="multipart/form-data">
                <?php if ($isEditing): ?>
                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                <?php endif; ?>

                <div class="product-form-grid">
                    <div>
                        <div class="product-form-group">
                            <label for="nombre" class="product-form-label product-form-label-required">Nombre</label>
                            <input type="text" class="product-form-input" id="nombre" name="nombre"
                                value="<?php echo $isEditing ? htmlspecialchars($product['nombre']) : ''; ?>" required>
                            <div class="product-form-invalid-feedback" id="nombre-error"></div>
                        </div>

                        <div class="product-form-group">
                            <label for="tipo" class="product-form-label product-form-label-required">Tipo</label>
                            <input type="text" class="product-form-input" id="tipo" name="tipo"
                                value="<?php echo $isEditing ? htmlspecialchars($product['tipo']) : ''; ?>" required>
                            <div class="product-form-invalid-feedback" id="tipo-error"></div>
                        </div>

                        <div class="product-form-group">
                            <label for="color" class="product-form-label product-form-label-required">Color</label>
                            <input type="text" class="product-form-input" id="color" name="color"
                                value="<?php echo $isEditing ? htmlspecialchars($product['color']) : ''; ?>" required>
                            <div class="product-form-invalid-feedback" id="color-error"></div>
                        </div>

                        <div class="product-form-group">
                            <label for="sexo" class="product-form-label product-form-label-required">Sexo</label>
                            <select class="product-form-select" id="sexo" name="sexo" required>
                                <option value="">Seleccionar...</option>
                                <option value="1" <?php echo $isEditing && $product['sexo'] == 1 ? 'selected' : ''; ?>>
                                    Macho</option>
                                <option value="0" <?php echo $isEditing && $product['sexo'] == 0 ? 'selected' : ''; ?>>
                                    Hembra</option>
                            </select>
                            <div class="product-form-invalid-feedback" id="sexo-error"></div>
                        </div>

                        <div class="product-form-group">
                            <label for="precio" class="product-form-label product-form-label-required">Precio
                                (€)</label>
                            <input type="number" class="product-form-input" id="precio" name="precio" step="0.01"
                                min="0"
                                value="<?php echo $isEditing ? number_format($product['precio'], 2, '.', '') : ''; ?>"
                                required>
                            <div class="product-form-invalid-feedback" id="precio-error"></div>
                        </div>
                    </div>

                    <div>
                        <div class="product-form-group">
                            <label for="foto" class="product-form-label">Foto</label>
                            <div class="product-form-file">
                                <input type="file" class="product-form-file-input" id="foto" name="foto"
                                    accept="image/*">
                                <label class="product-form-file-label" for="foto">
                                    <span class="product-form-file-text">Seleccionar archivo...</span>
                                    <span class="product-form-file-button">Examinar</span>
                                </label>
                            </div>
                            <div class="product-form-text">Formatos aceptados: JPG, PNG, GIF. Tamaño máximo: 2MB.</div>
                            <div class="product-form-invalid-feedback" id="foto-error"></div>
                        </div>

                        <div class="product-form-group">
                            <label class="product-form-image-preview-label">Vista previa</label>
                            <div class="product-form-image-preview">
                                <div class="product-form-image-container">
                                    <?php if ($isEditing && !empty($product['foto'])): ?>
                                        <img id="image-preview"
                                            src="<?php echo 'data:image/jpeg;base64,' . base64_encode($product['foto']); ?>"
                                            alt="Vista previa" class="product-form-image">
                                    <?php else: ?>
                                        <div class="product-form-image-placeholder">
                                            <i class="fas fa-cat"></i>
                                            <span class="product-form-image-placeholder-text">No hay imagen
                                                seleccionada</span>
                                        </div>
                                        <img id="image-preview" src="/assets/img/cat-placeholder.png" alt="Vista previa"
                                            class="product-form-image">
                                    <?php endif; ?>
                                    <div class="product-form-image-overlay">
                                        <button type="button" class="product-form-image-action" id="change-image">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($isEditing): ?>
                            <div class="product-form-group">
                                <label class="product-form-label">Fecha de creación</label>
                                <input type="text" class="product-form-input"
                                    value="<?php echo date('d/m/Y H:i', strtotime($product['fecha_anyadido'])); ?>"
                                    readonly>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="product-form-actions">
                    <button type="submit" class="product-form-btn product-form-btn-primary">
                        <i class="fas fa-save"></i> <?php echo $isEditing ? 'Actualizar' : 'Guardar'; ?>
                    </button>
                    <a href="/views/admin/products.php" class="product-form-btn product-form-btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Notification toast -->
<div id="notification-toast" class="notification-toast">
    <div class="notification-toast-content">
        <div class="notification-toast-icon">
            <i class="fas fa-check-circle notification-toast-success-icon"></i>
            <i class="fas fa-exclamation-circle notification-toast-error-icon"></i>
        </div>
        <div class="notification-toast-message"></div>
    </div>
    <div class="notification-toast-progress"></div>
</div>

<style>
    /* Estilos adicionales en línea */
    #change-image {
        background-color: var(--form-primary);
        color: var(--form-white);
    }

    #change-image:hover {
        background-color: var(--form-primary-hover);
    }

    .product-form-file-input:focus~.product-form-file-label {
        border-color: var(--form-primary);
        box-shadow: 0 0 0 0.2rem rgba(74, 108, 247, 0.15);
    }

    /* Animación para el botón de guardar */
    .product-form-btn-primary:active {
        transform: scale(0.98);
    }

    /* Notification Toast */
    .notification-toast {
        position: fixed;
        top: 20px;
        right: 20px;
        width: 350px;
        background-color: white;
        box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
        padding: 15px 20px;
        z-index: 9999;
        overflow: hidden;
        transform: translateX(400px);
        transition: all 0.5s cubic-bezier(0.68, -0.55, 0.25, 1.35);
        opacity: 0;
        pointer-events: none;
    }

    .notification-toast.active {
        transform: translateX(0);
        opacity: 1;
        pointer-events: auto;
    }

    .notification-toast-content {
        display: flex;
        align-items: center;
    }

    .notification-toast-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 35px;
        width: 35px;
        border-radius: 50%;
        margin-right: 15px;
    }

    .notification-toast-success .notification-toast-icon {
        background-color: #4caf50;
    }

    .notification-toast-error .notification-toast-icon {
        background-color: #f44336;
    }

    .notification-toast-icon i {
        font-size: 20px;
        color: white;
    }

    .notification-toast-success-icon {
        display: none;
    }

    .notification-toast-error-icon {
        display: none;
    }

    .notification-toast-success .notification-toast-success-icon {
        display: block;
    }

    .notification-toast-error .notification-toast-error-icon {
        display: block;
    }

    .notification-toast-message {
        font-size: 16px;
        font-weight: 500;
    }

    .notification-toast-progress {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        width: 100%;
        background: #ddd;
    }

    .notification-toast-progress:before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        height: 100%;
        width: 100%;
        background-color: #4a6cf7;
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.25s linear;
    }

    .notification-toast.active .notification-toast-progress:before {
        transform: scaleX(1);
        transition: transform 5s linear;
    }

    .notification-toast-success .notification-toast-progress:before {
        background-color: #4caf50;
    }

    .notification-toast-error .notification-toast-progress:before {
        background-color: #f44336;
    }

    /* Loading overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9998;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }

    .loading-overlay.active {
        opacity: 1;
        pointer-events: auto;
    }

    .loading-spinner {
        width: 50px;
        height: 50px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid var(--form-primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
</style>

<script>
    // Script para manejar la vista previa de la imagen
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('foto');
        const imagePreview = document.getElementById('image-preview');
        const changeImageBtn = document.getElementById('change-image');
        const placeholder = document.querySelector('.product-form-image-placeholder');
        const form = document.getElementById('product-form');
        const notificationToast = document.getElementById('notification-toast');
        const notificationMessage = document.querySelector('.notification-toast-message');

        // Crear el overlay de carga
        const loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'loading-overlay';
        loadingOverlay.innerHTML = '<div class="loading-spinner"></div>';
        document.body.appendChild(loadingOverlay);

        // Función para mostrar notificación
        function showNotification(message, type) {
            notificationMessage.textContent = message;
            notificationToast.className = 'notification-toast active notification-toast-' + type;

            // Ocultar notificación después de 5 segundos
            setTimeout(() => {
                notificationToast.classList.remove('active');
            }, 5000);
        }

        // Función para mostrar/ocultar overlay de carga
        function toggleLoading(show) {
            if (show) {
                loadingOverlay.classList.add('active');
            } else {
                loadingOverlay.classList.remove('active');
            }
        }

        // Manejar envío del formulario
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Mostrar overlay de carga
                toggleLoading(true);

                // Crear FormData para enviar los datos del formulario
                const formData = new FormData(this);

                // Enviar solicitud AJAX
                fetch(this.action, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        toggleLoading(false);

                        if (data.success) {
                            // Mostrar notificación de éxito
                            showNotification(data.message || 'Producto guardado correctamente', 'success');

                            // Redireccionar después de 1 segundo
                            setTimeout(() => {
                                window.location.href = '/views/admin/products.php';
                            }, 1000);
                        } else {
                            // Mostrar notificación de error
                            showNotification(data.message || 'Error al guardar el producto', 'error');
                        }
                    })
                    .catch(error => {
                        toggleLoading(false);
                        showNotification('Error de conexión', 'error');
                        console.error('Error:', error);
                    });
            });
        }

        // Actualizar la vista previa cuando se selecciona un archivo
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                    imagePreview.style.display = 'block';
                };

                reader.readAsDataURL(this.files[0]);
            }
        });

        // Botón para cambiar la imagen
        if (changeImageBtn) {
            changeImageBtn.addEventListener('click', function() {
                fileInput.click();
            });
        }

        // Actualizar el texto del input file cuando se selecciona un archivo
        fileInput.addEventListener('change', function() {
            const fileText = document.querySelector('.product-form-file-text');
            if (fileText) {
                fileText.textContent = this.files.length > 0 ?
                    this.files[0].name :
                    'Seleccionar archivo...';
            }
        });
    });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

<!-- Cart Drawer -->
<div id="cart-drawer" class="cart-drawer">
    <div class="cart-drawer-header">
        <h3>Tu Carrito</h3>
        <button id="cart-close" class="cart-close">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div id="cart-drawer-content" class="cart-drawer-content">
        <!-- Contenido del carrito se cargará dinámicamente -->
    </div>
</div>

<!-- Cart Overlay -->
<div id="cart-overlay" class="cart-overlay"></div>

<!-- Incluir fixed_drawer.js -->
<script src="/fixed_drawer.js"></script>