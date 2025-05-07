<?php

/**
 * Depuración detallada de la API del carrito
 * Cat Store - Tienda de Gatos
 */

// Configuración de encabezados
header('Content-Type: text/html; charset=UTF-8');

// Crear y abrir archivo de log
$logFile = __DIR__ . '/../debug_api_output.log';
$fh = fopen($logFile, 'a');
fprintf($fh, "\n\n========== NUEVA SOLICITUD DE DEPURACIÓN ==========\n");
fprintf($fh, "Fecha y hora: %s\n", date('Y-m-d H:i:s'));
fprintf($fh, "IP Solicitante: %s\n", $_SERVER['REMOTE_ADDR']);
fprintf($fh, "User Agent: %s\n\n", $_SERVER['HTTP_USER_AGENT']);

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Registrar información de sesión
fprintf($fh, "ID de sesión: %s\n", session_id());
fprintf($fh, "Usuario en sesión: %s\n\n", isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'No autenticado');

// Función para realizar una solicitud y registrar todos los detalles
function testApiCall($url, $method = 'GET', $data = null, $logFile)
{
    fprintf($logFile, "Probando URL: %s\n", $url);
    fprintf($logFile, "Método: %s\n", $method);

    if ($data) {
        fprintf($logFile, "Datos: %s\n", json_encode($data));
    }

    // Configurar cURL para la solicitud
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . session_id());

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
    }

    // Ejecutar la solicitud
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);

    // Registrar la respuesta
    fprintf($logFile, "Código de respuesta HTTP: %d\n", $httpCode);
    fprintf($logFile, "Encabezados de respuesta:\n%s\n", $headers);
    fprintf($logFile, "Cuerpo de la respuesta (tamaño: %d bytes):\n", strlen($body));
    fprintf($logFile, "%s\n", $body);

    // Intentar decodificar como JSON
    $jsonData = json_decode($body, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        fprintf($logFile, "Datos JSON decodificados:\n%s\n", print_r($jsonData, true));
    } else {
        fprintf($logFile, "Error al decodificar JSON: %s\n", json_last_error_msg());

        // Mostrar el cuerpo en formato hexadecimal para depuración adicional
        fprintf($logFile, "Cuerpo en hex:\n");
        for ($i = 0; $i < strlen($body); $i++) {
            fprintf($logFile, "%02X ", ord($body[$i]));
            if (($i + 1) % 16 === 0) fprintf($logFile, "\n");
        }
        fprintf($logFile, "\n");
    }

    curl_close($ch);

    return [
        'httpCode' => $httpCode,
        'headers' => $headers,
        'body' => $body,
        'jsonData' => $jsonData
    ];
}

// URLs a probar
$urls = [
    'cart_api_local' => 'http://localhost:8081/api/cart_api.php?action=get',
    'cart_api_relative' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/api/cart_api.php?action=get',
    'cart_php_direct' => 'http://localhost:8081/api/cart.php?action=get',
];

// Probar cada URL
$results = [];
foreach ($urls as $key => $url) {
    fprintf($fh, "\n----- Probando %s -----\n", $key);
    $results[$key] = testApiCall($url, 'GET', null, $fh);
}

fclose($fh);

// Mostrar resultados en formato HTML
?>
<!DOCTYPE html>
<html>

<head>
    <title>Depuración de API - Cat Store</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        h1,
        h2,
        h3 {
            color: #444;
        }

        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 5px;
            overflow: auto;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }

        .debug-section {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .hexdump {
            font-family: monospace;
            word-break: break-all;
            line-height: 1.4;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h1>Depuración de API - Cat Store</h1>

    <div class="debug-section">
        <h2>Información de Sesión</h2>
        <p><strong>ID de sesión:</strong> <?php echo session_id(); ?></p>
        <p><strong>Usuario:</strong> <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'No autenticado'; ?></p>
    </div>

    <div class="debug-section">
        <h2>Resultados de Pruebas API</h2>

        <?php foreach ($results as $key => $result): ?>
            <h3><?php echo htmlspecialchars($key); ?></h3>
            <p><strong>URL:</strong> <?php echo htmlspecialchars($urls[$key]); ?></p>
            <p><strong>Código HTTP:</strong>
                <span class="<?php echo ($result['httpCode'] >= 200 && $result['httpCode'] < 300) ? 'success' : 'error'; ?>">
                    <?php echo $result['httpCode']; ?>
                </span>
            </p>

            <?php if (isset($result['jsonData']) && is_array($result['jsonData'])): ?>
                <h4>Datos JSON:</h4>
                <pre><?php echo htmlspecialchars(json_encode($result['jsonData'], JSON_PRETTY_PRINT)); ?></pre>
            <?php else: ?>
                <h4>Contenido de la respuesta:</h4>
                <pre><?php echo htmlspecialchars($result['body']); ?></pre>

                <h4>Dump Hexadecimal:</h4>
                <div class="hexdump">
                    <?php
                    $body = $result['body'];
                    for ($i = 0; $i < strlen($body); $i++) {
                        echo sprintf("%02X ", ord($body[$i]));
                        if (($i + 1) % 16 === 0) echo "<br>";
                    }
                    ?>
                </div>
            <?php endif; ?>

            <hr>
        <?php endforeach; ?>
    </div>

    <div class="debug-section">
        <h2>Acciones adicionales</h2>
        <p><a href="<?php echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']; ?>/views/cart/checkout.php">Ir a la página del carrito</a></p>
        <p><a href="<?php echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']; ?>/api/debug_paths.php">Ver información de rutas</a></p>
    </div>

    <p><small>Archivo de log guardado en: <?php echo htmlspecialchars($logFile); ?></small></p>
</body>

</html>