/**
 * JavaScript para gestión del carrito
 * Cat Store - Tienda de Gatos
 */

document.addEventListener('DOMContentLoaded', function () {
    // Configurar botones de eliminar del carrito
    const removeButtons = document.querySelectorAll('.remove-from-cart');
    removeButtons.forEach(button => {
        button.addEventListener('click', function () {
            const productId = this.getAttribute('data-id');
            const username = this.getAttribute('data-username');
            removeFromCart(productId, username, this);
        });
    });

    // Configurar botón de vaciar carrito
    const clearCartButton = document.getElementById('clear-cart-button');
    if (clearCartButton) {
        clearCartButton.addEventListener('click', clearCart);
    }

    // Configurar sistema de modal de confirmación
    const checkoutButton = document.getElementById('checkout-button');
    const confirmationModal = document.getElementById('confirmation-modal');
    const modalOverlay = document.getElementById('modal-overlay');
    const modalClose = document.getElementById('modal-close');
    const cancelCheckout = document.getElementById('cancel-checkout');
    const confirmCheckout = document.getElementById('confirm-checkout');

    if (checkoutButton && confirmationModal && modalOverlay) {
        // Mostrar modal al hacer clic en finalizar compra
        checkoutButton.addEventListener('click', function () {
            confirmationModal.classList.add('active');
            modalOverlay.classList.add('active');
        });

        // Ocultar modal
        if (modalClose) {
            modalClose.addEventListener('click', closeModal);
        }
        if (cancelCheckout) {
            cancelCheckout.addEventListener('click', closeModal);
        }
        if (modalOverlay) {
            modalOverlay.addEventListener('click', closeModal);
        }

        // Confirmar checkout
        if (confirmCheckout) {
            confirmCheckout.addEventListener('click', function () {
                // Deshabilitar botón
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

                // Enviar solicitud de compra
                checkout();
            });
        }
    }
});

/**
 * Función para manejar el procesamiento de respuestas y extracción de JSON
 * @param {Response} response Respuesta de fetch
 * @returns {Promise<Object>} Objeto JSON
 */
function handleResponse(response) {
    if (!response.ok) {
        throw new Error(`Error HTTP: ${response.status}`);
    }

    return response.text().then(text => {
        // Intentar buscar el inicio de un posible JSON en caso de que haya contenido antes
        let jsonStart = text.indexOf('{');
        let jsonText = text;

        // Si encontramos un { pero no es al inicio, puede haber texto HTML antes
        if (jsonStart > 0) {
            console.warn("Posible contenido no JSON antes del objeto JSON:",
                text.substring(0, jsonStart));
            jsonText = text.substring(jsonStart);
        }

        try {
            return JSON.parse(jsonText);
        } catch (e) {
            console.error('Error al parsear JSON:', text);
            throw new Error('Error al parsear JSON: ' + e.message);
        }
    });
}

/**
 * Elimina un producto del carrito
 * @param {number} productId ID del producto
 * @param {string} username Username del usuario
 * @param {HTMLElement} button Botón que disparó la acción
 */
function removeFromCart(productId, username, button) {
    // Mostrar indicador de carga
    if (button) {
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;
    }

    // Usar el proxy del carrito en lugar del controlador directo
    const proxyUrl = window.location.origin + '/api/cart_proxy.php';

    // Enviar solicitud al servidor
    fetch(`${proxyUrl}?action=remove`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: productId,
            username: username
        }),
        credentials: 'include'
    })
        .then(handleResponse)
        .then(data => {
            if (data.success) {
                // Recargar la página para mostrar el carrito actualizado
                window.location.reload();
            } else {
                // Restaurar el botón
                if (button) {
                    button.innerHTML = '<i class="fas fa-trash"></i>';
                    button.disabled = false;
                }

                // Mostrar mensaje de error
                alert('Error al eliminar el producto: ' + (data.message || 'Error desconocido'));
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
}

/**
 * Vacía el carrito
 */
function clearCart() {
    if (!confirm('¿Estás seguro de que deseas vaciar el carrito?')) {
        return;
    }

    // Usar el proxy del carrito en lugar del controlador directo
    const proxyUrl = window.location.origin + '/api/cart_proxy.php';

    // Enviar solicitud al servidor
    fetch(`${proxyUrl}?action=clear`, {
        method: 'POST',
        credentials: 'include'
    })
        .then(handleResponse)
        .then(data => {
            if (data.success) {
                // Recargar la página para mostrar el carrito vacío
                window.location.reload();
            } else {
                // Mostrar mensaje de error
                alert('Error al vaciar el carrito: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Mostrar mensaje de error
            alert('Error al procesar la solicitud: ' + error.message);
        });
}

/**
 * Finaliza la compra
 */
function checkout() {
    // Usar el proxy del carrito en lugar del controlador directo
    const proxyUrl = window.location.origin + '/api/cart_proxy.php';

    // Enviar solicitud al servidor
    fetch(`${proxyUrl}?action=checkout`, {
        method: 'POST',
        credentials: 'include'
    })
        .then(handleResponse)
        .then(data => {
            if (data.success) {
                // Redirigir a la página de confirmación
                window.location.href = '/views/cart/success.php';
            } else {
                // Cerrar modal
                closeModal();

                // Mostrar mensaje de error
                alert('Error al procesar la compra: ' + (data.message || 'Error desconocido'));

                // Restaurar botón
                const confirmCheckout = document.getElementById('confirm-checkout');
                if (confirmCheckout) {
                    confirmCheckout.disabled = false;
                    confirmCheckout.innerHTML = 'Confirmar';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);

            // Cerrar modal
            closeModal();

            // Mostrar mensaje de error
            alert('Error al procesar la solicitud: ' + error.message);

            // Restaurar botón
            const confirmCheckout = document.getElementById('confirm-checkout');
            if (confirmCheckout) {
                confirmCheckout.disabled = false;
                confirmCheckout.innerHTML = 'Confirmar';
            }
        });
}

/**
 * Cierra el modal de confirmación
 */
function closeModal() {
    const confirmationModal = document.getElementById('confirmation-modal');
    const modalOverlay = document.getElementById('modal-overlay');

    if (confirmationModal) {
        confirmationModal.classList.remove('active');
    }
    if (modalOverlay) {
        modalOverlay.classList.remove('active');
    }
} 