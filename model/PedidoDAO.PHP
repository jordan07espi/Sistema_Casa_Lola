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

    // LISTAR: Con filtros de Búsqueda, Estado y Rango de Fechas
    public function listar($inicio, $limite, $busqueda = '', $filtroEstado = '', $desde = '', $hasta = '') {
        // SELECT con subconsulta para obtener los códigos adicionales (tillos_secundarios)
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

        // 1. Filtro de Búsqueda (Código Principal, Cliente o Tillos Secundarios)
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
        
        // 2. Filtro de Estado
        if (!empty($filtroEstado)) {
            $sql .= " AND p.estado = :estado";
        }

        // 3. Filtro de Rango de Fechas
        if (!empty($desde) && !empty($hasta)) {
            $sql .= " AND p.fecha_entrega BETWEEN :desde AND :hasta";
        } elseif (!empty($desde)) {
            $sql .= " AND p.fecha_entrega >= :desde";
        } elseif (!empty($hasta)) {
            $sql .= " AND p.fecha_entrega <= :hasta";
        }

        // Ordenamos por ID descendente (el último creado va primero)
        $sql .= " ORDER BY p.id_pedido DESC LIMIT :inicio, :limite";

        $stmt = $this->conexion->prepare($sql);

        if (!empty($busqueda)) {
            $stmt->bindValue(':busqueda', "%$busqueda%");
        }
        if (!empty($filtroEstado)) {
            $stmt->bindValue(':estado', $filtroEstado);
        }
        if (!empty($desde)) {
            $stmt->bindValue(':desde', $desde);
        }
        if (!empty($hasta)) {
            $stmt->bindValue(':hasta', $hasta);
        }

        $stmt->bindValue(':inicio', (int)$inicio, PDO::PARAM_INT);
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    // CONTAR TOTAL: Debe tener los mismos filtros que listar para que la paginación coincida
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
        
        if (!empty($filtroEstado)) {
            $sql .= " AND p.estado = :estado";
        }

        if (!empty($desde) && !empty($hasta)) {
            $sql .= " AND p.fecha_entrega BETWEEN :desde AND :hasta";
        } elseif (!empty($desde)) {
            $sql .= " AND p.fecha_entrega >= :desde";
        } elseif (!empty($hasta)) {
            $sql .= " AND p.fecha_entrega <= :hasta";
        }
        
        $stmt = $this->conexion->prepare($sql);
        
        if (!empty($busqueda)) {
            $stmt->bindValue(':busqueda', "%$busqueda%");
        }
        if (!empty($filtroEstado)) {
            $stmt->bindValue(':estado', $filtroEstado);
        }
        if (!empty($desde)) {
            $stmt->bindValue(':desde', $desde);
        }
        if (!empty($hasta)) {
            $stmt->bindValue(':hasta', $hasta);
        }
        
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function obtenerPorId($id_pedido) {
        // 1. Obtener Cabecera
        $sql = "SELECT p.*, c.nombre AS nombre_cliente, c.cedula, c.telefono, u.nombre_completo AS nombre_usuario 
                FROM pedidos p 
                INNER JOIN clientes c ON p.id_cliente = c.id_cliente 
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario 
                WHERE p.id_pedido = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':id', $id_pedido);
        $stmt->execute();
        $pedido = $stmt->fetchObject('Pedido');

        if (!$pedido) return null;

        // 2. Obtener Detalles (Productos)
        $sqlDetalles = "SELECT dp.cantidad, pr.nombre_producto, dp.id_producto 
                        FROM detalles_pedido dp 
                        INNER JOIN productos pr ON dp.id_producto = pr.id_producto 
                        WHERE dp.id_pedido = :id";
        $stmtDet = $this->conexion->prepare($sqlDetalles);
        $stmtDet->bindValue(':id', $id_pedido);
        $stmtDet->execute();
        $pedido->detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

        // 3. OBTENER TILLOS (Incluyendo id_producto para la vista detallada)
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

    public function registrar($pedido, $detalles, $tillosArray) {
        try {
            $this->conexion->beginTransaction();
            
            // Usamos el primer tillo como código principal si existe, sino un genérico
            $codigoPrincipal = !empty($tillosArray) ? $tillosArray[0]['codigo'] : 'S/T-' . time();
            
            // Insertar Cabecera del Pedido
            $sql = "INSERT INTO pedidos (codigo_pedido, id_cliente, id_usuario, fecha_entrega, hora_entrega, total, observaciones, evidencia_foto, estado) 
                    VALUES (:codigo, :cliente, :usuario, :fecha, :hora, :total, :obs, :foto, 'Pendiente')";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':codigo', $codigoPrincipal);
            $stmt->bindValue(':cliente', $pedido->id_cliente);
            $stmt->bindValue(':usuario', $pedido->id_usuario);
            $stmt->bindValue(':fecha', $pedido->fecha_entrega);
            $stmt->bindValue(':hora', $pedido->hora_entrega);
            $stmt->bindValue(':total', $pedido->total);
            $stmt->bindValue(':obs', $pedido->observaciones);
            $stmt->bindValue(':foto', $pedido->evidencia_foto);
            $stmt->execute();
            $id_pedido = $this->conexion->lastInsertId();

            // Insertar Detalles (Productos)
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

            // Insertar Tillos (Con relación al Producto)
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
        
        // Si el estado es Entregado, actualizamos también la tabla de tillos
        if($nuevo_estado === 'Entregado'){
            $sql2 = "UPDATE pedidos_tillos SET estado = 'Entregado' WHERE id_pedido = :id";
            $stmt2 = $this->conexion->prepare($sql2);
            $stmt2->bindValue(':id', $id_pedido);
            $stmt2->execute();
        }
        
        return $stmt->execute();
    }
}
?>