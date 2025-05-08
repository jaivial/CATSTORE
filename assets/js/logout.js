/**
 * JavaScript para gestión del cierre de sesión
 * Cat Store - Tienda de Gatos
 */

document.addEventListener('DOMContentLoaded', function () {
    // Buscar todos los enlaces de logout
    const logoutLinks = document.querySelectorAll('.logout-link');

    // Añadir evento a cada enlace de logout
    logoutLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();

            // Realizar la petición de logout
            fetch('/controllers/AuthController.php?action=logout')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Redireccionar a la página indicada en la respuesta
                        window.location.href = data.redirect;
                    } else {
                        // En caso de error, mostrar mensaje y redireccionar a inicio
                        console.error('Error al cerrar sesión:', data.message);
                        window.location.href = '/views/auth/login.php';
                    }
                })
                .catch(error => {
                    console.error('Error al procesar la solicitud:', error);
                    // En caso de error, redireccionar a inicio de todos modos
                    window.location.href = '/views/auth/login.php';
                });
        });
    });
}); 