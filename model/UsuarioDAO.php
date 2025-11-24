<?php
// Archivo: model/UsuarioDAO.php
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/dto/Usuario.php';

class UsuarioDAO {
    private $conexion;

    public function __construct() {
        $db = new Conexion();
        $this->conexion = $db->getConnection();
    }

    // Método que ya tenías, lo mantenemos para el login
    public function obtenerUsuarioPorCedula($cedula) {
        $sql = "SELECT u.*, r.nombre_rol FROM usuarios u JOIN roles r ON u.id_rol = r.id_rol WHERE u.cedula = :cedula AND u.activo = 1";
        try {
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cedula', $cedula, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Error en UsuarioDAO::obtenerUsuarioPorCedula: " . $e->getMessage());
            return null;
        }
    }

    // --- NUEVOS MÉTODOS PARA EL CRUD ---

    public function listar() {
        $sql = "SELECT u.id_usuario, u.nombre_completo, u.cedula, r.nombre_rol 
                FROM usuarios u 
                JOIN roles r ON u.id_rol = r.id_rol 
                WHERE u.activo = 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarRoles() {
        $sql = "SELECT * FROM roles";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function agregar(Usuario $usuario) {
        $sql = "INSERT INTO usuarios (nombre_completo, cedula, password, id_rol) VALUES (:nombre, :cedula, :password, :id_rol)";
        $stmt = $this->conexion->prepare($sql);
        
        // Hashear la contraseña antes de guardarla
        $passwordHash = password_hash($usuario->password, PASSWORD_DEFAULT);

        $stmt->bindValue(':nombre', $usuario->nombre_completo);
        $stmt->bindValue(':cedula', $usuario->cedula);
        $stmt->bindValue(':password', $passwordHash);
        $stmt->bindValue(':id_rol', $usuario->id_rol);
        return $stmt->execute();
    }

    public function obtenerPorId($id) {
        $sql = "SELECT id_usuario, nombre_completo, cedula, id_rol FROM usuarios WHERE id_usuario = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar(Usuario $usuario) {
        // Si se proporciona una nueva contraseña, la actualizamos. Si no, no.
        if (!empty($usuario->password)) {
            $sql = "UPDATE usuarios SET nombre_completo = :nombre, cedula = :cedula, id_rol = :id_rol, password = :password WHERE id_usuario = :id";
            $passwordHash = password_hash($usuario->password, PASSWORD_DEFAULT);
        } else {
            $sql = "UPDATE usuarios SET nombre_completo = :nombre, cedula = :cedula, id_rol = :id_rol WHERE id_usuario = :id";
        }
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':nombre', $usuario->nombre_completo);
        $stmt->bindValue(':cedula', $usuario->cedula);
        $stmt->bindValue(':id_rol', $usuario->id_rol);
        $stmt->bindValue(':id', $usuario->id_usuario);
        if (!empty($usuario->password)) {
            $stmt->bindValue(':password', $passwordHash);
        }
        return $stmt->execute();
    }

    public function eliminar($id) {
        $sql = "UPDATE usuarios SET activo = 0 WHERE id_usuario = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
}
?>