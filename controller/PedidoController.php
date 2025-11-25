<?php
// Archivo: controller/PedidoController.php
session_start();
require_once '../model/PedidoDAO.php';
require_once '../model/dto/Pedido.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Acción no válida.'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

$pedidoDAO = new PedidoDAO();

try {
    switch ($action) {
        case 'verificar_tillo':
            $codigo = $_POST['codigo_pedido'] ?? '';
            $ocupado = $pedidoDAO->verificarTilloOcupado($codigo);
            $response['success'] = true;
            $response['ocupado'] = $ocupado;
            break;

        case 'registrar':
            // --- NUEVA LÓGICA DE RECEPCIÓN DE TILLOS ---
            $mapaTillos = $_POST['tillos_asignados'] ?? []; 
            $tillosParaGuardar = []; // Estructura: ['codigo' => '...', 'id_producto' => 1]

            // Aplanamos el array para validación y guardado
            foreach ($mapaTillos as $idProd => $codigos) {
                foreach ($codigos as $codigo) {
                    if (!empty($codigo)) {
                        $tillosParaGuardar[] = [
                            'codigo' => $codigo, 
                            'id_producto' => $idProd
                        ];
                    }
                }
            }

            // 1. Validar ocupados
            if (empty($tillosParaGuardar)) {
                // Nota: Podrías permitir pedidos sin tillos si son solo guarniciones
                // pero si quieres obligar, deja este check.
            }

            foreach ($tillosParaGuardar as $item) {
                if ($pedidoDAO->verificarTilloOcupado($item['codigo'])) {
                    $response['message'] = "El Tillo {$item['codigo']} ya está ocupado.";
                    echo json_encode($response);
                    exit;
                }
            }

            // 2. Manejo de la Imagen (Igual que antes) ...
            $rutaFoto = null;
            if (isset($_FILES['evidencia_foto']) && $_FILES['evidencia_foto']['error'] === UPLOAD_ERR_OK) {
                $nombreArchivo = uniqid() . '_' . basename($_FILES['evidencia_foto']['name']);
                $directorio = '../uploads/evidencias/';
                if (!is_dir($directorio)) mkdir($directorio, 0777, true);
                if (move_uploaded_file($_FILES['evidencia_foto']['tmp_name'], $directorio . $nombreArchivo)) {
                    $rutaFoto = $nombreArchivo;
                }
            }

            // 3. Crear Objeto Pedido
            $pedido = new Pedido();
            // Ya no asignamos un solo código aquí, lo maneja el DAO con el array
            $pedido->id_cliente = $_POST['id_cliente'];
            $pedido->id_usuario = $_SESSION['id_usuario']; 
            $pedido->fecha_entrega = $_POST['fecha_entrega'];
            $pedido->hora_entrega = $_POST['hora_entrega'];
            $pedido->total = $_POST['precio_total']; 
            $pedido->observaciones = $_POST['observaciones'];
            $pedido->evidencia_foto = $rutaFoto; 

            // 4. Procesar Detalles (Productos)
            $detalles = [];
            if (isset($_POST['productos']) && is_array($_POST['productos'])) {
                foreach ($_POST['productos'] as $idProd => $cantidad) {
                    if ($cantidad > 0) {
                        $detalles[] = ['id_producto' => $idProd, 'cantidad' => $cantidad];
                    }
                }
            }

            if (empty($detalles)) {
                $response['message'] = 'Debe ingresar al menos una cantidad en los productos.';
                break;
            }

            // 5. Guardar (Enviamos el nuevo array estructurado)
            // NOTA: Asegúrate de pasar $tillosParaGuardar en lugar de $tillos
            if ($pedidoDAO->registrar($pedido, $detalles, $tillosParaGuardar)) {
                $response['success'] = true;
                $response['message'] = 'Pedido registrado correctamente.';
            } else {
                $response['message'] = 'Error al guardar el pedido en la base de datos.';
            }
            break;

            // 2. Manejo de la Imagen (Evidencia)
            $rutaFoto = null;
            if (isset($_FILES['evidencia_foto']) && $_FILES['evidencia_foto']['error'] === UPLOAD_ERR_OK) {
                $nombreArchivo = uniqid() . '_' . basename($_FILES['evidencia_foto']['name']);
                $directorio = '../uploads/evidencias/';
                
                if (!is_dir($directorio)) mkdir($directorio, 0777, true); // Crear carpeta si no existe

                if (move_uploaded_file($_FILES['evidencia_foto']['tmp_name'], $directorio . $nombreArchivo)) {
                    $rutaFoto = $nombreArchivo;
                }
            }

            // 3. Crear Objeto Pedido
            $pedido = new Pedido();
            $pedido->codigo_pedido = $_POST['tillo'];
            $pedido->id_cliente = $_POST['id_cliente'];
            $pedido->id_usuario = $_SESSION['id_usuario']; // Del usuario logueado
            $pedido->fecha_entrega = $_POST['fecha_entrega'];
            $pedido->hora_entrega = $_POST['hora_entrega'];
            $pedido->total = $_POST['precio_total']; // Precio abierto
            $pedido->observaciones = $_POST['observaciones'];
            $pedido->evidencia_foto = $rutaFoto;

            // 4. Procesar Detalles (Productos dinámicos)
            // Los productos vienen en el POST como array: productos[id_producto] = cantidad
            $detalles = [];
            if (isset($_POST['productos']) && is_array($_POST['productos'])) {
                foreach ($_POST['productos'] as $idProd => $cantidad) {
                    if ($cantidad > 0) {
                        $detalles[] = ['id_producto' => $idProd, 'cantidad' => $cantidad];
                    }
                }
            }

            if (empty($detalles)) {
                $response['message'] = 'Debe ingresar al menos una cantidad en los productos.';
                break;
            }

            // 5. Guardar en BD
            if ($pedidoDAO->registrar($pedido, $detalles)) {
                $response['success'] = true;
                $response['message'] = 'Pedido registrado correctamente.';
            } else {
                $response['message'] = 'Error al guardar el pedido en la base de datos.';
            }
            break;
            
        case 'listar':
             // ... (Tu código existente de listar) ...
             $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
             $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
             $filtroEstado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
             $limite = 10;
             $inicio = ($pagina - 1) * $limite;
             $lista = $pedidoDAO->listar($inicio, $limite, $busqueda, $filtroEstado);
             $totalRegistros = $pedidoDAO->contarTotal($busqueda, $filtroEstado);
             $totalPaginas = ceil($totalRegistros / $limite);
             $response['success'] = true;
             $response['data'] = $lista;
             $response['pagination'] = [
                 'pagina_actual' => $pagina,
                 'total_paginas' => $totalPaginas,
                 'total_registros' => $totalRegistros
             ];
             break;

        case 'cambiar_estado':
            $id = $_POST['id_pedido'];
            $estado = $_POST['nuevo_estado'];

            // Validar estados permitidos
            $estadosPermitidos = ['Pendiente', 'Entregado', 'Cancelado'];
            if (!in_array($estado, $estadosPermitidos)) {
                $response['message'] = 'Estado no válido.';
                break;
            }

            if ($pedidoDAO->cambiarEstado($id, $estado)) {
                $response['success'] = true;
                $response['message'] = 'Estado actualizado a: ' . $estado;
            } else {
                $response['message'] = 'No se pudo actualizar el estado.';
            }
            break;
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>