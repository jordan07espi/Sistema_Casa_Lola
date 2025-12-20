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
        // --- 1. VERIFICAR SI UN TILLO ESTÁ OCUPADO ---
        case 'verificar_tillo':
            $codigo = $_POST['codigo_pedido'] ?? '';
            $ocupado = $pedidoDAO->verificarTilloOcupado($codigo);
            $response['success'] = true;
            $response['ocupado'] = $ocupado;
            break;

        // --- 2. REGISTRAR UN NUEVO PEDIDO ---
        case 'registrar':
            // A. Procesar Tillos (Array multidimensional: [id_producto] => [codigos...])
            $mapaTillos = $_POST['tillos_asignados'] ?? []; 
            $tillosParaGuardar = []; // Estructura plana final: [['codigo' => '...', 'id_producto' => 1], ...]

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

            // B. Validar si algún tillo ya está ocupado (Seguridad en servidor)
            foreach ($tillosParaGuardar as $item) {
                if ($pedidoDAO->verificarTilloOcupado($item['codigo'])) {
                    $response['message'] = "El Tillo {$item['codigo']} ya está ocupado. Por favor verifique.";
                    echo json_encode($response);
                    exit;
                }
            }

            // C. Manejo de la Imagen (Evidencia)
            $rutaFoto = null;
            if (isset($_FILES['evidencia_foto']) && $_FILES['evidencia_foto']['error'] === UPLOAD_ERR_OK) {
                $nombreArchivo = uniqid() . '_' . basename($_FILES['evidencia_foto']['name']);
                $directorio = '../uploads/evidencias/';
                
                if (!is_dir($directorio)) mkdir($directorio, 0777, true);

                if (move_uploaded_file($_FILES['evidencia_foto']['tmp_name'], $directorio . $nombreArchivo)) {
                    $rutaFoto = $nombreArchivo;
                }
            }

            // D. Crear Objeto Pedido (Datos Cabecera)
            $pedido = new Pedido();
            $pedido->id_cliente = $_POST['id_cliente'];
            $pedido->id_usuario = $_SESSION['id_usuario']; 
            $pedido->fecha_entrega = $_POST['fecha_entrega'];
            $pedido->hora_entrega = $_POST['hora_entrega'];
            $pedido->total = $_POST['precio_total']; 
            $pedido->pagado = isset($_POST['es_pagado']) ? 1 : 0;
            $pedido->observaciones = $_POST['observaciones'];
            $pedido->evidencia_foto = $rutaFoto; 

            // E. Procesar Detalles (Productos y Cantidades)
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

            // F. Guardar en Base de Datos
            $resultado = $pedidoDAO->registrar($pedido, $detalles, $tillosParaGuardar);

            if ($resultado !== false) { // Si no es false, es el ID
                $response['success'] = true;
                $response['message'] = 'Pedido registrado correctamente.';
                $response['id_pedido'] = $resultado; // <--- ENVIAMOS EL ID AL JS
            } else {
                $response['message'] = 'Error al guardar el pedido en la base de datos.';
            }
            break;
            
        // --- 3. LISTAR PEDIDOS (Con filtros) ---
        case 'listar':
             $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
             $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
             $filtroEstado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
             
             // RECIBIR RANGO DE FECHAS
             $desde = isset($_GET['desde']) ? trim($_GET['desde']) : '';
             $hasta = isset($_GET['hasta']) ? trim($_GET['hasta']) : '';

             $limite = 10;
             $inicio = ($pagina - 1) * $limite;
             
             // PASAR AMBOS PARÁMETROS AL DAO
             $lista = $pedidoDAO->listar($inicio, $limite, $busqueda, $filtroEstado, $desde, $hasta);
             $totalRegistros = $pedidoDAO->contarTotal($busqueda, $filtroEstado, $desde, $hasta);
             
             $totalPaginas = ceil($totalRegistros / $limite);
             
             $response['success'] = true;
             $response['data'] = $lista;
             $response['pagination'] = [
                 'pagina_actual' => $pagina,
                 'total_paginas' => $totalPaginas,
                 'total_registros' => $totalRegistros
             ];
             break;

        // --- 4. CAMBIAR ESTADO (Pendiente -> Entregado/Cancelado) ---
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

        // --- 5. CAMBIAR ESTADO DE PAGO (AJAX) ---
        case 'cambiar_pago':
            $id = $_POST['id_pedido'];
            $pagado = $_POST['pagado']; // 1 o 0

            if ($pedidoDAO->cambiarEstadoPago($id, $pagado)) {
                $response['success'] = true;
                $txt = $pagado == 1 ? 'Pagado' : 'Pendiente de Pago';
                $response['message'] = 'Pago actualizado a: ' . $txt;
            } else {
                $response['message'] = 'No se pudo actualizar el pago.';
            }
            break;
    }
} catch (Exception $e) {
    $response['message'] = 'Error del servidor: ' . $e->getMessage();
}

echo json_encode($response);
?>