<?php
// Archivo: model/dto/Pedido.php
class Pedido {
    public $id_pedido;
    public $codigo_pedido;
    public $id_cliente;
    public $id_usuario;
    public $fecha_creacion;
    public $fecha_entrega;
    public $hora_entrega;
    public $total;
    public $estado; // Pendiente, Entregado, Cancelado
    public $observaciones;
    public $evidencia_foto;
    
    // Propiedades auxiliares (JOINs)
    public $nombre_cliente;
    public $nombre_usuario;

    // --- AGREGAMOS ESTAS PROPIEDADES FALTANTES ---
    public $cedula;
    public $telefono;
    public $detalles = []; // Inicializamos como array vacío por seguridad
}
?>