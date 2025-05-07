/**
 * JavaScript para la tienda
 * Cat Store - Tienda de Gatos
 */

document.addEventListener('DOMContentLoaded', function () {
    // Referencias a elementos del DOM
    const filtersToggle = document.getElementById('filters-toggle');
    const filtersClose = document.getElementById('filters-close');
    const filtersPanel = document.getElementById('filters-panel');
    const filtersOverlay = document.getElementById('filters-overlay');
    const resetFilters = document.getElementById('reset-filters');
    const clearFilters = document.getElementById('clear-filters');
    const sortSelect = document.getElementById('sort-select');
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    const cartCount = document.getElementById('cart-count');
    const cartToggle = document.getElementById('cart-toggle');
    const cartDrawer = document.getElementById('cart-drawer');
    const cartClose = document.getElementById('cart-close');
    const cartOverlay = document.getElementById('cart-overlay');
    const cartDrawerContent = document.getElementById('cart-drawer-content');

    // Mostrar/ocultar panel de filtros
    if (filtersToggle) {
        filtersToggle.addEventListener('click', function () {
            filtersPanel.classList.add('active');
            filtersOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }

    if (filtersClose) {
        filtersClose.addEventListener('click', function () {
            filtersPanel.classList.remove('active');
            filtersOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    }

    if (filtersOverlay) {
        filtersOverlay.addEventListener('click', function () {
            filtersPanel.classList.remove('active');
            filtersOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    }

    // Limpiar filtros
    if (resetFilters) {
        resetFilters.addEventListener('click', function () {
            window.location.href = '/views/store/index.php';
        });
    }

    if (clearFilters) {
        clearFilters.addEventListener('click', function () {
            window.location.href = '/views/store/index.php';
        });
    }

    // Cambiar ordenación
    if (sortSelect) {
        sortSelect.addEventListener('change', function () {
            const [orderBy, orderDir] = this.value.split('-');

            // Obtener URL actual
            const url = new URL(window.location.href);

            // Actualizar parámetros de ordenación
            url.searchParams.set('orderBy', orderBy);
            url.searchParams.set('orderDir', orderDir);

            // Redirigir a la URL actualizada
            window.location.href = url.toString();
        });
    }

    // Añadir al carrito
    if (addToCartButtons.length > 0) {
        addToCartButtons.forEach(button => {
            button.addEventListener('click', function () {
                const productId = this.getAttribute('data-id');

                // Mostrar estado de carga
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Añadiendo...';
                this.disabled = true;

                // Usar el proxy del carrito en vez del API directamente
                const proxyUrl = window.location.origin + '/api/cart_proxy.php';

                // Enviar solicitud al servidor
                fetch(`${proxyUrl}?action=add`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: productId }),
                    credentials: 'include' // Importante para enviar cookies
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Error HTTP: ${response.status}`);
                        }
                        return response.text();
                    })
                    .then(text => {
                        // Intentar extraer JSON
                        let jsonStart = text.indexOf('{');
                        let jsonText = text;

                        // Si encontramos un { pero no es al inicio, puede haber texto HTML antes
                        if (jsonStart > 0) {
                            console.warn("Contenido no JSON antes del objeto JSON:", text.substring(0, jsonStart));
                            jsonText = text.substring(jsonStart);
                        }

                        return JSON.parse(jsonText);
                    })
                    .then(data => {
                        if (data.success) {
                            // Actualizar contador del carrito
                            if (cartCount) {
                                cartCount.textContent = data.count;
                            }

                            // Mostrar mensaje de éxito
                            this.innerHTML = '<i class="fas fa-check"></i> Añadido';

                            // Restaurar botón después de un tiempo
                            setTimeout(() => {
                                this.innerHTML = '<i class="fas fa-cart-plus"></i> Añadir al carrito';
                                this.disabled = false;
                            }, 2000);
                        } else {
                            // Mostrar mensaje de error
                            this.innerHTML = '<i class="fas fa-times"></i> Error';

                            // Restaurar botón después de un tiempo
                            setTimeout(() => {
                                this.innerHTML = '<i class="fas fa-cart-plus"></i> Añadir al carrito';
                                this.disabled = false;
                            }, 2000);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);

                        // Mostrar mensaje de error
                        this.innerHTML = '<i class="fas fa-times"></i> Error';

                        // Restaurar botón después de un tiempo
                        setTimeout(() => {
                            this.innerHTML = '<i class="fas fa-cart-plus"></i> Añadir al carrito';
                            this.disabled = false;
                        }, 2000);
                    });
            });
        });
    }

    // Mostrar/ocultar drawer del carrito
    if (cartToggle && cartDrawer && cartClose && cartOverlay) {
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
    }

    // Obtener la URL base actual para las llamadas API
    const getBaseUrl = () => {
        // En un entorno real, la API está en la misma raíz que el sitio
        return ''; // Devolver una cadena vacía para usar URLs relativas
    };

    // Cargar contenido del carrito
    function loadCartContent() {
        if (!cartDrawerContent) return;

        // Mostrar estado de carga
        cartDrawerContent.innerHTML = '<div class="cart-loading"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';

        // Usar el proxy del carrito en vez del API directamente
        const proxyUrl = window.location.origin + '/api/cart_proxy.php';

        // Cargar contenido del carrito usando el proxy
        fetch(`${proxyUrl}?action=get`, {
            credentials: 'include', // Importante para enviar cookies
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                // Intentar buscar el inicio de un posible JSON en caso de que haya contenido antes
                let jsonStart = text.indexOf('{');
                let jsonText = text;

                // Si encontramos un { pero no es al inicio, puede haber texto HTML antes
                if (jsonStart > 0) {
                    console.warn("Posible contenido no JSON antes del objeto JSON, intentando extraer JSON:",
                        text.substring(0, jsonStart));
                    jsonText = text.substring(jsonStart);
                }

                return JSON.parse(jsonText);
            })
            .then(data => {
                if (data.success && data.items && data.items.length > 0) {
                    // Crear HTML para los elementos del carrito con mejor estilo
                    let html = '<div class="cart-items">';

                    data.items.forEach(item => {
                        // Guardar el ID del animal y el username para eliminar después
                        const itemId = item.id || 0;
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
                                        <span class="cart-item-quantity">Cantidad: ${item.repetitions || item.cantidad || 1}</span>
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

                    // Añadir total con mejor estilo
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
                    // Mostrar mensaje de carrito vacío con estilo mejorado
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
                            cartDrawer.classList.remove('active');
                            cartOverlay.classList.remove('active');
                            document.body.style.overflow = '';
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
                            <button class="btn btn-outline-primary" id="retry-cart-load">
                                <i class="fas fa-sync"></i> Reintentar
                            </button>
                        </div>
                    `;
                    const retryBtn = document.getElementById('retry-cart-load');
                    if (retryBtn) retryBtn.addEventListener('click', loadCartContent);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Mostrar mensaje de error con estilo mejorado
                cartDrawerContent.innerHTML = `
                    <div class="cart-error">
                        <div class="cart-error-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3>Error de conexión</h3>
                        <p>No se pudo conectar con el servidor</p>
                        <button class="btn btn-outline-primary" id="retry-cart-load">
                            <i class="fas fa-sync"></i> Reintentar
                        </button>
                    </div>
                `;
                const retryBtn = document.getElementById('retry-cart-load');
                if (retryBtn) retryBtn.addEventListener('click', loadCartContent);
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

    // Eliminar producto del carrito
    function removeFromCart(productId, username) {
        // Mostrar indicador de carga en el botón
        const button = document.querySelector(`.remove-from-cart[data-id="${productId}"]`);
        if (button) {
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            button.disabled = true;
        }

        // Usar el proxy del carrito en vez del API directamente
        const proxyUrl = window.location.origin + '/api/cart_proxy.php';

        fetch(`${proxyUrl}?action=remove`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: productId,
                username: username  // Incluir el username para mejor precisión
            }),
            credentials: 'include'  // Importante para enviar cookies
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                // Intentar extraer JSON
                let jsonStart = text.indexOf('{');
                let jsonText = text;

                // Si encontramos un { pero no es al inicio, puede haber texto HTML antes
                if (jsonStart > 0) {
                    console.warn("Contenido no JSON antes del objeto JSON:", text.substring(0, jsonStart));
                    jsonText = text.substring(jsonStart);
                }

                return JSON.parse(jsonText);
            })
            .then(data => {
                if (data.success) {
                    // Actualizar contador del carrito
                    if (cartCount) {
                        cartCount.textContent = data.count;
                    }

                    // Recargar contenido del carrito
                    loadCartContent();
                } else {
                    console.error('Error al eliminar del carrito:', data.message);
                    // Restaurar el botón
                    if (button) {
                        button.innerHTML = '<i class="fas fa-trash"></i>';
                        button.disabled = false;
                    }

                    // Mostrar mensaje de error
                    alert('Error al eliminar el producto del carrito: ' + data.message);
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
                alert('Error de conexión al eliminar el producto');
            });
    }
}); 
