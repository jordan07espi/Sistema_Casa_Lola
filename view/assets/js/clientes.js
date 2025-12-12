/**
 * Archivo: view/assets/js/clientes.js
 * Descripción: Gestión de clientes (Nombre y Teléfono).
 * Actualizado: Lógica para listar inactivos y permitir reactivación.
 */

document.addEventListener('DOMContentLoaded', function() {
    // --- 1. REFERENCIAS AL DOM ---
    const modal = document.getElementById('clienteModal');
    const form = document.getElementById('clienteForm');
    const tbody = document.getElementById('tablaClientesBody');
    const buscador = document.getElementById('buscadorCliente');
    const paginacionContainer = document.getElementById('paginacionContainer');
    const txtTotal = document.getElementById('txtTotal');

    // Inputs del formulario
    const inpNombre = document.getElementById('nombre');
    const inpTelefono = document.getElementById('telefono');
    
    // Contenedores de error
    const errorTelefono = document.getElementById('errorTelefono');

    // Botones del Modal
    const btnNuevo = document.getElementById('btnNuevoCliente');
    const btnCerrar = document.getElementById('closeModal');
    const btnCancelar = document.getElementById('btnCancelar');

    // --- 2. VARIABLES DE ESTADO ---
    let paginaActual = 1;
    let busquedaActual = '';
    let timeoutBusqueda; 

    // --- 3. FUNCIONES DE UI Y VALIDACIÓN VISUAL ---

    function mostrarError(input, divError, mensaje) {
        divError.textContent = mensaje;
        divError.classList.remove('hidden');
        input.classList.add('border-red-500', 'bg-red-50');
        input.classList.remove('border-gray-300', 'focus:ring-orange-500');
    }

    function limpiarError(input, divError) {
        divError.classList.add('hidden');
        input.classList.remove('border-red-500', 'bg-red-50');
        input.classList.add('border-gray-300', 'focus:ring-orange-500');
    }

    // --- 4. ALGORITMOS DE VALIDACIÓN ---

    function validarTelefonoEcuador(telefono) {
        if (telefono.length !== 10) return "Debe tener 10 dígitos.";
        if (!telefono.startsWith('09')) return "Debe empezar con '09'.";
        return true;
    }

    // --- 5. LÓGICA DE PAGINACIÓN Y CARGA ---

    cargarClientes(); 

    function cargarClientes(pagina = 1) {
        paginaActual = pagina;
        const url = `../../controller/ClienteController.php?action=listar&pagina=${pagina}&busqueda=${encodeURIComponent(busquedaActual)}`;
        
        tbody.innerHTML = `<tr><td colspan="3" class="text-center py-4 text-gray-500">Cargando...</td></tr>`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderizarTabla(data.data);
                    renderizarPaginacion(data.pagination);
                } else {
                    tbody.innerHTML = `<tr><td colspan="3" class="text-center py-4 text-red-500">${data.message}</td></tr>`;
                }
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = `<tr><td colspan="3" class="text-center py-4 text-red-500">Error de conexión.</td></tr>`;
            });
    }

    function renderizarTabla(lista) {
        tbody.innerHTML = '';
        if (!lista || lista.length === 0) {
            tbody.innerHTML = `<tr><td colspan="3" class="text-center py-4 text-gray-500">No se encontraron resultados.</td></tr>`;
            return;
        }

        lista.forEach(c => {
            // Lógica visual para clientes inactivos
            // Comparamos con 1 (activo) o 0 (inactivo). 
            // Usamos == para que funcione si viene como string "1" o número 1
            const esActivo = (c.activo == 1); 
            
            // Estilos: Si es inactivo, fondo gris, opacidad y sin efecto hover naranja
            const rowClass = esActivo 
                ? 'bg-white hover:bg-orange-50' 
                : 'bg-gray-100 opacity-75';
            
            // Badge visual "INACTIVO" junto al nombre
            const badgeEstado = esActivo
                ? ''
                : '<span class="ml-2 px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-600 border border-red-200">INACTIVO</span>';

            // Botón dinámico: Desactivar (Rojo/Gris) o Reactivar (Verde)
            let btnAccion = '';
            
            if (esActivo) {
                // Botón para DESACTIVAR
                btnAccion = `
                    <button onclick="desactivarCliente(${c.id_cliente})" class="bg-gray-100 text-gray-600 hover:bg-red-100 hover:text-red-600 px-3 py-2 rounded-lg transition shadow-sm flex items-center gap-2 border border-gray-200" title="Desactivar Cliente">
                        <i class="fas fa-user-slash"></i> <span class="md:hidden text-sm font-bold">Desactivar</span>
                    </button>`;
            } else {
                // Botón para REACTIVAR
                btnAccion = `
                    <button onclick="activarCliente(${c.id_cliente})" class="bg-green-100 text-green-600 hover:bg-green-200 px-3 py-2 rounded-lg transition shadow-sm flex items-center gap-2 border border-green-200" title="Reactivar Cliente">
                        <i class="fas fa-user-check"></i> <span class="md:hidden text-sm font-bold">Reactivar</span>
                    </button>`;
            }

            // Renderizado de la fila
            tbody.innerHTML += `
                <tr class="${rowClass} border md:border-b border-gray-200 block md:table-row rounded-xl shadow-sm md:shadow-none mb-4 md:mb-0 transition">
                    
                    <td class="p-4 md:py-3 md:px-6 block md:table-cell border-b md:border-none">
                        <span class="md:hidden text-xs font-bold text-gray-400 uppercase mb-1 block">Cliente</span>
                        <div class="flex flex-col">
                            <span class="font-bold text-gray-800 uppercase text-lg md:text-base">
                                ${sanitizeHTML(c.nombre)}
                                ${badgeEstado}
                            </span>
                        </div>
                    </td>

                    <td class="p-4 md:py-3 md:px-6 block md:table-cell border-b md:border-none">
                        <span class="md:hidden text-xs font-bold text-gray-400 uppercase mb-1 block">Teléfono</span>
                        <span class="text-gray-600 font-mono font-medium"><i class="fas fa-phone-alt text-orange-400 mr-2"></i>${sanitizeHTML(c.telefono)}</span>
                    </td>

                    <td class="p-4 md:py-3 md:px-6 block md:table-cell text-center">
                        <div class="flex md:justify-center justify-end gap-3">
                            <button onclick='editarCliente(${JSON.stringify(c)})' class="bg-amber-100 text-amber-600 hover:bg-amber-200 px-3 py-2 rounded-lg transition shadow-sm flex items-center gap-2" title="Editar">
                                <i class="fas fa-edit"></i> <span class="md:hidden text-sm font-bold">Editar</span>
                            </button>
                            
                            ${btnAccion}
                        </div>
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
        btnPrev.className = `px-3 py-1 border rounded transition ${current === 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white hover:bg-gray-100 text-gray-700'}`;
        btnPrev.innerHTML = '<i class="fas fa-chevron-left"></i>';
        btnPrev.disabled = current === 1;
        btnPrev.onclick = () => cargarClientes(current - 1);
        paginacionContainer.appendChild(btnPrev);

        // Números de Página
        for (let i = 1; i <= totalPaginas; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.className = `px-3 py-1 border rounded transition ${i === current ? 'bg-orange-600 text-white border-orange-600' : 'bg-white hover:bg-gray-100 text-gray-700'}`;
            btn.onclick = () => cargarClientes(i);
            paginacionContainer.appendChild(btn);
        }

        // Botón Siguiente
        const btnNext = document.createElement('button');
        btnNext.className = `px-3 py-1 border rounded transition ${current === totalPaginas ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white hover:bg-gray-100 text-gray-700'}`;
        btnNext.innerHTML = '<i class="fas fa-chevron-right"></i>';
        btnNext.disabled = current === totalPaginas;
        btnNext.onclick = () => cargarClientes(current + 1);
        paginacionContainer.appendChild(btnNext);
    }

    // --- 6. EVENTOS DE INPUTS (VALIDACIÓN EN TIEMPO REAL) ---

    // Nombre: Mayúsculas automáticas
    inpNombre.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Teléfono: Validación dinámica
    inpTelefono.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (!errorTelefono.classList.contains('hidden')) {
            limpiarError(inpTelefono, errorTelefono);
        }
    });

    inpTelefono.addEventListener('blur', function() {
        if (this.value === "") return;
        const resultado = validarTelefonoEcuador(this.value);
        if (resultado !== true) {
            mostrarError(inpTelefono, errorTelefono, resultado);
        } else {
            limpiarError(inpTelefono, errorTelefono);
        }
    });

    // --- 7. BUSCADOR (DEBOUNCE) ---
    buscador.addEventListener('input', (e) => {
        clearTimeout(timeoutBusqueda);
        timeoutBusqueda = setTimeout(() => {
            busquedaActual = e.target.value;
            cargarClientes(1); 
        }, 300); 
    });

    // --- 8. ENVÍO DEL FORMULARIO (GUARDAR/EDITAR) ---
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validación final antes de enviar
        const resTelefono = validarTelefonoEcuador(inpTelefono.value);
        let hayErrores = false;

        if (resTelefono !== true) {
            mostrarError(inpTelefono, errorTelefono, resTelefono);
            hayErrores = true;
        }

        if (hayErrores) return;

        const formData = new FormData(form);
        if (!formData.get('action')) formData.set('action', 'agregar');

        fetch('../../controller/ClienteController.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    cerrarModal();
                    cargarClientes(1); // Recarga para ver cambios
                } else {
                    alert(data.message);
                }
            })
            .catch(err => alert("Error al procesar la solicitud."));
    });

    // --- 9. FUNCIONES GLOBALES Y MODAL ---

    window.editarCliente = function(cliente) {
        document.getElementById('modalTitle').textContent = 'Editar Cliente';
        document.getElementById('action').value = 'actualizar';
        document.getElementById('id_cliente').value = cliente.id_cliente;
        
        inpNombre.value = cliente.nombre;
        inpTelefono.value = cliente.telefono;
        
        limpiarError(inpTelefono, errorTelefono);
        
        modal.classList.remove('hidden');
    };

    // Función para DESACTIVAR (Soft Delete)
    window.desactivarCliente = function(id) {
        if (confirm('¿Está seguro de DESACTIVAR este cliente?\n\nNo aparecerá en las búsquedas rápidas, pero su historial se mantendrá.')) {
            const formData = new FormData();
            formData.append('action', 'eliminar'); // El backend lo marca como activo=0
            formData.append('id_cliente', id);

            fetch('../../controller/ClienteController.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        cargarClientes(paginaActual); 
                    } else {
                        alert(data.message);
                    }
                })
                .catch(err => alert("Error de conexión al desactivar."));
        }
    };

    // Función para REACTIVAR
    window.activarCliente = function(id) {
        if (confirm('¿Desea REACTIVAR este cliente? Volverá a aparecer en las búsquedas de pedidos.')) {
            const formData = new FormData();
            formData.append('action', 'activar'); // Requiere soporte en Controller
            formData.append('id_cliente', id);

            fetch('../../controller/ClienteController.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        cargarClientes(paginaActual); 
                    } else {
                        alert(data.message);
                    }
                })
                .catch(err => alert("Error de conexión al activar."));
        }
    };

    function abrirModal() {
        form.reset();
        limpiarError(inpTelefono, errorTelefono);

        document.getElementById('modalTitle').textContent = 'Nuevo Cliente';
        document.getElementById('action').value = 'agregar';
        document.getElementById('id_cliente').value = '';
        
        modal.classList.remove('hidden');
    }

    function cerrarModal() {
        modal.classList.add('hidden');
    }

    if(btnNuevo) btnNuevo.addEventListener('click', abrirModal);
    if(btnCerrar) btnCerrar.addEventListener('click', cerrarModal);
    if(btnCancelar) btnCancelar.addEventListener('click', cerrarModal);
});