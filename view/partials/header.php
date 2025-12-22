<?php
// Archivo: view/partials/header.php

// 1. INICIO DE SESIÓN SEGURO
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. CONTROL DE CACHÉ
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// 3. VERIFICACIÓN DE SESIÓN
$tiempo_limite_inactividad = 30 * 60; // 30 minutos

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../login.php?error=no_session');
    exit();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $tiempo_limite_inactividad)) {
    session_unset();
    session_destroy();
    header('Location: ../../login.php?error=inactive');
    exit();
}

$_SESSION['last_activity'] = time();

$nombreUsuario = $_SESSION['nombre_completo'] ?? 'Usuario';
$rolUsuario = $_SESSION['rol'] ?? 'Invitado';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Casa Lola - Gestión</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="icon" type="image/png" href="../../view/assets/img/icon-192.png">
    <link rel="manifest" href="../../manifest.json">
    <meta name="theme-color" content="#ea580c">
    <link rel="apple-touch-icon" href="../../view/assets/img/icon-192.png">

    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('../../service-worker.js')
            .then(reg => console.log('SW registrado correctamente:', reg.scope))
            .catch(err => console.log('Error al registrar SW:', err));
        }
    </script>

    <style>
        @keyframes bounceIn {
            0% { transform: scale(0.9); opacity: 0; }
            60% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(1); }
        }
        .animate-bounce-in {
            animation: bounceIn 0.6s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        }
    </style>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen font-sans"> 

    <header class="bg-gray-900 text-white shadow-lg border-b-4 border-orange-600 relative z-50">
        <div class="container mx-auto flex items-center justify-between p-4 flex-row-reverse md:flex-row">
            
            <div class="flex items-center space-x-3 z-50">
                <div class="bg-orange-600 p-2 rounded-full">
                    <i class="fas fa-fire fa-lg text-white"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-wider uppercase">Casa Lola</h1>
                    <span class="text-xs text-orange-400 block -mt-1 tracking-widest">Hornos a Leña</span>
                </div>
            </div>

            <nav class="hidden md:flex space-x-6 font-medium">
                <?php if ($rolUsuario === 'Administrador') : ?>
                    <a href="dashboard.php" class="hover:text-orange-400 transition-colors flex items-center gap-2">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                <?php endif; ?>

                <?php if ($rolUsuario === 'Administrador' || $rolUsuario === 'Empleado') : ?>
                    <a href="pedidos.php" class="hover:text-orange-400 transition-colors flex items-center gap-2">
                        <i class="fas fa-utensils"></i> Pedidos
                    </a>
                    
                    <a href="cocina.php" class="hover:text-orange-400 transition-colors flex items-center gap-2 text-orange-100 font-bold bg-orange-800 bg-opacity-30 px-3 py-1 rounded-md">
                        <i class="fas fa-fire-burner"></i> Cocina
                    </a>
                    
                    <a href="clientes.php" class="hover:text-orange-400 transition-colors flex items-center gap-2">
                        <i class="fas fa-users"></i> Clientes
                    </a>
                <?php endif; ?>

                <?php if ($rolUsuario === 'Administrador') : ?>
                    <a href="reportes.php" class="hover:text-orange-400 transition-colors flex items-center gap-2">
                        <i class="fas fa-chart-pie"></i> Reportes
                    </a>
                    <a href="usuarios.php" class="hover:text-orange-400 transition-colors flex items-center gap-2">
                        <i class="fas fa-user-shield"></i> Usuarios
                    </a>
                <?php endif; ?>
            </nav>

            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block">
                    <span class="block text-sm font-semibold text-orange-100"><?php echo htmlspecialchars($nombreUsuario); ?></span>
                    <span class="block text-xs text-gray-400"><?php echo $rolUsuario; ?></span>
                </div>

                <a href="../../controller/logout.php" class="hidden md:inline-flex bg-orange-700 hover:bg-orange-600 px-4 py-2 rounded-lg transition duration-300 shadow text-white" title="Cerrar Sesión">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
                
                <button id="btnMenuMovil" class="md:hidden text-white hover:text-orange-400 focus:outline-none relative z-[60]">
                    <i class="fas fa-bars fa-2x transition-transform duration-300" id="iconMenu"></i>
                </button>
            </div>
        </div>

        <div id="mobileOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden transition-opacity duration-300 backdrop-blur-sm"></div>

        <div id="menuMovil" class="fixed top-0 left-0 h-full w-64 bg-gray-900 shadow-2xl transform -translate-x-full transition-transform duration-300 ease-in-out z-50 pt-20 flex flex-col border-r border-gray-700">
            
            <div class="px-6 mb-6">
                <div class="p-4 bg-gray-800 rounded-lg border border-gray-700 shadow-inner">
                    <p class="text-orange-400 text-xs uppercase font-bold mb-1">Usuario Activo</p>
                    <p class="text-white font-bold text-lg leading-tight truncate"><?php echo htmlspecialchars($nombreUsuario); ?></p>
                    <p class="text-gray-400 text-sm"><?php echo $rolUsuario; ?></p>
                </div>
            </div>

            <nav class="flex-1 px-4 space-y-2 overflow-y-auto">
                <?php if ($rolUsuario === 'Administrador') : ?>
                    <a href="dashboard.php" class="flex items-center space-x-3 px-4 py-3 text-white hover:bg-orange-700 hover:text-white rounded-lg transition-colors group">
                        <i class="fas fa-chart-line w-6 text-center text-orange-500 group-hover:text-white"></i> 
                        <span class="font-medium">Dashboard</span>
                    </a>
                <?php endif; ?>

                <?php if ($rolUsuario === 'Administrador' || $rolUsuario === 'Empleado') : ?>
                    <a href="pedidos.php" class="flex items-center space-x-3 px-4 py-3 text-white hover:bg-orange-700 hover:text-white rounded-lg transition-colors group">
                        <i class="fas fa-utensils w-6 text-center text-orange-500 group-hover:text-white"></i> 
                        <span class="font-medium">Pedidos</span>
                    </a>
                    
                    <a href="cocina.php" class="flex items-center space-x-3 px-4 py-3 text-white hover:bg-orange-700 hover:text-white rounded-lg transition-colors group">
                        <i class="fas fa-fire-burner w-6 text-center text-orange-500 group-hover:text-white"></i> 
                        <span class="font-medium">Cocina</span>
                    </a>

                    <a href="clientes.php" class="flex items-center space-x-3 px-4 py-3 text-white hover:bg-orange-700 hover:text-white rounded-lg transition-colors group">
                        <i class="fas fa-users w-6 text-center text-orange-500 group-hover:text-white"></i> 
                        <span class="font-medium">Clientes</span>
                    </a>
                <?php endif; ?>

                <?php if ($rolUsuario === 'Administrador') : ?>
                    <a href="reportes.php" class="flex items-center space-x-3 px-4 py-3 text-white hover:bg-orange-700 hover:text-white rounded-lg transition-colors group">
                        <i class="fas fa-chart-pie w-6 text-center text-orange-500 group-hover:text-white"></i> 
                        <span class="font-medium">Reportes</span>
                    </a>
                    <a href="usuarios.php" class="flex items-center space-x-3 px-4 py-3 text-white hover:bg-orange-700 hover:text-white rounded-lg transition-colors group">
                        <i class="fas fa-user-shield w-6 text-center text-orange-500 group-hover:text-white"></i> 
                        <span class="font-medium">Usuarios</span>
                    </a>
                <?php endif; ?>
            </nav>

            <div class="p-4 border-t border-gray-800 mt-auto">
                <a href="../../controller/logout.php" class="flex items-center justify-center w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg transition-colors shadow-lg">
                    <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </header>

    <main class="container mx-auto p-4 flex-grow mt-4">

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('btnMenuMovil');
            const menu = document.getElementById('menuMovil');
            const overlay = document.getElementById('mobileOverlay');
            const icon = document.getElementById('iconMenu');
            let isOpen = false;

            function toggleMenu() {
                isOpen = !isOpen;
                if (isOpen) {
                    // Abrir: Quitar la clase que lo esconde a la izquierda
                    menu.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times'); // Cambiar a X
                } else {
                    // Cerrar: Volver a esconder a la izquierda
                    menu.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars'); // Cambiar a Hamburguesa
                }
            }

            if(btn) btn.addEventListener('click', toggleMenu);
            if(overlay) overlay.addEventListener('click', toggleMenu); // Cerrar al tocar fuera
        });
    </script>