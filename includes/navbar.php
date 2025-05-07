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

<!-- Script para el menú móvil -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
    });
</script>