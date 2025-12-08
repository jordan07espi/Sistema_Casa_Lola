// Archivo: view/assets/js/cocina.js

document.addEventListener('DOMContentLoaded', function() {
    const contenedor = document.getElementById('contenedorCocina');
    const indicador = document.getElementById('indicadorConexion');
    let pedidosPrevios = new Set();

    // Iniciar conexión SSE
    const evtSource = new EventSource('../../controller/CocinaSSE.php');

    evtSource.onmessage = function(event) {
        const pedidos = JSON.parse(event.data);
        renderizarCocina(pedidos);
    };

    evtSource.onerror = function() {
        console.error("Error en conexión SSE");
        indicador.className = "flex items-center gap-2 px-4 py-2 bg-red-100 text-red-700 rounded-lg font-bold";
        indicador.innerHTML = '<div class="w-3 h-3 bg-red-600 rounded-full"></div> Desconectado';
    };

    evtSource.onopen = function() {
        indicador.className = "flex items-center gap-2 px-4 py-2 bg-green-100 text-green-700 rounded-lg font-bold animate-pulse";
        indicador.innerHTML = '<div class="w-3 h-3 bg-green-600 rounded-full"></div> En Vivo';
    };

    function renderizarCocina(lista) {
        // No borramos todo el HTML de golpe para no perder interacciones si el usuario está a punto de clickear
        // Pero para simplificar y asegurar orden, redibujamos y mantenemos IDs para animaciones
        
        contenedor.innerHTML = '';
        const pedidosActuales = new Set();

        if (lista.length === 0) {
            contenedor.innerHTML = `
                <div class="col-span-full flex flex-col items-center justify-center h-96 text-gray-400 bg-gray-100 rounded-3xl border-4 border-dashed border-gray-300">
                    <i class="fas fa-check-circle fa-5x mb-4 text-green-200"></i>
                    <p class="text-4xl font-bold">¡Todo listo en cocina!</p>
                    <p class="text-xl mt-2">No hay pedidos pendientes de guarnición.</p>
                </div>`;
            return;
        }

        lista.forEach(p => {
            pedidosActuales.add(p.id_pedido);
            
            // Animación de entrada para nuevos
            const esNuevo = !pedidosPrevios.has(p.id_pedido) && pedidosPrevios.size > 0;
            const animacionClase = esNuevo ? 'animate-bounce-in' : '';

            // Formato de Fecha y Hora
            const fechaObj = new Date(p.fecha_entrega + 'T00:00:00');
            const opcionesFecha = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const fechaTexto = fechaObj.toLocaleDateString('es-ES', opcionesFecha);
            const fechaFinal = fechaTexto.charAt(0).toUpperCase() + fechaTexto.slice(1);
            const hora = p.hora_entrega.substring(0, 5);

            // Procesar items
            const items = p.detalle_guarniciones.split('|');
            let itemsHtml = '';
            items.forEach(item => {
                const partes = item.trim().split(' ');
                const cant = partes[0];
                const nombre = partes.slice(1).join(' ');

                itemsHtml += `
                    <div class="flex justify-between items-center bg-orange-50 p-3 rounded-lg border-l-4 border-orange-500 mb-2 last:mb-0">
                        <span class="text-2xl font-bold text-gray-800 uppercase tracking-tight">${nombre}</span>
                        <span class="text-3xl font-black text-orange-700">${cant}</span>
                    </div>
                `;
            });

            // Observaciones
            let obsHtml = '';
            if (p.observaciones) {
                obsHtml = `
                    <div class="mt-4 p-3 bg-yellow-100 border border-yellow-300 rounded-lg flex items-start gap-3">
                        <i class="fas fa-exclamation-triangle text-yellow-600 text-xl mt-1"></i>
                        <p class="text-lg font-bold text-yellow-800 italic leading-tight">"${p.observaciones}"</p>
                    </div>
                `;
            }

            // --- TARJETA ---
            const card = document.createElement('div');
            // Agregamos un ID único al elemento DOM para poder borrarlo individualmente
            card.id = `pedido-card-${p.id_pedido}`;
            card.className = `bg-white rounded-2xl shadow-xl border-2 border-gray-200 overflow-hidden transform transition hover:scale-[1.01] flex flex-col justify-between ${animacionClase}`;
            
            card.innerHTML = `
                <div>
                    <div class="bg-gray-900 text-white p-4 text-center">
                        <p class="text-lg font-medium text-orange-200 uppercase tracking-wide mb-1">${fechaFinal}</p>
                        <div class="flex justify-center items-center gap-3">
                            <i class="far fa-clock text-3xl text-orange-500"></i>
                            <span class="text-5xl font-black tracking-tighter">${hora}</span>
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="flex justify-between items-center mb-6 pb-4 border-b-2 border-dashed border-gray-300">
                            <span class="text-gray-500 font-bold uppercase text-sm">Orden #</span>
                            <span class="text-4xl font-black text-gray-800 bg-gray-100 px-4 py-1 rounded-lg border border-gray-300">
                                ${p.codigo_pedido}
                            </span>
                        </div>

                        <div class="space-y-2">
                            ${itemsHtml}
                        </div>

                        ${obsHtml}
                    </div>
                </div>
                
                <div class="p-4 bg-gray-50 border-t border-gray-200">
                    <button onclick="marcarListo(${p.id_pedido})" 
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-black py-4 rounded-xl shadow-md transition transform active:scale-95 flex justify-center items-center gap-3 text-xl">
                        <i class="fas fa-check-circle fa-lg"></i>
                        ¡PEDIDO LISTO!
                    </button>
                </div>
            `;

            contenedor.appendChild(card);
        });

        pedidosPrevios = pedidosActuales;
    }
});

// --- FUNCIÓN GLOBAL PARA MARCAR COMO LISTO ---
function marcarListo(idPedido) {
    if(!confirm('¿Confirmar que este pedido está completamente cocinado?')) return;

    // Efecto visual inmediato (Optimistic UI)
    const card = document.getElementById(`pedido-card-${idPedido}`);
    if(card) {
        card.style.transition = "all 0.5s ease";
        card.style.transform = "scale(0.9)";
        card.style.opacity = "0";
        setTimeout(() => card.remove(), 500);
    }

    const formData = new FormData();
    formData.append('action', 'marcar_listo');
    formData.append('id_pedido', idPedido);

    fetch('../../controller/CocinaController.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                // Si falla, recargar para mostrar de nuevo (rollback visual básico)
                alert("Error al guardar: " + data.message);
                location.reload();
            }
            // Si funciona, el SSE actualizará la lista en el siguiente ciclo (3 segundos),
            // pero como ya lo borramos visualmente, el usuario no nota el delay.
        })
        .catch(err => console.error("Error:", err));
}

// Estilos CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes bounceIn {
        0% { transform: scale(0.9); opacity: 0; }
        60% { transform: scale(1.05); opacity: 1; }
        100% { transform: scale(1); }
    }
    .animate-bounce-in {
        animation: bounceIn 0.6s cubic-bezier(0.68, -0.55, 0.27, 1.55);
    }
`;
document.head.appendChild(style);