/**
 * Archivo: view/assets/js/nuevo_pedido.js
 * Descripción: Lógica completa para el ingreso de pedidos con múltiples tillos y gestión de clientes.
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. VARIABLES GLOBALES Y REFERENCIAS ---
    const PREFIJO_GLOBAL = document.getElementById('prefijo_global').value; // Ej: "2025_"
    const contenedorTillos = document.getElementById('contenedorTillos');
    const inputsCantidad = document.querySelectorAll('.input-cantidad');
    const formPedido = document.getElementById('formPedido');
    
    // Referencias Cliente
    const inpClienteBusqueda = document.getElementById('cliente_busqueda');
    const inpIdCliente = document.getElementById('id_cliente_seleccionado');
    const listaClientes = document.getElementById('listaClientes');

    // Referencias Modal Cliente Rápido
    const modalCliente = document.getElementById('clienteModal');
    const btnQuickClient = document.getElementById('btnQuickAddCliente');
    const btnCerrarModal = document.getElementById('closeModal');
    const btnCancelarModal = document.getElementById('btnCancelar');
    const formQuick = document.getElementById('clienteFormQuick');

    // --- 2. GENERACIÓN DINÁMICA DE TILLOS ---
    
    // Escuchar cambios en todos los inputs de cantidad
    inputsCantidad.forEach(input => {
        input.addEventListener('input', generarCamposTillo);
    });

    function generarCamposTillo() {
        // Nota: Esta función reconstruye los inputs. 
        // Mejora posible: conservar valores ya escritos si se aumenta la cantidad.
        
        // Guardar valores actuales temporalmente para intentar restaurarlos (UX básica)
        const valoresPrevios = {}; 
        document.querySelectorAll('.input-tillo-dinamico').forEach(inp => {
            // Usamos un identificador compuesto: ProductoID_Indice
            if(inp.dataset.idProd && inp.dataset.index) {
                valoresPrevios[`${inp.dataset.idProd}_${inp.dataset.index}`] = inp.value;
            }
        });

        contenedorTillos.innerHTML = ''; 
        let hayTillos = false;

        inputsCantidad.forEach(input => {
            const cantidad = parseInt(input.value) || 0;
            // data-tillo="1" viene del PHP (productos.requiere_tillo)
            const requiereTillo = input.dataset.tillo === "1"; 
            const nombreProd = input.dataset.nombre;
            // Obtenemos el ID del producto desde el atributo name="productos[ID]"
            const idProd = input.name.match(/\d+/)[0]; 

            if (requiereTillo && cantidad > 0) {
                hayTillos = true;
                
                // Crear encabezado del grupo
                const grupo = document.createElement('div');
                grupo.className = "mb-3 pb-2 border-b border-gray-200 last:border-0 animate-fade-in";
                grupo.innerHTML = `<h4 class="text-xs font-bold text-orange-700 uppercase mb-2 flex items-center gap-2"><i class="fas fa-utensils"></i> ${nombreProd} (${cantidad})</h4>`;
                
                for (let i = 1; i <= cantidad; i++) {
                    const divInput = document.createElement('div');
                    divInput.className = "flex items-center gap-2 mb-2";
                    
                    // Recuperar valor previo si existe, sino usar prefijo
                    const key = `${idProd}_${i}`;
                    const valorInicial = valoresPrevios[key] || PREFIJO_GLOBAL;

                    divInput.innerHTML = `
                        <span class="text-xs text-gray-500 w-6 font-mono">#${i}</span>
                        <div class="relative flex-1">
                            <input type="text" name="tillos_generados[]" 
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

        if (!hayTillos) {
            contenedorTillos.innerHTML = `
                <div class="flex flex-col items-center justify-center h-40 text-gray-400 border-2 border-dashed border-gray-200 rounded-lg">
                    <i class="fas fa-tag fa-2x mb-2"></i>
                    <p class="text-sm text-center">Seleccione productos (carnes)<br>para asignar etiquetas.</p>
                </div>`;
        } else {
            // Asignar eventos a los nuevos inputs creados
            asignarEventosTillos();
        }
    }

    // --- 3. LÓGICA DE VALIDACIÓN DE TILLOS (Eventos) ---

    function asignarEventosTillos() {
        const nuevosInputs = document.querySelectorAll('.input-tillo-dinamico');
        
        nuevosInputs.forEach(inp => {
            // A. Protección del prefijo "2025_"
            inp.addEventListener('input', function() {
                const prefix = this.dataset.prefix;
                if (!this.value.startsWith(prefix)) {
                    // Si borran parte del prefijo, intentamos salvar el resto
                    let sufijo = this.value.replace(prefix, '');
                    // Si el valor es menor al prefijo, reiniciamos
                    if (this.value.length < prefix.length) sufijo = "";
                    this.value = prefix + sufijo;
                }
                this.value = this.value.toUpperCase();
            });

            // B. Bloquear borrado del prefijo con teclado
            inp.addEventListener('keydown', function(e) {
                const prefix = this.dataset.prefix;
                if ((e.key === 'Backspace' || e.key === 'Delete') && this.value.length <= prefix.length) {
                    e.preventDefault();
                }
            });

            // C. Validación al salir del campo (AJAX + Local)
            inp.addEventListener('blur', function() {
                validarInputTillo(this);
            });
        });
    }

    function validarInputTillo(input) {
        const val = input.value.trim();
        const iconContainer = input.nextElementSibling; // .status-icon
        const prefix = input.dataset.prefix;

        // 1. Validar vacío (solo prefijo)
        if (val === prefix) {
            input.classList.add('border-red-300');
            input.classList.remove('border-green-500', 'border-yellow-500');
            iconContainer.innerHTML = '';
            return;
        }

        // 2. Validar duplicado local (en el mismo formulario)
        if (verificarDuplicadoLocal(input)) {
            iconContainer.innerHTML = '<i class="fas fa-exclamation-triangle text-yellow-500" title="Duplicado en este pedido"></i>';
            input.classList.add('border-yellow-500', 'text-yellow-600');
            input.classList.remove('border-green-500', 'text-green-700', 'border-red-500', 'text-red-600');
            return;
        }

        // 3. Validar disponibilidad en BD (AJAX)
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
            if (i.value === inputActual.value && i.value !== inputActual.dataset.prefix) {
                count++;
            }
        });
        return count > 1; // Si aparece más de una vez
    }

    // --- 4. BUSCADOR DE CLIENTES (DATALIST) ---
    
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
            
            // Feedback visual
            if(encontrado) {
                this.classList.add('border-green-500', 'bg-green-50');
            } else {
                this.classList.remove('border-green-500', 'bg-green-50');
            }
        });
    }

    // --- 5. MODAL CLIENTE RÁPIDO (Validaciones Completas) ---

    // Inputs del Modal
    const inpCedulaQ = document.getElementById('cedulaQuick');
    const errorCedulaQ = document.getElementById('errorCedulaQuick');
    const inpNombreQ = document.getElementById('nombreQuick');
    const inpTelefonoQ = document.getElementById('telefonoQuick');
    const errorTelefonoQ = document.getElementById('errorTelefonoQuick');

    // Funciones de ayuda UI
    function mostrarError(input, divError, mensaje) {
        divError.textContent = mensaje;
        divError.classList.remove('hidden');
        input.classList.add('border-red-500', 'bg-red-50');
    }
    function limpiarError(input, divError) {
        divError.classList.add('hidden');
        input.classList.remove('border-red-500', 'bg-red-50');
    }

    // Validadores Reglas Ecuador
    function validarCedulaEcuador(cedula) {
        if (cedula.length !== 10) return "Debe tener 10 dígitos.";
        const digitoRegion = parseInt(cedula.substring(0, 2));
        if (digitoRegion < 1 || digitoRegion > 24) return "Código de provincia inválido.";
        const tercerDigito = parseInt(cedula.substring(2, 3));
        if (tercerDigito >= 6) return "Tercer dígito inválido (Solo personas naturales).";
        
        const coef = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        let suma = 0;
        for (let i = 0; i < 9; i++) {
            let val = parseInt(cedula[i]) * coef[i];
            suma += (val >= 10) ? val - 9 : val;
        }
        const digitoCalc = (suma % 10 === 0) ? 0 : 10 - (suma % 10);
        return (digitoCalc === parseInt(cedula[9])) ? true : "Cédula inválida (Dígito verificador).";
    }

    function validarTelefonoEcuador(telefono) {
        if (telefono.length !== 10) return "Debe tener 10 dígitos.";
        if (!telefono.startsWith('09')) return "Debe empezar con '09'.";
        return true;
    }

    // Eventos Inputs Modal
    if(inpNombreQ) inpNombreQ.addEventListener('input', function() { this.value = this.value.toUpperCase(); });

    if(inpCedulaQ) {
        inpCedulaQ.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (!errorCedulaQ.classList.contains('hidden')) limpiarError(this, errorCedulaQ);
        });
        inpCedulaQ.addEventListener('blur', function() {
            if (this.value === "") return;
            const res = validarCedulaEcuador(this.value);
            if (res !== true) mostrarError(this, errorCedulaQ, res);
        });
    }

    if(inpTelefonoQ) {
        inpTelefonoQ.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (!errorTelefonoQ.classList.contains('hidden')) limpiarError(this, errorTelefonoQ);
        });
        inpTelefonoQ.addEventListener('blur', function() {
            if (this.value === "") return;
            const res = validarTelefonoEcuador(this.value);
            if (res !== true) mostrarError(this, errorTelefonoQ, res);
        });
    }

    // Abrir/Cerrar Modal
    function abrirModal() {
        formQuick.reset();
        limpiarError(inpCedulaQ, errorCedulaQ);
        limpiarError(inpTelefonoQ, errorTelefonoQ);
        modalCliente.classList.remove('hidden');
    }
    function cerrarModal() { modalCliente.classList.add('hidden'); }

    if(btnQuickClient) btnQuickClient.addEventListener('click', abrirModal);
    if(btnCerrarModal) btnCerrarModal.addEventListener('click', cerrarModal);
    if(btnCancelarModal) btnCancelarModal.addEventListener('click', cerrarModal);

    // Submit Cliente Rápido
    if(formQuick) {
        formQuick.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const resCedula = validarCedulaEcuador(inpCedulaQ.value);
            const resTelefono = validarTelefonoEcuador(inpTelefonoQ.value);

            if (resCedula !== true) { mostrarError(inpCedulaQ, errorCedulaQ, resCedula); return; }
            if (resTelefono !== true) { mostrarError(inpTelefonoQ, errorTelefonoQ, resTelefono); return; }

            const formData = new FormData(formQuick);
            
            fetch('../../controller/ClienteController.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Cliente registrado correctamente.');
                        cerrarModal();
                        // Recargar la página es la forma más segura de actualizar el datalist
                        // (Opcional: podrías agregar el option al DOM manualmente)
                        location.reload(); 
                    } else {
                        if (data.message.toLowerCase().includes('cédula')) {
                            mostrarError(inpCedulaQ, errorCedulaQ, data.message);
                        } else {
                            alert(data.message);
                        }
                    }
                });
        });
    }

    // --- 6. ENVÍO FINAL DEL PEDIDO ---
    
    formPedido.addEventListener('submit', function(e) {
        e.preventDefault();

        // A. Validar Cliente Seleccionado
        if (!inpIdCliente.value) {
            alert('Error: Debe seleccionar un cliente válido de la lista o registrar uno nuevo.');
            inpClienteBusqueda.focus();
            inpClienteBusqueda.classList.add('animate-pulse', 'border-red-500');
            return;
        }

        // B. Validar Tillos
        const tillos = document.querySelectorAll('.input-tillo-dinamico');
        let tillosValidos = true;
        let mensajeErrorTillo = '';

        if (tillos.length > 0) {
            tillos.forEach(t => {
                const val = t.value.trim();
                const prefix = t.dataset.prefix;
                
                // Validar que no esté vacío (solo prefijo)
                if (val === prefix || val === "") {
                    tillosValidos = false;
                    t.classList.add('border-red-500');
                    mensajeErrorTillo = 'Hay códigos de Tillo incompletos.';
                }
                // Validar que no esté marcado como ocupado/duplicado (clases visuales)
                if (t.classList.contains('border-red-500') || t.classList.contains('border-yellow-500')) {
                    tillosValidos = false;
                    mensajeErrorTillo = 'Hay códigos de Tillo ocupados o duplicados.';
                }
            });
        }

        if (!tillosValidos) {
            alert('Error en Tillos: ' + mensajeErrorTillo + '\nPor favor revise los campos marcados en rojo o amarillo.');
            return;
        }

        // C. Enviar Formulario
        const formData = new FormData(formPedido);

        // Mostrar estado de carga en el botón
        const btnSubmit = formPedido.querySelector('button[type="submit"]');
        const textoOriginal = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando...';

        fetch('../../controller/PedidoController.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('¡Pedido registrado con éxito!');
                    window.location.href = 'pedidos.php';
                } else {
                    alert('Error del servidor: ' + data.message);
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = textoOriginal;
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error de conexión al intentar guardar el pedido.');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = textoOriginal;
            });
    });
});