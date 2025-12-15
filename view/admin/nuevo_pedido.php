<?php
// Archivo: view/admin/nuevo_pedido.php
session_start();
include '../partials/header.php';
require_once '../../model/ProductoDAO.php';
require_once '../../model/ClienteDAO.php';

$productoDAO = new ProductoDAO();
$productos = $productoDAO->listarActivos();

// Separar productos en dos grupos según si requieren tillo o no
$proteinas = [];
$guarniciones = [];

foreach ($productos as $prod) {
    if ($prod['requiere_tillo'] == 1) {
        $proteinas[] = $prod;
    } else {
        $guarniciones[] = $prod;
    }
}

$clienteDAO = new ClienteDAO();
$clientes = $clienteDAO->listar(0, 1000);

$prefijoTillo = date('Y') . '_';
?>

<div class="max-w-4xl mx-auto"> 
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800 border-l-4 border-orange-600 pl-4">Nuevo Pedido</h1>
        <a href="pedidos.php" class="text-gray-600 hover:text-orange-600 transition-colors flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <form id="formPedido" class="bg-white rounded-lg shadow-lg p-6" enctype="multipart/form-data">
        <input type="hidden" name="action" value="registrar">
        <input type="hidden" id="prefijo_global" value="<?php echo $prefijoTillo; ?>">
        
        <div class="mb-6">
            <label class="block text-gray-700 font-bold mb-2">* Cliente</label>
            <div class="flex gap-2">
                <input list="listaClientes" name="cliente_busqueda" id="cliente_busqueda" 
                    class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 px-4 py-2 border" 
                    placeholder="Buscar por nombre o cédula..." autocomplete="off">
                
                <input type="hidden" name="id_cliente" id="id_cliente_seleccionado" required>
                
                <button type="button" id="btnQuickAddCliente" class="bg-green-500 text-white p-2 rounded-lg hover:bg-green-600 shadow transition-colors" title="Registrar Nuevo Cliente Rápido">
                    <i class="fas fa-user-plus"></i>
                </button>
            </div>
            
            <datalist id="listaClientes">
                <?php foreach ($clientes as $c): ?>
                    <option data-id="<?php echo $c['id_cliente']; ?>" value="<?php echo $c['telefono'] . ' | ' . $c['nombre']; ?>">
                <?php endforeach; ?>
            </datalist>
            <p id="msgCliente" class="text-xs text-red-500 mt-1 hidden">Por favor seleccione un cliente de la lista.</p>
        </div>

        <hr class="border-gray-200 mb-6">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-6">
            
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-drumstick-bite text-orange-600"></i> Tipos de Trabajo
                </h3>
                
                <div class="bg-orange-50 p-4 rounded-lg border border-orange-100">
                    
                    <h4 class="text-xs font-bold text-orange-800 uppercase mb-2 tracking-wider border-b border-orange-200 pb-1">
                        Proteínas (Generan Tillo)
                    </h4>
                    <div class="grid grid-cols-2 gap-3 mb-5">
                        <?php foreach ($proteinas as $prod): ?>
                            <div class="flex flex-col">
                                <label class="text-xs font-bold text-gray-700 mb-1 truncate" title="<?php echo htmlspecialchars($prod['nombre_producto']); ?>">
                                    <?php echo htmlspecialchars($prod['nombre_producto']); ?>
                                </label>
                                <input type="number" 
                                    name="productos[<?php echo $prod['id_producto']; ?>]" 
                                    class="input-cantidad border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 px-2 py-1 border text-center font-bold text-gray-800 shadow-sm" 
                                    min="0" value="" placeholder="0"
                                    data-nombre="<?php echo htmlspecialchars($prod['nombre_producto']); ?>"
                                    data-tillo="1">
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <h4 class="text-xs font-bold text-gray-600 uppercase mb-2 tracking-wider border-b border-gray-300 pb-1">
                        Guarniciones
                    </h4>
                    <div class="grid grid-cols-2 gap-3">
                        <?php foreach ($guarniciones as $prod): ?>
                            <div class="flex flex-col">
                                <label class="text-xs font-bold text-gray-600 mb-1 truncate" title="<?php echo htmlspecialchars($prod['nombre_producto']); ?>">
                                    <?php echo htmlspecialchars($prod['nombre_producto']); ?>
                                </label>
                                <input type="number" 
                                    name="productos[<?php echo $prod['id_producto']; ?>]" 
                                    class="input-cantidad border-gray-300 rounded-lg focus:ring-gray-500 focus:border-gray-500 px-2 py-1 border text-center font-bold text-gray-600 bg-white" 
                                    min="0" value="" placeholder="0"
                                    data-nombre="<?php echo htmlspecialchars($prod['nombre_producto']); ?>"
                                    data-tillo="0">
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </div>

            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-tag text-orange-600"></i> Asignación de Tillos
                </h3>
                <div id="contenedorTillos" class="bg-gray-50 p-4 rounded-lg border border-gray-200 space-y-3 max-h-[400px] overflow-y-auto shadow-inner">
                    <div class="flex flex-col items-center justify-center h-32 text-gray-400">
                        <i class="fas fa-arrow-left fa-lg mb-2"></i>
                        <p class="text-sm text-center italic">Ingrese cantidades en las proteínas<br>para generar las etiquetas.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div>
                <label class="block text-gray-700 font-bold mb-2">* Precio Total ($)</label>
                <div class="mt-3 flex items-center">
                    <input type="checkbox" id="es_pagado" name="es_pagado" value="1" class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                    <label for="es_pagado" class="ml-2 text-sm font-bold text-gray-700">
                        ¿Pedido Pagado? <span class="text-green-600 text-xs">(Marcar si ya canceló)</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-gray-700 font-bold mb-2">* Fecha Entrega</label>
                <input type="date" name="fecha_entrega" 
                    class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 px-4 py-2 border" 
                    value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div>
                <label class="block text-gray-700 font-bold mb-2">* Hora Entrega</label>
                <input type="time" name="hora_entrega" 
                    class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 px-4 py-2 border" 
                    value="<?php echo date('H:i'); ?>" required>
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 font-bold mb-2">Observaciones</label>
            <textarea name="observaciones" rows="2" 
                class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 px-4 py-2 border" 
                placeholder="Detalles adicionales..."></textarea>
        </div>

        <div class="mb-8">
            <label class="block text-gray-700 font-bold mb-2">
                <i class="fas fa-camera text-gray-500 mr-1"></i> Evidencia (Foto)
            </label>
            <input type="file" name="evidencia_foto" accept="image/*" capture="environment" 
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer">
        </div>

        <button type="submit" class="w-full bg-orange-600 text-white font-bold py-3 rounded-lg hover:bg-orange-700 transition duration-300 shadow-lg text-lg flex justify-center items-center gap-2 transform hover:scale-[1.01]">
            <i class="fas fa-save"></i> REGISTRAR PEDIDO
        </button>
    </form>
</div>

<div id="clienteModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center hidden z-50 backdrop-blur-sm">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-md transform transition-all scale-100">
        <div class="bg-gray-900 text-white px-6 py-4 rounded-t-lg flex justify-between items-center border-b-4 border-orange-600">
            <h2 class="text-xl font-bold">Nuevo Cliente Rápido</h2>
            <button id="closeModal" class="text-gray-400 hover:text-white transition text-2xl focus:outline-none">&times;</button>
        </div>
        <form id="clienteFormQuick" class="p-6">
            <input type="hidden" name="action" value="agregar">
            
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Nombre</label>
                <input type="text" name="nombre" id="nombreQuick" class="w-full border-gray-300 rounded-lg px-4 py-2 border focus:outline-none focus:ring-2 focus:ring-orange-500" required>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-2">Teléfono</label>
                <input type="text" name="telefono" id="telefonoQuick" class="w-full border-gray-300 rounded-lg px-4 py-2 border transition-colors focus:outline-none focus:ring-2 focus:ring-orange-500" maxlength="10" required>
                <p id="errorTelefonoQuick" class="text-red-500 text-xs mt-1 hidden font-semibold"></p>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" id="btnCancelar" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg">Cancelar</button>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/nuevo_pedido.js"></script>
<?php include '../partials/footer.php'; ?>