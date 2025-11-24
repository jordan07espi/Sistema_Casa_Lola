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
                        // Sanitizar datos antes de insertarlos
                        const nombreCompleto = sanitizeHTML(u.nombre_completo);
                        const cedula = sanitizeHTML(u.cedula);
                        const nombreRol = sanitizeHTML(u.nombre_rol);

                        tbody.innerHTML += `
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2 px-4">${nombreCompleto}</td>
                                <td class="py-2 px-4">${cedula}</td>
                                <td class="py-2 px-4">${nombreRol}</td>
                                <td class="py-2 px-4">
                                    <button class="btn-editar bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600" data-id="${u.id_usuario}">Editar</button>
                                    <button class="btn-eliminar bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600" data-id="${u.id_usuario}">Eliminar</button>
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