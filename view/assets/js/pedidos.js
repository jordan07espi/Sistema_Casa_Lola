/**
 * Archivo: view/assets/js/pedidos.js
 * Versión: Multi-Tillos + Día en Español
 */
document.addEventListener('DOMContentLoaded', function() {
    
    // Referencias
    const tbody = document.getElementById('tablaPedidosBody');
    const buscador = document.getElementById('buscadorPedido');
    const filtroEstado = document.getElementById('filtroEstado');
    const paginacionContainer = document.getElementById('paginacionContainer');
    const txtTotal = document.getElementById('txtTotal');

    let paginaActual = 1;
    let busquedaActual = '';
    let estadoActual = '';
    let timeoutBusqueda;

    cargarPedidos();

    function cargarPedidos(pagina = 1) {
        paginaActual = pagina;
        const url = `../../controller/PedidoController.php?action=listar&pagina=${pagina}&busqueda=${encodeURIComponent(busquedaActual)}&estado=${estadoActual}`;

        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-gray-500">Cargando...</td></tr>`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderizarTabla(data.data);
                    renderizarPaginacion(data.pagination);
                } else {
                    tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-red-500">${data.message}</td></tr>`;
                }
            })
            .catch(err => console.error(err));
    }

    function renderizarTabla(lista) {
        tbody.innerHTML = '';
        if (!lista || lista.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-gray-500">No se encontraron pedidos.</td></tr>`;
            return;
        }

        lista.forEach(p => {
            // --- Lógica de Colores y Estado ---
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

            // --- Lógica de Tillos ---
            let htmlTillos = `<div class="font-black text-gray-800 text-xl md:text-lg">#${sanitizeHTML(p.codigo_pedido)}</div>`;
            
            if (p.tillos_secundarios) {
                const extras = p.tillos_secundarios.split(',');
                htmlTillos += `<div class="mt-2 md:mt-1 flex flex-wrap gap-2 md:gap-1">`;
                extras.forEach(code => {
                    htmlTillos += `<span class="bg-gray-200 text-gray-600 text-xs px-2 py-1 md:py-0.5 rounded font-mono border border-gray-300">#${sanitizeHTML(code)}</span>`;
                });
                htmlTillos += `</div>`;
            }

            // --- Lógica de Día en Español ---
            const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            const fechaParts = p.fecha_entrega.split('-'); 
            const fechaObj = new Date(fechaParts[0], fechaParts[1] - 1, fechaParts[2]); 
            const nombreDia = diasSemana[fechaObj.getDay()];

            // --- RENDERIZADO RESPONSIVE (CARD vs TABLE) ---
            tbody.innerHTML += `
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
                        <span class="md:hidden text-xs font-bold text-gray-400 uppercase mb-1 block">Fecha de Entrega</span>
                        <div class="flex md:block items-center gap-3">
                            <div class="text-base md:text-sm font-semibold text-gray-700">${p.fecha_entrega}</div>
                            <div class="text-xs md:text-[10px] font-bold text-orange-600 uppercase tracking-wide md:mb-1 bg-orange-50 md:bg-transparent px-2 md:px-0 rounded">${nombreDia}</div>
                        </div>
                        <div class="text-sm md:text-xs text-gray-400 mt-1"><i class="far fa-clock"></i> ${p.hora_entrega}</div>
                    </td>
                    
                    <td class="px-5 pb-2 md:py-3 md:px-6 align-top block md:table-cell">
                        <span class="md:hidden text-xs font-bold text-gray-400 uppercase mb-1 block">Total a Pagar</span>
                        <div class="font-black text-gray-800 text-xl md:text-base">$${parseFloat(p.total).toFixed(2)}</div>
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
    }
    
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
        btnPrev.onclick = () => cargarPedidos(current - 1);
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

    // Buscador con Debounce
    buscador.addEventListener('input', (e) => {
        clearTimeout(timeoutBusqueda);
        timeoutBusqueda = setTimeout(() => {
            busquedaActual = e.target.value;
            cargarPedidos(1);
        }, 300);
    });

    // Filtro por Estado
    filtroEstado.addEventListener('change', (e) => {
        estadoActual = e.target.value;
        cargarPedidos(1);
    });
});