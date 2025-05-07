/**
 * JavaScript para el carrito de compra
 * Cat Store - Tienda de Gatos
 */

document.addEventListener('DOMContentLoaded', function () {
    // Referencias a elementos del DOM
    const removeButtons = document.querySelectorAll('.remove-from-cart');
    const clearCartButton = document.getElementById('clear-cart-button');
    const checkoutButton = document.getElementById('checkout-button');
    const confirmationModal = document.getElementById('confirmation-modal');
    const modalOverlay = document.getElementById('modal-overlay');
    const modalClose = document.getElementById('modal-close');
    const cancelCheckout = document.getElementById('cancel-checkout');
    const confirmCheckout = document.getElementById('confirm-checkout');
    const cartCount = document.getElementById('cart-count');

    // Eliminar producto del carrito
    if (removeButtons.length > 0) {
        removeButtons.forEach(button => {
            button.addEventListener('click', function () {
                const productId = this.getAttribute('data-id');

                // Mostrar estado de carga
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                this.disabled = true;

                // Enviar solicitud al servidor
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

                            // Recargar la página para actualizar el carrito
                            window.location.reload();
                        } else {
                            // Restaurar botón
                            this.innerHTML = '<i class="fas fa-trash"></i>';
                            this.disabled = false;

                            // Mostrar mensaje de error
                            alert('Error al eliminar el producto del carrito');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);

                        // Restaurar botón
                        this.innerHTML = '<i class="fas fa-trash"></i>';
                        this.disabled = false;

                        // Mostrar mensaje de error
                        alert('Error al procesar la solicitud');
                    });
            });
        });
    }

    // Vaciar carrito
    if (clearCartButton) {
        clearCartButton.addEventListener('click', function () {
            if (confirm('¿Estás seguro de que deseas vaciar el carrito?')) {
                // Mostrar estado de carga
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Vaciando...';
                this.disabled = true;

                // Enviar solicitud al servidor
                fetch('/api/cart.php?action=clear', {
                    method: 'POST'
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Actualizar contador del carrito
                            if (cartCount) {
                                cartCount.textContent = '0';
                            }

                            // Recargar la página para actualizar el carrito
                            window.location.reload();
                        } else {
                            // Restaurar botón
                            this.innerHTML = 'Vaciar Carrito';
                            this.disabled = false;

                            // Mostrar mensaje de error
                            alert('Error al vaciar el carrito');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);

                        // Restaurar botón
                        this.innerHTML = 'Vaciar Carrito';
                        this.disabled = false;

                        // Mostrar mensaje de error
                        alert('Error al procesar la solicitud');
                    });
            }
        });
    }

    // Mostrar/ocultar modal de confirmación
    if (checkoutButton && confirmationModal && modalOverlay) {
        checkoutButton.addEventListener('click', function () {
            confirmationModal.classList.add('active');
            modalOverlay.classList.add('active');
        });

        modalClose.addEventListener('click', function () {
            confirmationModal.classList.remove('active');
            modalOverlay.classList.remove('active');
        });

        modalOverlay.addEventListener('click', function () {
            confirmationModal.classList.remove('active');
            modalOverlay.classList.remove('active');
        });

        cancelCheckout.addEventListener('click', function () {
            confirmationModal.classList.remove('active');
            modalOverlay.classList.remove('active');
        });
    }

    // Realizar checkout
    if (confirmCheckout) {
        confirmCheckout.addEventListener('click', function () {
            // Mostrar estado de carga
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
            this.disabled = true;

            // Enviar solicitud al servidor
            fetch('/api/cart.php?action=checkout', {
                method: 'POST'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Actualizar contador del carrito
                        if (cartCount) {
                            cartCount.textContent = '0';
                        }

                        // Mostrar mensaje de éxito
                        alert('¡Compra realizada con éxito!');

                        // Redirigir a la página de historial de compras
                        window.location.href = '/views/profile/purchase_history.php';
                    } else {
                        // Restaurar botón
                        this.innerHTML = 'Confirmar';
                        this.disabled = false;

                        // Cerrar modal
                        confirmationModal.classList.remove('active');
                        modalOverlay.classList.remove('active');

                        // Mostrar mensaje de error
                        alert('Error al procesar la compra: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);

                    // Restaurar botón
                    this.innerHTML = 'Confirmar';
                    this.disabled = false;

                    // Cerrar modal
                    confirmationModal.classList.remove('active');
                    modalOverlay.classList.remove('active');

                    // Mostrar mensaje de error
                    alert('Error al procesar la solicitud');
                });
        });
    }
}); 