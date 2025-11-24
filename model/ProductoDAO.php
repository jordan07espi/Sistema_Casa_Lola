<?php
// Archivo: model/ProductoDAO.php
require_once __DIR__ . '/../config/Conexion.php';

class ProductoDAO {
    private $conexion;

    public function __construct() {
        $db = new Conexion();
        $this->conexion = $db->getConnection();
    }

    public function listarActivos() {
        // Aseguramos traer la columna requiere_tillo
        $sql = "SELECT id_producto, nombre_producto, requiere_tillo FROM productos WHERE activo = 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>