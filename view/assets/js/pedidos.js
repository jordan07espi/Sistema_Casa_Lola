/**
 * Archivo: view/assets/js/pedidos.js
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
            // Definir color del badge según estado
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

            tbody.innerHTML += `
                <tr class="border-b hover:bg-orange-50 transition">
                    <td class="py-3 px-6 font-bold text-gray-800">#${sanitizeHTML(p.codigo_pedido)}</td>
                    <td class="py-3 px-6">
                        <div class="font-medium text-gray-900">${sanitizeHTML(p.nombre_cliente)}</div>
                        <div class="text-xs text-gray-500">Reg. por: ${sanitizeHTML(p.nombre_usuario)}</div>
                    </td>
                    <td class="py-3 px-6">
                        <div class="text-sm">${p.fecha_entrega}</div>
                        <div class="text-xs text-gray-500">${p.hora_entrega}</div>
                    </td>
                    <td class="py-3 px-6 font-bold text-gray-700">$${parseFloat(p.total).toFixed(2)}</td>
                    <td class="py-3 px-6 text-center">
                        <span class="px-3 py-1 rounded-full text-xs border ${badgeColor} font-semibold inline-flex items-center">
                            ${icon} ${p.estado}
                        </span>
                    </td>
                    <td class="py-3 px-6 text-center">
                        <a href="ver_pedido.php?id=${p.id_pedido}" class="text-blue-600 hover:text-blue-800 mx-1" title="Ver Detalles">
                            <i class="fas fa-eye"></i>
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

        // Botón Anterior
        const btnPrev = document.createElement('button');
        btnPrev.className = `px-3 py-1 border rounded transition ${current === 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white hover:bg-gray-100'}`;
        btnPrev.innerHTML = '<i class="fas fa-chevron-left"></i>';
        btnPrev.disabled = current === 1;
        btnPrev.onclick = () => cargarPedidos(current - 1);
        paginacionContainer.appendChild(btnPrev);

        // Números
        for (let i = 1; i <= totalPaginas; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.className = `px-3 py-1 border rounded transition ${i === current ? 'bg-orange-600 text-white border-orange-600' : 'bg-white hover:bg-gray-100'}`;
            btn.onclick = () => cargarPedidos(i);
            paginacionContainer.appendChild(btn);
        }

        // Botón Siguiente
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