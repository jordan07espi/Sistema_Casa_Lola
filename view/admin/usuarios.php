<?php
// Archivo: view/admin/usuarios.php
session_start();
include '../partials/header.php';

// Verificación de rol de Administrador
if ($rolUsuario !== 'Administrador') {
    // Si no es admin, redirigir al dashboard o mostrar un mensaje de error
    echo '<script>window.location.href = "dashboard.php";</script>';
    exit();
}
?>

<h1 class="text-3xl font-bold text-gray-800">Gestión de Usuarios</h1>
<hr class="my-4">

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Listado de Usuarios</h2>
        <button id="btnNuevoUsuario" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Nuevo Usuario
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead class="hidden md:table-header-group bg-gray-800 text-white text-sm uppercase tracking-wider">
                <tr>
                    <th class="py-3 px-6 font-semibold rounded-tl-lg">Nombre Completo</th>
                    <th class="py-3 px-6 font-semibold">Cédula</th>
                    <th class="py-3 px-6 font-semibold">Rol</th>
                    <th class="py-3 px-6 font-semibold text-center rounded-tr-lg">Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaUsuariosBody">
                <!-- Filas se cargarán con AJAX -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para Agregar/Editar Usuario -->
<div id="usuarioModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h2 id="modalTitle" class="text-2xl font-bold"></h2>
            <button id="closeModal" class="text-gray-500 hover:text-gray-800 text-3xl leading-none">&times;</button>
        </div>
        <form id="usuarioForm">
            <input type="hidden" name="action" id="action">
            <input type="hidden" name="id_usuario" id="id_usuario">
            
            <div class="mb-4">
                <label for="nombre_completo" class="block text-gray-700">Nombre Completo</label>
                <input type="text" name="nombre_completo" id="nombre_completo" class="w-full border rounded px-3 py-2 mt-1" required>
            </div>
            <div class="mb-4">
                <label for="cedula" class="block text-gray-700">Cédula</label>
                <input type="text" name="cedula" id="cedula" class="w-full border rounded px-3 py-2 mt-1" required>
            </div>
            <div class="mb-4">
                <label for="id_rol" class="block text-gray-700">Rol</label>
                <select name="id_rol" id="id_rol" class="w-full border rounded px-3 py-2 mt-1" required>
                    <!-- Opciones de roles se cargarán con AJAX -->
                </select>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700">Contraseña</label>
                <input type="password" name="password" id="password" class="w-full border rounded px-3 py-2 mt-1">
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/usuarios.js"></script>

<?php 
include '../partials/footer.php'; 
?>