<?php
// Archivo: controller/CocinaSSE.php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

require_once '../model/CocinaDAO.php';

// Importante: Cerrar sesión de escritura para no bloquear otras pestañas del navegador
session_start();
session_write_close();

$cocinaDAO = new CocinaDAO();
$ultimoHash = null;

// Bucle infinito controlado por el servidor web
// Nota: En producción real con muchos usuarios se usan otras técnicas, 
// pero para un sistema local de cocina esto funciona perfecto.
while (true) {
    
    $pedidos = $cocinaDAO->obtenerPedidosCocina();
    
    // Convertimos a JSON para comparar si hubo cambios
    $datosJson = json_encode($pedidos);
    $hashActual = md5($datosJson);

    // Solo enviamos datos si algo cambió (Nueva orden, o cambio de estado)
    // Opcional: Enviar siempre cada X segundos para asegurar conexión (Heartbeat)
    if ($hashActual !== $ultimoHash) {
        echo "data: {$datosJson}\n\n";
        $ultimoHash = $hashActual;
        
        // Forzar envío del buffer
        if (ob_get_level() > 0) ob_flush();
        flush();
    }

    // Esperar 3 segundos antes de volver a consultar la BD
    sleep(3);
    
    // Verificar si el cliente cerró la conexión para matar el proceso PHP
    if (connection_aborted()) {
        break;
    }
}
?>