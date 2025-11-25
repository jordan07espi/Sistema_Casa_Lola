<?php
// Archivo: view/admin/pedidos.php
session_start();
include '../partials/header.php';
?>

<div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
    <h1 class="text-3xl font-bold text-gray-800 border-l-4 border-orange-600 pl-4">Gestión de Pedidos</h1>
    <a href="nuevo_pedido.php" class="bg-orange-600 text-white px-5 py-2.5 rounded-lg hover:bg-orange-700 transition duration-300 shadow flex items-center gap-2 font-medium">
        <i class="fas fa-plus"></i> Nuevo Pedido
    </a>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="p-4 border-b border-gray-100 bg-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
        
        <div class="flex items-center gap-2 w-full md:w-auto">
            <span class="text-sm font-semibold text-gray-600">Filtrar por:</span>
            <select id="filtroEstado" class="border-gray-300 rounded-lg text-sm focus:ring-orange-500 focus:border-orange-500 p-2 border">
                <option value="">Todos los Estados</option>
                <option value="Pendiente">Pendientes</option>
                <option value="Entregado">Entregados</option>
                <option value="Cancelado">Cancelados</option>
            </select>
        </div>

        <div class="relative w-full md:w-auto">
            <input type="text" id="buscadorPedido" placeholder="Buscar por código o cliente..." class="pl-9 pr-4 py-2 border rounded-lg text-sm focus:ring-orange-500 focus:border-orange-500 w-full md:w-64">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="hidden md:table-header-group bg-gray-800 text-white text-sm uppercase tracking-wider">
                <tr>
                    <th class="py-3 px-6 rounded-tl-lg">Tillo</th> 
                    <th class="py-3 px-6">Cliente</th>
                    <th class="py-3 px-6">Entrega</th>
                    <th class="py-3 px-6">Total</th>
                    <th class="py-3 px-6 text-center">Estado</th>
                    <th class="py-3 px-6 text-center rounded-tr-lg">Acciones</th>
                </tr>
            </thead>
            
            <tbody id="tablaPedidosBody" class="text-gray-700 text-sm block md:table-row-group space-y-4 md:space-y-0 p-4 md:p-0">
                <tr><td colspan="6" class="text-center py-4">Cargando pedidos...</td></tr>
            </tbody>
        </table>
    </div>

    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 flex items-center justify-between sm:px-6">
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <p class="text-sm text-gray-700">Total: <span class="font-medium" id="txtTotal">0</span> pedidos</p>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <div id="paginacionContainer" class="flex gap-1"></div>
            </nav>
        </div>
    </div>
</div>

<script src="../assets/js/pedidos.js"></script>

<?php include '../partials/footer.php'; ?>