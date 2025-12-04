<?php
// Archivo: view/admin/ticket.php
session_start();
require_once '../../model/PedidoDAO.php';

// Validar ID
if (!isset($_GET['id'])) {
    die("Error: Pedido no especificado.");
}

$id_pedido = $_GET['id'];
$dao = new PedidoDAO();
$p = $dao->obtenerPorId($id_pedido);

if (!$p) die("Pedido no encontrado.");

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket #<?php echo $p->codigo_pedido; ?></title>
    <style>
        /* CONFIGURACIÓN CRÍTICA PARA IMPRESORA 76MM (EPSON TM-U220) */
        @page {
            size: 72mm auto; /* Ancho seguro y largo automático */
            margin: 0mm; /* Elimina márgenes del navegador */
        }

        body {
            /* Ancho de impresión real (76mm papel - 4mm márgenes físicos) */
            width: 72mm; 
            margin: 0 auto; 
            padding: 2mm;   
            
            /* Fuente optimizada para matricial/impacto */
            font-family: 'Courier New', Courier, monospace; 
            font-size: 13px; 
            line-height: 1.2;
            color: #000;
            background-color: #fff;
        }

        /* CLASES UTILITARIAS */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        /* SECCIONES */
        .header { 
            margin-bottom: 10px; 
            border-bottom: 1px dashed #000; 
            padding-bottom: 5px; 
        }
        
        .info-cliente { 
            margin-bottom: 8px; 
            border-bottom: 1px dashed #000; 
            padding-bottom: 5px;
            font-size: 12px; 
        }
        
        /* TABLA DE PRODUCTOS */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 5px; 
        }
        
        th { 
            text-align: left; 
            border-bottom: 1px solid #000; 
            font-size: 12px;
        }
        
        td { 
            vertical-align: top; 
            padding: 4px 0; 
            font-size: 12px;
        }

        /* ESTILO PARA LOS TILLOS DEBAJO DEL PRODUCTO */
        .tillo-row {
            font-size: 11px;
            font-weight: bold;
            padding-left: 5px;
            padding-bottom: 4px; /* Separación antes del siguiente producto */
        }
        
        /* TOTALES */
        .totales { 
            border-top: 1px solid #000; 
            padding-top: 5px; 
            margin-top: 5px; 
            font-size: 14px;
        }
        
        /* TILLOS HUÉRFANOS (Sin producto asociado) */
        .tillos-extra { 
            margin-top: 10px; 
            border-top: 1px dashed #000;
            padding-top: 5px; 
            text-align: center; 
        }
        
        /* OCULTAR EN IMPRESIÓN */
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 1mm; } 
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 15px; text-align: center;">
        <button onclick="window.print()" style="background: #ea580c; color: white; border: none; padding: 10px 20px; font-weight: bold; cursor: pointer; border-radius: 5px; width: 100%;">
            🖨️ IMPRIMIR TICKET
        </button>
    </div>

    <div class="header text-center">
        <h3 style="margin: 0; font-size: 16px;">CASA LOLA</h3>
        <p style="margin: 2px 0; font-size: 12px;">Hornos a Leña</p>
        <br>
        <span class="font-bold" style="font-size: 14px;">ORDEN #<?php echo $p->codigo_pedido; ?></span>
        <br>
        <span style="font-size: 11px;"><?php echo $p->fecha_entrega . ' ' . substr($p->hora_entrega, 0, 5); ?></span>
    </div>

    <div class="info-cliente">
        <div><strong>CLI:</strong> <?php echo mb_strtoupper($p->nombre_cliente); ?></div>
        <div><strong>CI/RUC:</strong> <?php echo $p->cedula; ?></div>
        <div><strong>TEL:</strong> <?php echo $p->telefono; ?></div>
        <div><strong>VEND:</strong> <?php echo mb_strtoupper($p->nombre_usuario); ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Cant</th>
                <th style="width: 85%;">Descripción</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            // Array para rastrear qué tillos ya se mostraron junto a su producto
            $tillosMostrados = [];
            
            foreach ($p->detalles as $prod): 
            ?>
            <tr>
                <td class="text-center font-bold" style="vertical-align: top;"><?php echo $prod['cantidad']; ?></td>
                <td><?php echo mb_strtoupper($prod['nombre_producto']); ?></td>
            </tr>
            
            <?php 
                // Buscar tillos asociados a ESTE producto específico
                $tillosEsteProducto = array_filter($p->tillos, function($t) use ($prod) {
                    return isset($t['id_producto']) && $t['id_producto'] == $prod['id_producto'];
                });
            ?>

            <?php if (!empty($tillosEsteProducto)): ?>
            <tr>
                <td></td> <td class="tillo-row">
                    <?php foreach ($tillosEsteProducto as $tillo): 
                        // Marcamos como mostrado
                        $tillosMostrados[] = $tillo['codigo_tillo'];
                    ?>
                        <span>[#<?php echo $tillo['codigo_tillo']; ?>] </span>
                    <?php endforeach; ?>
                </td>
            </tr>
            <?php endif; ?>

            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totales text-right">
        <div class="font-bold">TOTAL: $<?php echo number_format($p->total, 2); ?></div>
    </div>

    <?php 
    // Filtramos los tillos que NO están en la lista de mostrados
    $tillosSinAsignar = array_filter($p->tillos, function($t) use ($tillosMostrados) {
        return !in_array($t['codigo_tillo'], $tillosMostrados);
    });
    ?>

    <?php if (!empty($tillosSinAsignar)): ?>
    <div class="tillos-extra">
        <div class="font-bold" style="font-size: 11px; margin-bottom: 2px;">OTRAS ETIQUETAS</div>
        <div style="font-size: 12px; font-weight: bold;">
            <?php foreach ($tillosSinAsignar as $tillo): ?>
                <span>[#<?php echo $tillo['codigo_tillo']; ?>] </span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if(!empty($p->observaciones)): ?>
    <div style="margin-top: 10px; border-top: 1px dashed #000; padding-top: 5px;">
        <strong>NOTA:</strong>
        <div style="font-style: italic; margin-top: 2px;"><?php echo $p->observaciones; ?></div>
    </div>
    <?php endif; ?>

    <div class="text-center" style="margin-top: 20px; font-size: 11px;">
        <p>*** GRACIAS POR SU PREFERENCIA ***</p>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>