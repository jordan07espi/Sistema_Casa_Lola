<?php
session_start();
if (isset($_SESSION['id_usuario'])) {
    header('Location: view/admin/dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale-1.0">
    <title>Login - Casa Lola</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-sm p-8 bg-white rounded-lg shadow-lg border-t-4 border-orange-600">
        <div class="flex justify-center mb-6">
            <img src="view/assets/img/logo.png" alt="Casa Lola Logo" class="h-32 w-auto object-contain">
        </div>

        <?php
        if (isset($_GET['error'])) {
            $mensaje = '';
            if ($_GET['error'] === 'inactive') $mensaje = 'Tu sesión ha expirado por inactividad.';
            elseif ($_GET['error'] === 'no_session') $mensaje = 'Debes iniciar sesión para acceder.';
            
            if ($mensaje) {
                echo '<div class="bg-orange-100 border-l-4 border-orange-500 text-orange-700 p-4 mb-4 text-sm" role="alert"><p>' . $mensaje . '</p></div>';
            }
        }
        ?>
        
        <form id="loginForm" method="POST">
            <div class="mb-4">
                <label for="cedula" class="block text-gray-700 font-medium mb-2">Cédula</label>
                <input type="text" id="cedula" name="cedula" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all" 
                    placeholder="Ingrese su cédula" required>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700 font-medium mb-2">Contraseña</label>
                <input type="password" id="password" name="password" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all" 
                    placeholder="••••••••" required>
            </div>
            
            <div id="errorMessage" class="text-red-500 text-sm text-center mb-4 font-semibold"></div>

            <button type="submit" class="w-full bg-orange-600 text-white font-bold py-2 rounded-lg hover:bg-orange-700 transition duration-300 shadow-md transform hover:scale-[1.02]">
                Ingresar al Sistema
            </button>
        </form>
    </div>

    <script src="view/assets/js/login.js"></script>
</body>
</html>