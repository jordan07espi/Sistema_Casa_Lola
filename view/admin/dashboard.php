<?php
// Archivo: view/admin/dashboard.php
session_start();
include '../partials/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Hola, <?php echo htmlspecialchars($_SESSION['nombre_completo']); ?> 👋</h1>
        <p class="text-gray-500 text-sm mt-1">Resumen general de tu negocio.</p>
    </div>
    
    <div class="bg-white p-2 rounded-lg shadow-sm border border-gray-200 flex items-center gap-2">
        <div class="flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-md border border-gray-200">
            <span class="text-xs font-bold text-gray-500 uppercase">Desde:</span>
            <input type="date" id="dashDesde" class="bg-transparent border-none text-sm font-semibold text-gray-700 focus:ring-0 p-0 w-32" value="<?php echo date('Y-m-01'); ?>">
        </div>
        <span class="text-gray-400">-</span>
        <div class="flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-md border border-gray-200">
            <span class="text-xs font-bold text-gray-500 uppercase">Hasta:</span>
            <input type="date" id="dashHasta" class="bg-transparent border-none text-sm font-semibold text-gray-700 focus:ring-0 p-0 w-32" value="<?php echo date('Y-m-t'); ?>">
        </div>
        <button id="btnFiltrar" class="bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-md transition shadow-sm" title="Actualizar Datos">
            <i class="fas fa-sync-alt"></i>
        </button>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    
    <div class="rounded-xl shadow-lg p-6 relative overflow-hidden bg-gradient-to-r from-emerald-500 to-emerald-400 text-white transform hover:scale-105 transition duration-300">
        <div class="relative z-10 flex justify-between items-center h-full">
            <div>
                <p class="text-emerald-100 text-xs font-bold uppercase tracking-wider mb-1">Total Vendido</p>
                <h3 class="text-4xl font-black tracking-tight" id="kpiDinero">$0.00</h3>
                <p class="text-xs text-emerald-100 mt-2 opacity-90">Ingresos netos (No cancelados)</p>
            </div>
            <div class="bg-white bg-opacity-20 p-4 rounded-full shadow-inner backdrop-blur-sm">
                <i class="fas fa-dollar-sign fa-2x text-white"></i>
            </div>
        </div>
        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-white opacity-10 rounded-full"></div>
    </div>

    <div class="rounded-xl shadow-lg p-6 relative overflow-hidden bg-gradient-to-r from-blue-600 to-blue-500 text-white transform hover:scale-105 transition duration-300">
        <div class="relative z-10 flex justify-between items-center h-full">
            <div>
                <p class="text-blue-100 text-xs font-bold uppercase tracking-wider mb-1">Total Pedidos</p>
                <h3 class="text-4xl font-black tracking-tight" id="kpiPedidos">0</h3>
                <p class="text-xs text-blue-100 mt-2 opacity-90">Órdenes procesadas</p>
            </div>
            <div class="bg-white bg-opacity-20 p-4 rounded-full shadow-inner backdrop-blur-sm">
                <i class="fas fa-shopping-bag fa-2x text-white"></i>
            </div>
        </div>
        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-white opacity-10 rounded-full"></div>
    </div>

    <div class="rounded-xl shadow-lg p-6 relative overflow-hidden bg-gradient-to-r from-orange-500 to-orange-400 text-white transform hover:scale-105 transition duration-300">
        <div class="relative z-10 flex justify-between items-center h-full">
            <div class="flex-1 min-w-0 pr-2"> <p class="text-orange-100 text-xs font-bold uppercase tracking-wider mb-1">Producto Estrella</p>
                <h3 class="text-2xl font-bold truncate leading-tight" id="kpiProducto">---</h3>
                <p class="text-xs text-orange-100 mt-2 opacity-90">Vendidos: <span id="kpiCantidadProducto" class="font-bold text-white text-sm">0</span> unds.</p>
            </div>
            <div class="bg-white bg-opacity-20 p-4 rounded-full shadow-inner backdrop-blur-sm flex-shrink-0">
                <i class="fas fa-star fa-2x text-white"></i>
            </div>
        </div>
        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-white opacity-10 rounded-full"></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    
    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-md border border-gray-100">
        <h3 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2 flex items-center gap-2">
            <i class="fas fa-chart-bar text-blue-500"></i> Tendencia de Ventas (Últimos 7 días)
        </h3>
        <div class="relative h-72">
            <canvas id="chartVentas"></canvas>
        </div>
    </div>

    <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow-md border border-gray-100">
        <h3 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2 flex items-center gap-2">
            <i class="fas fa-chart-pie text-orange-500"></i> Estado de Pedidos
        </h3>
        <div class="relative h-64 flex justify-center items-center">
            <canvas id="chartEstados"></canvas>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
    <div class="p-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
        <h3 class="font-bold text-gray-700">Acciones Rápidas</h3>
        <a href="nuevo_pedido.php" class="text-sm font-semibold text-white bg-gray-900 px-4 py-2 rounded-lg hover:bg-gray-700 transition">
            <i class="fas fa-plus mr-1"></i> Crear Nuevo Pedido
        </a>
    </div>
</div>

<script src="../assets/js/dashboard.js"></script>

<?php include '../partials/footer.php'; ?>