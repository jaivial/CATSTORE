/**
 * JavaScript para autenticación
 * Cat Store - Tienda de Gatos
 */

document.addEventListener('DOMContentLoaded', function () {
    // Referencias a elementos del DOM
    const loginTab = document.getElementById('login-tab');
    const registerTab = document.getElementById('register-tab');
    const loginContent = document.getElementById('login-content');
    const registerContent = document.getElementById('register-content');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const loginMessage = document.getElementById('login-message');
    const registerMessage = document.getElementById('register-message');

    // Cambiar entre tabs de login y registro
    loginTab.addEventListener('click', function (e) {
        e.preventDefault();
        loginTab.classList.add('active');
        registerTab.classList.remove('active');
        loginContent.classList.remove('hidden');
        registerContent.classList.add('hidden');
    });

    registerTab.addEventListener('click', function (e) {
        e.preventDefault();
        registerTab.classList.add('active');
        loginTab.classList.remove('active');
        registerContent.classList.remove('hidden');
        loginContent.classList.add('hidden');
    });

    // Procesar formulario de login
    loginForm.addEventListener('submit', function (e) {
        e.preventDefault();

        // Mostrar mensaje de carga
        loginMessage.textContent = 'Procesando...';
        loginMessage.className = 'form-message info';

        // Obtener datos del formulario
        const formData = new FormData(loginForm);

        // Enviar solicitud al servidor
        fetch('/controllers/AuthController.php?action=login', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mostrar mensaje de éxito
                    loginMessage.textContent = data.message;
                    loginMessage.className = 'form-message success';

                    // Redirigir después de un breve retraso
                    setTimeout(function () {
                        window.location.href = data.redirect;
                    }, 1000);
                } else {
                    // Mostrar mensaje de error
                    loginMessage.textContent = data.message;
                    loginMessage.className = 'form-message error';
                }
            })
            .catch(error => {
                // Mostrar mensaje de error
                loginMessage.textContent = 'Error al procesar la solicitud';
                loginMessage.className = 'form-message error';
                console.error('Error:', error);
            });
    });

    // Procesar formulario de registro
    registerForm.addEventListener('submit', function (e) {
        e.preventDefault();

        // Mostrar mensaje de carga
        registerMessage.textContent = 'Procesando...';
        registerMessage.className = 'form-message info';

        // Obtener datos del formulario
        const formData = new FormData(registerForm);

        // Enviar solicitud al servidor
        fetch('/controllers/AuthController.php?action=register', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mostrar mensaje de éxito
                    registerMessage.textContent = data.message;
                    registerMessage.className = 'form-message success';

                    // Redirigir después de un breve retraso
                    setTimeout(function () {
                        window.location.href = data.redirect;
                    }, 1000);
                } else {
                    // Mostrar mensaje de error
                    registerMessage.textContent = data.message;
                    registerMessage.className = 'form-message error';
                }
            })
            .catch(error => {
                // Mostrar mensaje de error
                registerMessage.textContent = 'Error al procesar la solicitud';
                registerMessage.className = 'form-message error';
                console.error('Error:', error);
            });
    });
}); 