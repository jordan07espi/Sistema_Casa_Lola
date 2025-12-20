<?php
// Archivo: view/admin/dashboard.php
session_start();
include '../partials/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-8 gap-6">
    
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Hola, <?php echo htmlspecialchars($_SESSION['nombre_completo']); ?> 👋</h1>
        <p class="text-gray-500 text-sm mt-1">Resumen general de tu negocio.</p>
    </div>
    
    <div class="bg-white p-3 rounded-xl shadow-sm border border-gray-200 flex flex-col sm:flex-row items-center gap-3 w-full xl:w-auto">
        
        <div class="flex items-center gap-3 bg-gray-50 px-4 py-2 rounded-lg border border-gray-200 w-full sm:w-auto">
            <span class="text-xs font-bold text-gray-500 uppercase whitespace-nowrap">Desde:</span>
            <input type="date" id="dashDesde" class="bg-transparent border-none text-sm font-bold text-gray-700 focus:ring-0 p-0 w-full sm:w-32 cursor-pointer" value="<?php echo date('Y-m-01'); ?>">
        </div>
        
        <span class="text-gray-400 hidden sm:block text-xl">›</span>
        
        <div class="flex items-center gap-3 bg-gray-50 px-4 py-2 rounded-lg border border-gray-200 w-full sm:w-auto">
            <span class="text-xs font-bold text-gray-500 uppercase whitespace-nowrap">Hasta:</span>
            <input type="date" id="dashHasta" class="bg-transparent border-none text-sm font-bold text-gray-700 focus:ring-0 p-0 w-full sm:w-32 cursor-pointer" value="<?php echo date('Y-m-t'); ?>">
        </div>
        
        <button id="btnFiltrar" class="bg-blue-600 hover:bg-blue-700 text-white py-2.5 px-5 rounded-lg transition shadow-md w-full sm:w-auto flex justify-center items-center gap-2 group active:scale-95">
            <i class="fas fa-sync-alt group-hover:rotate-180 transition-transform duration-500"></i>
            <span class="sm:hidden font-bold text-sm">Actualizar Datos</span>
        </button>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    
    <div class="rounded-2xl shadow-lg p-6 relative overflow-hidden bg-gradient-to-br from-emerald-500 to-emerald-600 text-white transform hover:-translate-y-1 transition duration-300">
        <div class="relative z-10 flex justify-between items-start">
            <div>
                <p class="text-emerald-100 text-xs font-bold uppercase tracking-wider mb-2">Total Vendido</p>
                <h3 class="text-4xl font-black tracking-tight" id="kpiDinero">$0.00</h3>
                <p class="text-xs text-emerald-100 mt-2 font-medium bg-emerald-700 bg-opacity-30 inline-block px-2 py-1 rounded">
                    Ingresos netos
                </p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-xl shadow-inner backdrop-blur-sm">
                <i class="fas fa-dollar-sign fa-2x text-white"></i>
            </div>
        </div>
        <div class="absolute -right-6 -bottom-10 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"></div>
    </div>

    <div class="rounded-2xl shadow-lg p-6 relative overflow-hidden bg-gradient-to-br from-blue-500 to-blue-600 text-white transform hover:-translate-y-1 transition duration-300">
        <div class="relative z-10 flex justify-between items-start">
            <div>
                <p class="text-blue-100 text-xs font-bold uppercase tracking-wider mb-2">Total Pedidos</p>
                <h3 class="text-4xl font-black tracking-tight" id="kpiPedidos">0</h3>
                <p class="text-xs text-blue-100 mt-2 font-medium bg-blue-700 bg-opacity-30 inline-block px-2 py-1 rounded">
                    Órdenes procesadas
                </p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-xl shadow-inner backdrop-blur-sm">
                <i class="fas fa-shopping-bag fa-2x text-white"></i>
            </div>
        </div>
        <div class="absolute -right-6 -bottom-10 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"></div>
    </div>

    <div class="rounded-2xl shadow-lg p-6 relative overflow-hidden bg-gradient-to-br from-orange-500 to-orange-600 text-white transform hover:-translate-y-1 transition duration-300">
        <div class="relative z-10 flex justify-between items-start">
            <div class="flex-1 min-w-0 pr-3"> 
                <p class="text-orange-100 text-xs font-bold uppercase tracking-wider mb-2">Más Vendido</p>
                <h3 class="text-2xl font-bold leading-tight truncate" id="kpiProducto">---</h3>
                <p class="text-xs text-orange-100 mt-2 font-medium bg-orange-700 bg-opacity-30 inline-block px-2 py-1 rounded">
                    Cant: <span id="kpiCantidadProducto" class="font-bold text-white">0</span>
                </p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-xl shadow-inner backdrop-blur-sm flex-shrink-0">
                <i class="fas fa-star fa-2x text-white"></i>
            </div>
        </div>
        <div class="absolute -right-6 -bottom-10 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    
    <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-md border border-gray-100">
        <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                    <i class="fas fa-chart-bar"></i>
                </div>
                Tendencia de Ventas
            </h3>
            <span class="text-xs font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded">Últimos 7 días</span>
        </div>
        <div class="relative h-72">
            <canvas id="chartVentas"></canvas>
        </div>
    </div>

    <div class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-md border border-gray-100">
        <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-orange-600">
                    <i class="fas fa-chart-pie"></i>
                </div>
                Estado Pedidos
            </h3>
        </div>
        <div class="relative h-64 flex justify-center items-center">
            <canvas id="chartEstados"></canvas>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 transition hover:shadow-lg">
    <div class="p-5 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-4">
            <div class="bg-gray-100 p-3 rounded-full text-gray-600">
                <i class="fas fa-bolt fa-lg"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-800">Acciones Rápidas</h3>
                <p class="text-sm text-gray-500">Gestione sus operaciones frecuentes desde aquí.</p>
            </div>
        </div>
        <a href="nuevo_pedido.php" class="w-full sm:w-auto text-center text-sm font-bold text-white bg-gray-900 px-6 py-3 rounded-xl hover:bg-gray-800 transition transform hover:-translate-y-0.5 shadow-md flex items-center justify-center gap-2">
            <i class="fas fa-plus"></i> Crear Nuevo Pedido
        </a>
    </div>
</div>

<script src="../assets/js/dashboard.js"></script>

<?php include '../partials/footer.php'; ?>