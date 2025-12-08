<?php
// Archivo: view/admin/cocina.php
session_start();
include '../partials/header.php';
?>

<audio id="audioAlerta" src="../assets/audio/ding.mp3" preload="auto"></audio>

<div id="pantallaInicio" class="fixed inset-0 bg-gray-900 bg-opacity-95 z-50 flex flex-col items-center justify-center text-white text-center transition-opacity duration-500">
    <div class="mb-6">
        <i class="fas fa-volume-up fa-5x text-orange-500 animate-bounce"></i>
    </div>
    <h1 class="text-4xl font-bold mb-4">Monitor de Cocina</h1>
    <p class="text-xl mb-8 text-gray-300">Haga clic para activar el sonido y comenzar</p>
    <button id="btnActivarAudio" class="bg-green-600 hover:bg-green-500 text-white font-bold py-4 px-10 rounded-full text-2xl shadow-lg transform hover:scale-105 transition">
        <i class="fas fa-play mr-3"></i> INICIAR SISTEMA
    </button>
</div>

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
        <p class="text-2xl font-bold">Esperando activación...</p>
    </div>
</div>

<script src="../assets/js/cocina.js"></script>

<?php include '../partials/footer.php'; ?>