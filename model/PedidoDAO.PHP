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

    public function listar($inicio, $limite, $busqueda = '', $filtroEstado = '') {
        $sql = "SELECT p.*, c.nombre AS nombre_cliente, u.nombre_completo AS nombre_usuario 
                FROM pedidos p 
                INNER JOIN clientes c ON p.id_cliente = c.id_cliente 
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario 
                WHERE 1=1"; // Truco para concatenar ANDs dinámicamente

        if (!empty($busqueda)) {
            $sql .= " AND (p.codigo_pedido LIKE :busqueda OR c.nombre LIKE :busqueda)";
        }
        
        if (!empty($filtroEstado)) {
            $sql .= " AND p.estado = :estado";
        }

        $sql .= " ORDER BY p.fecha_entrega DESC, p.hora_entrega DESC LIMIT :inicio, :limite";

        $stmt = $this->conexion->prepare($sql);

        if (!empty($busqueda)) {
            $stmt->bindValue(':busqueda', "%$busqueda%");
        }
        if (!empty($filtroEstado)) {
            $stmt->bindValue(':estado', $filtroEstado);
        }

        $stmt->bindValue(':inicio', (int)$inicio, PDO::PARAM_INT);
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public function contarTotal($busqueda = '', $filtroEstado = '') {
        $sql = "SELECT COUNT(*) FROM pedidos p 
                INNER JOIN clientes c ON p.id_cliente = c.id_cliente 
                WHERE 1=1";

        if (!empty($busqueda)) {
            $sql .= " AND (p.codigo_pedido LIKE :busqueda OR c.nombre LIKE :busqueda)";
        }
        if (!empty($filtroEstado)) {
            $sql .= " AND p.estado = :estado";
        }

        $stmt = $this->conexion->prepare($sql);
        if (!empty($busqueda)) $stmt->bindValue(':busqueda', "%$busqueda%");
        if (!empty($filtroEstado)) $stmt->bindValue(':estado', $filtroEstado);
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }


    // Método para verificar si UN tillo específico está ocupado
    public function verificarTilloOcupado($codigo_tillo) {
        // Buscamos en la nueva tabla de tillos
        $sql = "SELECT COUNT(*) FROM pedidos_tillos pt 
                INNER JOIN pedidos p ON pt.id_pedido = p.id_pedido
                WHERE pt.codigo_tillo = :codigo AND p.estado = 'Pendiente'";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':codigo', $codigo_tillo);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // REGISTRAR ACTUALIZADO
    // Recibe $tillosArray que es un arreglo simple: ['2025_45', '2025_46']
    public function registrar($pedido, $detalles, $tillosArray) {
        try {
            $this->conexion->beginTransaction();

            // 1. Insertar Cabecera del Pedido
            // Nota: Guardamos el primer Tillo como "Código Principal" para referencia rápida en la tabla antigua
            $codigoPrincipal = !empty($tillosArray) ? $tillosArray[0] : 'S/T-' . time();

            $sql = "INSERT INTO pedidos (codigo_pedido, id_cliente, id_usuario, fecha_entrega, hora_entrega, total, observaciones, evidencia_foto, estado) 
                    VALUES (:codigo, :cliente, :usuario, :fecha, :hora, :total, :obs, :foto, 'Pendiente')";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':codigo', $codigoPrincipal); // Guardamos el primero como referencia
            $stmt->bindValue(':cliente', $pedido->id_cliente);
            $stmt->bindValue(':usuario', $pedido->id_usuario);
            $stmt->bindValue(':fecha', $pedido->fecha_entrega);
            $stmt->bindValue(':hora', $pedido->hora_entrega);
            $stmt->bindValue(':total', $pedido->total);
            $stmt->bindValue(':obs', $pedido->observaciones);
            $stmt->bindValue(':foto', $pedido->evidencia_foto);
            $stmt->execute();

            $id_pedido = $this->conexion->lastInsertId();

            // 2. Insertar Productos (Cantidades)
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

            // 3. INSERTAR LOS TILLOS INDIVIDUALES (NUEVO)
            $sqlTillo = "INSERT INTO pedidos_tillos (id_pedido, codigo_tillo, estado) VALUES (:id_pedido, :codigo, 'Pendiente')";
            $stmtTillo = $this->conexion->prepare($sqlTillo);

            foreach ($tillosArray as $codigoTillo) {
                if (!empty($codigoTillo)) {
                    $stmtTillo->bindValue(':id_pedido', $id_pedido);
                    $stmtTillo->bindValue(':codigo', $codigoTillo);
                    $stmtTillo->execute();
                }
            }

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollBack();
            // Sería bueno loguear el error real: error_log($e->getMessage());
            return false;
        }
    }

    // Obtener un pedido completo por ID (Cabecera + Detalles)
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
        $sqlDetalles = "SELECT dp.cantidad, pr.nombre_producto 
                        FROM detalles_pedido dp 
                        INNER JOIN productos pr ON dp.id_producto = pr.id_producto 
                        WHERE dp.id_pedido = :id";
        $stmtDet = $this->conexion->prepare($sqlDetalles);
        $stmtDet->bindValue(':id', $id_pedido);
        $stmtDet->execute();
        
        // Guardamos los detalles en una propiedad dinámica temporal para la vista
        $pedido->detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

        return $pedido;
    }

    // Cambiar estado (Entregado / Cancelado / Pendiente)
    public function cambiarEstado($id_pedido, $nuevo_estado) {
        $sql = "UPDATE pedidos SET estado = :estado WHERE id_pedido = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':estado', $nuevo_estado);
        $stmt->bindValue(':id', $id_pedido);
        return $stmt->execute();
    }
}
?>