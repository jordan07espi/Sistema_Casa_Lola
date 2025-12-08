<?php
// Archivo: controller/CocinaController.php
session_start();
require_once '../model/CocinaDAO.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Acción no válida.'];
$action = $_POST['action'] ?? '';

$cocinaDAO = new CocinaDAO();

try {
    if ($action === 'marcar_listo') {
        $id = $_POST['id_pedido'];
        
        if ($cocinaDAO->marcarComoListo($id)) {
            $response['success'] = true;
            $response['message'] = 'Pedido marcado como listo.';
        } else {
            $response['message'] = 'No se pudo actualizar el pedido.';
        }
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>