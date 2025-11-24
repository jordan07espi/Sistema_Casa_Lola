<?php
// Archivo: view/admin/ver_pedido.php
session_start();
include '../partials/header.php';
require_once '../../model/PedidoDAO.php';

// Verificar ID
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

// Colores según estado
$colorEstado = 'gray';
if ($p->estado === 'Pendiente') $colorEstado = 'yellow';
if ($p->estado === 'Entregado') $colorEstado = 'green';
if ($p->estado === 'Cancelado') $colorEstado = 'red';
?>

<div class="max-w-2xl mx-auto pb-10">
    <div class="flex justify-between items-center mb-6">
        <a href="pedidos.php" class="text-gray-600 hover:text-orange-600 text-lg">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
        
        <?php if ($p->estado === 'Pendiente'): ?>
            <a href="editar_pedido.php?id=<?php echo $p->id_pedido; ?>" class="text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-2 bg-blue-50 px-4 py-2 rounded-lg">
                <i class="fas fa-edit"></i> Editar Pedido
            </a>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
        
        <div class="bg-orange-600 p-8 text-center text-white relative">
            <p class="text-sm uppercase tracking-widest opacity-80 mb-1">Tillo / Código</p>
            <h1 class="text-7xl font-black tracking-tighter drop-shadow-md">
                <?php echo htmlspecialchars($p->codigo_pedido); ?>
            </h1>
            
            <div class="absolute top-4 right-4">
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-white text-orange-600 shadow-sm">
                    <?php echo $p->estado; ?>
                </span>
            </div>
        </div>

        <div class="p-6 border-b border-gray-100 bg-orange-50/30">
            <div class="flex items-start gap-4">
                <div class="bg-gray-200 p-3 rounded-full text-gray-600">
                    <i class="fas fa-user fa-lg"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($p->nombre_cliente); ?></h2>
                    <p class="text-gray-600 font-mono"><?php echo htmlspecialchars($p->cedula); ?></p>
                    <a href="tel:<?php echo htmlspecialchars($p->telefono); ?>" class="text-orange-600 font-semibold hover:underline mt-1 block">
                        <i class="fas fa-phone-alt mr-1"></i> <?php echo htmlspecialchars($p->telefono); ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="p-6">
            <h3 class="text-gray-500 text-sm font-bold uppercase mb-4 border-b pb-2">Detalle del Trabajo</h3>
            <ul class="space-y-3">
                <?php foreach ($p->detalles as $prod): ?>
                    <li class="flex justify-between items-center text-lg">
                        <span class="text-gray-700">
                            <i class="fas fa-drumstick-bite text-orange-400 mr-2"></i> 
                            <?php echo htmlspecialchars($prod['nombre_producto']); ?>
                        </span>
                        <span class="font-bold text-gray-900 bg-gray-100 px-3 py-1 rounded-lg">
                            x<?php echo $prod['cantidad']; ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="mt-6 pt-4 border-t border-gray-100 flex justify-between items-end">
                <div class="text-sm text-gray-500">
                    <p>Entrega: <strong><?php echo $p->fecha_entrega; ?></strong></p>
                    <p>Hora: <strong><?php echo $p->hora_entrega; ?></strong></p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Total a Pagar</p>
                    <p class="text-4xl font-bold text-green-600">$<?php echo number_format($p->total, 2); ?></p>
                </div>
            </div>
        </div>

        <?php if (!empty($p->observaciones) || !empty($p->evidencia_foto)): ?>
            <div class="p-6 bg-gray-50 border-t border-gray-100">
                <?php if (!empty($p->observaciones)): ?>
                    <div class="mb-4">
                        <p class="text-xs font-bold text-gray-400 uppercase">Observaciones</p>
                        <p class="text-gray-700 italic bg-white p-3 rounded border border-gray-200 mt-1">
                            "<?php echo nl2br(htmlspecialchars($p->observaciones)); ?>"
                        </p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($p->evidencia_foto)): ?>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase mb-2">Evidencia Fotográfica</p>
                        <a href="../../uploads/evidencias/<?php echo $p->evidencia_foto; ?>" target="_blank">
                            <img src="../../uploads/evidencias/<?php echo $p->evidencia_foto; ?>" 
                                 class="w-full h-48 object-cover rounded-lg border-2 border-orange-200 hover:opacity-90 transition cursor-zoom-in" 
                                 alt="Evidencia del pedido">
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($p->estado === 'Pendiente'): ?>
            <div class="p-6 bg-gray-100 border-t border-gray-200 grid grid-cols-1 md:grid-cols-2 gap-4">
                <button onclick="cambiarEstado(<?php echo $p->id_pedido; ?>, 'Entregado')" 
                    class="bg-green-500 hover:bg-green-600 text-white font-bold py-4 rounded-xl shadow-lg transform hover:scale-[1.02] transition flex flex-col items-center justify-center gap-1">
                    <i class="fas fa-check-circle fa-2x"></i>
                    <span>ENTREGAR PEDIDO</span>
                </button>
                
                <button onclick="cambiarEstado(<?php echo $p->id_pedido; ?>, 'Cancelado')" 
                    class="bg-red-500 hover:bg-red-600 text-white font-bold py-4 rounded-xl shadow-lg transform hover:scale-[1.02] transition flex flex-col items-center justify-center gap-1">
                    <i class="fas fa-times-circle fa-2x"></i>
                    <span>CANCELAR PEDIDO</span>
                </button>
            </div>
        <?php else: ?>
            <div class="p-6 text-center bg-gray-100 border-t border-gray-200">
                <p class="text-gray-500 italic">Este pedido ya está <strong><?php echo $p->estado; ?></strong> y no se puede modificar.</p>
                </div>
        <?php endif; ?>
        
    </div>
</div>

<script>
    function cambiarEstado(id, nuevoEstado) {
        let mensaje = nuevoEstado === 'Entregado' 
            ? '¿Confirmas que el cliente ya retiró el pedido y pagó?' 
            : '¿Estás seguro de CANCELAR este pedido? Esta acción no se puede deshacer fácilmente.';
            
        if (!confirm(mensaje)) return;

        const formData = new FormData();
        formData.append('action', 'cambiar_estado');
        formData.append('id_pedido', id);
        formData.append('nuevo_estado', nuevoEstado);

        fetch('../../controller/PedidoController.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Recargar página para ver cambios
                    window.location.reload(); 
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => console.error(err));
    }
</script>

<?php include '../partials/footer.php'; ?>