/**
 * Archivo: view/assets/js/nuevo_pedido.js
 * Descripción: Lógica completa para gestión de pedidos, validaciones y facturación/impresión.
 * Actualizado: Manejo robusto de errores de servidor y flujo de impresión.
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ==========================================
    // 1. CONFIGURACIÓN Y REFERENCIAS DEL DOM
    // ==========================================
    const PREFIJO_GLOBAL = document.getElementById('prefijo_global')?.value || "2025_";
    const contenedorTillos = document.getElementById('contenedorTillos');
    const inputsCantidad = document.querySelectorAll('.input-cantidad');
    const formPedido = document.getElementById('formPedido');
    
    // Referencias Búsqueda de Cliente
    const inpClienteBusqueda = document.getElementById('cliente_busqueda');
    const inpIdCliente = document.getElementById('id_cliente_seleccionado');
    const listaClientes = document.getElementById('listaClientes');

    // Referencias Modal Cliente Rápido
    const modalCliente = document.getElementById('clienteModal');
    const btnQuickClient = document.getElementById('btnQuickAddCliente');
    const btnCerrarModal = document.getElementById('closeModal');
    const btnCancelarModal = document.getElementById('btnCancelar');
    const formQuick = document.getElementById('clienteFormQuick');

    // ==========================================
    // 2. GESTIÓN DE TILLOS (ETIQUETAS)
    // ==========================================

    // Escuchar cambios en inputs de cantidad
    inputsCantidad.forEach(input => {
        input.addEventListener('input', generarCamposTillo);
    });

    /**
     * Genera dinámicamente los campos de entrada para los códigos de Tillo
     * basándose en la cantidad ingresada en los productos marcados.
     */
    function generarCamposTillo() {
        // A. Guardar valores actuales para no perderlos al redibujar
        const valoresPrevios = {}; 
        document.querySelectorAll('.input-tillo-dinamico').forEach(inp => {
            if(inp.dataset.idProd && inp.dataset.index) {
                valoresPrevios[`${inp.dataset.idProd}_${inp.dataset.index}`] = inp.value;
            }
        });

        contenedorTillos.innerHTML = ''; 
        let hayTillos = false;

        // B. Recorrer productos y generar inputs
        inputsCantidad.forEach(input => {
            const cantidad = parseInt(input.value) || 0;
            const requiereTillo = input.dataset.tillo === "1"; 
            
            if (requiereTillo && cantidad > 0) {
                hayTillos = true;
                const nombreProd = input.dataset.nombre;
                const idProd = input.name.match(/\d+/)[0]; // Extraer ID de 'productos[ID]'

                // Crear contenedor del grupo
                const grupo = document.createElement('div');
                grupo.className = "mb-3 pb-2 border-b border-gray-200 last:border-0 animate-fade-in";
                grupo.innerHTML = `
                    <h4 class="text-xs font-bold text-orange-700 uppercase mb-2 flex items-center gap-2">
                        <i class="fas fa-utensils"></i> ${nombreProd} (${cantidad})
                    </h4>`;
                
                for (let i = 1; i <= cantidad; i++) {
                    const divInput = document.createElement('div');
                    divInput.className = "flex items-center gap-2 mb-2";
                    
                    // Restaurar valor previo o usar prefijo por defecto
                    const key = `${idProd}_${i}`;
                    const valorInicial = valoresPrevios[key] || PREFIJO_GLOBAL;

                    divInput.innerHTML = `
                        <span class="text-xs text-gray-500 w-6 font-mono">#${i}</span>
                        <div class="relative flex-1">
                            <input type="text" name="tillos_asignados[${idProd}][]" 
                                class="input-tillo-dinamico w-full border-gray-300 rounded focus:ring-orange-500 focus:border-orange-500 px-2 py-1 text-sm font-mono font-bold uppercase transition-colors"
                                value="${valorInicial}"
                                data-prefix="${PREFIJO_GLOBAL}"
                                data-id-prod="${idProd}"
                                data-index="${i}"
                                required>
                            <div class="status-icon absolute right-2 top-1 text-xs"></div>
                        </div>
                    `;
                    grupo.appendChild(divInput);
                }
                contenedorTillos.appendChild(grupo);
            }
        });

        // C. Mensaje si no hay tillos
        if (!hayTillos) {
            contenedorTillos.innerHTML = `
                <div class="flex flex-col items-center justify-center h-40 text-gray-400 border-2 border-dashed border-gray-200 rounded-lg">
                    <i class="fas fa-tag fa-2x mb-2"></i>
                    <p class="text-sm text-center">Seleccione productos (carnes)<br>para asignar etiquetas.</p>
                </div>`;
        } else {
            asignarEventosTillos();
        }
    }

    /**
     * Asigna validaciones y máscaras a los nuevos inputs de tillos
     */
    function asignarEventosTillos() {
        const nuevosInputs = document.querySelectorAll('.input-tillo-dinamico');
        nuevosInputs.forEach(inp => {
            // Máscara para forzar el prefijo
            inp.addEventListener('input', function() {
                const prefix = this.dataset.prefix;
                if (!this.value.startsWith(prefix)) {
                    let sufijo = this.value.replace(prefix, '');
                    if (this.value.length < prefix.length) sufijo = "";
                    this.value = prefix + sufijo;
                }
                this.value = this.value.toUpperCase();
            });

            // Evitar borrar el prefijo con Backspace
            inp.addEventListener('keydown', function(e) {
                const prefix = this.dataset.prefix;
                if ((e.key === 'Backspace' || e.key === 'Delete') && this.value.length <= prefix.length) {
                    e.preventDefault();
                }
            });

            // Validar al perder el foco
            inp.addEventListener('blur', function() { validarInputTillo(this); });
        });
    }

    /**
     * Valida un input de tillo específico (Local y Remoto)
     */
    function validarInputTillo(input) {
        const val = input.value.trim();
        const iconContainer = input.nextElementSibling;
        const prefix = input.dataset.prefix;

        // 1. Validar vacío
        if (val === prefix || val === "") {
            input.classList.add('border-red-300');
            input.classList.remove('border-green-500', 'border-yellow-500');
            iconContainer.innerHTML = '';
            return;
        }

        // 2. Validar duplicado local
        if (verificarDuplicadoLocal(input)) {
            iconContainer.innerHTML = '<i class="fas fa-exclamation-triangle text-yellow-500" title="Duplicado en este pedido"></i>';
            input.classList.add('border-yellow-500', 'text-yellow-600');
            input.classList.remove('border-green-500', 'text-green-700', 'border-red-500', 'text-red-600');
            return;
        }

        // 3. Validar en Servidor (AJAX)
        const formData = new FormData();
        formData.append('action', 'verificar_tillo');
        formData.append('codigo_pedido', val);

        fetch('../../controller/PedidoController.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.ocupado) {
                    iconContainer.innerHTML = '<i class="fas fa-times-circle text-red-500" title="Ya existe y está Pendiente"></i>';
                    input.classList.add('border-red-500', 'text-red-600');
                    input.classList.remove('border-green-500', 'text-green-700', 'border-yellow-500');
                } else {
                    iconContainer.innerHTML = '<i class="fas fa-check-circle text-green-500"></i>';
                    input.classList.add('border-green-500', 'text-green-700');
                    input.classList.remove('border-red-500', 'text-red-600', 'border-yellow-500');
                }
            })
            .catch(err => console.error("Error validando tillo:", err));
    }

    function verificarDuplicadoLocal(inputActual) {
        const todos = document.querySelectorAll('.input-tillo-dinamico');
        let count = 0;
        todos.forEach(i => {
            if (i.value === inputActual.value && i.value !== inputActual.dataset.prefix) count++;
        });
        return count > 1;
    }

    // ==========================================
    // 3. BÚSQUEDA DE CLIENTES
    // ==========================================
    if(inpClienteBusqueda){
        inpClienteBusqueda.addEventListener('input', function() {
            const val = this.value;
            const opts = listaClientes.childNodes;
            let encontrado = false;
            
            for (let i = 0; i < opts.length; i++) {
                if (opts[i].value === val) {
                    inpIdCliente.value = opts[i].getAttribute('data-id');
                    encontrado = true;
                    break;
                }
            }
            
            if (!encontrado) inpIdCliente.value = '';
            
            // Feedback Visual
            if(encontrado) {
                this.classList.add('border-green-500', 'bg-green-50');
            } else {
                this.classList.remove('border-green-500', 'bg-green-50');
            }
        });
    }

    // ==========================================
    // 4. MODAL CLIENTE RÁPIDO Y VALIDACIONES
    // ==========================================

    // Validadores Reglas Ecuador
    function validarCedulaEcuador(cedula) {
        if (cedula.length !== 10) return "Debe tener 10 dígitos.";
        const digitoRegion = parseInt(cedula.substring(0, 2));
        if (digitoRegion < 1 || digitoRegion > 24) return "Provincia inválida.";
        const tercerDigito = parseInt(cedula.substring(2, 3));
        if (tercerDigito >= 6) return "Solo personas naturales.";
        
        const coef = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        let suma = 0;
        for (let i = 0; i < 9; i++) {
            let val = parseInt(cedula[i]) * coef[i];
            suma += (val >= 10) ? val - 9 : val;
        }
        const digitoCalc = (suma % 10 === 0) ? 0 : 10 - (suma % 10);
        return (digitoCalc === parseInt(cedula[9])) ? true : "Cédula inválida.";
    }

    function validarTelefonoEcuador(telefono) {
        if (telefono.length !== 10) return "Debe tener 10 dígitos.";
        if (!telefono.startsWith('09')) return "Debe empezar con '09'.";
        return true;
    }

    // Helpers UI
    function mostrarError(input, idError, msg) {
        const div = document.getElementById(idError);
        div.textContent = msg;
        div.classList.remove('hidden');
        input.classList.add('border-red-500', 'bg-red-50');
    }
    function limpiarError(input, idError) {
        const div = document.getElementById(idError);
        div.classList.add('hidden');
        input.classList.remove('border-red-500', 'bg-red-50');
    }

    // Control del Modal
    function abrirModal() { 
        formQuick.reset(); 
        limpiarError(document.getElementById('cedulaQuick'), 'errorCedulaQuick');
        limpiarError(document.getElementById('telefonoQuick'), 'errorTelefonoQuick');
        modalCliente.classList.remove('hidden'); 
    }
    function cerrarModal() { modalCliente.classList.add('hidden'); }

    if(btnQuickClient) btnQuickClient.addEventListener('click', abrirModal);
    if(btnCerrarModal) btnCerrarModal.addEventListener('click', cerrarModal);
    if(btnCancelarModal) btnCancelarModal.addEventListener('click', cerrarModal);

    // Envío Formulario Cliente Rápido
    if(formQuick) {
        const inpCed = document.getElementById('cedulaQuick');
        const inpTel = document.getElementById('telefonoQuick');
        
        // Validaciones en tiempo real
        inpCed.addEventListener('blur', function() {
            if(this.value) {
                const res = validarCedulaEcuador(this.value);
                res === true ? limpiarError(this, 'errorCedulaQuick') : mostrarError(this, 'errorCedulaQuick', res);
            }
        });
        inpTel.addEventListener('blur', function() {
            if(this.value) {
                const res = validarTelefonoEcuador(this.value);
                res === true ? limpiarError(this, 'errorTelefonoQuick') : mostrarError(this, 'errorTelefonoQuick', res);
            }
        });

        formQuick.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const resCed = validarCedulaEcuador(inpCed.value);
            const resTel = validarTelefonoEcuador(inpTel.value);

            if (resCed !== true) { mostrarError(inpCed, 'errorCedulaQuick', resCed); return; }
            if (resTel !== true) { mostrarError(inpTel, 'errorTelefonoQuick', resTel); return; }

            const formData = new FormData(formQuick);
            fetch('../../controller/ClienteController.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Cliente registrado.');
                        location.reload(); 
                    } else {
                        alert(data.message);
                    }
                });
        });
    }

    // ==========================================
    // 5. ENVÍO FINAL DEL PEDIDO (Blindado)
    // ==========================================
    
    formPedido.addEventListener('submit', function(e) {
        e.preventDefault();

        // A. Validar Cliente
        if (!inpIdCliente.value) {
            alert('Error: Debe seleccionar un cliente de la lista.');
            inpClienteBusqueda.focus();
            inpClienteBusqueda.classList.add('animate-pulse', 'border-red-500');
            return;
        }

        // B. Validar Tillos (Estado visual)
        const tillos = document.querySelectorAll('.input-tillo-dinamico');
        let hayErrorTillo = false;
        tillos.forEach(t => {
            if (t.classList.contains('border-red-500') || t.value === t.dataset.prefix) hayErrorTillo = true;
        });

        if (hayErrorTillo) {
            alert('Error: Revise los códigos de tillos (campos rojos o incompletos).');
            return;
        }

        // C. Preparar Envío
        const formData = new FormData(formPedido);
        const btnSubmit = formPedido.querySelector('button[type="submit"]');
        const textoOriginal = btnSubmit.innerHTML;
        
        // Estado de carga
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

        // D. Petición Robusta (Texto -> JSON)
        fetch('../../controller/PedidoController.php', { method: 'POST', body: formData })
            .then(res => {
                // 1. Verificar nivel de red HTTP
                if (!res.ok) throw new Error(`Error de Red: ${res.status} ${res.statusText}`);
                return res.text(); // 2. Obtener texto crudo para inspeccionar
            })
            .then(texto => {
                try {
                    return JSON.parse(texto); // 3. Intentar parsear JSON
                } catch (error) {
                    // Si falla el parseo, es probable que PHP haya lanzado un Fatal Error
                    console.error("Respuesta inválida del servidor:", texto);
                    throw new Error("Error interno del servidor (PHP). Revise la consola para más detalles.");
                }
            })
            .then(data => {
                // 4. Lógica de negocio
                if (data.success) {
                    // Preguntar por impresión
                    if (confirm('✅ ¡Pedido registrado correctamente!\n\n¿Desea imprimir el comprobante ahora?')) {
                        const idPedido = data.id_pedido;
                        if(idPedido) {
                            // Abrir ticket en popup
                            window.open(`ticket.php?id=${idPedido}`, 'ImprimirTicket', 'width=400,height=600,scrollbars=yes');
                        }
                    }
                    
                    // Redirigir siempre
                    setTimeout(() => {
                        window.location.href = 'pedidos.php';
                    }, 500);

                } else {
                    throw new Error(data.message || "Error desconocido al guardar.");
                }
            })
            .catch(err => {
                console.error(err);
                alert('⚠️ Ocurrió un problema:\n' + err.message);
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = textoOriginal;
            });
    });
});