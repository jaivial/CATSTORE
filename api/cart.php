<?php

/**
 * API del carrito (Proxy hacia cart_api.php)
 * Cat Store - Tienda de Gatos
 */

// Registrar la solicitud de proxy
file_put_contents(
    __DIR__ . '/../debug_cart_proxy.log',
    date('Y-m-d H:i:s') . " - Proxy cart.php - " .
        "Método: " . $_SERVER['REQUEST_METHOD'] . " - " .
        "Acción: " . (isset($_GET['action']) ? $_GET['action'] : 'none') . " - " .
        "URL: " . $_SERVER['REQUEST_URI'] . "\n",
    FILE_APPEND
);

// Configurar cabeceras para JSON y CORS
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Incluir el archivo cart_api.php para reutilizar sus funciones
require_once __DIR__ . '/cart_api.php';

// La lógica principal ya está implementada en cart_api.php y se ejecutará automáticamente
// No es necesario hacer nada más, ya que la respuesta se enviará desde cart_api.php
