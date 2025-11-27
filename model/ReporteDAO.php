<?php
// Archivo: model/ReporteDAO.php
require_once __DIR__ . '/../config/Conexion.php';

class ReporteDAO {
    private $conexion;

    public function __construct() {
        $db = new Conexion();
        $this->conexion = $db->getConnection();
    }

    public function obtenerResumen($desde, $hasta) {
        // 1. Total Dinero y Cantidad de Pedidos (Excluyendo Cancelados)
        $sql = "SELECT 
                    IFNULL(SUM(total), 0) as total_ventas,
                    COUNT(*) as cantidad_pedidos
                FROM pedidos 
                WHERE fecha_entrega BETWEEN :desde AND :hasta 
                AND estado != 'Cancelado'";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':desde', $desde);
        $stmt->bindValue(':hasta', $hasta);
        $stmt->execute();
        $resumen = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Producto Más Vendido (SOLO PROTEÍNAS -> requiere_tillo = 1)
        // Modificación: Agregamos "AND pr.requiere_tillo = 1"
        $sqlTop = "SELECT pr.nombre_producto, SUM(dp.cantidad) as total_cantidad
                   FROM detalles_pedido dp
                   INNER JOIN pedidos p ON dp.id_pedido = p.id_pedido
                   INNER JOIN productos pr ON dp.id_producto = pr.id_producto
                   WHERE p.fecha_entrega BETWEEN :desde AND :hasta
                   AND p.estado != 'Cancelado'
                   AND pr.requiere_tillo = 1 
                   GROUP BY pr.id_producto
                   ORDER BY total_cantidad DESC
                   LIMIT 1";
        
        $stmtTop = $this->conexion->prepare($sqlTop);
        $stmtTop->bindValue(':desde', $desde);
        $stmtTop->bindValue(':hasta', $hasta);
        $stmtTop->execute();
        $topProducto = $stmtTop->fetch(PDO::FETCH_ASSOC);

        return [
            'dinero' => $resumen['total_ventas'],
            'pedidos' => $resumen['cantidad_pedidos'],
            'top_producto' => $topProducto ? $topProducto['nombre_producto'] : 'N/A',
            'top_cantidad' => $topProducto ? $topProducto['total_cantidad'] : 0
        ];
    }

    public function obtenerListadoReporte($desde, $hasta) {
        // Traemos datos detallados para la tabla
        $sql = "SELECT p.id_pedido, p.codigo_pedido, p.fecha_entrega, p.total, p.estado, 
                       c.nombre as cliente, u.nombre_completo as usuario
                FROM pedidos p
                INNER JOIN clientes c ON p.id_cliente = c.id_cliente
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE p.fecha_entrega BETWEEN :desde AND :hasta
                ORDER BY p.fecha_entrega ASC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':desde', $desde);
        $stmt->bindValue(':hasta', $hasta);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // NUEVO MÉTODO PARA GRÁFICOS
    public function obtenerVentasUltimosDias($dias = 7) {
        $sql = "SELECT 
                    DATE(fecha_entrega) as fecha, 
                    SUM(total) as total_venta
                FROM pedidos 
                WHERE fecha_entrega >= DATE(NOW()) - INTERVAL :dias DAY
                AND estado != 'Cancelado'
                GROUP BY DATE(fecha_entrega)
                ORDER BY fecha ASC";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':dias', $dias, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // NUEVO MÉTODO PARA GRÁFICO DE ESTADOS
    public function obtenerConteoPorEstado() {
        $sql = "SELECT estado, COUNT(*) as cantidad FROM pedidos GROUP BY estado";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDesgloseProteinas($desde, $hasta) {
        $sql = "SELECT 
                    pr.nombre_producto, 
                    SUM(dp.cantidad) as cantidad_total
                FROM detalles_pedido dp
                INNER JOIN pedidos p ON dp.id_pedido = p.id_pedido
                INNER JOIN productos pr ON dp.id_producto = pr.id_producto
                WHERE p.fecha_entrega BETWEEN :desde AND :hasta
                AND p.estado != 'Cancelado'
                AND pr.requiere_tillo = 1 
                GROUP BY pr.id_producto
                ORDER BY cantidad_total DESC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':desde', $desde);
        $stmt->bindValue(':hasta', $hasta);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>