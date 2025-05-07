<?php

/**
 * Vista de checkout del carrito
 * Cat Store - Tienda de Gatos
 */

require_once __DIR__ . '/../../includes/auth_middleware.php';
require_once __DIR__ . '/../../controllers/CartController.php';

// Verificar autenticación
requireAuth();

// Título de la página
$pageTitle = "Carrito de Compra - Cat Store";

// CSS adicional
$extraCss = ['/assets/css/cart.css'];

// JS adicional
$extraJs = ['/assets/js/cart.js'];

// Incluir navbar
$includeNavbar = true;

// Instanciar controlador
$cartController = new CartController();

// Obtener contenido del carrito
$result = $cartController->getCartContent();

// Incluir cabecera
include_once __DIR__ . '/../../includes/header.php';
?>

<main class="main-content">
    <section class="cart-section">
        <div class="container">
            <h1>Carrito de Compra</h1>

            <?php if ($result['success'] && !empty($result['items'])): ?>
                <div class="cart-content">
                    <div class="cart-items">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th>Precio</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($result['items'] as $item): ?>
                                    <tr>
                                        <td class="cart-item-image">
                                            <?php if (!empty($item['foto'])): ?>
                                                <img src="data:image/jpeg;base64,<?php echo base64_encode($item['foto']); ?>" alt="<?php echo htmlspecialchars($item['nombre']); ?>">
                                            <?php else: ?>
                                                <img src="/assets/img/cat-placeholder.jpg" alt="Imagen no disponible">
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($item['tipo']); ?></td>
                                        <td><?php echo number_format($item['precio'], 2, ',', '.'); ?> €</td>
                                        <td><?php echo $item['repetitions']; ?></td>
                                        <td><?php echo number_format($item['precio'] * $item['repetitions'], 2, ',', '.'); ?> €</td>
                                        <td>
                                            <button class="btn btn-sm remove-from-cart" data-id="<?php echo $item['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="cart-summary">
                        <h3>Resumen del pedido</h3>

                        <div class="cart-summary-item">
                            <span>Subtotal:</span>
                            <span><?php echo number_format($result['total'], 2, ',', '.'); ?> €</span>
                        </div>

                        <div class="cart-summary-item">
                            <span>Gastos de envío:</span>
                            <span>Gratis</span>
                        </div>

                        <div class="cart-summary-item cart-total">
                            <span>Total:</span>
                            <span><?php echo number_format($result['total'], 2, ',', '.'); ?> €</span>
                        </div>

                        <div class="cart-actions">
                            <button class="btn btn-primary btn-lg btn-block" id="checkout-button">
                                Finalizar Compra
                            </button>

                            <button class="btn btn-block" id="clear-cart-button">
                                Vaciar Carrito
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="cart-empty">
                    <p>No hay productos en el carrito</p>
                    <a href="/views/store/index.php" class="btn btn-primary">Ver Productos</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modal de confirmación -->
    <div class="modal" id="confirmation-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirmar compra</h3>
                <button class="modal-close" id="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body">
                <p>¿Estás seguro de que deseas finalizar la compra?</p>
            </div>

            <div class="modal-footer">
                <button class="btn" id="cancel-checkout">Cancelar</button>
                <button class="btn btn-primary" id="confirm-checkout">Confirmar</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modal-overlay"></div>
</main>

<?php
// Incluir pie de página
include_once __DIR__ . '/../../includes/footer.php';
?>