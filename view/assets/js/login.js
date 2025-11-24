// Archivo: views/assets/js/login.js

// Nos aseguramos de que el DOM esté completamente cargado antes de ejecutar el script
document.addEventListener('DOMContentLoaded', function() {
    
    const loginForm = document.getElementById('loginForm');

    // Verificamos que el formulario exista en la página actual para evitar errores en otras páginas
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            // Prevenimos el envío tradicional del formulario
            event.preventDefault();

            const formData = new FormData(loginForm);
            const errorMessageDiv = document.getElementById('errorMessage');

            // Limpiamos errores previos
            errorMessageDiv.textContent = '';

            // Usamos fetch para enviar los datos al controlador
            fetch('controller/LoginController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Primero, verificamos si la respuesta del servidor es OK (código 200)
                if (!response.ok) {
                    throw new Error('Error en el servidor: ' + response.statusText);
                }
                return response.json(); // Si es OK, intentamos convertirla a JSON
            })
            .then(data => {
                if (data.success) {
                    // Si el login es exitoso, redirigimos al dashboard
                    window.location.href =  data.redirect;
                } else {
                    // Si hay un error lógico (ej. contraseña incorrecta), mostramos el mensaje
                    errorMessageDiv.textContent = data.message || 'Ocurrió un error inesperado.';
                }
            })
            .catch(error => {
                // Este bloque se activa si hay un error de red o si el JSON es inválido
                console.error('Error en la petición fetch:', error);
                errorMessageDiv.textContent = 'No se pudo conectar o la respuesta no es válida. Revisa la consola (F12).';
            });
        });
    }
}); 