// Archivo: view/assets/js/usuarios.js
document.addEventListener('DOMContentLoaded', function() {
    const btnNuevo = document.getElementById('btnNuevoUsuario');
    const modal = document.getElementById('usuarioModal');
    const closeModalBtn = document.getElementById('closeModal');
    const usuarioForm = document.getElementById('usuarioForm');
    const modalTitle = document.getElementById('modalTitle');
    const actionInput = document.getElementById('action');
    const idInput = document.getElementById('id_usuario');
    const passwordInput = document.getElementById('password');
    const passwordLabel = document.querySelector('label[for="password"]');

    function abrirModal(titulo, action, usuario = null) {
        modalTitle.textContent = titulo;
        actionInput.value = action;
        usuarioForm.reset();
        idInput.value = '';
        passwordInput.required = true;
        passwordLabel.textContent = 'Contraseña';

        if (usuario) {
            idInput.value = usuario.id_usuario;
            document.getElementById('nombre_completo').value = usuario.nombre_completo;
            document.getElementById('cedula').value = usuario.cedula;
            document.getElementById('id_rol').value = usuario.id_rol;
            passwordInput.required = false; // La contraseña es opcional al editar
            passwordLabel.textContent = 'Nueva Contraseña (opcional)';
        }
        
        modal.classList.remove('hidden');
    }

    function cerrarModal() {
        modal.classList.add('hidden');
    }
    
    // Cargar roles en el select/dropdown
    function cargarRoles() {
        fetch('../../controller/UsuarioController.php?action=listarRoles')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const rolSelect = document.getElementById('id_rol');
                    rolSelect.innerHTML = '<option value="">Seleccione un rol</option>';
                    data.data.forEach(rol => {
                        rolSelect.innerHTML += `<option value="${rol.id_rol}">${rol.nombre_rol}</option>`;
                    });
                }
            });
    }

    btnNuevo.addEventListener('click', () => abrirModal('Nuevo Usuario', 'agregar'));
    closeModalBtn.addEventListener('click', cerrarModal);
    
    function cargarUsuarios() {
        fetch('../../controller/UsuarioController.php?action=listar')
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('tablaUsuariosBody');
                tbody.innerHTML = '';
                if (data.success) {
                    data.data.forEach(u => {
                        const nombreCompleto = sanitizeHTML(u.nombre_completo);
                        const cedula = sanitizeHTML(u.cedula);
                        const nombreRol = sanitizeHTML(u.nombre_rol);

                        // Renderizado Responsivo
                        tbody.innerHTML += `
                            <tr class="bg-white border md:border-b border-gray-200 block md:table-row rounded-xl shadow-sm md:shadow-none mb-4 md:mb-0 hover:bg-gray-50 transition">
                                
                                <td class="p-4 md:py-3 md:px-6 block md:table-cell border-b md:border-none">
                                    <span class="md:hidden text-xs font-bold text-gray-400 uppercase mb-1 block">Nombre</span>
                                    <div class="font-bold text-gray-800 flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 font-bold text-xs">
                                            ${nombreCompleto.charAt(0)}
                                        </div>
                                        ${nombreCompleto}
                                    </div>
                                </td>

                                <td class="p-4 md:py-3 md:px-6 block md:table-cell border-b md:border-none bg-gray-50 md:bg-transparent">
                                    <span class="md:hidden text-xs font-bold text-gray-400 uppercase mb-1 block">Cédula</span>
                                    <span class="font-mono text-gray-600">${cedula}</span>
                                </td>

                                <td class="p-4 md:py-3 md:px-6 block md:table-cell border-b md:border-none">
                                    <span class="md:hidden text-xs font-bold text-gray-400 uppercase mb-1 block">Rol</span>
                                    <span class="px-2 py-1 rounded text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200 inline-block">
                                        ${nombreRol}
                                    </span>
                                </td>

                                <td class="p-4 md:py-3 md:px-6 block md:table-cell text-center">
                                    <div class="flex md:justify-center justify-end gap-3">
                                        <button class="btn-editar bg-yellow-100 text-yellow-700 hover:bg-yellow-200 px-4 py-2 rounded-lg transition font-medium text-sm flex items-center gap-2" data-id="${u.id_usuario}">
                                            <i class="fas fa-edit pointer-events-none"></i> Editar
                                        </button>
                                        <button class="btn-eliminar bg-red-100 text-red-600 hover:bg-red-200 px-4 py-2 rounded-lg transition font-medium text-sm flex items-center gap-2" data-id="${u.id_usuario}">
                                            <i class="fas fa-trash-alt pointer-events-none"></i> Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                }
            });
    }

    usuarioForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(usuarioForm);
        
        fetch('../../controller/UsuarioController.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                cerrarModal();
                cargarUsuarios();
            } else {
                alert(data.message);
            }
        });
    });

    document.getElementById('tablaUsuariosBody').addEventListener('click', function(e) {
        const id = e.target.dataset.id;
        if (e.target.classList.contains('btn-editar')) {
            const formData = new FormData();
            formData.append('action', 'obtener');
            formData.append('id_usuario', id);
            
            fetch('../../controller/UsuarioController.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        abrirModal('Editar Usuario', 'actualizar', data.data);
                    }
                });
        }

        if (e.target.classList.contains('btn-eliminar')) {
            if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
                const formData = new FormData();
                formData.append('action', 'eliminar');
                formData.append('id_usuario', id);

                fetch('../../controller/UsuarioController.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            cargarUsuarios();
                        } else {
                            alert(data.message);
                        }
                    });
            }
        }
    });

    cargarRoles();
    cargarUsuarios();
});