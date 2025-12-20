<?php
// Archivo: model/dto/Pedido.php

// AGREGA ESTA LÍNEA para permitir propiedades dinámicas si usas PHP 8.2+
// O simplemente declara las variables faltantes abajo.
#[AllowDynamicProperties] 
class Pedido {
    public $id_pedido;
    public $codigo_pedido;
    public $id_cliente;
    public $id_usuario;
    public $fecha_creacion;
    public $fecha_entrega;
    public $hora_entrega;
    public $total;
    public $pagado; // <--- NUEVO CAMPO
    public $estado; 
    public $observaciones;
    public $evidencia_foto;
    
    // --- NUEVAS PROPIEDADES QUE FALTABAN (Están en tu BD) ---
    public $veces_impreso;
    public $fecha_ultima_impresion;

    // Propiedades auxiliares (JOINs)
    public $nombre_cliente;
    public $nombre_usuario;
    public $cedula;
    public $telefono;
    
    public $detalles = []; 

    public $tillos_secundarios; 
    
    public $tillos = []; 
}
?>