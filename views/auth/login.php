<?php

/**
 * Vista de inicio de sesión
 * Cat Store - Tienda de Gatos
 */

require_once __DIR__ . '/../../includes/auth_middleware.php';

// Redirigir si ya está autenticado
redirectIfAuthenticated();

// Título de la página
$pageTitle = "Iniciar Sesión - Cat Store";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>

<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Cat Store</h1>
                <h2>Iniciar Sesión</h2>
            </div>

            <div class="auth-tabs">
                <a href="#" class="tab active" id="login-tab">Iniciar Sesión</a>
                <a href="#" class="tab" id="register-tab">Registrarse</a>
            </div>

            <div class="auth-content" id="login-content">
                <form id="login-form" method="post">
                    <div class="form-group">
                        <label for="username">Usuario</label>
                        <input type="text" id="username" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="form-message" id="login-message"></div>

                    <div class="form-group">
                        <button type="submit" class="btn-primary">Iniciar Sesión</button>
                    </div>
                </form>
            </div>

            <div class="auth-content hidden" id="register-content">
                <form id="register-form" method="post">
                    <div class="form-group">
                        <label for="reg-username">Usuario</label>
                        <input type="text" id="reg-username" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="reg-password">Contraseña</label>
                        <input type="password" id="reg-password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>

                    <div class="form-group">
                        <label for="apellido">Apellido</label>
                        <input type="text" id="apellido" name="apellido" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-message" id="register-message"></div>

                    <div class="form-group">
                        <button type="submit" class="btn-primary">Registrarse</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/assets/js/auth.js"></script>
</body>

</html>