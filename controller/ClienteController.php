<?php
// Archivo: controller/ClienteController.php
require_once '../model/ClienteDAO.php';
require_once '../model/dto/Cliente.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Acción no válida.'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

$clienteDAO = new ClienteDAO();

// Función auxiliar para validar cédula en el servidor (Seguridad extra)
function validarCedulaPHP($cedula) {
    if (strlen($cedula) !== 10) return false;
    $digitoRegion = substr($cedula, 0, 2);
    if ($digitoRegion < 1 || $digitoRegion > 24) return false;
    $tercerDigito = substr($cedula, 2, 1);
    if ($tercerDigito >= 6) return false;
    // Algoritmo simplificado de validación de checksum
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
            // Recibir página y búsqueda
            $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
            $limite = 20; // Cantidad de clientes por página
            
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
            $cedula = trim($_POST['cedula']);
            
            // 1. Validaciones de formato Servidor
            if (!validarCedulaPHP($cedula)) {
                $response['message'] = 'La cédula ingresada no es válida.';
                break;
            }
            
            // 2. Validación de duplicados (DAO)
            if ($clienteDAO->existeCedula($cedula)) {
                $response['message'] = 'Ya existe un cliente registrado con esta cédula.';
                break;
            }

            $cliente = new Cliente();
            $cliente->cedula = $cedula;
            $cliente->nombre = mb_strtoupper(trim($_POST['nombre']), 'UTF-8'); // Mayúsculas servidor
            $cliente->email = trim($_POST['email'] ?? '');
            $cliente->telefono = trim($_POST['telefono']);

            if ($clienteDAO->agregar($cliente)) {
                $response['success'] = true;
                $response['message'] = 'Cliente registrado correctamente.';
            } else {
                $response['message'] = 'Error técnico al guardar el cliente.';
            }
            break;

        case 'obtener':
            $id = $_POST['id_cliente'];
            $data = $clienteDAO->obtenerPorId($id);
            if ($data) {
                $response['success'] = true;
                $response['data'] = $data;
            } else {
                $response['message'] = 'Cliente no encontrado.';
            }
            break;

        case 'actualizar':
            $id = $_POST['id_cliente'];
            $cedula = trim($_POST['cedula']);

            // Validar formato
            if (!validarCedulaPHP($cedula)) {
                $response['message'] = 'La cédula ingresada no es válida.';
                break;
            }

            // Validar duplicado excluyendo el ID actual
            if ($clienteDAO->existeCedula($cedula, $id)) {
                $response['message'] = 'Esa cédula ya pertenece a otro cliente.';
                break;
            }

            $cliente = new Cliente();
            $cliente->id_cliente = $id;
            $cliente->cedula = $cedula;
            $cliente->nombre = mb_strtoupper(trim($_POST['nombre']), 'UTF-8');
            $cliente->email = trim($_POST['email'] ?? '');
            $cliente->telefono = trim($_POST['telefono']);

            if ($clienteDAO->actualizar($cliente)) {
                $response['success'] = true;
                $response['message'] = 'Datos actualizados.';
            } else {
                $response['message'] = 'No se pudo actualizar.';
            }
            break;

        case 'eliminar':
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