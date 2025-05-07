/**
 * Profile JavaScript
 * Handles client-side functionality for the user profile
 */

document.addEventListener('DOMContentLoaded', function () {
    // Password validation
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const passwordForm = document.querySelector('form[name="change_password"]');

    if (passwordForm) {
        passwordForm.addEventListener('submit', function (event) {
            if (newPasswordInput.value !== confirmPasswordInput.value) {
                event.preventDefault();
                showAlert('Las contraseñas no coinciden', 'danger');
                return false;
            }

            if (newPasswordInput.value.length < 6) {
                event.preventDefault();
                showAlert('La contraseña debe tener al menos 6 caracteres', 'danger');
                return false;
            }

            return true;
        });
    }

    // Real-time password match validation
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function () {
            validatePasswordMatch();
        });
    }

    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function () {
            validatePasswordMatch();
        });
    }

    // Form validation for profile update
    const profileForm = document.querySelector('form[name="update_profile"]');

    if (profileForm) {
        profileForm.addEventListener('submit', function (event) {
            const nombreInput = document.getElementById('nombre');
            const apellidoInput = document.getElementById('apellido');
            const emailInput = document.getElementById('email');
            let isValid = true;

            // Validate name
            if (nombreInput.value.trim().length < 2) {
                isValid = false;
                showInputError(nombreInput, 'El nombre debe tener al menos 2 caracteres');
            } else {
                clearInputError(nombreInput);
            }

            // Validate last name
            if (apellidoInput.value.trim().length < 2) {
                isValid = false;
                showInputError(apellidoInput, 'El apellido debe tener al menos 2 caracteres');
            } else {
                clearInputError(apellidoInput);
            }

            // Validate email
            if (!isValidEmail(emailInput.value)) {
                isValid = false;
                showInputError(emailInput, 'Por favor, introduce un email válido');
            } else {
                clearInputError(emailInput);
            }

            if (!isValid) {
                event.preventDefault();
                return false;
            }

            return true;
        });
    }

    // Initialize tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Initialize popovers if Bootstrap is available
    if (typeof bootstrap !== 'undefined' && bootstrap.Popover) {
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }
});

/**
 * Validate if passwords match and show feedback
 */
function validatePasswordMatch() {
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');

    if (!newPasswordInput || !confirmPasswordInput || !confirmPasswordInput.value) {
        return;
    }

    if (newPasswordInput.value !== confirmPasswordInput.value) {
        confirmPasswordInput.classList.add('is-invalid');
        confirmPasswordInput.classList.remove('is-valid');

        // Add or update the feedback message
        let feedbackElement = document.getElementById('password-match-feedback');
        if (!feedbackElement) {
            feedbackElement = document.createElement('div');
            feedbackElement.id = 'password-match-feedback';
            feedbackElement.className = 'invalid-feedback';
            confirmPasswordInput.parentNode.appendChild(feedbackElement);
        }
        feedbackElement.textContent = 'Las contraseñas no coinciden';
    } else {
        confirmPasswordInput.classList.remove('is-invalid');
        confirmPasswordInput.classList.add('is-valid');

        // Add or update the feedback message
        let feedbackElement = document.getElementById('password-match-feedback');
        if (!feedbackElement) {
            feedbackElement = document.createElement('div');
            feedbackElement.id = 'password-match-feedback';
            feedbackElement.className = 'valid-feedback';
            confirmPasswordInput.parentNode.appendChild(feedbackElement);
        }
        feedbackElement.textContent = 'Las contraseñas coinciden';
    }
}

/**
 * Show an alert message
 * @param {string} message Message to display
 * @param {string} type Alert type (success, danger, warning, info)
 */
function showAlert(message, type = 'info') {
    const alertContainer = document.createElement('div');
    alertContainer.className = `alert alert-${type} alert-dismissible fade show shadow-sm border-left-${type} animate__animated animate__fadeIn`;
    alertContainer.role = 'alert';

    // Define icon based on alert type
    let icon = 'bi-info-circle-fill';
    if (type === 'success') icon = 'bi-check-circle-fill';
    if (type === 'danger') icon = 'bi-exclamation-triangle-fill';
    if (type === 'warning') icon = 'bi-exclamation-circle-fill';

    const title = type.charAt(0).toUpperCase() + type.slice(1);

    alertContainer.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi ${icon} text-${type} me-3 fs-4"></i>
            <div>
                <strong>${type === 'success' ? '¡Éxito!' : type === 'danger' ? '¡Error!' : type === 'warning' ? '¡Advertencia!' : 'Información'}</strong>
                <p class="mb-0">${message}</p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    // Find a suitable container for the alert
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(alertContainer, container.firstChild);

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertContainer.parentNode) {
            alertContainer.classList.remove('show');
            setTimeout(() => {
                if (alertContainer.parentNode) {
                    alertContainer.parentNode.removeChild(alertContainer);
                }
            }, 150);
        }
    }, 5000);
}

/**
 * Show error message for an input field
 * @param {HTMLElement} inputElement The input element
 * @param {string} message Error message
 */
function showInputError(inputElement, message) {
    inputElement.classList.add('is-invalid');
    inputElement.classList.remove('is-valid');

    // Add or update the feedback message
    let feedbackElement = inputElement.nextElementSibling;
    if (!feedbackElement || !feedbackElement.classList.contains('invalid-feedback')) {
        feedbackElement = document.createElement('div');
        feedbackElement.className = 'invalid-feedback';
        inputElement.parentNode.insertBefore(feedbackElement, inputElement.nextSibling);
    }
    feedbackElement.textContent = message;
}

/**
 * Clear error message for an input field
 * @param {HTMLElement} inputElement The input element
 */
function clearInputError(inputElement) {
    inputElement.classList.remove('is-invalid');
    inputElement.classList.add('is-valid');

    // Remove any existing feedback
    const feedbackElement = inputElement.nextElementSibling;
    if (feedbackElement && (feedbackElement.classList.contains('invalid-feedback') || feedbackElement.classList.contains('valid-feedback'))) {
        feedbackElement.textContent = '';
    }
}

/**
 * Validate email format
 * @param {string} email Email to validate
 * @return {boolean} True if valid, false otherwise
 */
function isValidEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
} 