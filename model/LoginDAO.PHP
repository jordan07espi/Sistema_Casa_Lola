<?php
// Archivo: model/LoginDAO.php
require_once __DIR__ . '/../config/Conexion.php';

class LoginDAO {
    private $conexion;

    public function __construct() {
        $db = new Conexion();
        $this->conexion = $db->getConnection();
    }

    public function registrarIntentoFallido($cedula, $ip) {
        $sql = "INSERT INTO intentos_login_fallidos (cedula, ip_address) VALUES (:cedula, :ip)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':cedula', $cedula);
        $stmt->bindValue(':ip', $ip);
        return $stmt->execute();
    }

    public function contarIntentosRecientes($cedula, $ip, $minutos = 15) {
        $sql = "SELECT COUNT(*) FROM intentos_login_fallidos 
                WHERE cedula = :cedula AND ip_address = :ip AND fecha_intento >= NOW() - INTERVAL :minutos MINUTE";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':cedula', $cedula);
        $stmt->bindValue(':ip', $ip);
        $stmt->bindValue(':minutos', $minutos, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function limpiarIntentos($cedula, $ip) {
        $sql = "DELETE FROM intentos_login_fallidos WHERE cedula = :cedula AND ip_address = :ip";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':cedula', $cedula);
        $stmt->bindValue(':ip', $ip);
        return $stmt->execute();
    }
}
?>