/**
 * Archivo: view/assets/js/pedidos.js
 * Versión: Con Auto-Recarga (AJAX Polling)
 */
document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. REFERENCIAS AL DOM ---
    const tbody = document.getElementById('tablaPedidosBody');
    const buscador = document.getElementById('buscadorPedido');
    const filtroEstado = document.getElementById('filtroEstado');
    const paginacionContainer = document.getElementById('paginacionContainer');
    const txtTotal = document.getElementById('txtTotal');

    // Referencias Nuevas (Rango de Fechas)
    const inpFechaDesde = document.getElementById('fechaDesde');
    const inpFechaHasta = document.getElementById('fechaHasta');

    // --- 2. VARIABLES DE ESTADO ---
    let paginaActual = 1;
    let busquedaActual = '';
    let estadoActual = '';
    let fechaDesde = '';
    let fechaHasta = '';
    let timeoutBusqueda;
    let intervaloRecarga; // Variable para controlar el reloj

    // Carga inicial
    cargarPedidos();
    
    // INICIO DEL RELOJ DE AUTO-RECARGA (Cada 5 segundos)
    iniciarAutoRecarga();

    // --- 3. FUNCIÓN DE CARGA ---
    // Agregamos el parámetro 'silencioso' para evitar parpadeos
    function cargarPedidos(pagina = 1, silencioso = false) {
        paginaActual = pagina;
        
        // Construcción de URL con todos los parámetros
        const url = `../../controller/PedidoController.php?action=listar&pagina=${pagina}&busqueda=${encodeURIComponent(busquedaActual)}&estado=${estadoActual}&desde=${fechaDesde}&hasta=${fechaHasta}`;

        // SOLO mostramos "Cargando..." si NO es una recarga automática (silenciosa)
        if (!silencioso) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-gray-500">Cargando...</td></tr>`;
        }

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderizarTabla(data.data);
                    renderizarPaginacion(data.pagination);
                } else {
                    if(!silencioso) { // Solo mostrar error si no es silencioso
                        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-red-500">${data.message}</td></tr>`;
                    }
                }
            })
            .catch(err => console.error(err));
    }

    function iniciarAutoRecarga() {
        // Limpiamos cualquier intervalo previo por seguridad
        if(intervaloRecarga) clearInterval(intervaloRecarga);

        intervaloRecarga = setInterval(() => {
            // Llamamos a cargarPedidos en modo silencioso (true)
            // Mantenemos la página actual para no devolver al usuario a la página 1 si está navegando
            cargarPedidos(paginaActual, true);
        }, 5000); // 5000 ms = 5 segundos
    }

    // --- 4. RENDERIZADO (TARJETA vs TABLA) ---
    function renderizarTabla(lista) {
        // Nota: No limpiamos tbody aquí con innerHTML = '' al inicio para evitar parpadeo blanco,
        // construimos todo el HTML primero.
        
        if (!lista || lista.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-gray-500">No se encontraron pedidos.</td></tr>`;
            return;
        }

        let htmlFinal = '';

        lista.forEach(p => {
            // A. Lógica de Colores para Estado del Pedido
            let badgeColor = '';
            let icon = '';
            if (p.estado === 'Pendiente') {
                badgeColor = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                icon = '<i class="fas fa-clock mr-1"></i>';
            } else if (p.estado === 'Entregado') {
                badgeColor = 'bg-green-100 text-green-800 border-green-200';
                icon = '<i class="fas fa-check-circle mr-1"></i>';
            } else {
                badgeColor = 'bg-red-100 text-red-800 border-red-200';
                icon = '<i class="fas fa-times-circle mr-1"></i>';
            }

            // B. Lógica de Tillos
            let htmlTillos = `<div class="font-black text-gray-800 text-xl md:text-lg">#${sanitizeHTML(p.codigo_pedido)}</div>`;
            if (p.tillos_secundarios) {
                const extras = p.tillos_secundarios.split(',');
                htmlTillos += `<div class="mt-2 md:mt-1 flex flex-wrap gap-2 md:gap-1">`;
                extras.forEach(code => {
                    htmlTillos += `<span class="bg-gray-200 text-gray-600 text-xs px-2 py-1 md:py-0.5 rounded font-mono border border-gray-300">#${sanitizeHTML(code)}</span>`;
                });
                htmlTillos += `</div>`;
            }

            // C. Lógica de Día
            const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            // Fix para compatibilidad Safari/Firefox con fechas: YYYY-MM-DD
            const fechaParts = p.fecha_entrega.split('-'); 
            const fechaObj = new Date(fechaParts[0], fechaParts[1] - 1, fechaParts[2]); 
            const nombreDia = diasSemana[fechaObj.getDay()];

            // D. Lógica de Pago
            const esPagado = (p.pagado == 1);
            const btnPagoClase = esPagado 
                ? 'bg-green-100 text-green-700 border-green-200 hover:bg-green-200' 
                : 'bg-red-100 text-red-700 border-red-200 hover:bg-red-200';
            const iconPago = esPagado ? 'fa-money-bill-wave' : 'fa-hand-holding-usd';
            const textoPago = esPagado ? 'PAGADO' : 'PENDIENTE';
            const nuevoEstadoPago = esPagado ? 0 : 1; 

            htmlFinal += `
                <tr class="bg-white md:hover:bg-orange-50 transition border md:border-b border-gray-200 rounded-xl shadow-md md:shadow-none block md:table-row relative overflow-hidden">
                    <td class="p-5 md:py-3 md:px-6 align-top block md:table-cell border-b md:border-none border-gray-100 bg-gray-50 md:bg-transparent">
                        <span class="md:hidden text-xs font-bold text-gray-400 uppercase mb-1 block">Código Tillo</span>
                        ${htmlTillos}
                        <div class="md:hidden absolute top-4 right-4">
                            <span class="px-3 py-1 rounded-full text-xs border ${badgeColor} font-bold inline-flex items-center">
                                ${icon} ${p.estado}
                            </span>
                        </div>
                    </td>
                    <td class="p-5 md:py-3 md:px-6 align-top block md:table-cell">
                        <span class="md:hidden text-xs font-bold text-gray-400 uppercase mb-1 block">Cliente</span>
                        <div class="font-medium text-gray-900 uppercase text-lg md:text-base">${sanitizeHTML(p.nombre_cliente)}</div>
                        <div class="text-sm md:text-xs text-gray-500 mt-1 flex items-center gap-1">
                            <i class="fas fa-user-edit text-orange-400"></i> ${sanitizeHTML(p.nombre_usuario)}
                        </div>
                    </td>
                    <td class="px-5 pb-2 md:py-3 md:px-6 align-top block md:table-cell">
                        <span class="md:hidden text-xs font-bold text-gray-400 uppercase mb-1 block">Entrega</span>
                        <div class="text-xs font-bold text-orange-600 uppercase tracking-wider mb-1">${nombreDia}</div>
                        <div class="text-xl md:text-lg font-black text-gray-800 leading-none mb-1">
                            ${p.hora_entrega.substring(0, 5)} <span class="text-xs text-gray-400 font-normal">hrs</span>
                        </div>
                        <div class="text-sm md:text-xs text-gray-400 font-medium">
                            <i class="far fa-calendar-alt mr-1 text-[10px]"></i> ${p.fecha_entrega}
                        </div>
                    </td>
                    <td class="px-5 pb-2 md:py-3 md:px-6 align-top block md:table-cell">
                        <span class="md:hidden text-xs font-bold text-gray-400 uppercase mb-1 block">Total y Pago</span>
                        <div class="font-black text-gray-800 text-xl md:text-base mb-1">$${parseFloat(p.total).toFixed(2)}</div>
                        <button onclick="cambiarPago(${p.id_pedido}, ${nuevoEstadoPago})" 
                            class="px-3 py-1 rounded-md text-[10px] md:text-xs font-bold border ${btnPagoClase} transition flex items-center gap-2 cursor-pointer shadow-sm w-fit"
                            title="Clic para cambiar estado de pago">
                            <i class="fas ${iconPago}"></i> ${textoPago}
                        </button>
                    </td>
                    <td class="py-3 px-6 text-center align-top hidden md:table-cell">
                        <span class="px-3 py-1 rounded-full text-xs border ${badgeColor} font-semibold inline-flex items-center">
                            ${icon} ${p.estado}
                        </span>
                    </td>
                    <td class="p-5 md:py-3 md:px-6 text-center align-top block md:table-cell border-t md:border-none border-gray-100 mt-2 md:mt-0">
                        <a href="ver_pedido.php?id=${p.id_pedido}" class="w-full md:w-auto bg-blue-50 md:bg-transparent text-blue-600 hover:text-blue-800 p-3 md:p-2 rounded-lg hover:bg-blue-100 md:hover:bg-blue-50 transition flex items-center justify-center gap-2 font-bold md:font-normal" title="Ver Detalles">
                            <i class="fas fa-eye fa-lg"></i> <span class="md:hidden">Ver Detalles del Pedido</span>
                        </a>
                    </td>
                </tr>
            `;
        });
        
        // Actualizamos el DOM de una sola vez
        tbody.innerHTML = htmlFinal;
    }
    
    // --- 5. RENDERIZADO PAGINACIÓN (Igual que antes) ---
    function renderizarPaginacion(pagination) {
        txtTotal.textContent = pagination.total_registros;
        paginacionContainer.innerHTML = '';
        const totalPaginas = pagination.total_paginas;
        const current = pagination.pagina_actual;

        if (totalPaginas <= 1) return;

        const btnPrev = document.createElement('button');
        btnPrev.className = `px-3 py-1 border rounded transition ${current === 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white hover:bg-gray-100'}`;
        btnPrev.innerHTML = '<i class="fas fa-chevron-left"></i>';
        btnPrev.disabled = current === 1;
        btnPrev.onclick = () => cargarPedidos(current - 1); // Click manual = carga normal (con spinner)
        paginacionContainer.appendChild(btnPrev);

        for (let i = 1; i <= totalPaginas; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.className = `px-3 py-1 border rounded transition ${i === current ? 'bg-orange-600 text-white border-orange-600' : 'bg-white hover:bg-gray-100'}`;
            btn.onclick = () => cargarPedidos(i);
            paginacionContainer.appendChild(btn);
        }

        const btnNext = document.createElement('button');
        btnNext.className = `px-3 py-1 border rounded transition ${current === totalPaginas ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white hover:bg-gray-100'}`;
        btnNext.innerHTML = '<i class="fas fa-chevron-right"></i>';
        btnNext.disabled = current === totalPaginas;
        btnNext.onclick = () => cargarPedidos(current + 1);
        paginacionContainer.appendChild(btnNext);
    }

    // --- 6. EVENT LISTENERS ---
    // En cada evento, reiniciamos el intervalo para que no se cruce con una búsqueda manual

    buscador.addEventListener('input', (e) => {
        clearTimeout(timeoutBusqueda);
        timeoutBusqueda = setTimeout(() => {
            busquedaActual = e.target.value;
            cargarPedidos(1);
            iniciarAutoRecarga(); // Reiniciar reloj
        }, 300);
    });

    filtroEstado.addEventListener('change', (e) => {
        estadoActual = e.target.value;
        cargarPedidos(1);
        iniciarAutoRecarga();
    });

    if(inpFechaDesde) {
        inpFechaDesde.addEventListener('change', (e) => {
            fechaDesde = e.target.value;
            cargarPedidos(1);
            iniciarAutoRecarga();
        });
    }

    if(inpFechaHasta) {
        inpFechaHasta.addEventListener('change', (e) => {
            fechaHasta = e.target.value;
            cargarPedidos(1);
            iniciarAutoRecarga();
        });
    }

    // --- 7. FUNCIÓN GLOBAL: CAMBIAR PAGO ---
    window.cambiarPago = function(id, nuevoEstado) {
        const accion = nuevoEstado === 1 ? 'MARCAR COMO PAGADO' : 'MARCAR COMO NO PAGADO';
        if(!confirm(`¿Desea ${accion} este pedido?`)) return;

        const formData = new FormData();
        formData.append('action', 'cambiar_pago');
        formData.append('id_pedido', id);
        formData.append('pagado', nuevoEstado);

        fetch('../../controller/PedidoController.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    cargarPedidos(paginaActual, true); // Recarga silenciosa tras el cambio
                } else {
                    alert(data.message);
                }
            })
            .catch(err => console.error("Error al cambiar pago:", err));
    };
});