<?php
// Archivo: view/admin/ver_pedido.php
session_start();
include '../partials/header.php';
require_once '../../model/PedidoDAO.php';

// Verificación de ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>window.location.href = 'pedidos.php';</script>";
    exit();
}

$id_pedido = $_GET['id'];
$dao = new PedidoDAO();
$p = $dao->obtenerPorId($id_pedido);

if (!$p) {
    echo "<div class='text-center mt-10 text-red-500 font-bold'>Pedido no encontrado.</div>";
    exit();
}

// Configuración visual del estado
$estadoClasses = [
    'Pendiente' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
    'Entregado' => 'bg-green-100 text-green-800 border-green-300',
    'Cancelado' => 'bg-red-100 text-red-800 border-red-300'
];
$claseEstado = $estadoClasses[$p->estado] ?? 'bg-gray-100';
?>

<div class="max-w-3xl mx-auto pb-12">
    
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <a href="pedidos.php" class="text-gray-600 hover:text-orange-600 text-lg transition flex items-center gap-2 mr-2">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            
            <button onclick="imprimirTicket(<?php echo $p->id_pedido; ?>)" 
                class="bg-gray-800 text-white px-4 py-2 rounded-lg shadow hover:bg-gray-700 transition text-sm flex items-center gap-2 font-medium">
                <i class="fas fa-print"></i> Imprimir
            </button>

            <button onclick="enviarWhatsApp()" 
                class="bg-green-500 text-white px-4 py-2 rounded-lg shadow hover:bg-green-600 transition text-sm flex items-center gap-2 font-medium">
                <i class="fab fa-whatsapp fa-lg"></i> Enviar WhatsApp
            </button>
        </div>

        <div class="text-sm text-gray-400 font-mono">
            Pedido #<?php echo $p->codigo_pedido; ?>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
        
        <div class="p-6 bg-gray-900 text-white flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b-4 border-orange-600">
            <div class="flex items-center gap-4">
                <div class="bg-orange-600 p-3 rounded-full shadow-lg">
                    <i class="fas fa-user fa-2x text-white"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold tracking-wide uppercase"><?php echo htmlspecialchars($p->nombre_cliente); ?></h2>
                    <p class="text-gray-300 flex items-center gap-3 text-sm mt-1">
                        <span><i class="fas fa-phone mr-1"></i> <?php echo htmlspecialchars($p->telefono); ?></span>
                    </p>
                </div>
            </div>
            
            <span class="px-4 py-2 rounded-lg text-sm font-bold uppercase shadow-sm border <?php echo $claseEstado; ?>">
                <?php echo $p->estado; ?>
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3">
            
            <div class="md:col-span-2 p-6 border-r border-gray-100">
                <h3 class="text-gray-500 text-xs font-bold uppercase mb-6 pb-2 border-b flex justify-between items-center">
                    <span>Resumen del Trabajo</span>
                    <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-[10px]">CON ETIQUETAS</span>
                </h3>

                <ul class="space-y-6">
                    <?php 
                    $tillosMostrados = [];
                    ?>

                    <?php foreach ($p->detalles as $prod): ?>
                        <li class="group">
                            <div class="flex justify-between items-center text-lg mb-2">
                                <span class="text-gray-800 font-bold flex items-center gap-3">
                                    <i class="fas fa-drumstick-bite text-orange-500"></i> 
                                    <?php echo htmlspecialchars($prod['nombre_producto']); ?>
                                </span>
                                <span class="font-bold text-gray-900 bg-gray-100 px-3 py-1 rounded text-base">
                                    x<?php echo $prod['cantidad']; ?>
                                </span>
                            </div>

                            <?php 
                            // Buscar tillos asociados a este producto
                            $tillosEsteProducto = array_filter($p->tillos, function($t) use ($prod) {
                                return isset($t['id_producto']) && $t['id_producto'] == $prod['id_producto'];
                            });
                            ?>

                            <?php if (!empty($tillosEsteProducto)): ?>
                                <div class="ml-8 flex flex-wrap gap-2 animate-fade-in-down">
                                    <?php foreach ($tillosEsteProducto as $tillo): 
                                        $tillosMostrados[] = $tillo['codigo_tillo'];
                                        $colorTillo = ($tillo['estado'] === 'Entregado') ? 'bg-green-100 text-green-700 border-green-200' : 'bg-white text-gray-600 border-gray-300';
                                    ?>
                                        <div class="flex items-center gap-2 border <?php echo $colorTillo; ?> px-3 py-1 rounded-md shadow-sm text-sm">
                                            <i class="fas fa-tag fa-xs opacity-50"></i>
                                            <span class="font-mono font-bold tracking-wide">
                                                #<?php echo htmlspecialchars($tillo['codigo_tillo']); ?>
                                            </span>
                                            <?php if($tillo['estado'] === 'Entregado'): ?>
                                                <i class="fas fa-check-circle text-xs"></i>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php 
                // Tillos huérfanos o antiguos
                $tillosSinAsignar = array_filter($p->tillos, function($t) use ($tillosMostrados) {
                    return !in_array($t['codigo_tillo'], $tillosMostrados);
                });
                ?>

                <?php if (!empty($tillosSinAsignar)): ?>
                    <div class="mt-8 pt-6 border-t border-dashed border-gray-300">
                        <p class="text-xs font-bold text-gray-400 uppercase mb-3">
                            <i class="fas fa-exclamation-circle"></i> Etiquetas adicionales
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($tillosSinAsignar as $tillo): 
                                $colorTillo = ($tillo['estado'] === 'Entregado') ? 'bg-green-100 text-green-700 border-green-200' : 'bg-gray-100 text-gray-600 border-gray-300';
                            ?>
                                <div class="flex items-center gap-2 border <?php echo $colorTillo; ?> px-3 py-1 rounded-md text-sm opacity-80">
                                    <i class="fas fa-tag fa-xs opacity-50"></i>
                                    <span class="font-mono font-bold">
                                        #<?php echo htmlspecialchars($tillo['codigo_tillo']); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($p->observaciones)): ?>
                    <div class="mt-8 p-4 bg-yellow-50 rounded-lg border border-yellow-100 flex gap-3">
                        <i class="fas fa-comment-alt text-yellow-400 mt-1"></i>
                        <div>
                            <p class="text-xs font-bold text-yellow-700 uppercase mb-1">Observaciones</p>
                            <p class="text-gray-700 italic text-sm">"<?php echo nl2br(htmlspecialchars($p->observaciones)); ?>"</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="p-6 bg-gray-50 flex flex-col justify-between">
                <div>
                    <h3 class="text-gray-500 text-xs font-bold uppercase mb-4">Datos de Entrega</h3>
                    <div class="mb-4">
                        <p class="text-xs text-gray-400">Fecha</p>
                        <p class="text-lg font-semibold text-gray-800">
                            <i class="far fa-calendar-alt mr-2 text-orange-500"></i>
                            <?php echo $p->fecha_entrega; ?>
                        </p>
                        
                        <?php 
                            $diasSemana = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
                            $numDia = date('w', strtotime($p->fecha_entrega));
                            $nombreDia = $diasSemana[$numDia];
                        ?>
                        <p class="text-sm text-orange-600 font-bold ml-7 uppercase tracking-wider mt-1">
                            <?php echo $nombreDia; ?>
                        </p>
                    </div>
                    <div class="mb-4">
                        <p class="text-xs text-gray-400">Hora</p>
                        <p class="text-lg font-semibold text-gray-800">
                            <i class="far fa-clock mr-2 text-orange-500"></i>
                            <?php echo substr($p->hora_entrega, 0, 5); ?>
                        </p>
                    </div>
                    
                    <div class="mb-4 pt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-400">Vendedor</p>
                        <p class="text-sm font-medium text-gray-600 uppercase">
                            <i class="fas fa-user-edit mr-1"></i> <?php echo htmlspecialchars($p->nombre_usuario); ?>
                        </p>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-sm text-right text-gray-500 mb-1">Total a Pagar</p>
                    <p class="text-4xl font-black text-right text-green-600 tracking-tight">
                        $<?php echo number_format($p->total, 2); ?>
                    </p>
                </div>
            </div>
        </div>

        <?php if (!empty($p->evidencia_foto)): ?>
            <div class="p-6 border-t border-gray-200 bg-gray-50">
                <p class="text-xs font-bold text-gray-400 uppercase mb-3">Evidencia Adjunta</p>
                <a href="../../uploads/evidencias/<?php echo $p->evidencia_foto; ?>" target="_blank" class="block group">
                    <div class="relative overflow-hidden rounded-xl border-2 border-white shadow-sm group-hover:shadow-md transition">
                        <img src="../../uploads/evidencias/<?php echo $p->evidencia_foto; ?>" 
                             class="w-full h-64 object-cover transform group-hover:scale-105 transition duration-500" 
                             alt="Evidencia del pedido">
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition flex items-center justify-center">
                            <span class="text-white opacity-0 group-hover:opacity-100 font-bold bg-black bg-opacity-50 px-4 py-2 rounded-full backdrop-blur-sm">
                                <i class="fas fa-search-plus mr-2"></i> Ampliar
                            </span>
                        </div>
                    </div>
                </a>
            </div>
        <?php endif; ?>

        <?php if ($p->estado === 'Pendiente'): ?>
            <div class="p-4 bg-gray-100 border-t border-gray-200 flex flex-col sm:flex-row gap-3">
                <button onclick="cambiarEstado(<?php echo $p->id_pedido; ?>, 'Entregado')" 
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5 flex justify-center items-center gap-2">
                    <i class="fas fa-check-circle fa-lg"></i> ENTREGAR
                </button>
                
                <button onclick="cambiarEstado(<?php echo $p->id_pedido; ?>, 'Cancelado')" 
                    class="flex-1 bg-white border-2 border-red-500 text-red-600 hover:bg-red-50 font-bold py-4 rounded-xl shadow-sm hover:shadow-md transition flex justify-center items-center gap-2">
                    <i class="fas fa-times-circle fa-lg"></i> CANCELAR
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // 1. Función para cambiar estado
    function cambiarEstado(id, nuevoEstado) {
        let mensaje = nuevoEstado === 'Entregado' 
            ? '¿Confirmar entrega del pedido? \n\nSe marcarán todos los tillos como entregados.' 
            : '¿Estás seguro de CANCELAR este pedido?';
            
        if (!confirm(mensaje)) return;

        const formData = new FormData();
        formData.append('action', 'cambiar_estado');
        formData.append('id_pedido', id);
        formData.append('nuevo_estado', nuevoEstado);

        fetch('../../controller/PedidoController.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload(); 
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => console.error(err));
    }

    // 2. Función para Imprimir
    function imprimirTicket(id) {
        const url = `ticket.php?id=${id}`;
        window.open(url, 'ImprimirTicket', 'width=400,height=600,scrollbars=yes');
    }

    // 3. NUEVA FUNCIÓN: ENVIAR POR WHATSAPP
    function enviarWhatsApp() {
        // Datos inyectados desde PHP
        const telefono = "<?php echo $p->telefono; ?>";
        const nombre = "<?php echo $p->nombre_cliente; ?>";
        const codigo = "<?php echo $p->codigo_pedido; ?>";
        const total = "<?php echo number_format($p->total, 2); ?>";
        const fecha = "<?php echo $p->fecha_entrega; ?>";
        const hora = "<?php echo substr($p->hora_entrega, 0, 5); ?>";
        
        // Limpiar teléfono para formato internacional (Ecuador)
        let phoneClean = telefono.replace(/\D/g, ''); // Quitar no numéricos
        
        if (phoneClean.startsWith('0')) {
            phoneClean = '593' + phoneClean.substring(1); // Reemplazar 0 inicial con 593
        } else if (phoneClean.length === 9) {
            phoneClean = '593' + phoneClean; // Si falta el 0 pero tiene 9 dígitos
        }
        // Si ya tiene 593 al inicio, se deja igual.

        // Construir detalle de productos
        let detalleTexto = "";
        <?php foreach ($p->detalles as $d): ?>
            // Usamos codificación URL para caracteres especiales
            detalleTexto += "▪ <?php echo $d['cantidad']; ?> x <?php echo urlencode($d['nombre_producto']); ?>%0A";
        <?php endforeach; ?>

        // Mensaje estructurado con formato de WhatsApp (*negrita*, %0A salto de línea)
        const mensaje = `Hola *${nombre}*! 👋%0A%0A` +
                        `Su pedido en *CASA LOLA* ha sido registrado.%0A` +
                        `--------------------------------%0A` +
                        `🧾 *Orden:* ${codigo}%0A` +
                        `📅 *Entrega:* ${fecha} ${hora}%0A` +
                        `💰 *Total:* $${total}%0A` +
                        `--------------------------------%0A` +
                        `*Detalle:*%0A${detalleTexto}%0A` +
                        `¡Gracias por su preferencia! 🐷🔥`;

        // Abrir API de WhatsApp
        const url = `https://wa.me/${phoneClean}?text=${mensaje}`;
        window.open(url, '_blank');
    }
</script>

<?php include '../partials/footer.php'; ?>