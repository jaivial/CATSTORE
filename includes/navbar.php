<?php

/**
 * Barra de navegación
 * Cat Store - Tienda de Gatos
 */

// Verificar si el usuario está autenticado
$isAuthenticated = isAuthenticated();
$isAdmin = $isAuthenticated && $_SESSION['user_id'] === 'javial';

// Obtener el número de elementos en el carrito
$cartCount = 0;
if ($isAuthenticated) {
    try {
        $pdo = connectDB();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM carrito WHERE username_usuario = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $cartCount = $stmt->fetchColumn();
    } catch (PDOException $e) {
        // Manejar error silenciosamente
    }
}
?>

<header class="navbar">
    <div class="container">
        <div class="navbar-content">
            <div class="navbar-brand">
                <a href="/views/store/index.php">
                    <h1>Cat Store</h1>
                </a>
            </div>

            <!-- Botón de menú hamburguesa (solo visible en móvil) -->
            <button class="navbar-toggle" id="navbar-toggle" aria-label="Abrir menú">
                <span class="navbar-toggle-icon"></span>
            </button>

            <!-- Contenedor para elementos de navegación (colapsable en móvil) -->
            <div class="navbar-collapse" id="navbar-collapse">
                <?php if ($isAuthenticated): ?>
                    <div class="navbar-actions">
                        <!-- Icono de inicio -->
                        <div class="navbar-action">
                            <a href="/views/store/index.php" class="navbar-icon" title="Inicio">
                                <i class="fas fa-home"></i>
                                <span class="navbar-icon-text">Inicio</span>
                            </a>
                        </div>

                        <!-- Icono de usuario -->
                        <div class="navbar-action">
                            <a href="/views/profile/user_info.php" class="navbar-icon" title="Mi Perfil">
                                <i class="fas fa-user"></i>
                                <span class="navbar-icon-text">Mi Perfil</span>
                            </a>
                        </div>

                        <!-- Icono de cerrar sesión -->
                        <div class="navbar-action">
                            <a href="/controllers/AuthController.php?action=logout" class="navbar-icon"
                                title="Cerrar Sesión">
                                <i class="fas fa-sign-out-alt"></i>
                                <span class="navbar-icon-text">Cerrar Sesión</span>
                            </a>
                        </div>

                        <?php if ($isAdmin): ?>
                            <!-- Icono de administración -->
                            <div class="navbar-action">
                                <a href="/views/admin/products.php" class="navbar-icon" title="Administración">
                                    <i class="fas fa-cog"></i>
                                    <span class="navbar-icon-text">Admin</span>
                                </a>
                            </div>
                        <?php endif; ?>

                        <!-- Icono de carrito -->
                        <div class="navbar-action">
                            <a href="#" class="navbar-icon cart-icon" id="cart-toggle" title="Carrito">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="cart-count" id="cart-count"><?php echo $cartCount; ?></span>
                                <span class="navbar-icon-text">Carrito</span>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="navbar-actions">
                        <!-- Icono de inicio para usuarios no autenticados -->
                        <div class="navbar-action">
                            <a href="/views/store/index.php" class="navbar-icon" title="Inicio">
                                <i class="fas fa-home"></i>
                                <span class="navbar-icon-text">Inicio</span>
                            </a>
                        </div>
                        <a href="/views/auth/login.php" class="btn btn-primary">Iniciar Sesión</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- Overlay para menú móvil -->
<div class="navbar-overlay" id="navbar-overlay"></div>

<?php if ($isAuthenticated): ?>
    <!-- Drawer del carrito -->
    <div class="cart-drawer" id="cart-drawer">
        <div class="cart-drawer-header">
            <h3>Carrito de Compra</h3>
            <button class="cart-close" id="cart-close">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="cart-drawer-content" id="cart-drawer-content">
            <!-- El contenido del carrito se cargará dinámicamente mediante JavaScript -->
            <div class="cart-loading">
                <i class="fas fa-spinner fa-spin"></i> Cargando...
            </div>
        </div>

        <div class="cart-drawer-footer">
            <a href="/views/cart/checkout.php" class="btn btn-primary btn-block">Ver Carrito</a>
        </div>
    </div>

    <!-- Overlay para el drawer del carrito -->
    <div class="cart-overlay" id="cart-overlay"></div>
<?php endif; ?>

<!-- Script para el menú móvil y carrito -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Menú móvil
        const navbarToggle = document.getElementById('navbar-toggle');
        const navbarCollapse = document.getElementById('navbar-collapse');
        const navbarOverlay = document.getElementById('navbar-overlay');

        if (navbarToggle && navbarCollapse && navbarOverlay) {
            navbarToggle.addEventListener('click', function() {
                navbarToggle.classList.toggle('active');
                navbarCollapse.classList.toggle('show');
                navbarOverlay.classList.toggle('show');
                document.body.classList.toggle('navbar-open');
            });

            navbarOverlay.addEventListener('click', function() {
                navbarToggle.classList.remove('active');
                navbarCollapse.classList.remove('show');
                navbarOverlay.classList.remove('show');
                document.body.classList.remove('navbar-open');
            });

            // Cerrar menú al hacer clic en un enlace
            const navLinks = navbarCollapse.querySelectorAll('a');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    navbarToggle.classList.remove('active');
                    navbarCollapse.classList.remove('show');
                    navbarOverlay.classList.remove('show');
                    document.body.classList.remove('navbar-open');
                });
            });
        }

        // Carrito drawer
        const cartToggle = document.getElementById('cart-toggle');
        const cartDrawer = document.getElementById('cart-drawer');
        const cartClose = document.getElementById('cart-close');
        const cartOverlay = document.getElementById('cart-overlay');
        const cartDrawerContent = document.getElementById('cart-drawer-content');

        if (cartToggle && cartDrawer && cartClose && cartOverlay) {
            cartToggle.addEventListener('click', function(e) {
                e.preventDefault();
                cartDrawer.classList.add('active');
                cartOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';

                // Cargar contenido del carrito
                loadCartContent();
            });

            cartClose.addEventListener('click', function() {
                cartDrawer.classList.remove('active');
                cartOverlay.classList.remove('active');
                document.body.style.overflow = '';
            });

            cartOverlay.addEventListener('click', function() {
                cartDrawer.classList.remove('active');
                cartOverlay.classList.remove('active');
                document.body.style.overflow = '';
            });
        }

        // Cargar contenido del carrito
        function loadCartContent() {
            if (!cartDrawerContent) return;

            // Mostrar estado de carga
            cartDrawerContent.innerHTML = '<div class="cart-loading"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';

            // Función para obtener la URL base
            const getBaseUrl = () => {
                // Devolver una cadena vacía para usar URLs relativas
                return '';
            };

            const baseUrl = getBaseUrl();

            // Cargar contenido del carrito usando la API simplificada
            fetch(`${baseUrl}/api/cart_api.php?action=get`)
                .then(response => {
                    // Verificar si la respuesta es exitosa (código 200-299)
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }

                    // Verificar que la respuesta no esté vacía
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error('La respuesta no es de tipo JSON');
                    }

                    return response.text().then(text => {
                        if (!text || text.trim() === '') {
                            throw new Error('Respuesta vacía del servidor');
                        }

                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Error al parsear JSON:', text);
                            throw new Error('Error al parsear JSON: ' + e.message);
                        }
                    });
                })
                .then(data => {
                    if (data.success && data.items && data.items.length > 0) {
                        // Crear HTML para los elementos del carrito
                        let html = '<div class="cart-items">';

                        data.items.forEach(item => {
                            html += `
                                <div class="cart-item">
                                    <div class="cart-item-image">
                                        ${item.foto
                                    ? `<img src="data:image/jpeg;base64,${item.foto}" alt="${item.nombre}">`
                                    : `<img src="/assets/img/cat-placeholder.jpg" alt="Imagen no disponible">`
                                }
                                    </div>
                                    <div class="cart-item-info">
                                        <h4>${item.nombre}</h4>
                                        <p>${item.tipo} - ${item.color}</p>
                                        <p class="cart-item-price">${parseFloat(item.precio).toFixed(2).replace('.', ',')} €</p>
                                    </div>
                                    <div class="cart-item-actions">
                                        <button class="btn btn-sm remove-from-cart" data-id="${item.id}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            `;
                        });

                        html += '</div>';

                        // Añadir total
                        html += `
                            <div class="cart-total">
                                <span>Total:</span>
                                <span>${parseFloat(data.total).toFixed(2).replace('.', ',')} €</span>
                            </div>
                        `;

                        cartDrawerContent.innerHTML = html;

                        // Añadir eventos a los botones de eliminar
                        const removeButtons = document.querySelectorAll('.remove-from-cart');
                        removeButtons.forEach(button => {
                            button.addEventListener('click', function() {
                                const productId = this.getAttribute('data-id');
                                removeFromCart(productId);
                            });
                        });
                    } else if (data.success && (!data.items || data.items.length === 0)) {
                        cartDrawerContent.innerHTML = '<div class="cart-empty">No hay productos en el carrito</div>';
                    } else {
                        // Mostrar mensaje de error amigable y botón de reintentar
                        cartDrawerContent.innerHTML = `
                            <div class="cart-error">
                                <p>Ocurrió un error al cargar el carrito.</p>
                                <p class="error-details">${data.message || 'Error desconocido'}</p>
                                <button class="btn btn-outline-primary" id="retry-cart-load">Reintentar</button>
                            </div>
                        `;
                        const retryBtn = document.getElementById('retry-cart-load');
                        if (retryBtn) retryBtn.addEventListener('click', loadCartContent);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    cartDrawerContent.innerHTML = `
                        <div class="cart-error">
                            <p>Ocurrió un error al cargar el carrito.</p>
                            <p class="error-details">${error.message}</p>
                            <button class="btn btn-outline-primary" id="retry-cart-load">Reintentar</button>
                        </div>
                    `;
                    const retryBtn = document.getElementById('retry-cart-load');
                    if (retryBtn) retryBtn.addEventListener('click', loadCartContent);
                });
        }

        // Eliminar producto del carrito
        function removeFromCart(productId) {
            // Obtener la URL base
            const getBaseUrl = () => {
                return '';
            };

            const baseUrl = getBaseUrl();

            // Enviar solicitud al servidor usando la API simplificada
            fetch(`${baseUrl}/api/cart_api.php?action=remove`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: productId
                    })
                })
                .then(response => {
                    // Verificar si la respuesta es exitosa (código 200-299)
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }

                    // Verificar que la respuesta no esté vacía
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error('La respuesta no es de tipo JSON');
                    }

                    return response.text().then(text => {
                        if (!text || text.trim() === '') {
                            throw new Error('Respuesta vacía del servidor');
                        }

                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Error al parsear JSON:', text);
                            throw new Error('Error al parsear JSON: ' + e.message);
                        }
                    });
                })
                .then(data => {
                    if (data.success) {
                        // Actualizar contador del carrito
                        const cartCount = document.getElementById('cart-count');
                        if (cartCount) {
                            cartCount.textContent = data.count;
                        }

                        // Actualizar todos los contadores del carrito (incluido el del menú lateral)
                        const allCartCounts = document.querySelectorAll('.cart-count');
                        allCartCounts.forEach(counter => {
                            counter.textContent = data.count;
                        });

                        // Recargar contenido del carrito
                        loadCartContent();
                    } else {
                        // Mostrar mensaje de error
                        console.error('Error al eliminar el producto:', data.message || 'Error desconocido');
                        alert('Error al eliminar el producto del carrito: ' + (data.message || 'Error desconocido'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud: ' + error.message);
                });
        }
    });
</script>