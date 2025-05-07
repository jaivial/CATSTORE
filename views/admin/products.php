<?php
require_once __DIR__ . '/../../includes/auth_middleware.php';
require_once __DIR__ . '/../../controllers/AdminController.php';

// Verify admin authentication
$adminController = new AdminController();
if (!$adminController->isAdmin()) {
    header('Location: /views/store/index.php');
    exit;
}

// Get products
$orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'id';
$orderDir = isset($_GET['orderDir']) ? $_GET['orderDir'] : 'ASC';
$result = $adminController->getAllProducts($orderBy, $orderDir);

// Title of the page
$pageTitle = "Administración de Productos - Cat Store";

// Additional CSS
$extraCss = [
    '/assets/css/admin.css',
    'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css'
];

// Additional JS
$extraJs = [
    '/assets/js/admin.js',
    'https://code.jquery.com/jquery-3.5.1.slim.min.js',
    'https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js',
    'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js'
];

// Include navbar
$includeNavbar = true;

// Include header
include_once __DIR__ . '/../../includes/header.php';
?>

<style>
    /* Variables CSS para tema coherente */
    :root {
        --primary-color: #4a6cf7;
        --primary-dark: #3256e7;
        --primary-light: #eaefff;
        --secondary-color: #6c757d;
        --success-color: #28a745;
        --info-color: #17a2b8;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --light-color: #f8f9fa;
        --dark-color: #343a40;
        --white-color: #ffffff;

        --border-radius-sm: 4px;
        --border-radius-md: 8px;
        --border-radius-lg: 16px;
        --border-radius-xl: 24px;
        --border-radius-circle: 50%;

        --box-shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.05);
        --box-shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
        --box-shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.12);

        --transition-fast: 0.2s ease;
        --transition-normal: 0.3s ease;

        --font-size-xs: 0.75rem;
        --font-size-sm: 0.875rem;
        --font-size-md: 1rem;
        --font-size-lg: 1.25rem;
        --font-size-xl: 1.5rem;
        --font-size-xxl: 2rem;
    }

    /* Estilos generales del contenedor admin */
    .admin-container {
        padding: 1.5rem;
        max-width: 1400px;
        margin: 0 auto;
        color: var(--dark-color);
    }

    /* Tarjetas con efecto de elevación */
    .admin-card {
        border-radius: var(--border-radius-md);
        box-shadow: var(--box-shadow-sm);
        border: none;
        margin-bottom: 1.5rem;
        transition: transform var(--transition-fast), box-shadow var(--transition-fast);
        background-color: var(--white-color);
    }

    .admin-card:hover {
        box-shadow: var(--box-shadow-md);
        transform: translateY(-2px);
    }

    .admin-card-header {
        background-color: var(--white-color);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 1.25rem 1.5rem;
        border-radius: var(--border-radius-md) var(--border-radius-md) 0 0;
    }

    /* Badges mejorados */
    .admin-badge-rounded {
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 500;
        font-size: var(--font-size-xs);
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }

    .admin-badge-info {
        background-color: rgba(23, 162, 184, 0.15);
        color: var(--info-color);
    }

    .admin-badge-primary {
        background-color: rgba(74, 108, 247, 0.15);
        color: var(--primary-color);
    }

    .admin-badge-success {
        background-color: rgba(40, 167, 69, 0.15);
        color: var(--success-color);
    }

    .admin-badge-warning {
        background-color: rgba(255, 193, 7, 0.15);
        color: #d6a206;
    }

    .admin-badge-danger {
        background-color: rgba(220, 53, 69, 0.15);
        color: var(--danger-color);
    }

    /* Botones circulares y con efecto hover */
    .admin-btn-circle {
        border-radius: 50px;
        transition: all var(--transition-fast);
    }

    .admin-btn-circle:hover {
        transform: translateY(-2px);
        box-shadow: var(--box-shadow-sm);
    }

    /* Tabla administrativa mejorada */
    .admin-table {
        margin-bottom: 0;
    }

    .admin-table td,
    .admin-table th {
        vertical-align: middle;
        padding: 1rem;
    }

    .admin-table thead th {
        font-weight: 600;
        border-top: none;
        background-color: rgba(0, 0, 0, 0.02);
    }

    .admin-table tbody tr {
        transition: background-color var(--transition-fast);
    }

    .admin-table tbody tr:hover {
        background-color: rgba(74, 108, 247, 0.03);
    }

    /* Punto de color para indicadores visuales */
    .admin-color-dot {
        width: 14px;
        height: 14px;
        border-radius: var(--border-radius-circle);
        display: inline-block;
        border: 1px solid rgba(0, 0, 0, 0.1);
        margin-right: 8px;
        position: relative;
    }

    .admin-color-dot::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 6px;
        height: 6px;
        border-radius: var(--border-radius-circle);
        background-color: rgba(255, 255, 255, 0.8);
        opacity: 0;
        transition: opacity var(--transition-fast);
    }

    .admin-color-dot:hover::after {
        opacity: 1;
    }

    /* Íconos con fondo circular */
    .admin-icon-rounded {
        width: 50px;
        height: 50px;
        border-radius: var(--border-radius-circle);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform var(--transition-fast);
    }

    .admin-icon-rounded:hover {
        transform: scale(1.05);
    }

    .admin-icon-bg-primary {
        background-color: rgba(74, 108, 247, 0.1);
    }

    .admin-icon-bg-success {
        background-color: rgba(40, 167, 69, 0.1);
    }

    .admin-icon-bg-info {
        background-color: rgba(23, 162, 184, 0.1);
    }

    .admin-icon-bg-warning {
        background-color: rgba(255, 193, 7, 0.1);
    }

    .admin-icon-bg-danger {
        background-color: rgba(220, 53, 69, 0.1);
    }

    /* Miniaturas de imágenes mejoradas */
    .admin-thumbnail {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: var(--border-radius-md);
        transition: transform var(--transition-fast);
        border: 2px solid rgba(0, 0, 0, 0.03);
    }

    .admin-thumbnail:hover {
        transform: scale(1.05);
    }

    .admin-thumbnail-placeholder {
        width: 60px;
        height: 60px;
        border-radius: var(--border-radius-md);
        background-color: rgba(0, 0, 0, 0.03);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color var(--transition-fast);
    }

    .admin-thumbnail-placeholder:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }

    /* Alertas con diseño mejorado */
    .admin-alert {
        border-radius: var(--border-radius-md);
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        box-shadow: var(--box-shadow-sm);
    }

    .admin-alert-success {
        background-color: #f0fff4;
        border-left: 4px solid var(--success-color);
    }

    .admin-alert-danger {
        background-color: #fff5f5;
        border-left: 4px solid var(--danger-color);
    }

    .admin-alert-warning {
        background-color: #fffbeb;
        border-left: 4px solid var(--warning-color);
    }

    .admin-alert-info {
        background-color: #e6f7ff;
        border-left: 4px solid var(--info-color);
    }

    /* Botones de acción agrupados */
    .admin-action-buttons {
        display: flex;
        justify-content: center;
    }

    .admin-action-buttons .btn {
        margin: 0 3px;
        transition: all var(--transition-fast);
        border-radius: var(--border-radius-circle);
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .admin-action-buttons .btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--box-shadow-sm);
    }

    /* Migas de pan mejoradas */
    .breadcrumb {
        border-radius: var(--border-radius-md);
        box-shadow: var(--box-shadow-sm);
        padding: 0.75rem 1.5rem;
    }

    .breadcrumb-item a {
        color: var(--primary-color);
        font-weight: 500;
        transition: color var(--transition-fast);
    }

    .breadcrumb-item a:hover {
        color: var(--primary-dark);
        text-decoration: none;
    }

    .breadcrumb-item.active {
        font-weight: 600;
        color: var(--secondary-color);
    }

    /* Fix para Bootstrap modificando estilos del navbar */
    .navbar-actions .btn {
        display: inline-block;
        padding: 0.6rem 1.2rem;
        background-color: var(--primary-color);
        color: white;
        border: none;
        border-radius: var(--border-radius-sm);
        font-size: var(--font-size-md);
        font-weight: 500;
        cursor: pointer;
        transition: all var(--transition-fast);
        text-align: center;
    }

    .navbar-actions .btn:hover {
        background-color: var(--primary-dark);
    }

    /* Revertir los estilos de Bootstrap que afectan al navbar */
    .navbar,
    .navbar .container,
    .navbar-content,
    .navbar-brand,
    .navbar-actions,
    .navbar-collapse,
    .navbar-action,
    .navbar-icon,
    .navbar-icon-text,
    .navbar-toggle,
    .navbar-toggle-icon,
    .navbar-overlay {
        box-sizing: border-box;
    }

    .navbar-toggle {
        padding: 0.5rem;
    }

    /* Paginación mejorada */
    .pagination .page-link {
        border-radius: var(--border-radius-sm);
        margin: 0 2px;
        color: var(--primary-color);
        font-weight: 500;
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all var(--transition-fast);
    }

    .pagination .page-link:hover {
        background-color: var(--primary-light);
        color: var(--primary-dark);
        border-color: var(--primary-light);
        z-index: 1;
    }

    .pagination .page-item.active .page-link {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    /* Animaciones para elementos clave */
    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-up {
        animation: fadeUp 0.5s ease forwards;
    }

    /* Estilos específicos para tarjetas de estadísticas */
    .stats-wrapper {
        display: flex;
        align-items: center;
    }

    .stats-value-container {
        display: flex;
        align-items: baseline;
    }

    .stats-meta-container {
        display: flex;
        align-items: center;
    }

    /* Estilos de las tarjetas */
    .stats-card {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        padding: 1.25rem;
        height: 100%;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        position: relative;
        overflow: hidden;
        border: none;
        margin-bottom: 1.5rem;
    }

    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .stats-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        flex-shrink: 0;
    }

    .stats-icon-primary {
        background-color: rgba(74, 108, 247, 0.1);
    }

    .stats-icon-success {
        background-color: rgba(40, 167, 69, 0.1);
    }

    .stats-icon-info {
        background-color: rgba(23, 162, 184, 0.1);
    }

    .stats-icon i {
        font-size: 1.25rem;
        margin-right: 0;
    }

    .stats-icon-primary i {
        color: #4a6cf7;
    }

    .stats-icon-success i {
        color: #28a745;
    }

    .stats-icon-info i {
        color: #17a2b8;
    }

    .stats-content {
        flex-grow: 1;
    }

    .stats-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 0.25rem;
    }

    .stats-value {
        font-size: 2.25rem;
        font-weight: 700;
        color: #343a40;
        line-height: 1.2;
        margin-bottom: 0.25rem;
        display: flex;
        align-items: baseline;
    }

    .stats-value .badge {
        font-size: 0.7rem;
        padding: 0.35rem 0.5rem;
        margin-left: 0.75rem;
        border-radius: 50px;
        display: inline-flex;
        align-items: center;
    }

    .stats-value .badge-success {
        background-color: rgba(40, 167, 69, 0.15);
        color: #28a745;
    }

    .stats-value .badge-danger {
        background-color: rgba(220, 53, 69, 0.15);
        color: #dc3545;
    }

    .stats-value .badge i {
        margin-right: 0.25rem;
    }

    .stats-meta {
        font-size: 0.7rem;
        color: #6c757d;
        display: flex;
        align-items: center;
    }

    .stats-meta i {
        margin-right: 0.25rem;
    }

    /* Alertas */
    .custom-alert {
        display: flex;
        align-items: center;
        border-radius: 8px;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .custom-alert-success {
        background-color: #f0fff4;
        border-left: 4px solid #28a745;
    }

    .custom-alert-danger {
        background-color: #fff5f5;
        border-left: 4px solid #dc3545;
    }

    .alert-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
    }

    .alert-icon-success {
        background-color: rgba(40, 167, 69, 0.1);
    }

    .alert-icon-danger {
        background-color: rgba(220, 53, 69, 0.1);
    }

    .alert-icon i {
        font-size: 1.25rem;
        margin-right: 0;
    }

    .alert-icon-success i {
        color: #28a745;
    }

    .alert-icon-danger i {
        color: #dc3545;
    }

    .alert-content {
        flex: 1;
    }

    .alert-title {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .alert-message {
        margin-bottom: 0;
    }

    .alert-close {
        background: none;
        border: none;
        font-size: 1.25rem;
        cursor: pointer;
        margin-left: auto;
        padding: 0;
        line-height: 1;
    }

    /* Estilos para el encabezado de página */
    .page-header {
        display: flex;
        flex-direction: column;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    @media (min-width: 768px) {
        .page-header {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
        }
    }

    .page-title {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: #343a40;
    }

    .page-description {
        color: #6c757d;
        margin-bottom: 1rem;
    }

    /* Estilos para el botón de nuevo producto */
    .btn-new {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        border-radius: 50px;
        border: none;
        background-color: #4a6cf7;
        color: white;
        transition: 0.2s ease;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .btn-new:hover {
        background-color: #3256e7;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        color: white;
        text-decoration: none;
    }

    .btn-new i {
        margin-right: 0.75rem;
        font-size: 1rem;
    }

    /* Breadcrumb estilizado */
    .custom-breadcrumb {
        display: flex;
        list-style: none;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        padding: 0.75rem 1.5rem;
        margin-bottom: 1.5rem;
    }

    .custom-breadcrumb-item {
        display: flex;
        align-items: center;
    }

    .custom-breadcrumb-item+.custom-breadcrumb-item::before {
        content: "/";
        display: inline-block;
        padding: 0 0.5rem;
        color: #6c757d;
    }

    .custom-breadcrumb-item a {
        color: #4a6cf7;
        font-weight: 500;
        text-decoration: none;
        display: flex;
        align-items: center;
    }

    .custom-breadcrumb-item a:hover {
        color: #3256e7;
    }

    .custom-breadcrumb-item.active {
        font-weight: 600;
        color: #6c757d;
    }

    /* Sección de búsqueda y filtros */
    .search-card {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .search-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .search-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
    }

    .search-title {
        font-weight: 600;
        margin-bottom: 0;
        font-size: 1rem;
        display: flex;
        align-items: center;
    }

    .search-title i {
        color: #4a6cf7;
        margin-right: 0.5rem;
    }

    .search-options-btn {
        padding: 0.375rem 0.75rem;
        font-size: 0.85rem;
        border-radius: 50px;
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        color: #6c757d;
        cursor: pointer;
        display: flex;
        align-items: center;
        transition: all 0.2s ease;
    }

    .search-options-btn:hover {
        background-color: #e9ecef;
    }

    .search-options-btn i {
        margin-right: 0.35rem;
    }

    .search-form-row {
        display: flex;
        flex-wrap: wrap;
        margin-right: -0.75rem;
        margin-left: -0.75rem;
    }

    .search-form-col {
        position: relative;
        width: 100%;
        padding-right: 0.75rem;
        padding-left: 0.75rem;
        margin-bottom: 1rem;
    }

    @media (min-width: 768px) {
        .search-form-col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }

    @media (min-width: 992px) {
        .search-form-col-lg-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }

        .search-form-col-lg-3 {
            flex: 0 0 25%;
            max-width: 25%;
        }

        .search-form-col-lg-2 {
            flex: 0 0 16.666667%;
            max-width: 16.666667%;
        }
    }

    .form-label {
        font-size: 0.75rem;
        color: #6c757d;
        margin-bottom: 0.5rem;
        display: block;
    }

    .search-input-wrapper {
        display: flex;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .search-input-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 1rem;
        background-color: white;
        border: none;
    }

    .search-input-icon i {
        color: #4a6cf7;
        margin-right: 0;
    }

    .search-input {
        flex-grow: 1;
        padding: 0.75rem 1rem;
        border: none;
        outline: none;
    }

    .search-input-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #4a6cf7;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        cursor: pointer;
    }

    .search-input-btn i {
        margin-right: 0;
    }

    .search-select {
        width: 100%;
        padding: 0.75rem;
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        background-color: white;
        appearance: none;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="%236c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>');
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 1rem;
    }

    .gender-filter {
        display: flex;
        height: 44px;
    }

    .gender-btn-group {
        display: flex;
        width: 100%;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .gender-btn {
        flex: 1;
        border: none;
        padding: 0.75rem;
        background-color: white;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .gender-btn.all {
        border-radius: 8px 0 0 8px;
        border-right: 1px solid #e9ecef;
    }

    .gender-btn.male {
        border-right: 1px solid #e9ecef;
    }

    .gender-btn.female {
        border-radius: 0 8px 8px 0;
    }

    .gender-btn i {
        margin-right: 0.35rem;
    }

    .gender-btn.all.active {
        background-color: #e9ecef;
        color: #6c757d;
        font-weight: 500;
    }

    .gender-btn.male.active {
        background-color: rgba(74, 108, 247, 0.1);
        color: #4a6cf7;
        font-weight: 500;
    }

    .gender-btn.female.active {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        font-weight: 500;
    }

    .search-actions {
        display: flex;
        align-items: flex-end;
        height: 100%;
    }

    .search-actions-wrapper {
        display: flex;
        width: 100%;
        gap: 0.5rem;
    }

    .search-btn {
        flex-grow: 1;
        height: 44px;
        background-color: #4a6cf7;
        color: white;
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .search-btn:hover {
        background-color: #3256e7;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .search-btn i {
        margin-right: 0.5rem;
    }

    .reset-btn {
        width: 44px;
        height: 44px;
        background-color: #f8f9fa;
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .reset-btn:hover {
        background-color: #e9ecef;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .reset-btn i {
        color: #6c757d;
        margin-right: 0;
    }

    .active-filters {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        margin-top: 1rem;
    }

    .active-filters-label {
        font-size: 0.75rem;
        color: #6c757d;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .filter-badge {
        display: flex;
        align-items: center;
        background-color: #f8f9fa;
        border-radius: 50px;
        padding: 0.35rem 0.75rem;
        font-size: 0.75rem;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .filter-badge-clear {
        margin-left: 0.5rem;
        color: #6c757d;
        display: flex;
        align-items: center;
        text-decoration: none;
        font-size: 0.7rem;
    }

    .filter-badge-clear:hover {
        color: #343a40;
    }

    .clear-all-badge {
        display: flex;
        align-items: center;
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        border-radius: 50px;
        padding: 0.35rem 0.75rem;
        font-size: 0.75rem;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .clear-all-badge:hover {
        background-color: rgba(220, 53, 69, 0.15);
        color: #dc3545;
    }

    .clear-all-badge i {
        margin-right: 0.35rem;
    }

    /* Estilos de tabla */
    .products-card {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
        overflow: hidden;
        transition: box-shadow 0.2s ease;
    }

    .products-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .products-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }

    @media (max-width: 768px) {
        .products-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .products-header-title {
            margin-bottom: 1rem;
        }
    }

    .products-header-title {
        font-weight: 600;
        margin-bottom: 0;
        display: flex;
        align-items: center;
    }

    .products-header-title i {
        color: #4a6cf7;
        margin-right: 0.5rem;
    }

    .products-header-title .badge {
        margin-left: 0.5rem;
        background-color: #4a6cf7;
        color: white;
        border-radius: 50px;
        padding: 0.35rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .products-header-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .products-dropdown {
        position: relative;
        margin-right: 0.5rem;
    }

    .products-dropdown-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.375rem 0.75rem;
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        font-size: 0.85rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        color: #6c757d;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .products-dropdown-btn:hover {
        background-color: #e9ecef;
    }

    .products-dropdown-btn i {
        margin-right: 0.35rem;
    }

    .products-buttons {
        display: flex;
        gap: 0.25rem;
    }

    .products-button {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: white;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        color: #6c757d;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .products-button:hover {
        background-color: #e9ecef;
        color: #343a40;
    }

    .products-table-wrapper {
        overflow-x: auto;
    }

    .products-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 0;
    }

    .products-table th {
        padding: 1rem;
        font-weight: 600;
        color: #6c757d;
        background-color: rgba(0, 0, 0, 0.01);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .products-table td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .products-table tr:last-child td {
        border-bottom: none;
    }

    .products-table tr {
        transition: all 0.2s ease;
    }

    .products-table tbody tr:hover {
        background-color: rgba(74, 108, 247, 0.03);
    }

    .product-id {
        font-weight: 600;
        color: #4a6cf7;
    }

    .product-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: all 0.2s ease;
    }

    .product-image:hover {
        transform: scale(1.05);
    }

    .product-image-placeholder {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f8f9fa;
        border-radius: 8px;
        color: #6c757d;
    }

    .product-name {
        font-weight: 500;
    }

    .product-tag {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 50px;
        font-size: 0.7rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .product-tag-info {
        background-color: rgba(23, 162, 184, 0.15);
        color: #17a2b8;
    }

    .product-tag-primary {
        background-color: rgba(74, 108, 247, 0.15);
        color: #4a6cf7;
    }

    .product-tag-danger {
        background-color: rgba(220, 53, 69, 0.15);
        color: #dc3545;
    }

    .product-color {
        display: flex;
        align-items: center;
    }

    .product-color-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 0.5rem;
        border: 1px solid rgba(0, 0, 0, 0.1);
    }

    .product-price {
        font-weight: 600;
        display: flex;
        align-items: center;
    }

    .product-price-icon {
        width: 24px;
        height: 24px;
        background-color: rgba(255, 193, 7, 0.15);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 0.5rem;
    }

    .product-price-icon i {
        color: #ffc107;
        font-size: 0.7rem;
        margin-right: 0;
    }

    .product-date {
        display: flex;
        align-items: center;
    }

    .product-date i {
        color: #17a2b8;
        margin-right: 0.5rem;
    }

    .product-actions {
        display: flex;
        justify-content: center;
        gap: 0.35rem;
    }

    .product-action-btn {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background-color: white;
        border: 1px solid #e9ecef;
        color: #6c757d;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .product-action-btn i {
        margin-right: 0;
        font-size: 0.85rem;
    }

    .product-action-btn-primary {
        color: #4a6cf7;
        border-color: rgba(74, 108, 247, 0.2);
    }

    .product-action-btn-primary:hover {
        background-color: rgba(74, 108, 247, 0.1);
        transform: translateY(-2px);
    }

    .product-action-btn-info {
        color: #17a2b8;
        border-color: rgba(23, 162, 184, 0.2);
    }

    .product-action-btn-info:hover {
        background-color: rgba(23, 162, 184, 0.1);
        transform: translateY(-2px);
    }

    .product-action-btn-danger {
        color: #dc3545;
        border-color: rgba(220, 53, 69, 0.2);
    }

    .product-action-btn-danger:hover {
        background-color: rgba(220, 53, 69, 0.1);
        transform: translateY(-2px);
    }

    .products-empty {
        padding: 4rem 2rem;
        text-align: center;
    }

    .products-empty-icon {
        width: 80px;
        height: 80px;
        background-color: #f8f9fa;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
    }

    .products-empty-icon i {
        color: #6c757d;
        font-size: 2.5rem;
        margin-right: 0;
    }

    .products-empty-title {
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 0.75rem;
    }

    .products-empty-message {
        color: #6c757d;
        margin-bottom: 1.5rem;
    }

    .products-empty-actions {
        display: flex;
        justify-content: center;
        gap: 0.75rem;
    }

    .products-footer {
        padding: 1rem 1.5rem;
        background-color: #f8f9fa;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }

    @media (max-width: 768px) {
        .products-footer {
            flex-direction: column;
            align-items: flex-start;
        }

        .products-pagination {
            margin-top: 1rem;
        }
    }

    .products-count {
        display: flex;
        align-items: center;
        color: #6c757d;
        font-size: 0.85rem;
    }

    .products-count-icon {
        width: 32px;
        height: 32px;
        background-color: rgba(74, 108, 247, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 0.75rem;
    }

    .products-count-icon i {
        color: #4a6cf7;
        margin-right: 0;
    }

    .products-count-number {
        color: #4a6cf7;
        font-weight: 600;
    }

    .products-page-size {
        display: flex;
        align-items: center;
    }

    .products-page-size-select {
        padding: 0.35rem 0.75rem;
        font-size: 0.85rem;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        background-color: white;
        appearance: none;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="%236c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>');
        background-repeat: no-repeat;
        background-position: right 0.5rem center;
        background-size: 0.85rem;
        padding-right: 1.75rem;
    }

    .products-pagination {
        display: flex;
        align-items: center;
    }

    .products-pagination-list {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .products-pagination-item {
        margin: 0 0.15rem;
    }

    .products-pagination-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        padding: 0 0.5rem;
        background-color: white;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        color: #6c757d;
        font-size: 0.85rem;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .products-pagination-link:hover {
        background-color: #e9ecef;
        color: #343a40;
    }

    .products-pagination-link.active {
        background-color: #4a6cf7;
        color: white;
        border-color: #4a6cf7;
    }

    .products-pagination-link.disabled {
        opacity: 0.5;
        pointer-events: none;
    }

    .products-pagination-link i {
        margin-right: 0;
        font-size: 0.7rem;
    }

    /* Estilos para el selector de ordenación */
    .products-sort {
        position: relative;
        margin-right: 0.75rem;
    }

    .products-sort-select {
        padding: 0.5rem 2rem 0.5rem 0.75rem;
        font-size: 0.85rem;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        background-color: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        appearance: none;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="%236c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>');
        background-repeat: no-repeat;
        background-position: right 0.5rem center;
        background-size: 0.85rem;
        cursor: pointer;
        min-width: 160px;
        transition: all 0.2s ease;
        color: #495057;
    }

    .products-sort-select:hover {
        border-color: #cbd3da;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .products-sort-select:focus {
        outline: none;
        border-color: rgba(74, 108, 247, 0.5);
        box-shadow: 0 0 0 0.2rem rgba(74, 108, 247, 0.25);
    }

    .products-sort-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #4a6cf7;
        font-size: 0.85rem;
        pointer-events: none;
    }

    .products-sort-select-wrapper {
        position: relative;
    }

    /* Actualización de los estilos header actions */
    .products-header-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
</style>

<div class="admin-container">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4 animate__animated animate__fadeIn">
        <ol class="custom-breadcrumb">
            <li class="custom-breadcrumb-item">
                <a href="/views/admin/dashboard.php">
                    <i class="fas fa-home text-primary"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="custom-breadcrumb-item active">Productos</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Productos</h1>
            <p class="page-description">Gestiona el catálogo de productos de la tienda</p>
        </div>
        <div>
            <a href="/views/admin/product_form.php" class="btn-new">
                <i class="fas fa-plus"></i> Nuevo Producto
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="custom-alert custom-alert-success animate-fade-up" style="animation-delay: 0.1s;">
            <div class="alert-icon alert-icon-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="alert-content">
                <h5 class="alert-title">¡Operación Exitosa!</h5>
                <p class="alert-message">El producto ha sido procesado correctamente.</p>
            </div>
            <button type="button" class="alert-close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
        <div class="custom-alert custom-alert-danger animate-fade-up" style="animation-delay: 0.1s;">
            <div class="alert-icon alert-icon-danger">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="alert-content">
                <h5 class="alert-title">¡Error!</h5>
                <p class="alert-message">Ha ocurrido un problema al procesar el producto.</p>
            </div>
            <button type="button" class="alert-close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Stats Cards Row -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="stats-card animate-fade-up" style="animation-delay: 0.1s;">
                <div class="stats-wrapper">
                    <div class="stats-icon stats-icon-primary">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-label">Total Productos</div>
                        <div class="stats-value">
                            25
                            <div class="badge badge-success">
                                <i class="fas fa-arrow-up"></i> 12%
                            </div>
                        </div>
                        <div class="stats-meta">
                            <i class="fas fa-info-circle"></i> Gatos registrados en la tienda
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stats-card animate-fade-up" style="animation-delay: 0.2s;">
                <div class="stats-wrapper">
                    <div class="stats-icon stats-icon-success">
                        <i class="fas fa-euro-sign"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-label">Precio Promedio</div>
                        <div class="stats-value">
                            685 €
                            <div class="badge badge-danger">
                                <i class="fas fa-arrow-down"></i> 3%
                            </div>
                        </div>
                        <div class="stats-meta">
                            <i class="fas fa-chart-line"></i> Basado en todos los productos
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stats-card animate-fade-up" style="animation-delay: 0.3s;">
                <div class="stats-wrapper">
                    <div class="stats-icon stats-icon-info">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-label">Último Añadido</div>
                        <div class="stats-value">
                            07/05
                        </div>
                        <div class="stats-meta">
                            <i class="fas fa-clock"></i> Hace 0 días
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="search-card animate-fade-up" style="animation-delay: 0.4s;">
        <div class="search-header">
            <h5 class="search-title">
                <i class="fas fa-search"></i> Buscar y Filtrar
            </h5>
            <button type="button" class="search-options-btn" data-toggle="collapse" data-target="#filterOptions" aria-expanded="true" aria-controls="filterOptions">
                <i class="fas fa-sliders-h"></i> Opciones
            </button>
        </div>

        <div class="collapse show" id="filterOptions">
            <form action="" method="GET" class="search-form">
                <div class="search-form-row">
                    <div class="search-form-col search-form-col-md-6 search-form-col-lg-4">
                        <label for="searchInput" class="form-label">Buscar por nombre:</label>
                        <div class="search-input-wrapper">
                            <div class="search-input-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <input type="text" id="searchInput" name="search" class="search-input" placeholder="Buscar productos..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button class="search-input-btn" type="submit">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <div class="search-form-col search-form-col-md-6 search-form-col-lg-3">
                        <label for="typeFilter" class="form-label">Tipo de gato:</label>
                        <select id="typeFilter" name="filter_type" class="search-select">
                            <option value="">Todos los tipos</option>
                            <option value="siamés" <?php echo (isset($_GET['filter_type']) && $_GET['filter_type'] == 'siamés') ? 'selected' : ''; ?>>Siamés</option>
                            <option value="persa" <?php echo (isset($_GET['filter_type']) && $_GET['filter_type'] == 'persa') ? 'selected' : ''; ?>>Persa</option>
                            <option value="bengalí" <?php echo (isset($_GET['filter_type']) && $_GET['filter_type'] == 'bengalí') ? 'selected' : ''; ?>>Bengalí</option>
                            <option value="ragdoll" <?php echo (isset($_GET['filter_type']) && $_GET['filter_type'] == 'ragdoll') ? 'selected' : ''; ?>>Ragdoll</option>
                        </select>
                    </div>

                    <div class="search-form-col search-form-col-md-6 search-form-col-lg-3">
                        <label class="form-label">Sexo:</label>
                        <div class="gender-filter">
                            <div class="gender-btn-group">
                                <button type="button" class="gender-btn all <?php echo !isset($_GET['filter_sex']) || $_GET['filter_sex'] === '' ? 'active' : ''; ?>" onclick="document.getElementById('allSex').checked = true;">
                                    <i class="fas fa-venus-mars"></i> Todos
                                </button>

                                <button type="button" class="gender-btn male <?php echo isset($_GET['filter_sex']) && $_GET['filter_sex'] === '1' ? 'active' : ''; ?>" onclick="document.getElementById('male').checked = true;">
                                    <i class="fas fa-mars"></i> Macho
                                </button>

                                <button type="button" class="gender-btn female <?php echo isset($_GET['filter_sex']) && $_GET['filter_sex'] === '0' ? 'active' : ''; ?>" onclick="document.getElementById('female').checked = true;">
                                    <i class="fas fa-venus"></i> Hembra
                                </button>
                            </div>

                            <input type="radio" name="filter_sex" id="allSex" value="" <?php echo !isset($_GET['filter_sex']) || $_GET['filter_sex'] === '' ? 'checked' : ''; ?> hidden>
                            <input type="radio" name="filter_sex" id="male" value="1" <?php echo isset($_GET['filter_sex']) && $_GET['filter_sex'] === '1' ? 'checked' : ''; ?> hidden>
                            <input type="radio" name="filter_sex" id="female" value="0" <?php echo isset($_GET['filter_sex']) && $_GET['filter_sex'] === '0' ? 'checked' : ''; ?> hidden>
                        </div>
                    </div>

                    <div class="search-form-col search-form-col-md-6 search-form-col-lg-2">
                        <div class="search-actions">
                            <div class="search-actions-wrapper">
                                <button type="submit" class="search-btn">
                                    <i class="fas fa-filter"></i> Aplicar
                                </button>
                                <a href="/views/admin/products.php" class="reset-btn">
                                    <i class="fas fa-redo-alt"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                // Mostrar etiquetas de filtros activos
                $activeFilters = [];
                if (isset($_GET['search']) && !empty($_GET['search'])) {
                    $activeFilters[] = ['name' => 'Búsqueda: ' . htmlspecialchars($_GET['search']), 'param' => 'search'];
                }
                if (isset($_GET['filter_type']) && !empty($_GET['filter_type'])) {
                    $activeFilters[] = ['name' => 'Tipo: ' . htmlspecialchars($_GET['filter_type']), 'param' => 'filter_type'];
                }
                if (isset($_GET['filter_sex']) && $_GET['filter_sex'] !== '') {
                    $sex = $_GET['filter_sex'] == '1' ? 'Macho' : 'Hembra';
                    $activeFilters[] = ['name' => 'Sexo: ' . $sex, 'param' => 'filter_sex'];
                }

                if (!empty($activeFilters)):
                ?>
                    <div class="active-filters">
                        <span class="active-filters-label">Filtros activos:</span>
                        <?php foreach ($activeFilters as $filter):
                            // Crear URL para remover este filtro
                            $removeUrl = '?';
                            foreach ($_GET as $key => $value) {
                                if ($key != $filter['param']) {
                                    $removeUrl .= urlencode($key) . '=' . urlencode($value) . '&';
                                }
                            }
                            $removeUrl = rtrim($removeUrl, '&');
                        ?>
                            <div class="filter-badge">
                                <span><?php echo $filter['name']; ?></span>
                                <a href="<?php echo $removeUrl; ?>" class="filter-badge-clear">
                                    <i class="fas fa-times-circle"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>

                        <a href="/views/admin/products.php" class="clear-all-badge">
                            <i class="fas fa-trash-alt"></i> Limpiar todos
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Main Products Card -->
    <div class="products-card animate-fade-up" style="animation-delay: 0.5s;">
        <div class="products-header">
            <h5 class="products-header-title">
                <i class="fas fa-list"></i> Listado de Productos
                <span class="badge"><?php echo $result['count']; ?></span>
            </h5>
            <div class="products-header-actions">
                <div class="products-sort">
                    <div class="products-sort-select-wrapper">
                        <select class="products-sort-select" id="orderBySelect">
                            <option value="id-ASC" <?php echo $orderBy === 'id' && $orderDir === 'ASC' ? 'selected' : ''; ?>>ID (ascendente)</option>
                            <option value="id-DESC" <?php echo $orderBy === 'id' && $orderDir === 'DESC' ? 'selected' : ''; ?>>ID (descendente)</option>
                            <option value="nombre-ASC" <?php echo $orderBy === 'nombre' && $orderDir === 'ASC' ? 'selected' : ''; ?>>Nombre (A-Z)</option>
                            <option value="nombre-DESC" <?php echo $orderBy === 'nombre' && $orderDir === 'DESC' ? 'selected' : ''; ?>>Nombre (Z-A)</option>
                            <option value="precio-ASC" <?php echo $orderBy === 'precio' && $orderDir === 'ASC' ? 'selected' : ''; ?>>Precio (menor a mayor)</option>
                            <option value="precio-DESC" <?php echo $orderBy === 'precio' && $orderDir === 'DESC' ? 'selected' : ''; ?>>Precio (mayor a menor)</option>
                            <option value="fecha_anyadido-DESC" <?php echo $orderBy === 'fecha_anyadido' && $orderDir === 'DESC' ? 'selected' : ''; ?>>Fecha (más recientes)</option>
                            <option value="fecha_anyadido-ASC" <?php echo $orderBy === 'fecha_anyadido' && $orderDir === 'ASC' ? 'selected' : ''; ?>>Fecha (más antiguos)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="products-table-wrapper">
            <table class="products-table">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th width="80">Imagen</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Color</th>
                        <th>Sexo</th>
                        <th>Precio</th>
                        <th>Fecha</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result['success'] && count($result['data']) > 0): ?>
                        <?php foreach ($result['data'] as $index => $product): ?>
                            <tr class="animate-fade-up" style="animation-delay: <?php echo 0.1 + ($index * 0.05); ?>s;">
                                <td><span class="product-id">#<?php echo $product['id']; ?></span></td>
                                <td>
                                    <?php if (!empty($product['foto'])): ?>
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($product['foto']); ?>"
                                            alt="<?php echo htmlspecialchars($product['nombre']); ?>"
                                            class="product-image">
                                    <?php else: ?>
                                        <div class="product-image-placeholder">
                                            <i class="fas fa-cat"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="product-name"><?php echo htmlspecialchars($product['nombre']); ?></span></td>
                                <td>
                                    <span class="product-tag product-tag-info">
                                        <?php echo htmlspecialchars($product['tipo']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="product-color">
                                        <span class="product-color-dot" style="background-color: <?php echo strtolower($product['color']) == 'blanco' ? '#f8f9fa' : (strtolower($product['color']) == 'negro' ? '#212529' : (strtolower($product['color']) == 'gris' ? '#adb5bd' : (strtolower($product['color']) == 'naranja' ? '#fd7e14' : (strtolower($product['color']) == 'marrón' ? '#6f4e37' : '#6c757d')))); ?>;"></span>
                                        <span><?php echo htmlspecialchars($product['color']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="product-tag <?php echo $product['sexo'] ? 'product-tag-primary' : 'product-tag-danger'; ?>">
                                        <i class="fas fa-<?php echo $product['sexo'] ? 'mars' : 'venus'; ?>"></i>
                                        <?php echo $product['sexo'] ? 'Macho' : 'Hembra'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="product-price">
                                        <div class="product-price-icon">
                                            <i class="fas fa-euro-sign"></i>
                                        </div>
                                        <?php echo number_format($product['precio'], 2, ',', '.'); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="product-date">
                                        <i class="far fa-calendar-alt"></i>
                                        <span data-toggle="tooltip" title="<?php echo date('d/m/Y H:i', strtotime($product['fecha_anyadido'])); ?>">
                                            <?php
                                            $date = new DateTime($product['fecha_anyadido']);
                                            $now = new DateTime();
                                            $interval = $date->diff($now);

                                            if ($interval->days == 0) {
                                                echo 'Hoy';
                                            } elseif ($interval->days == 1) {
                                                echo 'Ayer';
                                            } elseif ($interval->days < 7) {
                                                echo 'Hace ' . $interval->days . ' días';
                                            } else {
                                                echo date('d/m/Y', strtotime($product['fecha_anyadido']));
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="product-actions">
                                        <a href="/views/admin/product_form.php?id=<?php echo $product['id']; ?>" class="product-action-btn product-action-btn-primary" data-toggle="tooltip" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="/views/admin/product_view.php?id=<?php echo $product['id']; ?>" class="product-action-btn product-action-btn-info" data-toggle="tooltip" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="product-action-btn product-action-btn-danger delete-product"
                                            data-id="<?php echo $product['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($product['nombre']); ?>"
                                            data-toggle="tooltip" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9">
                                <div class="products-empty">
                                    <div class="products-empty-icon">
                                        <i class="fas fa-box-open"></i>
                                    </div>
                                    <h4 class="products-empty-title">No hay productos disponibles</h4>
                                    <p class="products-empty-message">No se encontraron productos que coincidan con los criterios de búsqueda</p>
                                    <div class="products-empty-actions">
                                        <a href="/views/admin/products.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-sync"></i> Reiniciar filtros
                                        </a>
                                        <a href="/views/admin/product_form.php" class="btn-new">
                                            <i class="fas fa-plus"></i> Añadir Producto
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="products-footer">
            <div class="products-count">
                <div class="products-count-icon">
                    <i class="fas fa-list"></i>
                </div>
                <div>
                    Mostrando <span class="products-count-number"><?php echo count($result['data']); ?></span> de <span class="products-count-number"><?php echo $result['count']; ?></span> productos
                    <?php if (isset($_GET['search']) || isset($_GET['filter_type']) || isset($_GET['filter_sex'])): ?>
                        <span class="filter-badge" style="margin-left: 0.5rem;">Filtrados</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="products-pagination">
                <div class="products-page-size">
                    <select class="products-page-size-select">
                        <option value="10">10 por página</option>
                        <option value="25" selected>25 por página</option>
                        <option value="50">50 por página</option>
                        <option value="100">100 por página</option>
                    </select>
                </div>

                <ul class="products-pagination-list">
                    <li class="products-pagination-item">
                        <a class="products-pagination-link disabled" href="#" tabindex="-1" aria-disabled="true">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <li class="products-pagination-item">
                        <a class="products-pagination-link active" href="#">1</a>
                    </li>
                    <li class="products-pagination-item">
                        <a class="products-pagination-link" href="#">2</a>
                    </li>
                    <li class="products-pagination-item">
                        <a class="products-pagination-link" href="#">3</a>
                    </li>
                    <li class="products-pagination-item">
                        <a class="products-pagination-link" href="#">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--border-radius-lg);">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title font-weight-bold" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Confirmar Eliminación
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="mx-auto mb-4 animate-fade-up" style="animation-delay: 0.1s;">
                        <div class="admin-icon-rounded admin-icon-bg-danger" style="width: 90px; height: 90px; margin: 0 auto;">
                            <i class="fas fa-trash-alt text-danger" style="font-size: var(--font-size-xl);"></i>
                        </div>
                    </div>
                    <h4 class="font-weight-bold mb-3 animate-fade-up" style="animation-delay: 0.2s;">¿Eliminar este producto?</h4>
                    <p class="text-muted mb-1 animate-fade-up" style="animation-delay: 0.3s;">¿Estás seguro de que deseas eliminar el producto:</p>
                    <p class="font-weight-bold text-dark mb-4 animate-fade-up" style="animation-delay: 0.4s; font-size: var(--font-size-lg);">
                        <span id="product-name" class="text-primary"></span>
                    </p>
                    <div class="alert alert-warning d-flex align-items-center animate-fade-up" style="animation-delay: 0.5s; border-radius: var(--border-radius-md);">
                        <i class="fas fa-info-circle text-warning mr-3" style="font-size: var(--font-size-lg);"></i>
                        <p class="mb-0 text-left">Esta acción no se puede deshacer. El producto será eliminado permanentemente de la base de datos.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0" style="border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg);">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger admin-btn-circle shadow-sm" id="confirm-delete">
                    <i class="fas fa-trash-alt mr-2"></i> Eliminar Producto
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tooltips
        if (typeof $ !== 'undefined') {
            $('[data-toggle="tooltip"]').tooltip();
        }

        // Delete product functionality
        const deleteButtons = document.querySelectorAll('.delete-product');
        const productNameSpan = document.getElementById('product-name');
        const confirmDeleteBtn = document.getElementById('confirm-delete');
        let productId = null;

        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                productId = this.getAttribute('data-id');
                const productName = this.getAttribute('data-name');
                productNameSpan.textContent = productName;

                // Show modal using jQuery
                $('#deleteModal').modal('show');
            });
        });

        confirmDeleteBtn.addEventListener('click', function() {
            if (productId) {
                // Mostrar indicador de carga en el botón
                this.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Eliminando...';
                this.disabled = true;

                // Redirigir después de un breve retraso para mostrar el efecto
                setTimeout(() => {
                    window.location.href = `/controllers/product_delete.php?id=${productId}`;
                }, 800);
            }
        });

        // Cambiar estilo de columnas ordenadas
        const currentOrderColumn = '<?php echo $orderBy; ?>';
        const currentOrderDir = '<?php echo $orderDir; ?>';

        if (currentOrderColumn) {
            const headerCells = document.querySelectorAll('th');
            headerCells.forEach(cell => {
                const link = cell.querySelector(`a[href*="orderBy=${currentOrderColumn}"]`);
                if (link) {
                    cell.classList.add('bg-light');
                }
            });
        }

        // Efecto hover para las filas
        const tableRows = document.querySelectorAll('.products-table tbody tr');
        tableRows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.boxShadow = 'var(--box-shadow-sm)';
                this.style.transform = 'translateY(-2px)';
                this.style.transition = 'all var(--transition-fast)';
                this.style.zIndex = '1';
            });

            row.addEventListener('mouseleave', function() {
                this.style.boxShadow = 'none';
                this.style.transform = 'translateY(0)';
                this.style.zIndex = 'auto';
            });
        });

        // Simular cambio de página con el selector de número de resultados
        const perPageSelect = document.querySelector('.products-page-size-select');
        if (perPageSelect) {
            perPageSelect.addEventListener('change', function() {
                console.log(`Cambiar a ${this.value} resultados por página`);
                // En una implementación real, aquí redirigiríamos o haríamos una petición AJAX
            });
        }

        // Manejo del selector de ordenación
        const orderBySelect = document.getElementById('orderBySelect');
        if (orderBySelect) {
            orderBySelect.addEventListener('change', function() {
                const [orderBy, orderDir] = this.value.split('-');

                // Construir la URL con parámetros existentes y nuevos valores de ordenación
                let url = new URL(window.location.href);
                let params = new URLSearchParams(url.search);

                // Actualizar parámetros de ordenación
                params.set('orderBy', orderBy);
                params.set('orderDir', orderDir);

                // Redirigir a la nueva URL
                window.location.href = `${url.pathname}?${params.toString()}`;
            });
        }
    });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>