<?php
// Archivo: view/admin/ticket.php
session_start();

$path = __DIR__ . '/../../model/PedidoDAO.php';
if (!file_exists($path)) {
    die("ERROR: no se encontró PedidoDAO.php en: $path");
}
require_once $path;

if (!isset($_GET['id'])) {
    die("Error: Pedido no especificado.");
}

$id_pedido = $_GET['id'];
$dao = new PedidoDAO();
$p = $dao->obtenerPorId($id_pedido);

if (!$p) die("Pedido no encontrado.");

// --- LÓGICA PARA FECHA EN ESPAÑOL ---
$diasSemana = ["DOMINGO", "LUNES", "MARTES", "MIÉRCOLES", "JUEVES", "VIERNES", "SÁBADO"];
$timestamp = strtotime($p->fecha_entrega);
$nombreDia = $diasSemana[date('w', $timestamp)]; // Obtiene el día (0-6) y busca el nombre
$fechaFormateada = date('d/m/Y', $timestamp); // Formato dia/mes/año

// --- NUEVA LÓGICA: ESTADO DE PAGO ---
$estadoPago = ($p->pagado == 1) ? 'PAGADO' : 'PENDIENTE';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket #<?php echo $p->codigo_pedido; ?></title>
    <style>
        @page {
            size: 72mm auto;
            margin: 0mm;
        }

        body {
            width: 72mm;
            margin: 0 auto;
            padding: 2mm;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            color: black;
            background-color: #fff;
            line-height: 1.2;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        /* ENCABEZADO */
        .header {
            margin-bottom: 10px;
            text-align: center;
        }
        
        .empresa { font-size: 20px; font-weight: 900; margin: 0; }
        .slogan { font-size: 12px; margin-bottom: 5px; }

        /* SECCIÓN ORDEN */
        .orden-wrapper {
            margin: 5px 0;
            text-align: center;
        }

        .orden-label {
            font-size: 12px; 
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
        }
        
        .orden-numero {
            font-size: 42px; 
            font-weight: 900;
            line-height: 1;
            display: block;
        }

        /* FECHA Y HORA DE ENTREGA */
        .entrega-box {
            margin-top: 8px;
            font-size: 13px;
            font-weight: bold;
            border-top: 2px dashed black;
            padding-top: 5px;
        }

        /* DATOS CLIENTE */
        .info-cliente {
            margin-bottom: 10px;
            border-bottom: 2px dashed black;
            padding-bottom: 10px;
            font-size: 13px;
            font-weight: bold;
        }

        /* TABLA PRODUCTOS */
        table { width: 100%; margin-bottom: 5px; border-collapse: collapse; }
        th { text-align: left; border-bottom: 2px solid black; font-size: 12px; padding-bottom: 3px;}
        td { vertical-align: top; padding: 4px 0; font-size: 14px; font-weight: bold; }

        /* TILLOS LIMPIOS */
        .tillo-text {
            font-weight: 900;
            font-size: 14px;
            display: inline-block;
            margin-right: 5px;
        }

        /* TOTALES */
        .totales {
            border-top: 2px solid black;
            padding-top: 5px;
            font-size: 20px;
            font-weight: 900;
            text-align: right;
            margin-top: 5px;
        }

        .no-print { display: none; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>

    <div class="no-print" style="text-align:center; margin-bottom:10px;">
        <button onclick="window.print()" style="background:black; color:white; padding:10px; width:100%; font-weight:bold; cursor:pointer;">IMPRIMIR TICKET</button>
    </div>

    <div class="header">
        <div class="empresa">CASA LOLA</div>
        <div class="slogan">HORNOS A LEÑA</div>
        
        <div class="orden-wrapper">
            <span class="orden-label">ORDEN #</span>
            <span class="orden-numero"><?php echo $p->codigo_pedido; ?></span>
        </div>

        <div class="entrega-box">
            ENTREGA: <?php echo $nombreDia . ' ' . $fechaFormateada; ?> <br>
            HORA: <span style="font-size: 16px; font-weight: 900;"><?php echo substr($p->hora_entrega, 0, 5); ?></span>
        </div>
    </div>

    <div class="info-cliente">
        <div>CLI: <?php echo mb_strtoupper($p->nombre_cliente); ?></div>
        <div>TEL: <?php echo $p->telefono; ?></div>
        <div>VEND: <?php echo mb_strtoupper($p->nombre_usuario); ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Cant</th>
                <th style="width: 85%;">Detalle</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($p->detalles as $prod): ?>
            <tr>
                <td class="text-center"><?php echo $prod['cantidad']; ?></td>
                <td><?php echo mb_strtoupper($prod['nombre_producto']); ?></td>
            </tr>
            
            <?php 
                $tillosProds = array_filter($p->tillos, function($t) use ($prod) {
                    return isset($t['id_producto']) && $t['id_producto'] == $prod['id_producto'];
                });
            ?>
            
            <?php if (!empty($tillosProds)): ?>
            <tr>
                <td></td>
                <td style="padding-bottom: 8px;">
                    <?php foreach ($tillosProds as $t): ?>
                        <span class="tillo-text">[#<?php echo $t['codigo_tillo']; ?>]</span>
                    <?php endforeach; ?>
                </td>
            </tr>
            <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totales">
        TOTAL: $<?php echo number_format($p->total, 2); ?>
        
        <div style="font-size: 14px; margin-top: 4px;">
            ESTADO: <?php echo $estadoPago; ?>
        </div>
    </div>

    <?php if(!empty($p->observaciones)): ?>
    <div style="margin-top: 10px; font-style: italic; border-top: 1px dashed black; padding-top:5px; font-size:12px;">
        <strong>NOTA:</strong> <?php echo $p->observaciones; ?>
    </div>
    <?php endif; ?>

    <div class="text-center" style="margin-top: 20px; font-size: 10px;">
        <p>*** GRACIAS POR SU COMPRA ***</p>
        <p>Documento sin validez tributaria</p>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
                window.onafterprint = function() {
                    window.close();
                };
            }, 300);
        };
    </script>
</body>
</html>