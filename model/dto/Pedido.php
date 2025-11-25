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
    public $estado; 
    public $observaciones;
    public $evidencia_foto;
    
    // Propiedades auxiliares (JOINs)
    public $nombre_cliente;
    public $nombre_usuario;
    public $cedula;
    public $telefono;
    
    public $detalles = []; 

    public $tillos_secundarios; // Este lo usamos para la TABLA (lista)
    
    // --- AGREGAR ESTA LÍNEA PARA CORREGIR EL ERROR EN VER_PEDIDO ---
    public $tillos = []; // Este lo usamos para el DETALLE (ver_pedido.php)
}
?>