<?php
// Archivo: model/PedidoDAO.php
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/dto/Pedido.php';

class PedidoDAO {
    private $conexion;

    public function __construct() {
        $db = new Conexion();
        $this->conexion = $db->getConnection();
    }

    // LISTAR: Corregido para usar nombre_completo en lugar de u.usuario
    public function listar($inicio, $limite, $busqueda = '', $filtroEstado = '', $desde = '', $hasta = '') {
        // AQUÍ ESTABA EL ERROR: Usamos u.nombre_completo en vez de u.usuario
        $sql = "SELECT p.*, c.nombre AS nombre_cliente, u.nombre_completo AS nombre_usuario,
                (
                    SELECT GROUP_CONCAT(pt.codigo_tillo SEPARATOR ',')
                    FROM pedidos_tillos pt
                    WHERE pt.id_pedido = p.id_pedido
                    AND pt.codigo_tillo != p.codigo_pedido
                ) as tillos_secundarios
                
                FROM pedidos p 
                INNER JOIN clientes c ON p.id_cliente = c.id_cliente 
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario 
                WHERE 1=1"; 

        if (!empty($busqueda)) {
            $sql .= " AND (
                p.codigo_pedido LIKE :busqueda 
                OR c.nombre LIKE :busqueda
                OR EXISTS (
                    SELECT 1 FROM pedidos_tillos pt_search 
                    WHERE pt_search.id_pedido = p.id_pedido 
                    AND pt_search.codigo_tillo LIKE :busqueda
                )
            )";
        }
        
        if (!empty($filtroEstado)) {
            $sql .= " AND p.estado = :estado";
        }

        if (!empty($desde) && !empty($hasta)) {
            $sql .= " AND DATE(p.fecha_entrega) BETWEEN :desde AND :hasta";
        }

        $sql .= " ORDER BY p.id_pedido DESC LIMIT :inicio, :limite";

        $stmt = $this->conexion->prepare($sql);

        if (!empty($busqueda)) $stmt->bindValue(':busqueda', "%$busqueda%");
        if (!empty($filtroEstado)) $stmt->bindValue(':estado', $filtroEstado);
        if (!empty($desde) && !empty($hasta)) {
            $stmt->bindValue(':desde', $desde);
            $stmt->bindValue(':hasta', $hasta);
        }

        $stmt->bindValue(':inicio', (int)$inicio, PDO::PARAM_INT);
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public function contarTotal($busqueda = '', $filtroEstado = '', $desde = '', $hasta = '') {
        $sql = "SELECT COUNT(*) FROM pedidos p 
                INNER JOIN clientes c ON p.id_cliente = c.id_cliente 
                WHERE 1=1";
        
        if (!empty($busqueda)) {
             $sql .= " AND (
                p.codigo_pedido LIKE :busqueda 
                OR c.nombre LIKE :busqueda
                OR EXISTS (
                    SELECT 1 FROM pedidos_tillos pt_search 
                    WHERE pt_search.id_pedido = p.id_pedido 
                    AND pt_search.codigo_tillo LIKE :busqueda
                )
            )";
        }
        
        if (!empty($filtroEstado)) $sql .= " AND p.estado = :estado";
        if (!empty($desde) && !empty($hasta)) {
            $sql .= " AND DATE(p.fecha_entrega) BETWEEN :desde AND :hasta";
        }
        
        $stmt = $this->conexion->prepare($sql);
        
        if (!empty($busqueda)) $stmt->bindValue(':busqueda', "%$busqueda%");
        if (!empty($filtroEstado)) $stmt->bindValue(':estado', $filtroEstado);
        if (!empty($desde) && !empty($hasta)) {
            $stmt->bindValue(':desde', $desde);
            $stmt->bindValue(':hasta', $hasta);
        }
        
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // OBTENER POR ID: Corregido para usar nombre_completo y quitar c.cedula
    public function obtenerPorId($id_pedido) {
        // AQUÍ TAMBIÉN: Usamos u.nombre_completo
        $sql = "SELECT p.*, c.nombre AS nombre_cliente, c.telefono, u.nombre_completo AS nombre_usuario 
                FROM pedidos p 
                INNER JOIN clientes c ON p.id_cliente = c.id_cliente 
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario 
                WHERE p.id_pedido = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':id', $id_pedido);
        $stmt->execute();
        $pedido = $stmt->fetchObject('Pedido');

        if (!$pedido) return null;

        // Detalles
        $sqlDetalles = "SELECT dp.cantidad, pr.nombre_producto, dp.id_producto 
                        FROM detalles_pedido dp 
                        INNER JOIN productos pr ON dp.id_producto = pr.id_producto 
                        WHERE dp.id_pedido = :id";
        $stmtDet = $this->conexion->prepare($sqlDetalles);
        $stmtDet->bindValue(':id', $id_pedido);
        $stmtDet->execute();
        $pedido->detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

        // Tillos
        $sqlTillos = "SELECT codigo_tillo, estado, id_producto FROM pedidos_tillos WHERE id_pedido = :id";
        $stmtTillos = $this->conexion->prepare($sqlTillos);
        $stmtTillos->bindValue(':id', $id_pedido);
        $stmtTillos->execute();
        $pedido->tillos = $stmtTillos->fetchAll(PDO::FETCH_ASSOC);

        return $pedido;
    }
    
    public function verificarTilloOcupado($codigo_tillo) {
        $sql = "SELECT COUNT(*) FROM pedidos_tillos pt 
                INNER JOIN pedidos p ON pt.id_pedido = p.id_pedido
                WHERE pt.codigo_tillo = :codigo AND p.estado = 'Pendiente'";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':codigo', $codigo_tillo);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // REGISTRAR: Se mantiene igual, guardando el campo 'pagado'
    public function registrar($pedido, $detalles, $tillosArray) {
        try {
            $this->conexion->beginTransaction();
            
            $codigoPrincipal = !empty($tillosArray) ? $tillosArray[0]['codigo'] : 'S/T-' . time();
            
            $sql = "INSERT INTO pedidos (codigo_pedido, id_cliente, id_usuario, fecha_entrega, hora_entrega, total, pagado, observaciones, evidencia_foto, estado) 
                    VALUES (:codigo, :cliente, :usuario, :fecha, :hora, :total, :pagado, :obs, :foto, 'Pendiente')";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':codigo', $codigoPrincipal);
            $stmt->bindValue(':cliente', $pedido->id_cliente);
            $stmt->bindValue(':usuario', $pedido->id_usuario);
            $stmt->bindValue(':fecha', $pedido->fecha_entrega);
            $stmt->bindValue(':hora', $pedido->hora_entrega);
            $stmt->bindValue(':total', $pedido->total);
            $stmt->bindValue(':pagado', $pedido->pagado); 
            $stmt->bindValue(':obs', $pedido->observaciones);
            $stmt->bindValue(':foto', $pedido->evidencia_foto);
            $stmt->execute();
            $id_pedido = $this->conexion->lastInsertId();

            // Insertar Detalles
            $sqlDetalle = "INSERT INTO detalles_pedido (id_pedido, id_producto, cantidad) VALUES (:id_pedido, :id_producto, :cantidad)";
            $stmtDetalle = $this->conexion->prepare($sqlDetalle);
            foreach ($detalles as $detalle) {
                if ($detalle['cantidad'] > 0) {
                    $stmtDetalle->bindValue(':id_pedido', $id_pedido);
                    $stmtDetalle->bindValue(':id_producto', $detalle['id_producto']);
                    $stmtDetalle->bindValue(':cantidad', $detalle['cantidad']);
                    $stmtDetalle->execute();
                }
            }

            // Insertar Tillos
            $sqlTillo = "INSERT INTO pedidos_tillos (id_pedido, codigo_tillo, id_producto, estado) VALUES (:id_pedido, :codigo, :id_producto, 'Pendiente')";
            $stmtTillo = $this->conexion->prepare($sqlTillo);
            
            foreach ($tillosArray as $itemTillo) {
                if (!empty($itemTillo['codigo'])) {
                    $stmtTillo->bindValue(':id_pedido', $id_pedido);
                    $stmtTillo->bindValue(':codigo', $itemTillo['codigo']);
                    $stmtTillo->bindValue(':id_producto', $itemTillo['id_producto']); 
                    $stmtTillo->execute();
                }
            }
            
            $this->conexion->commit();
            return $id_pedido;         
            
        } catch (Exception $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            return false;
        }
    }

    public function cambiarEstado($id_pedido, $nuevo_estado) {
        $sql = "UPDATE pedidos SET estado = :estado WHERE id_pedido = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':estado', $nuevo_estado);
        $stmt->bindValue(':id', $id_pedido);
        
        if($nuevo_estado === 'Entregado'){
            $sql2 = "UPDATE pedidos_tillos SET estado = 'Entregado' WHERE id_pedido = :id";
            $stmt2 = $this->conexion->prepare($sql2);
            $stmt2->bindValue(':id', $id_pedido);
            $stmt2->execute();
        }
        
        return $stmt->execute();
    }

    public function cambiarEstadoPago($id_pedido, $estado_pago) {
        $sql = "UPDATE pedidos SET pagado = :pagado WHERE id_pedido = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':pagado', $estado_pago, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id_pedido);
        return $stmt->execute();
    }
}
?>