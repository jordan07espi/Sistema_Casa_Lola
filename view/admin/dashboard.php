<?php
// Archivo: view/admin/dashboard.php
session_start();
include '../partials/header.php';
?>

<h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
<hr class="my-4">

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">Productos Totales</p>
            <p id="total-productos" class="text-2xl font-bold">0</p>
        </div>
        <i class="fas fa-box fa-3x text-blue-500"></i>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">Espacios Registrados</p>
            <p id="total-espacios" class="text-2xl font-bold">0</p>
        </div>
        <i class="fas fa-map-marker-alt fa-3x text-green-500"></i>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">Alertas de Stock</p>
            <p id="alertas-stock" class="text-2xl font-bold text-red-500">0</p>
        </div>
        <i class="fas fa-exclamation-triangle fa-3x text-red-500"></i>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">Movimientos Hoy</p>
            <p id="movimientos-hoy" class="text-2xl font-bold">0</p>
        </div>
        <i class="fas fa-exchange-alt fa-3x text-yellow-500"></i>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold mb-4">Productos con Menor Stock</h2>
        <table class="w-full">
            <thead>
                <tr class="text-left text-sm text-gray-600">
                    <th class="py-2 px-4">Producto</th>
                    <th class="py-2 px-4 text-right">Stock</th>
                    <th class="py-2 px-4">Unidad</th>
                </tr>
            </thead>
            <tbody id="inventario-body">
                </tbody>
        </table>
    </div>
    
    <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold mb-4">Actividad Reciente del Día</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-sm text-gray-600">
                        <th class="py-2 px-4">Descripción</th>
                        <th class="py-2 px-4">Usuario</th>
                    </tr>
                </thead>
                <tbody id="actividad-reciente-body">
                    </tbody>
            </table>
        </div>
    </div>
</div>

<script src="../assets/js/dashboard.js"></script>

<?php 
include '../partials/footer.php'; 
?>