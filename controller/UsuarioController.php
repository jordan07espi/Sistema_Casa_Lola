<?php
// Archivo: controller/UsuarioController.php
require_once '../model/UsuarioDAO.php';
require_once '../model/dto/Usuario.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Acción no reconocida.'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

$usuarioDAO = new UsuarioDAO();

switch ($action) {
    case 'listar':
        $response['success'] = true;
        $response['data'] = $usuarioDAO->listar();
        break;

    case 'listarRoles':
        $response['success'] = true;
        $response['data'] = $usuarioDAO->listarRoles();
        break;

    case 'agregar':
        $usuario = new Usuario();
        $usuario->nombre_completo = $_POST['nombre_completo'];
        $usuario->cedula = $_POST['cedula'];
        $usuario->password = $_POST['password'];
        $usuario->id_rol = $_POST['id_rol'];

        if ($usuarioDAO->agregar($usuario)) {
            $response['success'] = true;
            $response['message'] = 'Usuario agregado exitosamente.';
        } else {
            $response['message'] = 'Error al agregar el usuario. La cédula ya podría existir.';
        }
        break;
        
    case 'obtener':
        $id = $_POST['id_usuario'];
        $data = $usuarioDAO->obtenerPorId($id);
        if ($data) {
            $response['success'] = true;
            $response['data'] = $data;
        } else {
            $response['message'] = 'Usuario no encontrado.';
        }
        break;

    case 'actualizar':
        $usuario = new Usuario();
        $usuario->id_usuario = $_POST['id_usuario'];
        $usuario->nombre_completo = $_POST['nombre_completo'];
        $usuario->cedula = $_POST['cedula'];
        $usuario->id_rol = $_POST['id_rol'];
        $usuario->password = $_POST['password'] ?? ''; 

        if ($usuarioDAO->actualizar($usuario)) {
            $response['success'] = true;
            $response['message'] = 'Usuario actualizado exitosamente.';
        } else {
            $response['message'] = 'Error al actualizar el usuario.';
        }
        break;

    case 'eliminar':
        $id = $_POST['id_usuario'];
        if ($usuarioDAO->eliminar($id)) {
            $response['success'] = true;
            $response['message'] = 'Usuario eliminado exitosamente.';
        } else {
            $response['message'] = 'Error al eliminar el usuario.';
        }
        break;
}

echo json_encode($response);
?>