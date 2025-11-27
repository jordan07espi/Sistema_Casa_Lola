<?php
// Archivo: controller/DashboardController.php
session_start();
require_once '../model/ReporteDAO.php';
require_once '../model/ProductoDAO.php'; // Para alertas de stock si las hubiera

header('Content-Type: application/json');

$response = ['success' => false];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

$reporteDAO = new ReporteDAO();

try {
    switch ($action) {
        case 'obtener_datos':
            // Recibimos fechas del POST, o usamos el mes actual por defecto
            $inicio = $_POST['desde'] ?? date('Y-m-01');
            $fin = $_POST['hasta'] ?? date('Y-m-t');

            // 1. KPIs Generales (Con rango dinámico)
            $kpis = $reporteDAO->obtenerResumen($inicio, $fin);

            // 2. Gráficos (Usamos el mismo rango de fechas para consistencia)
            // Nota: Podrías ajustar obtenerVentasUltimosDias en el DAO para aceptar fechas exactas si quisieras
            // Por ahora mantenemos la lógica de la semana para el gráfico de barras, o puedes usar:
            $ventasSemana = $reporteDAO->obtenerVentasUltimosDias(7); 

            // 3. Estados
            $estados = $reporteDAO->obtenerConteoPorEstado();

            $response['success'] = true;
            $response['kpis'] = $kpis;
            $response['graficos'] = [
                'ventas' => $ventasSemana,
                'estados' => $estados
            ];
            break;
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>