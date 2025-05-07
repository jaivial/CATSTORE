<?php

/**
 * Componente de filtros para la tienda
 * Cat Store - Tienda de Gatos
 */

// Obtener tipos de gatos únicos
$tipos = [];
try {
    $pdo = connectDB();
    $stmt = $pdo->query("SELECT DISTINCT tipo FROM animal ORDER BY tipo ASC");
    while ($row = $stmt->fetch()) {
        $tipos[] = $row['tipo'];
    }
} catch (PDOException $e) {
    // Manejar error silenciosamente
}

// Obtener colores de gatos únicos
$colores = [];
try {
    $pdo = connectDB();
    $stmt = $pdo->query("SELECT DISTINCT color FROM animal ORDER BY color ASC");
    while ($row = $stmt->fetch()) {
        $colores[] = $row['color'];
    }
} catch (PDOException $e) {
    // Manejar error silenciosamente
}

// Valores actuales de los filtros
$currentTipo = isset($params['filters']['tipo']) ? $params['filters']['tipo'] : '';
$currentColor = isset($params['filters']['color']) ? $params['filters']['color'] : '';
$currentSexo = isset($params['filters']['sexo']) ? $params['filters']['sexo'] : '';
$currentPrecioMin = isset($params['filters']['precio_min']) ? $params['filters']['precio_min'] : '';
$currentPrecioMax = isset($params['filters']['precio_max']) ? $params['filters']['precio_max'] : '';
$currentNombre = isset($params['filters']['nombre']) ? $params['filters']['nombre'] : '';
?>

<div class="filters-panel" id="filters-panel">
    <div class="filters-header">
        <h3>Filtros</h3>
        <button class="filters-close" id="filters-close">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <form id="filters-form" method="get">
        <!-- Filtro por nombre -->
        <div class="filter-group">
            <label for="filter-nombre">Nombre:</label>
            <input type="text" id="filter-nombre" name="nombre" class="form-control" value="<?php echo htmlspecialchars($currentNombre); ?>">
        </div>

        <!-- Filtro por tipo -->
        <div class="filter-group">
            <label for="filter-tipo">Tipo:</label>
            <select id="filter-tipo" name="tipo" class="form-control">
                <option value="">Todos</option>
                <?php foreach ($tipos as $tipo): ?>
                    <option value="<?php echo htmlspecialchars($tipo); ?>" <?php echo ($currentTipo === $tipo) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($tipo); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Filtro por color -->
        <div class="filter-group">
            <label for="filter-color">Color:</label>
            <select id="filter-color" name="color" class="form-control">
                <option value="">Todos</option>
                <?php foreach ($colores as $color): ?>
                    <option value="<?php echo htmlspecialchars($color); ?>" <?php echo ($currentColor === $color) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($color); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Filtro por sexo -->
        <div class="filter-group">
            <label for="filter-sexo">Sexo:</label>
            <select id="filter-sexo" name="sexo" class="form-control">
                <option value="">Todos</option>
                <option value="1" <?php echo ($currentSexo === '1') ? 'selected' : ''; ?>>Macho</option>
                <option value="0" <?php echo ($currentSexo === '0') ? 'selected' : ''; ?>>Hembra</option>
            </select>
        </div>

        <!-- Filtro por precio -->
        <div class="filter-group">
            <label>Precio:</label>
            <div class="price-range">
                <input type="number" id="filter-precio-min" name="precio_min" class="form-control" placeholder="Min" min="0" value="<?php echo htmlspecialchars($currentPrecioMin); ?>">
                <span class="price-separator">-</span>
                <input type="number" id="filter-precio-max" name="precio_max" class="form-control" placeholder="Max" min="0" value="<?php echo htmlspecialchars($currentPrecioMax); ?>">
            </div>
        </div>

        <!-- Mantener parámetros de ordenación -->
        <input type="hidden" name="orderBy" value="<?php echo htmlspecialchars($params['orderBy']); ?>">
        <input type="hidden" name="orderDir" value="<?php echo htmlspecialchars($params['orderDir']); ?>">

        <div class="filter-actions">
            <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
            <button type="button" class="btn" id="reset-filters">Limpiar Filtros</button>
        </div>
    </form>
</div>

<div class="filters-overlay" id="filters-overlay"></div>