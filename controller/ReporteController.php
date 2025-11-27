<?php
// Archivo: controller/ReporteController.php
session_start();
require_once '../model/ReporteDAO.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Acción no válida.'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

$reporteDAO = new ReporteDAO();

try {
    switch ($action) {
        case 'generar':
            $desde = $_POST['desde'] ?? date('Y-m-01');
            $hasta = $_POST['hasta'] ?? date('Y-m-t');

            // 1. KPIs Generales
            $kpis = $reporteDAO->obtenerResumen($desde, $hasta);
            
            // 2. Lista Detallada de Pedidos
            $lista = $reporteDAO->obtenerListadoReporte($desde, $hasta);

            // 3. NUEVO: Desglose de Proteínas
            $proteinas = $reporteDAO->obtenerDesgloseProteinas($desde, $hasta);

            $response['success'] = true;
            $response['kpis'] = $kpis;
            $response['lista'] = $lista;
            $response['proteinas'] = $proteinas; // Enviamos el nuevo dato
            break;
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>