/**
 * Sanitiza una cadena de texto para prevenir ataques XSS al usar innerHTML.
 * Reemplaza los caracteres HTML peligrosos con sus equivalentes seguros.
 * @param {string | number | null} str La cadena a sanitizar.
 * @returns {string} La cadena sanitizada.
 */
function sanitizeHTML(str) {
    if (str === null || typeof str === 'undefined') {
        return '';
    }
    const temp = document.createElement('div');
    temp.textContent = str.toString();
    return temp.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {

    // --- LÓGICA PARA EL MENÚ MÓVIL ---
    const btnMenuMovil = document.getElementById('btnMenuMovil');
    const menuMovil = document.getElementById('menuMovil');

    if (btnMenuMovil) {
        btnMenuMovil.addEventListener('click', () => {
            menuMovil.classList.toggle('hidden');
        });
    }

    // Carga inicial
    // cargarDatosGlobales();  <--- ELIMINA O COMENTA ESTA LÍNEA (No existe la función)
});