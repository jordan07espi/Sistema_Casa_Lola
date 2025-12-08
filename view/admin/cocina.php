<?php
// Archivo: view/admin/cocina.php
session_start();
include '../partials/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-4xl font-black text-gray-800 border-l-8 border-orange-600 pl-6">
        <i class="fas fa-utensils mr-3"></i> MONITOR DE COCINA
    </h1>
    <div class="flex items-center gap-3">
        <div id="indicadorConexion" class="flex items-center gap-2 px-4 py-2 bg-green-100 text-green-700 rounded-lg font-bold animate-pulse">
            <div class="w-3 h-3 bg-green-600 rounded-full"></div>
            En Vivo
        </div>
        <a href="pedidos.php" class="text-gray-500 hover:text-gray-700 font-bold text-lg">
            <i class="fas fa-arrow-right"></i> Ir a Pedidos
        </a>
    </div>
</div>

<div id="contenedorCocina" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-6 pb-10">
    
    <div class="col-span-full text-center py-20 text-gray-400">
        <i class="fas fa-circle-notch fa-spin fa-3x mb-4 text-orange-300"></i>
        <p class="text-2xl font-bold">Conectando con el sistema...</p>
    </div>

</div>

<script src="../assets/js/cocina.js"></script>

<?php include '../partials/footer.php'; ?>