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

                // Enviar solicitud al servidor
                fetch('/api/cart.php?action=add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: productId })
                })
                    .then(response => response.json())
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

    // Cargar contenido del carrito
    function loadCartContent() {
        if (!cartDrawerContent) return;

        // Mostrar estado de carga
        cartDrawerContent.innerHTML = '<div class="cart-loading"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';

        // Cargar contenido del carrito
        fetch('/api/cart.php?action=get')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.items.length > 0) {
                    // Crear HTML para los elementos del carrito
                    let html = '<div class="cart-items">';

                    data.items.forEach(item => {
                        html += `
                            <div class="cart-item">
                                <div class="cart-item-image">
                                    ${item.foto
                                ? `<img src="data:image/jpeg;base64,${item.foto}" alt="${item.nombre}">`
                                : `<img src="/assets/img/cat-placeholder.jpg" alt="Imagen no disponible">`
                            }
                                </div>
                                <div class="cart-item-info">
                                    <h4>${item.nombre}</h4>
                                    <p>${item.tipo} - ${item.color}</p>
                                    <p class="cart-item-price">${parseFloat(item.precio).toFixed(2).replace('.', ',')} €</p>
                                </div>
                                <div class="cart-item-actions">
                                    <button class="btn btn-sm remove-from-cart" data-id="${item.id}">
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
                            <span>Total:</span>
                            <span>${parseFloat(data.total).toFixed(2).replace('.', ',')} €</span>
                        </div>
                    `;

                    cartDrawerContent.innerHTML = html;

                    // Añadir eventos a los botones de eliminar
                    const removeButtons = document.querySelectorAll('.remove-from-cart');
                    removeButtons.forEach(button => {
                        button.addEventListener('click', function () {
                            const productId = this.getAttribute('data-id');
                            removeFromCart(productId);
                        });
                    });
                } else if (data.success && data.items.length === 0) {
                    cartDrawerContent.innerHTML = '<div class="cart-empty">No hay productos en el carrito</div>';
                } else {
                    // Mostrar mensaje de error amigable y botón de reintentar
                    cartDrawerContent.innerHTML = `
                        <div class="cart-error">
                            <p>Ocurrió un error al cargar el carrito.</p>
                            <button class="btn btn-outline-primary" id="retry-cart-load">Reintentar</button>
                        </div>
                    `;
                    const retryBtn = document.getElementById('retry-cart-load');
                    if (retryBtn) retryBtn.addEventListener('click', loadCartContent);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                cartDrawerContent.innerHTML = `
                    <div class="cart-error">
                        <p>Ocurrió un error al cargar el carrito.</p>
                        <button class="btn btn-outline-primary" id="retry-cart-load">Reintentar</button>
                    </div>
                `;
                const retryBtn = document.getElementById('retry-cart-load');
                if (retryBtn) retryBtn.addEventListener('click', loadCartContent);
            });
    }

    // Eliminar producto del carrito
    function removeFromCart(productId) {
        fetch('/api/cart.php?action=remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: productId })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar contador del carrito
                    if (cartCount) {
                        cartCount.textContent = data.count;
                    }

                    // Recargar contenido del carrito
                    loadCartContent();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
}); 