<?php
// Archivo: controller/ClienteController.php
require_once '../model/ClienteDAO.php';
require_once '../model/dto/Cliente.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Acción no válida.'];

$clienteDAO = new ClienteDAO();

// Función auxiliar para validar cédula (solo si existe)
function validarCedulaPHP($cedula) {
    if (!$cedula) return true;
    if (strlen($cedula) !== 10) return false;
    $digitoRegion = substr($cedula, 0, 2);
    if ($digitoRegion < 1 || $digitoRegion > 24) return false;
    $coef = [2, 1, 2, 1, 2, 1, 2, 1, 2];
    $suma = 0;
    for ($i = 0; $i < 9; $i++) {
        $val = substr($cedula, $i, 1) * $coef[$i];
        $suma += ($val >= 10) ? $val - 9 : $val;
    }
    $res = $suma % 10 === 0 ? 0 : 10 - ($suma % 10);
    return $res == substr($cedula, 9, 1);
}

try {
    switch ($action) {
        case 'listar':
            // ... (código listar igual al anterior) ...
            $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
            $limite = 20;
            $inicio = ($pagina - 1) * $limite;

            $lista = $clienteDAO->listar($inicio, $limite, $busqueda);
            $totalRegistros = $clienteDAO->contarTotal($busqueda);
            $totalPaginas = ceil($totalRegistros / $limite);

            $response['success'] = true;
            $response['data'] = $lista;
            $response['pagination'] = [
                'pagina_actual' => $pagina,
                'total_paginas' => $totalPaginas,
                'total_registros' => $totalRegistros
            ];
            break;

        case 'agregar':
            $cedula = isset($_POST['cedula']) ? trim($_POST['cedula']) : null;
            if ($cedula === '') $cedula = null; 
            
            // Sanitización y Mayúsculas
            $nombre = mb_strtoupper(trim($_POST['nombre'] ?? ''), 'UTF-8');
            $telefono = trim($_POST['telefono'] ?? '');
            $email = trim($_POST['email'] ?? '');
            if ($email === '') $email = null;

            // --- VALIDACIÓN DE DUPLICADO (NOMBRE + TELÉFONO) ---
            if ($clienteDAO->existeClienteRapido($nombre, $telefono)) {
                $response['message'] = 'Ya existe un cliente registrado con ese Nombre y Teléfono.';
                echo json_encode($response);
                exit; // Detenemos aquí
            }
            
            // Crear Objeto
            $cliente = new Cliente();
            $cliente->cedula = $cedula;
            $cliente->nombre = $nombre;
            $cliente->email = $email;
            $cliente->telefono = $telefono;

            $resultado = $clienteDAO->agregar($cliente);

            if ($resultado) {
                $response['success'] = true;
                $response['message'] = 'Cliente registrado correctamente.';
                $response['nuevo_cliente'] = [
                    'id' => $resultado, 
                    'nombre' => $cliente->nombre,
                    'telefono' => $cliente->telefono
                ];
            } else {
                $response['message'] = 'Error técnico al guardar el cliente.';
            }
            break;

        case 'obtener':
             // ... (Igual que antes) ...
            $id = $_POST['id_cliente'];
            $data = $clienteDAO->obtenerPorId($id);
            if ($data) {
                $response['success'] = true;
                $response['data'] = $data;
            } else {
                $response['message'] = 'Cliente no encontrado.';
            }
            break;
            
        case 'activar':
            $id = $_POST['id_cliente'];
            if ($clienteDAO->activar($id)) {
                $response['success'] = true;
                $response['message'] = 'Cliente reactivado correctamente.';
            } else {
                $response['message'] = 'Error al reactivar cliente.';
            }
            break;

        case 'actualizar':
             // ... (Igual que antes) ...
            $id = $_POST['id_cliente'];
            $cedula = trim($_POST['cedula'] ?? '');
            if ($cedula === '') $cedula = null;

            if ($cedula && !validarCedulaPHP($cedula)) {
                $response['message'] = 'La cédula ingresada no es válida.';
                break;
            }
            if ($cedula && $clienteDAO->existeCedula($cedula, $id)) {
                $response['message'] = 'Esa cédula ya pertenece a otro cliente.';
                break;
            }

            $cliente = new Cliente();
            $cliente->id_cliente = $id;
            $cliente->cedula = $cedula;
            $cliente->nombre = mb_strtoupper(trim($_POST['nombre'] ?? ''), 'UTF-8');
            $cliente->email = trim($_POST['email'] ?? '');
            if ($cliente->email === '') $cliente->email = null;
            $cliente->telefono = trim($_POST['telefono'] ?? '');

            if ($clienteDAO->actualizar($cliente)) {
                $response['success'] = true;
                $response['message'] = 'Datos actualizados.';
            } else {
                $response['message'] = 'No se pudo actualizar.';
            }
            break;

        case 'eliminar':
             // ... (Igual que antes) ...
            $id = $_POST['id_cliente'];
            if ($clienteDAO->eliminar($id)) {
                $response['success'] = true;
                $response['message'] = 'Cliente eliminado.';
            } else {
                $response['message'] = 'Error al eliminar.';
            }
            break;
    }
} catch (Exception $e) {
    $response['message'] = 'Error del servidor: ' . $e->getMessage();
}

echo json_encode($response);
?>