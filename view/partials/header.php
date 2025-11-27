<?php
// Archivo: view/partials/header.php

// 1. INICIO DE SESIÓN SEGURO
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. CONTROL DE CACHÉ DEL NAVEGADOR
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// 3. VERIFICACIÓN DE SESIÓN Y TIEMPO DE INACTIVIDAD
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
    <meta name="viewport" content="width=device-width, initial-scale-1.0">
    <title>Casa Lola - Gestión</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 flex flex-col min-h-screen font-sans"> 

    <header class="bg-gray-900 text-white shadow-lg border-b-4 border-orange-600">
        <div class="container mx-auto flex items-center justify-between p-4">
            
            <div class="flex items-center space-x-3">
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
                    
                    <a href="clientes.php" class="hover:text-orange-400 transition-colors flex items-center gap-2">
                        <i class="fas fa-users"></i> Clientes
                    </a>
                <?php endif; ?>

                <?php if ($rolUsuario === 'Administrador') : ?>
                    <a href="reportes.php" class="hover:text-orange-400 transition-colors flex items-center gap-2">
                        <i class="fas fa-chart-pie"></i> Reportes
                    </a>
                <?php endif; ?>

                <?php if ($rolUsuario === 'Administrador') : ?>
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

                <a href="../../controller/logout.php" class="bg-orange-700 hover:bg-orange-600 px-4 py-2 rounded-lg transition duration-300 shadow text-white" title="Cerrar Sesión">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
                
                <button id="btnMenuMovil" class="md:hidden text-white hover:text-orange-400 focus:outline-none">
                    <i class="fas fa-bars fa-2x"></i>
                </button>
            </div>
        </div>

        <div id="menuMovil" class="hidden md:hidden bg-gray-800 border-t border-gray-700 pb-4">
            
            <?php if ($rolUsuario === 'Administrador') : ?>
                <a href="dashboard.php" class="block px-4 py-3 text-white hover:bg-gray-700 hover:text-orange-400 border-b border-gray-700">
                    <i class="fas fa-chart-line w-6 text-center"></i> Dashboard
                </a>
            <?php endif; ?>

            <?php if ($rolUsuario === 'Administrador' || $rolUsuario === 'Empleado') : ?>
                <a href="pedidos.php" class="block px-4 py-3 text-white hover:bg-gray-700 hover:text-orange-400 border-b border-gray-700">
                    <i class="fas fa-utensils w-6 text-center"></i> Pedidos
                </a>
                <a href="clientes.php" class="block px-4 py-3 text-white hover:bg-gray-700 hover:text-orange-400 border-b border-gray-700">
                    <i class="fas fa-users w-6 text-center"></i> Clientes
                </a>
            <?php endif; ?>

            <?php if ($rolUsuario === 'Administrador') : ?>
                <a href="usuarios.php" class="block px-4 py-3 text-white hover:bg-gray-700 hover:text-orange-400">
                    <i class="fas fa-user-shield w-6 text-center"></i> Usuarios
                </a>
            <?php endif; ?>
        </div>
    </header>

    <main class="container mx-auto p-4 flex-grow mt-4">