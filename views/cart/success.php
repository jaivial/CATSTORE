<?php

/**
 * Vista de confirmación de compra exitosa
 * Cat Store - Tienda de Gatos
 */

require_once __DIR__ . '/../../includes/auth_middleware.php';

// Verificar autenticación
requireAuth();

// Título de la página
$pageTitle = "¡Compra Exitosa! - Cat Store";

// Incluir navbar
$includeNavbar = true;

// Incluir cabecera
include_once __DIR__ . '/../../includes/header.php';
?>

<style>
    .success-section {
        padding: 3rem 0;
        text-align: center;
    }

    .success-container {
        max-width: 800px;
        margin: 0 auto;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 2rem;
    }

    .success-icon {
        font-size: 5rem;
        color: #4CAF50;
        margin-bottom: 1.5rem;
        animation: bounce 1s ease-in-out;
    }

    .success-title {
        font-size: 2rem;
        color: #333;
        margin-bottom: 1rem;
    }

    .success-message {
        font-size: 1.1rem;
        color: #666;
        margin-bottom: 2rem;
        line-height: 1.6;
    }

    .order-details {
        background-color: #f9f9f9;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        text-align: left;
    }

    .order-details h3 {
        margin-top: 0;
        color: #333;
        margin-bottom: 1rem;
        border-bottom: 1px solid #ddd;
        padding-bottom: 0.5rem;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }

    .detail-label {
        font-weight: 600;
        color: #555;
    }

    .actions {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        max-width: 300px;
        margin: 0 auto;
    }

    .btn {
        display: inline-block;
        padding: 0.8rem 1.5rem;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background-color: var(--primary-color);
        color: white;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        transform: translateY(-2px);
    }

    .btn-outline {
        border: 2px solid var(--primary-color);
        color: var(--primary-color);
        background-color: transparent;
    }

    .btn-outline:hover {
        background-color: #f0f7ff;
        transform: translateY(-2px);
    }

    @keyframes bounce {

        0%,
        20%,
        50%,
        80%,
        100% {
            transform: translateY(0);
        }

        40% {
            transform: translateY(-20px);
        }

        60% {
            transform: translateY(-10px);
        }
    }

    @media (max-width: 768px) {
        .success-container {
            padding: 1.5rem;
            margin: 0 1rem;
        }

        .success-icon {
            font-size: 4rem;
        }

        .success-title {
            font-size: 1.8rem;
        }
    }
</style>

<main class="main-content">
    <section class="success-section">
        <div class="container success-container">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>

            <h1 class="success-title">¡Compra Realizada con Éxito!</h1>

            <p class="success-message">
                Gracias por tu compra. Hemos procesado tu pedido correctamente y pronto recibirás un email con los detalles de tu compra.
                Tu nuevo amigo felino estará contigo muy pronto.
            </p>

            <div class="order-details">
                <h3>Detalles del Pedido</h3>

                <div class="detail-row">
                    <span class="detail-label">Número de Pedido:</span>
                    <span><?php echo '#' . rand(10000, 99999); ?></span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Fecha:</span>
                    <span><?php echo date('d/m/Y H:i'); ?></span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Estado:</span>
                    <span>Procesando</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Método de Pago:</span>
                    <span>Tarjeta de Crédito</span>
                </div>
            </div>

            <div class="actions">
                <a href="/views/store/index.php" class="btn btn-primary">Seguir Comprando</a>
                <a href="/views/profile/user_info.php" class="btn btn-outline">Ver Mi Perfil</a>
            </div>
        </div>
    </section>
</main>

<?php
// Incluir pie de página
include_once __DIR__ . '/../../includes/footer.php';
?>