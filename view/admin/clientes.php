<?php
// Archivo: view/admin/clientes.php
session_start();
include '../partials/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800 border-l-4 border-orange-600 pl-4">Gestión de Clientes</h1>
    <button id="btnNuevoCliente" class="bg-orange-600 text-white px-5 py-2.5 rounded-lg hover:bg-orange-700 transition duration-300 shadow flex items-center gap-2 font-medium">
        <i class="fas fa-plus"></i> Nuevo Cliente
    </button>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="p-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-700">Directorio de Clientes</h2>
        <div class="relative">
            <input type="text" id="buscadorCliente" placeholder="Buscar por nombre..." class="pl-9 pr-4 py-2 border rounded-lg text-sm focus:ring-orange-500 focus:border-orange-500">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="hidden md:table-header-group bg-gray-800 text-white text-sm uppercase tracking-wider">
                <tr>
                    <th class="py-3 px-6 font-semibold rounded-tl-lg">Nombre Completo</th>
                    <th class="py-3 px-6 font-semibold">Teléfono</th>
                    <th class="py-3 px-6 font-semibold text-center rounded-tr-lg">Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaClientesBody" class="text-gray-700 text-sm">
                <tr><td colspan="3" class="text-center py-4">Cargando datos...</td></tr>
            </tbody>
        </table>
    </div>
    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 flex items-center justify-between sm:px-6">
        <div class="w-full flex items-center justify-between">
            <p class="text-sm text-gray-700">
                Mostrando <span class="font-medium" id="txtTotal">0</span> resultados
            </p>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <div id="paginacionContainer" class="flex gap-1"></div>
            </nav>
        </div>
    </div>
</div>

<div id="clienteModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center hidden z-50 backdrop-blur-sm">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-md transform transition-all scale-100">
        <div class="bg-gray-900 text-white px-6 py-4 rounded-t-lg flex justify-between items-center border-b-4 border-orange-600">
            <h2 id="modalTitle" class="text-xl font-bold">Nuevo Cliente</h2>
            <button id="closeModal" class="text-gray-400 hover:text-white transition text-2xl focus:outline-none">&times;</button>
        </div>
        
        <form id="clienteForm" class="p-6">
            <input type="hidden" name="action" id="action">
            <input type="hidden" name="id_cliente" id="id_cliente">
            
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Nombre Completo</label>
                <input type="text" name="nombre" id="nombre" 
                    class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 px-4 py-2 border transition-colors font-bold uppercase" 
                    placeholder="Ej: JUAN PEREZ" required>
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-2">Teléfono</label>
                <input type="text" name="telefono" id="telefono" 
                    class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 px-4 py-2 border transition-colors font-mono" 
                    placeholder="Ej: 0991234567" maxlength="10">
                 <p id="errorTelefono" class="text-red-500 text-xs mt-1 hidden font-semibold"></p>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" id="btnCancelar" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Cancelar</button>
                <button type="submit" id="btnGuardar" class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition shadow font-medium">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/clientes.js"></script>

<?php include '../partials/footer.php'; ?>