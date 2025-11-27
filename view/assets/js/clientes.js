/**
 * Archivo: view/assets/js/clientes.js
 * Descripción: Gestión completa de clientes con validaciones (Ecuador),
 * paginación, búsqueda en servidor y modal.
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
    const inpCedula = document.getElementById('cedula');
    const inpNombre = document.getElementById('nombre');
    const inpTelefono = document.getElementById('telefono');
    
    // Contenedores de error
    const errorCedula = document.getElementById('errorCedula');
    const errorTelefono = document.getElementById('errorTelefono');

    // Botones del Modal
    const btnNuevo = document.getElementById('btnNuevoCliente');
    const btnCerrar = document.getElementById('closeModal');
    const btnCancelar = document.getElementById('btnCancelar');

    // --- 2. VARIABLES DE ESTADO ---
    let paginaActual = 1;
    let busquedaActual = '';
    let timeoutBusqueda; // Para el debounce del buscador

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

    // --- 4. ALGORITMOS DE VALIDACIÓN (ECUADOR) ---

    function validarCedulaEcuador(cedula) {
        if (cedula.length !== 10) return "Debe tener 10 dígitos.";
        
        const digitoRegion = parseInt(cedula.substring(0, 2));
        if (digitoRegion < 1 || digitoRegion > 24) return "Código de provincia inválido.";
        
        const tercerDigito = parseInt(cedula.substring(2, 3));
        if (tercerDigito >= 6) return "Tercer dígito inválido (Solo Personas Naturales).";

        const coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        const verificador = parseInt(cedula.substring(9, 10));
        let suma = 0;

        for (let i = 0; i < 9; i++) {
            let valor = parseInt(cedula.substring(i, i + 1)) * coeficientes[i];
            if (valor >= 10) valor -= 9;
            suma += valor;
        }

        const digitoCalculado = (suma % 10 === 0) ? 0 : 10 - (suma % 10);
        return (digitoCalculado === verificador) ? true : "Cédula inválida (Dígito verificador incorrecto).";
    }

    function validarTelefonoEcuador(telefono) {
        if (telefono.length !== 10) return "Debe tener 10 dígitos.";
        if (!telefono.startsWith('09')) return "Debe empezar con '09'.";
        return true;
    }

    // --- 5. LOGICA DE PAGINACIÓN Y CARGA ---

    cargarClientes(); // Carga inicial

    function cargarClientes(pagina = 1) {
        paginaActual = pagina;
        const url = `../../controller/ClienteController.php?action=listar&pagina=${pagina}&busqueda=${encodeURIComponent(busquedaActual)}`;
        
        // Efecto de carga
        tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-gray-500">Cargando...</td></tr>`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderizarTabla(data.data);
                    renderizarPaginacion(data.pagination);
                } else {
                    tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-red-500">${data.message}</td></tr>`;
                }
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-red-500">Error de conexión.</td></tr>`;
            });
    }

    function renderizarTabla(lista) {
        tbody.innerHTML = '';
        if (!lista || lista.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-gray-500">No se encontraron resultados.</td></tr>`;
            return;
        }

        lista.forEach(c => {
            // Diseño Responsivo: Card en Móvil / Tabla en Desktop
            tbody.innerHTML += `
                <tr class="bg-white border md:border-b border-gray-200 block md:table-row rounded-xl shadow-sm md:shadow-none mb-4 md:mb-0 hover:bg-orange-50 transition">
                    
                    <td class="p-4 md:py-3 md:px-6 block md:table-cell border-b md:border-none bg-gray-50 md:bg-transparent">
                        <span class="md:hidden text-xs font-bold text-gray-400 uppercase mb-1 block">Cédula</span>
                        <span class="font-mono font-bold text-gray-900">${sanitizeHTML(c.cedula)}</span>
                    </td>

                    <td class="p-4 md:py-3 md:px-6 block md:table-cell border-b md:border-none">
                        <span class="md:hidden text-xs font-bold text-gray-400 uppercase mb-1 block">Cliente</span>
                        <span class="font-medium text-gray-800 uppercase">${sanitizeHTML(c.nombre)}</span>
                    </td>

                    <td class="p-4 md:py-3 md:px-6 block md:table-cell border-b md:border-none">
                        <span class="md:hidden text-xs font-bold text-gray-400 uppercase mb-1 block">Teléfono</span>
                        <span class="text-gray-600"><i class="fas fa-phone-alt text-orange-400 mr-2"></i>${sanitizeHTML(c.telefono)}</span>
                    </td>

                    <td class="p-4 md:py-3 md:px-6 block md:table-cell text-center">
                        <div class="flex md:justify-center justify-end gap-3">
                            <button onclick='editarCliente(${JSON.stringify(c)})' class="bg-amber-100 text-amber-600 hover:bg-amber-200 px-3 py-2 rounded-lg transition shadow-sm flex items-center gap-2" title="Editar">
                                <i class="fas fa-edit"></i> <span class="md:hidden text-sm font-bold">Editar</span>
                            </button>
                            <button onclick="eliminarCliente(${c.id_cliente})" class="bg-red-100 text-red-600 hover:bg-red-200 px-3 py-2 rounded-lg transition shadow-sm flex items-center gap-2" title="Eliminar">
                                <i class="fas fa-trash-alt"></i> <span class="md:hidden text-sm font-bold">Eliminar</span>
                            </button>
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

        if (totalPaginas <= 1) return; // No mostrar paginación si solo hay 1 página

        // Botón Anterior
        const btnPrev = document.createElement('button');
        btnPrev.className = `px-3 py-1 border rounded transition ${current === 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white hover:bg-gray-100 text-gray-700'}`;
        btnPrev.innerHTML = '<i class="fas fa-chevron-left"></i>';
        btnPrev.disabled = current === 1;
        btnPrev.onclick = () => cargarClientes(current - 1);
        paginacionContainer.appendChild(btnPrev);

        // Números de Página
        // Lógica simple: mostrar todas las páginas (se puede mejorar para rangos grandes)
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

    // Cédula: Validación dinámica
    inpCedula.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, ''); // Solo números
        if (!errorCedula.classList.contains('hidden')) {
            limpiarError(inpCedula, errorCedula);
        }
    });

    inpCedula.addEventListener('blur', function() {
        if (this.value === "") return;
        const resultado = validarCedulaEcuador(this.value);
        if (resultado !== true) {
            mostrarError(inpCedula, errorCedula, resultado);
        } else {
            limpiarError(inpCedula, errorCedula);
        }
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
            cargarClientes(1); // Siempre volver a página 1 al buscar
        }, 300); // Esperar 300ms antes de enviar la petición
    });

    // --- 8. ENVÍO DEL FORMULARIO (GUARDAR/EDITAR) ---
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validación final antes de enviar
        const resCedula = validarCedulaEcuador(inpCedula.value);
        const resTelefono = validarTelefonoEcuador(inpTelefono.value);

        let hayErrores = false;

        if (resCedula !== true) {
            mostrarError(inpCedula, errorCedula, resCedula);
            hayErrores = true;
        }
        if (resTelefono !== true) {
            mostrarError(inpTelefono, errorTelefono, resTelefono);
            hayErrores = true;
        }

        if (hayErrores) return; // Detener si hay errores

        const formData = new FormData(form);
        if (!formData.get('action')) formData.set('action', 'agregar');

        fetch('../../controller/ClienteController.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    cerrarModal();
                    // Al guardar nuevo, vamos a la página 1 para ver el registro reciente
                    // Al editar, mantenemos la página actual (opcional, aquí reseteamos a 1 por simplicidad)
                    cargarClientes(1);
                    // Opcional: Mostrar Toast/Alerta de éxito
                } else {
                    // Manejo de errores del servidor (ej. duplicados)
                    if (data.message.toLowerCase().includes('cédula')) {
                         mostrarError(inpCedula, errorCedula, data.message);
                    } else {
                         alert(data.message);
                    }
                }
            })
            .catch(err => alert("Error al procesar la solicitud."));
    });

    // --- 9. FUNCIONES GLOBALES Y MODAL ---

    window.editarCliente = function(cliente) {
        document.getElementById('modalTitle').textContent = 'Editar Cliente';
        document.getElementById('action').value = 'actualizar';
        document.getElementById('id_cliente').value = cliente.id_cliente;
        
        inpCedula.value = cliente.cedula;
        inpNombre.value = cliente.nombre;
        inpTelefono.value = cliente.telefono;
        
        // Limpiar errores previos
        limpiarError(inpCedula, errorCedula);
        limpiarError(inpTelefono, errorTelefono);
        
        modal.classList.remove('hidden');
    };

    window.eliminarCliente = function(id) {
        if (confirm('¿Estás seguro de eliminar este cliente?')) {
            const formData = new FormData();
            formData.append('action', 'eliminar');
            formData.append('id_cliente', id);

            fetch('../../controller/ClienteController.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        cargarClientes(paginaActual); // Recargar página actual
                    } else {
                        alert(data.message);
                    }
                });
        }
    };

    function abrirModal() {
        form.reset();
        limpiarError(inpCedula, errorCedula);
        limpiarError(inpTelefono, errorTelefono);
        
        document.getElementById('modalTitle').textContent = 'Nuevo Cliente';
        document.getElementById('action').value = 'agregar';
        document.getElementById('id_cliente').value = '';
        
        modal.classList.remove('hidden');
    }

    function cerrarModal() {
        modal.classList.add('hidden');
    }

    // Listeners del modal
    btnNuevo.addEventListener('click', abrirModal);
    btnCerrar.addEventListener('click', cerrarModal);
    btnCancelar.addEventListener('click', cerrarModal);
});