<?php
// Archivo: model/CocinaDAO.php
require_once __DIR__ . '/../config/Conexion.php';

class CocinaDAO {
    private $conexion;

    public function __construct() {
        $db = new Conexion();
        $this->conexion = $db->getConnection();
    }

    public function obtenerPedidosCocina() {
        // CAMBIO: Agregamos "AND p.estado_cocina = 'Pendiente'"
        // Así desaparecen de la pantalla apenas el cocinero confirma
        
        $sql = "SELECT 
                    p.id_pedido,
                    p.codigo_pedido,
                    p.fecha_entrega,
                    p.hora_entrega,
                    p.observaciones,
                    GROUP_CONCAT(CONCAT(dp.cantidad, ' ', pr.nombre_producto) SEPARATOR '|') as detalle_guarniciones
                FROM pedidos p
                INNER JOIN detalles_pedido dp ON p.id_pedido = dp.id_pedido
                INNER JOIN productos pr ON dp.id_producto = pr.id_producto
                WHERE p.estado = 'Pendiente' 
                AND p.estado_cocina = 'Pendiente' 
                AND pr.requiere_tillo = 0 
                GROUP BY p.id_pedido
                ORDER BY p.fecha_entrega ASC, p.hora_entrega ASC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // NUEVO MÉTODO: Marcar como listo en cocina
    public function marcarComoListo($id_pedido) {
        $sql = "UPDATE pedidos SET estado_cocina = 'Listo' WHERE id_pedido = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':id', $id_pedido);
        return $stmt->execute();
    }
}
?>