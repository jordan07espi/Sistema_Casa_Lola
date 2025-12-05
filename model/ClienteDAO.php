<?php
// Archivo: model/ClienteDAO.php
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/dto/Cliente.php';

class ClienteDAO {
    private $conexion;

    public function __construct() {
        $db = new Conexion();
        $this->conexion = $db->getConnection();
    }

    // LISTAR CON PAGINACIÓN Y BÚSQUEDA
    public function listar($inicio, $limite, $busqueda = '') {
        $sql = "SELECT * FROM clientes WHERE activo = 1";
        
        // Si hay búsqueda, agregamos filtros
        if (!empty($busqueda)) {
            $sql .= " AND (nombre LIKE :busqueda OR cedula LIKE :busqueda)";
        }

        // Ordenamos por ID descendente para ver los nuevos primero
        $sql .= " ORDER BY id_cliente DESC LIMIT :inicio, :limite";
        
        $stmt = $this->conexion->prepare($sql);
        
        if (!empty($busqueda)) {
            $busquedaStr = "%" . $busqueda . "%";
            $stmt->bindValue(':busqueda', $busquedaStr);
        }
        
        $stmt->bindValue(':inicio', (int)$inicio, PDO::PARAM_INT);
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // CONTAR TOTAL DE REGISTROS (Para saber cuántas páginas hay)
    public function contarTotal($busqueda = '') {
        $sql = "SELECT COUNT(*) FROM clientes WHERE activo = 1";
        
        if (!empty($busqueda)) {
            $sql .= " AND (nombre LIKE :busqueda OR cedula LIKE :busqueda)";
        }

        $stmt = $this->conexion->prepare($sql);
        
        if (!empty($busqueda)) {
            $busquedaStr = "%" . $busqueda . "%";
            $stmt->bindValue(':busqueda', $busquedaStr);
        }

        $stmt->execute();
        return $stmt->fetchColumn();
    }


    // Buscar el método agregar y cambiar por:
    public function agregar(Cliente $cliente) {
        if ($this->existeCedula($cliente->cedula)) return false; 
        // Agregamos :email
        $sql = "INSERT INTO clientes (cedula, nombre, email, telefono) VALUES (:cedula, :nombre, :email, :telefono)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':cedula', $cliente->cedula);
        $stmt->bindValue(':nombre', $cliente->nombre);
        $stmt->bindValue(':email', $cliente->email); // <--- NUEVO
        $stmt->bindValue(':telefono', $cliente->telefono);
        return $stmt->execute();
    }

    // Buscar el método actualizar y cambiar por:
    public function actualizar(Cliente $cliente) {
        // Agregamos :email
        $sql = "UPDATE clientes SET nombre = :nombre, cedula = :cedula, email = :email, telefono = :telefono WHERE id_cliente = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':nombre', $cliente->nombre);
        $stmt->bindValue(':cedula', $cliente->cedula);
        $stmt->bindValue(':email', $cliente->email); // <--- NUEVO
        $stmt->bindValue(':telefono', $cliente->telefono);
        $stmt->bindValue(':id', $cliente->id_cliente);
        return $stmt->execute();
    }

    public function eliminar($id) {
        $sql = "UPDATE clientes SET activo = 0 WHERE id_cliente = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    public function obtenerPorId($id) {
        $sql = "SELECT * FROM clientes WHERE id_cliente = :id AND activo = 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function existeCedula($cedula, $id_excluir = null) {
        $sql = "SELECT COUNT(*) FROM clientes WHERE cedula = :cedula AND activo = 1";
        if ($id_excluir) $sql .= " AND id_cliente != :id_excluir";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':cedula', $cedula);
        if ($id_excluir) $stmt->bindValue(':id_excluir', $id_excluir);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}
?>