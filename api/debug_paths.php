<?php

/**
 * Archivo de diagnóstico de rutas y acceso
 * Cat Store - Tienda de Gatos
 */

// Configurar cabeceras para JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Recopilar información de rutas y entorno
$info = [
    'time' => date('Y-m-d H:i:s'),
    'session' => [
        'id' => session_id(),
        'status' => session_status(),
        'user' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null
    ],
    'server' => [
        'server_name' => $_SERVER['SERVER_NAME'],
        'server_port' => $_SERVER['SERVER_PORT'],
        'document_root' => $_SERVER['DOCUMENT_ROOT'],
        'request_uri' => $_SERVER['REQUEST_URI'],
        'script_filename' => $_SERVER['SCRIPT_FILENAME'],
        'php_self' => $_SERVER['PHP_SELF'],
        'http_host' => $_SERVER['HTTP_HOST'],
        'protocol' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http'
    ],
    'includes' => [
        'exists_cart_api' => file_exists(__DIR__ . '/cart_api.php'),
        'exists_session_debug' => file_exists(__DIR__ . '/session_debug.php'),
        'api_dir_contents' => scandir(__DIR__)
    ],
    'test_urls' => [
        'cart_api_relative' => './cart_api.php?action=get',
        'cart_api_absolute' => '/api/cart_api.php?action=get',
        'cart_api_dynamic' => rtrim(dirname($_SERVER['PHP_SELF']), '/api') . '/api/cart_api.php?action=get',
        'cart_api_full' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') .
            '://' . $_SERVER['HTTP_HOST'] .
            rtrim(dirname($_SERVER['PHP_SELF']), '/api') . '/api/cart_api.php?action=get'
    ],
    'php_info' => [
        'version' => PHP_VERSION,
        'os' => PHP_OS,
        'interface' => PHP_SAPI
    ]
];

// Devolver la información como JSON
echo json_encode($info, JSON_PRETTY_PRINT);
