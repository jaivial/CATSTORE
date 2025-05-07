<?php

/**
 * Vista principal de la tienda
 * Cat Store - Tienda de Gatos
 */

require_once __DIR__ . '/../../includes/auth_middleware.php';
require_once __DIR__ . '/../../controllers/StoreController.php';

// Verificar autenticación
requireAuth();

// Título de la página
$pageTitle = "Cat Store - Tienda de Gatos";

// CSS adicional
$extraCss = ['/assets/css/store.css'];

// JS adicional
$extraJs = ['/assets/js/store.js'];

// Incluir navbar
$includeNavbar = true;

// Instanciar controlador
$storeController = new StoreController();

// Procesar parámetros de la solicitud
$params = $storeController->processRequestParams();

// Obtener animales
$result = empty($params['filters'])
    ? $storeController->getAllAnimals($params['orderBy'], $params['orderDir'])
    : $storeController->getFilteredAnimals($params['filters'], $params['orderBy'], $params['orderDir']);

// Incluir cabecera
include_once __DIR__ . '/../../includes/header.php';
?>

<main class="main-content">
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Cat Store</h1>
            <p>Encuentra tu compañero felino perfecto</p>
        </div>
    </section>

    <!-- Contenido principal -->
    <section class="store-content">
        <div class="container">
            <div class="store-header">
                <div class="store-filters-toggle">
                    <button class="btn" id="filters-toggle">
                        <i class="fas fa-filter"></i> Filtros
                    </button>
                </div>

                <div class="store-sort">
                    <label for="sort-select">Ordenar por:</label>
                    <select id="sort-select" class="form-control">
                        <option value="fecha_anyadido-DESC" <?php echo ($params['orderBy'] === 'fecha_anyadido' && $params['orderDir'] === 'DESC') ? 'selected' : ''; ?>>Más recientes</option>
                        <option value="fecha_anyadido-ASC" <?php echo ($params['orderBy'] === 'fecha_anyadido' && $params['orderDir'] === 'ASC') ? 'selected' : ''; ?>>Más antiguos</option>
                        <option value="precio-ASC" <?php echo ($params['orderBy'] === 'precio' && $params['orderDir'] === 'ASC') ? 'selected' : ''; ?>>Precio: menor a mayor</option>
                        <option value="precio-DESC" <?php echo ($params['orderBy'] === 'precio' && $params['orderDir'] === 'DESC') ? 'selected' : ''; ?>>Precio: mayor a menor</option>
                        <option value="nombre-ASC" <?php echo ($params['orderBy'] === 'nombre' && $params['orderDir'] === 'ASC') ? 'selected' : ''; ?>>Nombre: A-Z</option>
                        <option value="nombre-DESC" <?php echo ($params['orderBy'] === 'nombre' && $params['orderDir'] === 'DESC') ? 'selected' : ''; ?>>Nombre: Z-A</option>
                    </select>
                </div>
            </div>

            <div class="store-container">
                <!-- Panel de filtros (drawer) -->
                <?php include_once __DIR__ . '/filters.php'; ?>

                <!-- Grid de productos -->
                <div class="products-grid" id="products-grid">
                    <?php if ($result['success'] && count($result['data']) > 0): ?>
                        <?php foreach ($result['data'] as $animal): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <?php if (!empty($animal['foto'])): ?>
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($animal['foto']); ?>" alt="<?php echo htmlspecialchars($animal['nombre']); ?>">
                                    <?php else: ?>
                                        <img src="/assets/img/cat-placeholder.jpg" alt="Imagen no disponible">
                                    <?php endif; ?>
                                </div>

                                <div class="product-info">
                                    <h3><?php echo htmlspecialchars($animal['nombre']); ?></h3>

                                    <div class="product-details">
                                        <p><strong>Tipo:</strong> <?php echo htmlspecialchars($animal['tipo']); ?></p>
                                        <p><strong>Color:</strong> <?php echo htmlspecialchars($animal['color']); ?></p>
                                        <p><strong>Sexo:</strong> <?php echo $animal['sexo'] ? 'Macho' : 'Hembra'; ?></p>
                                        <p class="product-price"><?php echo number_format($animal['precio'], 2, ',', '.'); ?> €</p>
                                    </div>

                                    <button class="btn btn-primary add-to-cart" data-id="<?php echo $animal['id']; ?>">
                                        <i class="fas fa-cart-plus"></i> Añadir al carrito
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-products">
                            <p>No se encontraron gatos con los filtros seleccionados.</p>
                            <button class="btn" id="clear-filters">Limpiar filtros</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
// Incluir el drawer del carrito
include_once __DIR__ . '/cart_drawer.php';

// Incluir pie de página
include_once __DIR__ . '/../../includes/footer.php';
?>