<?php
// Archivo: controller/PedidosSSE.php

// 1. Configuración de Cabeceras para SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// Evitar timeout del script PHP
set_time_limit(0);

require_once '../config/Conexion.php';
// require_once '../model/PedidoDAO.php'; // No es estrictamente necesario si usamos SQL directo aquí para ser más rápidos

// 2. Liberar sesión para no bloquear el navegador
session_start();
session_write_close();

$ultimoHash = null;

// 3. Bucle Infinito (Vigilante)
while (true) {
    
    // Verificar si el cliente se desconectó
    if (connection_aborted()) {
        break;
    }

    try {
        // --- AQUÍ ESTABA EL ERROR ANTERIOR (getConnection vs conectar) ---
        $conexion = new Conexion();
        $conn = $conexion->getConnection(); 

        // Consultamos un "resumen" de los últimos pedidos para detectar cambios
        // (ID, estado, pagado, y hora de entrega es suficiente para saber si algo cambió)
        $sql = "SELECT id_pedido, estado, pagado, fecha_entrega, hora_entrega FROM pedidos ORDER BY id_pedido DESC LIMIT 20";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Creamos una huella digital (Hash) de los datos actuales
        $datosJson = json_encode($pedidos);
        $hashActual = md5($datosJson);

        // Si la huella cambia, avisamos al cliente
        if ($hashActual !== $ultimoHash) {
            
            // A diferencia de Cocina que envía TODOS los datos, 
            // aquí enviamos una señal de "cambio" para que la tabla se recargue respetando filtros y paginación.
            echo "data: {\"cambio\": true}\n\n";
            
            $ultimoHash = $hashActual;
            
            // Forzar envío inmediato
            if (ob_get_level() > 0) ob_flush();
            flush();
        }

    } catch (Exception $e) {
        // Si hay error de conexión, esperar y reintentar
    }

    // Esperar 3 segundos
    sleep(3);
}
?>