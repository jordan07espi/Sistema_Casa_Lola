<?php
// Archivo: controller/LoginController.php
session_start();
require_once '../model/UsuarioDAO.php';
require_once '../model/LoginDAO.php'; // Incluimos el nuevo DAO

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Método no permitido.'];
$maxIntentos = 5; // Número máximo de intentos permitidos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cedula = trim($_POST['cedula'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'];

    $loginDAO = new LoginDAO();

    // 1. Verificar si el usuario está bloqueado
    $intentos = $loginDAO->contarIntentosRecientes($cedula, $ip);
    if ($intentos >= $maxIntentos) {
        $response['message'] = 'Has excedido el número de intentos. Por favor, espera 15 minutos.';
        echo json_encode($response);
        exit();
    }

    // 2. Intentar autenticar al usuario
    $usuarioDAO = new UsuarioDAO();
    $usuario = $usuarioDAO->obtenerUsuarioPorCedula($cedula);
    
    if ($usuario && password_verify($password, $usuario->password)) {
        // ÉXITO: Limpiar intentos y crear sesión
        $loginDAO->limpiarIntentos($cedula, $ip);
        
        $_SESSION['id_usuario'] = $usuario->id_usuario;
        $_SESSION['nombre_completo'] = $usuario->nombre_completo;
        $_SESSION['cedula'] = $usuario->cedula;
        $_SESSION['rol'] = $usuario->nombre_rol;
        $_SESSION['last_activity'] = time();
        
        $response['success'] = true;
        $response['message'] = 'Inicio de sesión exitoso.';
        // Redirigir según el rol
        if ($usuario->nombre_rol === 'CEO') {
            $response['redirect'] = 'view/admin/reportes.php'; // A la futura página de reportes
        } else {
            $response['redirect'] = 'view/admin/dashboard.php';
        }
    } else {
        // FALLO: Registrar intento y enviar mensaje
        $loginDAO->registrarIntentoFallido($cedula, $ip);
        $intentosRestantes = $maxIntentos - ($intentos + 1);
        $response['message'] = "Cédula o contraseña incorrectos. Te quedan {$intentosRestantes} intentos.";
    }
}

echo json_encode($response);
?>