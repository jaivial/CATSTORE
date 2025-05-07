<?php

/**
 * Drawer del carrito
 * Cat Store - Tienda de Gatos
 */
?>

<!-- Código de diagnóstico para la sesión -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Comprobar si hay un botón para depurar
        const debugBtn = document.getElementById('debug-session');
        if (debugBtn) {
            debugBtn.addEventListener('click', function() {
                // Mostrar un indicador de carga en el botón
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                // Obtener la URL base
                const getBaseUrl = () => {
                    // Devolver una cadena vacía para usar URLs relativas
                    return '';
                };

                const baseUrl = getBaseUrl();

                // Llamar a la API de diagnóstico
                fetch(`${baseUrl}/api/session_debug.php`, {
                        credentials: 'include' // Incluir cookies en la solicitud
                    })
                    .then(response => {
                        // Verificar si la respuesta es exitosa
                        if (!response.ok) {
                            throw new Error(`Error HTTP: ${response.status}`);
                        }

                        // Verificar que la respuesta no esté vacía
                        const contentType = response.headers.get('content-type');
                        if (!contentType || !contentType.includes('application/json')) {
                            throw new Error('La respuesta no es de tipo JSON');
                        }

                        return response.text().then(text => {
                            if (!text || text.trim() === '') {
                                throw new Error('Respuesta vacía del servidor');
                            }

                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                console.error('Error al parsear JSON:', text);
                                throw new Error('Error al parsear JSON: ' + e.message);
                            }
                        });
                    })
                    .then(data => {
                        console.log('Información de sesión:', data);

                        // Restaurar el botón
                        this.innerHTML = 'Debug';

                        // Mostrar información en un modal o directamente en la página
                        const debugInfo = `
                            <div class="debug-info">
                                <h4>Información de Sesión</h4>
                                <ul>
                                    <li><strong>ID de sesión:</strong> ${data.session_id}</li>
                                    <li><strong>Estado:</strong> ${data.session_status === 2 ? 'Activa' : 'Inactiva'}</li>
                                    <li><strong>Usuario (sesión):</strong> ${data.authentication.session_user || 'No autenticado'}</li>
                                    <li><strong>Usuario (cookie):</strong> ${data.authentication.cookie_user || 'No disponible'}</li>
                                    <li><strong>Autenticado:</strong> ${data.authentication.is_authenticated ? 'Sí' : 'No'}</li>
                                </ul>
                                <p>Información completa guardada en la consola.</p>
                            </div>
                        `;

                        // Crear un modal para mostrar la información
                        const debugModal = document.createElement('div');
                        debugModal.className = 'debug-modal';
                        debugModal.innerHTML = `
                            <div class="debug-modal-content">
                                <div class="debug-modal-header">
                                    <h3>Diagnóstico de Sesión</h3>
                                    <button class="debug-modal-close">&times;</button>
                                </div>
                                <div class="debug-modal-body">
                                    ${debugInfo}
                                </div>
                            </div>
                        `;

                        // Agregar estilos
                        const style = document.createElement('style');
                        style.innerHTML = `
                            .debug-modal {
                                position: fixed;
                                z-index: 1000;
                                left: 0;
                                top: 0;
                                width: 100%;
                                height: 100%;
                                background-color: rgba(0,0,0,0.5);
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            }
                            .debug-modal-content {
                                background-color: white;
                                padding: 20px;
                                border-radius: 5px;
                                max-width: 600px;
                                width: 90%;
                                max-height: 80vh;
                                overflow-y: auto;
                            }
                            .debug-modal-header {
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                margin-bottom: 15px;
                            }
                            .debug-modal-close {
                                background: none;
                                border: none;
                                font-size: 24px;
                                cursor: pointer;
                            }
                            .debug-info ul {
                                padding-left: 20px;
                            }
                            .debug-info li {
                                margin-bottom: 5px;
                            }
                        `;

                        document.head.appendChild(style);
                        document.body.appendChild(debugModal);

                        // Agregar evento para cerrar el modal
                        const closeBtn = debugModal.querySelector('.debug-modal-close');
                        closeBtn.addEventListener('click', function() {
                            document.body.removeChild(debugModal);
                        });
                    })
                    .catch(err => {
                        console.error('Error al obtener información de sesión:', err);
                        // Restaurar el botón
                        this.innerHTML = 'Debug';
                        alert('Error al obtener información de sesión: ' + err.message);
                    });
            });
        }
    });
</script>

<!-- Drawer del carrito -->
<div class="cart-drawer" id="cart-drawer">
    <div class="cart-drawer-header">
        <h3 class="cart-drawer-title">
            <i class="fas fa-shopping-cart"></i> Tu Carrito
            <!-- Botón para depurar la sesión -->
            <button id="debug-session" style="font-size: 12px; background: #f0f0f0; border: none; border-radius: 4px; padding: 2px 5px; margin-left: 10px; cursor: pointer;">Debug</button>
        </h3>
        <button class="cart-drawer-close" id="cart-close">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Herramientas de depuración para desarrolladores -->
    <div class="debug-tools" style="background: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 10px; border: 1px dashed #ccc; font-size: 13px;">
        <p style="margin: 0 0 5px 0; font-weight: bold;">Herramientas de depuración</p>
        <div style="display: flex; gap: 5px; margin-bottom: 5px;">
            <select id="api-url-select" style="flex-grow: 1; padding: 4px; border-radius: 3px; border: 1px solid #ccc;">
                <option value="autodetect">Autodetectar API</option>
                <option value="/api/cart_api.php">Ruta relativa (/api/cart_api.php)</option>
                <option value="http://localhost:8081/api/cart_api.php">localhost:8081</option>
                <option value="http://localhost:8000/api/cart_api.php">localhost:8000</option>
                <option value="/api/cart.php">Proxy original (/api/cart.php)</option>
            </select>
            <button id="test-api-button" style="padding: 4px 8px; border-radius: 3px; background: #007bff; color: white; border: none; cursor: pointer;">Probar</button>
        </div>
        <div style="display: flex; gap: 5px;">
            <a href="/api/debug_output.php" target="_blank" style="text-decoration: none; display: inline-block; padding: 4px 8px; border-radius: 3px; background: #6c757d; color: white; font-size: 12px; text-align: center;">Diagnóstico completo</a>
            <a href="/views/cart/checkout.php" style="text-decoration: none; display: inline-block; padding: 4px 8px; border-radius: 3px; background: #28a745; color: white; font-size: 12px; text-align: center;">Ir a checkout</a>
        </div>
    </div>

    <div class="cart-drawer-content" id="cart-drawer-content">
        <!-- El contenido del carrito se cargará dinámicamente con JavaScript -->
        <div class="cart-loading">
            <i class="fas fa-spinner fa-spin"></i> Cargando...
        </div>
    </div>
</div>

<!-- Overlay para el drawer -->
<div class="cart-overlay" id="cart-overlay"></div>

<!-- Cargar el fixed_drawer.js para hacer más robusto el carrito -->
<script>
    // Cargar el script de corrección para el drawer del carrito
    document.addEventListener('DOMContentLoaded', function() {
        // Configuración de depuración
        window.cartDebugConfig = {
            enableDebug: true,
            apiUrl: null, // Se determinará automáticamente
            forceApiUrl: false
        };

        // Configurar botones de depuración
        const apiSelect = document.getElementById('api-url-select');
        const testApiButton = document.getElementById('test-api-button');

        if (apiSelect && testApiButton) {
            testApiButton.addEventListener('click', function() {
                const selectedApi = apiSelect.value;

                if (selectedApi === 'autodetect') {
                    window.cartDebugConfig.forceApiUrl = false;
                    window.cartDebugConfig.apiUrl = null;
                } else {
                    window.cartDebugConfig.forceApiUrl = true;
                    window.cartDebugConfig.apiUrl = selectedApi;
                }

                // Intentar cargar el carrito con la nueva configuración
                if (typeof loadCartContent === 'function') {
                    loadCartContent();
                } else {
                    // Si la función loadCartContent no está disponible, recargar la página
                    window.location.reload();
                }
            });
        }

        // Crear elemento de script
        var script = document.createElement('script');
        script.src = '/fixed_drawer.js?v=' + new Date().getTime(); // Añadir timestamp para evitar caché
        script.onerror = function() {
            console.error('Error al cargar el script de corrección del carrito');
        };

        // Añadir al final del body
        document.body.appendChild(script);
    });
</script>