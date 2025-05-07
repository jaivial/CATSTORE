<?php

/**
 * API de depuración de sesión
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

// Registrar la solicitud
file_put_contents(
    __DIR__ . '/../debug_session.log',
    date('Y-m-d H:i:s') . " - Solicitud de depuración de sesión" .
        " - Remote IP: " . $_SERVER['REMOTE_ADDR'] .
        " - User Agent: " . $_SERVER['HTTP_USER_AGENT'] .
        "\n",
    FILE_APPEND
);

// Recopilar información
$sessionInfo = [
    'session_id' => session_id(),
    'session_status' => session_status(),
    'session_data' => $_SESSION,
    'cookies' => $_COOKIE,
    'server' => [
        'remote_addr' => $_SERVER['REMOTE_ADDR'],
        'http_host' => $_SERVER['HTTP_HOST'],
        'request_uri' => $_SERVER['REQUEST_URI'],
        'script_name' => $_SERVER['SCRIPT_NAME'],
        'http_referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'request_time' => $_SERVER['REQUEST_TIME'],
        'https' => isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'off',
        'server_port' => $_SERVER['SERVER_PORT']
    ],
    'authentication' => [
        'session_user' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
        'cookie_user' => isset($_COOKIE['user_id']) ? $_COOKIE['user_id'] : null,
        'is_authenticated' => isset($_SESSION['user_id']) || isset($_COOKIE['user_id']),
    ],
    'php_info' => [
        'version' => PHP_VERSION,
        'os' => PHP_OS,
        'sapi' => PHP_SAPI,
        'session_name' => session_name(),
        'session_save_path' => session_save_path(),
        'session_cookie_params' => session_get_cookie_params()
    ]
];

// Registrar la información recopilada
file_put_contents(
    __DIR__ . '/../debug_session.log',
    date('Y-m-d H:i:s') . " - Información de sesión: " . json_encode($sessionInfo, JSON_PRETTY_PRINT) . "\n",
    FILE_APPEND
);

// Devolver la información como JSON
echo json_encode($sessionInfo);
exit;
