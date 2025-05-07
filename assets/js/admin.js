/**
 * JavaScript para administración
 * Cat Store - Tienda de Gatos
 */

document.addEventListener('DOMContentLoaded', function () {
    // Referencias a elementos del DOM para la página de listado
    const deleteButtons = document.querySelectorAll('.delete-product');
    const deleteModal = document.getElementById('delete-modal');
    const modalOverlay = document.getElementById('modal-overlay');
    const modalClose = document.getElementById('modal-close');
    const cancelDelete = document.getElementById('cancel-delete');
    const confirmDelete = document.getElementById('confirm-delete');
    const productNameSpan = document.getElementById('product-name');

    // Referencias a elementos del DOM para el formulario de producto
    const productForm = document.getElementById('product-form');
    const fileInput = document.getElementById('foto');
    const imagePreview = document.getElementById('image-preview');

    // Mostrar/ocultar modal de confirmación de eliminación
    if (deleteButtons.length > 0 && deleteModal && modalOverlay) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
                const productId = this.getAttribute('data-id');
                const productName = this.getAttribute('data-name');

                // Establecer ID del producto a eliminar
                confirmDelete.setAttribute('data-id', productId);

                // Mostrar nombre del producto en el modal
                if (productNameSpan) {
                    productNameSpan.textContent = productName;
                }

                // Mostrar modal
                deleteModal.classList.add('active');
                modalOverlay.classList.add('active');
            });
        });

        // Cerrar modal al hacer clic en el botón de cerrar
        if (modalClose) {
            modalClose.addEventListener('click', function () {
                deleteModal.classList.remove('active');
                modalOverlay.classList.remove('active');
            });
        }

        // Cerrar modal al hacer clic en el overlay
        if (modalOverlay) {
            modalOverlay.addEventListener('click', function () {
                deleteModal.classList.remove('active');
                modalOverlay.classList.remove('active');
            });
        }

        // Cerrar modal al hacer clic en el botón de cancelar
        if (cancelDelete) {
            cancelDelete.addEventListener('click', function () {
                deleteModal.classList.remove('active');
                modalOverlay.classList.remove('active');
            });
        }

        // Confirmar eliminación
        if (confirmDelete) {
            confirmDelete.addEventListener('click', function () {
                const productId = this.getAttribute('data-id');

                // Mostrar estado de carga
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Eliminando...';
                this.disabled = true;

                // Enviar solicitud al servidor
                fetch('/api/admin.php?action=delete_product', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: productId })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Redirigir a la página de productos con mensaje de éxito
                            window.location.href = '/views/admin/products.php?success=1';
                        } else {
                            // Mostrar mensaje de error
                            alert('Error: ' + data.message);

                            // Restaurar botón
                            this.innerHTML = 'Eliminar';
                            this.disabled = false;

                            // Cerrar modal
                            deleteModal.classList.remove('active');
                            modalOverlay.classList.remove('active');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);

                        // Mostrar mensaje de error
                        alert('Error al procesar la solicitud');

                        // Restaurar botón
                        this.innerHTML = 'Eliminar';
                        this.disabled = false;

                        // Cerrar modal
                        deleteModal.classList.remove('active');
                        modalOverlay.classList.remove('active');
                    });
            });
        }
    }

    // Vista previa de imagen en el formulario de producto
    if (fileInput && imagePreview) {
        fileInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                };

                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    // Validación del formulario de producto
    if (productForm) {
        productForm.addEventListener('submit', function (event) {
            let isValid = true;

            // Validar nombre
            const nombreInput = document.getElementById('nombre');
            if (nombreInput && nombreInput.value.trim() === '') {
                isValid = false;
                showInputError(nombreInput, 'El nombre es obligatorio');
            } else if (nombreInput) {
                clearInputError(nombreInput);
            }

            // Validar tipo
            const tipoInput = document.getElementById('tipo');
            if (tipoInput && tipoInput.value.trim() === '') {
                isValid = false;
                showInputError(tipoInput, 'El tipo es obligatorio');
            } else if (tipoInput) {
                clearInputError(tipoInput);
            }

            // Validar color
            const colorInput = document.getElementById('color');
            if (colorInput && colorInput.value.trim() === '') {
                isValid = false;
                showInputError(colorInput, 'El color es obligatorio');
            } else if (colorInput) {
                clearInputError(colorInput);
            }

            // Validar sexo
            const sexoInput = document.getElementById('sexo');
            if (sexoInput && sexoInput.value === '') {
                isValid = false;
                showInputError(sexoInput, 'El sexo es obligatorio');
            } else if (sexoInput) {
                clearInputError(sexoInput);
            }

            // Validar precio
            const precioInput = document.getElementById('precio');
            if (precioInput) {
                if (precioInput.value === '') {
                    isValid = false;
                    showInputError(precioInput, 'El precio es obligatorio');
                } else if (parseFloat(precioInput.value) <= 0) {
                    isValid = false;
                    showInputError(precioInput, 'El precio debe ser mayor que 0');
                } else {
                    clearInputError(precioInput);
                }
            }

            // Validar imagen
            const fotoInput = document.getElementById('foto');
            if (fotoInput && fotoInput.files.length > 0) {
                const file = fotoInput.files[0];
                const fileSize = file.size / 1024 / 1024; // en MB

                if (fileSize > 2) {
                    isValid = false;
                    showInputError(fotoInput, 'La imagen no debe superar los 2MB');
                } else {
                    clearInputError(fotoInput);
                }
            }

            if (!isValid) {
                event.preventDefault();
            }
        });
    }
});

/**
 * Muestra un mensaje de error para un campo de formulario
 * @param {HTMLElement} inputElement Elemento de entrada
 * @param {string} message Mensaje de error
 */
function showInputError(inputElement, message) {
    inputElement.classList.add('is-invalid');
    inputElement.classList.remove('is-valid');

    // Buscar el elemento de feedback
    const feedbackElement = document.getElementById(inputElement.id + '-error');
    if (feedbackElement) {
        feedbackElement.textContent = message;
    }
}

/**
 * Elimina el mensaje de error de un campo de formulario
 * @param {HTMLElement} inputElement Elemento de entrada
 */
function clearInputError(inputElement) {
    inputElement.classList.remove('is-invalid');
    inputElement.classList.add('is-valid');

    // Buscar el elemento de feedback
    const feedbackElement = document.getElementById(inputElement.id + '-error');
    if (feedbackElement) {
        feedbackElement.textContent = '';
    }
} 