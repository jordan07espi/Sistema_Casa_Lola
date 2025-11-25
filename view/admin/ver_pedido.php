<?php
// Archivo: view/admin/ver_pedido.php
session_start();
include '../partials/header.php';
require_once '../../model/PedidoDAO.php';

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
    
    <div class="flex justify-between items-center mb-6">
        <a href="pedidos.php" class="text-gray-600 hover:text-orange-600 text-lg transition flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
        <div class="text-sm text-gray-400">
            Pedido #<?php echo $p->id_pedido; ?>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
        
        <div class="p-6 bg-gray-900 text-white flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b-4 border-orange-600">
            <div class="flex items-center gap-4">
                <div class="bg-orange-600 p-3 rounded-full shadow-lg">
                    <i class="fas fa-user fa-2x text-white"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold tracking-wide"><?php echo htmlspecialchars($p->nombre_cliente); ?></h2>
                    <p class="text-gray-300 flex items-center gap-3 text-sm mt-1">
                        <span><i class="fas fa-id-card mr-1"></i> <?php echo htmlspecialchars($p->cedula); ?></span>
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
                    // Array auxiliar para saber qué tillos ya mostramos y no repetirlos abajo
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
                            $tillosEsteProducto = array_filter($p->tillos, function($t) use ($prod) {
                                // Usamos isset() para evitar el error "Undefined array key" si el dato no existe
                                return isset($t['id_producto']) && $t['id_producto'] == $prod['id_producto'];
                            });
                            ?>

                            <?php if (!empty($tillosEsteProducto)): ?>
                                <div class="ml-8 flex flex-wrap gap-2 animate-fade-in-down">
                                    <?php foreach ($tillosEsteProducto as $tillo): 
                                        // Marcamos este tillo como mostrado para no repetirlo luego
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
                // Filtramos los tillos que NO están en la lista de mostrados
                $tillosSinAsignar = array_filter($p->tillos, function($t) use ($tillosMostrados) {
                    return !in_array($t['codigo_tillo'], $tillosMostrados);
                });
                ?>

                <?php if (!empty($tillosSinAsignar)): ?>
                    <div class="mt-8 pt-6 border-t border-dashed border-gray-300">
                        <p class="text-xs font-bold text-gray-400 uppercase mb-3">
                            <i class="fas fa-exclamation-circle"></i> Etiquetas sin producto específico (Antiguos)
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
                            // 1. Array de días (0=Domingo, 1=Lunes...)
                            $diasSemana = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
                            // 2. Obtenemos el número del día de la fecha
                            $numDia = date('w', strtotime($p->fecha_entrega));
                            // 3. Obtenemos el nombre
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
                            <?php echo $p->hora_entrega; ?>
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
</script>

<?php include '../partials/footer.php'; ?>