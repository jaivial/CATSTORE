/**
 * Script para corregir el drawer del carrito en todos los entornos
 * Cat Store - Tienda de Gatos
 */

(function () {
    console.log("🚀 Iniciando corrección del drawer del carrito...");

    // Función para inicializar el corrector cuando el DOM esté listo
    function init() {
        console.log("DOM cargado, inicializando corrector...");

        // Hacer global la función loadCartContent para que sea accesible desde el panel de depuración
        window.loadCartContent = loadCartContent;

        // Configurar el drawer
        fixCartDrawer();
    }

    // Función principal de corrección
    function fixCartDrawer() {
        // Referencias a elementos del DOM
        const cartToggle = document.getElementById('cart-toggle');
        const cartDrawer = document.getElementById('cart-drawer');
        const cartClose = document.getElementById('cart-close');
        const cartOverlay = document.getElementById('cart-overlay');
        const cartDrawerContent = document.getElementById('cart-drawer-content');

        if (!cartToggle || !cartDrawer || !cartClose || !cartOverlay || !cartDrawerContent) {
            console.error("No se encontraron todos los elementos del drawer del carrito");
            return;
        }

        console.log("Elementos del drawer encontrados, configurando eventos...");

        // Configurar eventos del drawer
        cartToggle.addEventListener('click', function (e) {
            e.preventDefault();
            cartDrawer.classList.add('active');
            cartOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';

            // Cargar contenido del carrito
            loadCartContent();
        });

        cartClose.addEventListener('click', function () {
            cartDrawer.classList.remove('active');
            cartOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });

        cartOverlay.addEventListener('click', function () {
            cartDrawer.classList.remove('active');
            cartOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });

        console.log("Eventos configurados correctamente");
    }

    // Función para determinar la URL completa de la API
    function getApiUrl() {
        // Verificar si hay una configuración de depuración forzada
        if (window.cartDebugConfig && window.cartDebugConfig.forceApiUrl && window.cartDebugConfig.apiUrl) {
            console.log("Usando URL de API forzada:", window.cartDebugConfig.apiUrl);
            return Promise.resolve(window.cartDebugConfig.apiUrl);
        }

        // Usar el proxy seguro del carrito que hemos creado
        const proxyUrl = window.location.origin + '/api/cart_proxy.php';
        return Promise.resolve(proxyUrl);
    }

    // Función para cargar el contenido del carrito
    function loadCartContent() {
        const cartDrawerContent = document.getElementById('cart-drawer-content');
        if (!cartDrawerContent) {
            console.error("No se encontró el elemento cart-drawer-content");
            return;
        }

        // Mostrar estado de carga
        cartDrawerContent.innerHTML = '<div class="cart-loading"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';

        // Obtener la URL de la API dinámicamente
        getApiUrl().then(apiUrl => {
            console.log("Usando URL de API:", apiUrl);

            // Hacer la solicitud al servidor
            fetch(apiUrl + '?action=get', {
                credentials: 'include',  // Importante para enviar cookies
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                }
            })
                .then(response => {
                    console.log("Respuesta recibida:", response.status, response.statusText);

                    // Verificar si la respuesta es exitosa
                    if (!response.ok) {
                        return response.text().then(text => {
                            // Intentar extraer información útil del texto
                            console.error("Error HTTP:", response.status, text);
                            throw new Error(`Error HTTP: ${response.status}`);
                        });
                    }

                    // Verificar que la respuesta no esté vacía
                    const contentType = response.headers.get('content-type');
                    console.log("Tipo de contenido:", contentType);

                    // Primero recuperamos el texto sin importar el tipo de contenido
                    return response.text().then(text => {
                        console.log("Texto respuesta:", text.substring(0, 100) + "...");

                        if (!text || text.trim() === '') {
                            throw new Error('Respuesta vacía del servidor');
                        }

                        try {
                            // Intentar buscar el inicio de un posible JSON en caso de que haya contenido antes
                            let jsonStart = text.indexOf('{');
                            let jsonText = text;

                            // Si encontramos un { pero no es al inicio, puede haber texto HTML antes
                            if (jsonStart > 0) {
                                console.warn("Contenido no JSON antes del objeto JSON, extrayendo JSON:",
                                    text.substring(0, jsonStart));
                                jsonText = text.substring(jsonStart);
                            }

                            try {
                                const data = JSON.parse(jsonText);
                                console.log("Datos parseados:", data);
                                return data;
                            } catch (e) {
                                // Si no podemos parsear como JSON, intentamos otra estrategia
                                console.error('Error al parsear JSON, buscando último objeto JSON válido:', e);

                                // Buscar el último objeto JSON válido en la respuesta
                                const jsonMatches = text.match(/\{(?:[^{}]|\{(?:[^{}]|\{[^{}]*\})*\})*\}/g);
                                if (jsonMatches && jsonMatches.length > 0) {
                                    const lastJsonMatch = jsonMatches[jsonMatches.length - 1];
                                    console.log("Encontrado posible JSON:", lastJsonMatch);
                                    try {
                                        const data = JSON.parse(lastJsonMatch);
                                        console.log("JSON extraído con éxito:", data);
                                        return data;
                                    } catch (e2) {
                                        console.error('Error al parsear JSON extraído:', e2);
                                    }
                                }

                                throw new Error('Error al parsear JSON: ' + e.message);
                            }
                        } catch (e) {
                            console.error('Error al procesar respuesta:', text, e);
                            throw new Error('Error al procesar respuesta: ' + e.message);
                        }
                    });
                })
                .then(data => {
                    console.log("Procesando datos del carrito:", data);

                    if (data.success && data.items && data.items.length > 0) {
                        // Crear HTML para los elementos del carrito
                        let html = '<div class="cart-items">';

                        data.items.forEach(item => {
                            // Asegurar que tengamos un id_animal válido
                            const itemId = item.id || 0;
                            // Usar el username_usuario de la sesión si está disponible
                            const username = item.username_usuario || '';

                            html += `
                            <div class="cart-item" data-id="${itemId}" data-username="${username}">
                                <div class="cart-item-image">
                                    ${item.foto
                                    ? `<img src="data:image/jpeg;base64,${item.foto}" alt="${item.nombre}">`
                                    : `<img src="/assets/img/cat-placeholder.jpg" alt="Imagen no disponible">`
                                }
                                </div>
                                <div class="cart-item-info">
                                    <h4>${item.nombre}</h4>
                                    <div class="cart-item-details">
                                        <span class="cart-item-type">${item.tipo}</span>
                                        <span class="cart-item-color" style="background-color: ${getColorCode(item.color)}"></span>
                                        <span class="cart-item-gender">
                                            <i class="fas fa-${item.sexo == 1 ? 'mars' : 'venus'}"></i>
                                        </span>
                                    </div>
                                    <div class="cart-item-price-row">
                                        <span class="cart-item-price">${parseFloat(item.precio).toFixed(2).replace('.', ',')} €</span>
                                        <span class="cart-item-quantity">Cantidad: ${item.cantidad || item.repetitions || 1}</span>
                                    </div>
                                </div>
                                <div class="cart-item-actions">
                                    <button class="btn btn-sm remove-from-cart" data-id="${itemId}" data-username="${username}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                        });

                        html += '</div>';

                        // Añadir total
                        html += `
                        <div class="cart-total">
                            <div class="cart-total-label">Total:</div>
                            <div class="cart-total-amount">${parseFloat(data.total).toFixed(2).replace('.', ',')} €</div>
                        </div>
                        <div class="cart-actions">
                            <a href="/views/cart/checkout.php" class="btn btn-primary btn-block checkout-btn">
                                <i class="fas fa-shopping-bag"></i> Finalizar compra
                            </a>
                        </div>
                    `;

                        cartDrawerContent.innerHTML = html;

                        // Añadir eventos a los botones de eliminar
                        const removeButtons = document.querySelectorAll('.remove-from-cart');
                        removeButtons.forEach(button => {
                            button.addEventListener('click', function () {
                                const productId = this.getAttribute('data-id');
                                const username = this.getAttribute('data-username');
                                removeFromCart(productId, username);
                            });
                        });

                    } else if (data.success && (!data.items || data.items.length === 0)) {
                        cartDrawerContent.innerHTML = `
                        <div class="cart-empty">
                            <div class="cart-empty-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <h3>Tu carrito está vacío</h3>
                            <p>Añade algunos productos para continuar</p>
                            <button class="btn btn-primary continue-shopping">
                                <i class="fas fa-arrow-left"></i> Seguir comprando
                            </button>
                        </div>
                    `;

                        // Añadir evento al botón de seguir comprando
                        const continueBtn = document.querySelector('.continue-shopping');
                        if (continueBtn) {
                            continueBtn.addEventListener('click', function () {
                                // Cerrar el drawer
                                const cartDrawer = document.getElementById('cart-drawer');
                                const cartOverlay = document.getElementById('cart-overlay');
                                if (cartDrawer && cartOverlay) {
                                    cartDrawer.classList.remove('active');
                                    cartOverlay.classList.remove('active');
                                    document.body.style.overflow = '';
                                }
                            });
                        }
                    } else {
                        // Mostrar mensaje de error amigable
                        cartDrawerContent.innerHTML = `
                        <div class="cart-error">
                            <div class="cart-error-icon">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <h3>Oops! Algo salió mal</h3>
                            <p>${data.message || 'No se pudo cargar el carrito'}</p>
                            <p class="error-details">Puedes intentar acceder directamente a la <a href="/views/cart/checkout.php">página del carrito</a></p>
                            <div class="error-actions">
                                <button class="btn btn-outline-primary" id="retry-cart-load">
                                    <i class="fas fa-sync"></i> Reintentar
                                </button>
                                <button class="btn btn-outline-secondary" id="debug-cart-api">
                                    <i class="fas fa-bug"></i> Diagnosticar
                                </button>
                            </div>
                        </div>
                    `;
                        const retryBtn = document.getElementById('retry-cart-load');
                        if (retryBtn) {
                            retryBtn.addEventListener('click', loadCartContent);
                        }

                        // Configurar botón de diagnóstico
                        const debugBtn = document.getElementById('debug-cart-api');
                        if (debugBtn) {
                            debugBtn.addEventListener('click', function () {
                                // Abrir diagnóstico en nueva ventana
                                window.open('/api/debug_output.php', '_blank');
                            });
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);

                    // Mensaje de error simplificado como en la imagen
                    cartDrawerContent.innerHTML = `
                    <div class="cart-error">
                        <div class="cart-error-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3>Error de conexión</h3>
                        <p>${error.message || 'No se pudo conectar con el servidor'}</p>
                        <div class="error-actions">
                            <button class="btn btn-outline-primary" id="retry-cart-load">
                                <i class="fas fa-sync"></i> Reintentar
                            </button>
                        </div>
                        <div style="margin-top: 20px; text-align: center;">
                            <a href="/views/cart/checkout.php" class="btn btn-primary">
                                Ver Carrito
                            </a>
                        </div>
                    </div>
                    `;

                    // Configurar botón de reintento
                    const retryBtn = document.getElementById('retry-cart-load');
                    if (retryBtn) {
                        retryBtn.addEventListener('click', loadCartContent);
                    }
                });
        });
    }

    // Función para eliminar producto del carrito
    function removeFromCart(productId, username) {
        // Mostrar indicador de carga
        const button = document.querySelector(`.remove-from-cart[data-id="${productId}"]`);
        if (button) {
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            button.disabled = true;
        }

        // Obtener la URL del proxy
        getApiUrl().then(apiUrl => {
            console.log("Usando API URL para eliminar:", apiUrl);

            // Enviar solicitud al servidor usando el proxy
            fetch(`${apiUrl}?action=remove`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: productId,
                    username: username // Incluir el username para mejor precisión
                }),
                credentials: 'include'
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
                            // Intentar buscar el inicio de un posible JSON en caso de que haya contenido antes
                            let jsonStart = text.indexOf('{');
                            let jsonText = text;

                            // Si encontramos un { pero no es al inicio, puede haber texto HTML antes
                            if (jsonStart > 0) {
                                console.warn("Posible contenido no JSON antes del objeto JSON, intentando extraer JSON");
                                jsonText = text.substring(jsonStart);
                            }

                            return JSON.parse(jsonText);
                        } catch (e) {
                            console.error('Error al parsear JSON:', text);
                            throw new Error('Error al parsear JSON: ' + e.message);
                        }
                    });
                })
                .then(data => {
                    if (data.success) {
                        // Actualizar contador del carrito
                        const cartCount = document.getElementById('cart-count');
                        if (cartCount) {
                            cartCount.textContent = data.count;
                        }

                        // Recargar contenido del carrito
                        loadCartContent();
                    } else {
                        // Restaurar el botón
                        if (button) {
                            button.innerHTML = '<i class="fas fa-trash"></i>';
                            button.disabled = false;
                        }

                        // Mostrar mensaje de error
                        alert('Error al eliminar el producto del carrito: ' + (data.message || 'Error desconocido'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);

                    // Restaurar el botón
                    if (button) {
                        button.innerHTML = '<i class="fas fa-trash"></i>';
                        button.disabled = false;
                    }

                    // Mostrar mensaje de error
                    alert('Error al procesar la solicitud: ' + error.message);
                });
        });
    }

    // Función para obtener código de color basado en el nombre
    function getColorCode(colorName) {
        if (!colorName) return '#CCCCCC';

        const colorMap = {
            'blanco': '#FFFFFF',
            'negro': '#000000',
            'gris': '#808080',
            'marrón': '#8B4513',
            'naranja': '#FFA500',
            'atigrado': '#D2B48C',
            'calico': '#F5DEB3'
        };

        return colorMap[colorName.toLowerCase()] || '#CCCCCC';
    }

    // Ejecutar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})(); 