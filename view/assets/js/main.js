// Archivo: view/assets/js/main.js

function sanitizeHTML(str) {
    if (str === null || typeof str === 'undefined') {
        return '';
    }
    const temp = document.createElement('div');
    temp.textContent = str.toString();
    return temp.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {

    // --- EL CÓDIGO DEL MENÚ MÓVIL FUE MOVIDO A HEADER.PHP ---
    // (Puedes borrar el bloque anterior que tenías aquí sobre btnMenuMovil)
    
});