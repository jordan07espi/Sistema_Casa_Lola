<?php
// Archivo: view/admin/reportes.php
session_start();
include '../partials/header.php';
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800 border-l-4 border-orange-600 pl-4">Reportes Financieros</h1>
</div>

<div class="bg-white p-6 rounded-lg shadow-md mb-6 border border-gray-200">
    
    <div class="flex flex-col lg:flex-row justify-between items-end gap-6">
        
        <form id="formReporte" class="flex flex-col md:flex-row gap-4 items-end w-full lg:w-auto">
            <div class="w-full md:w-auto">
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Desde:</label>
                <input type="date" id="fechaDesde" name="desde" class="w-full border-gray-300 rounded-lg p-2 border focus:ring-orange-500 text-gray-700" value="<?php echo date('Y-m-01'); ?>" required>
            </div>
            <div class="w-full md:w-auto">
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Hasta:</label>
                <input type="date" id="fechaHasta" name="hasta" class="w-full border-gray-300 rounded-lg p-2 border focus:ring-orange-500 text-gray-700" value="<?php echo date('Y-m-t'); ?>" required>
            </div>
            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white font-bold py-2.5 px-6 rounded-lg shadow transition flex items-center gap-2">
                <i class="fas fa-search"></i> Consultar
            </button>
        </form>

        <div class="flex gap-3 w-full lg:w-auto border-t lg:border-t-0 pt-4 lg:pt-0 border-gray-100 justify-end">
            <button onclick="exportarExcel()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-5 rounded-lg shadow transition flex items-center gap-2" title="Descargar Excel">
                <i class="fas fa-file-excel fa-lg"></i> <span class="hidden sm:inline">Excel</span>
            </button>
            
            <button onclick="exportarPDF()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-5 rounded-lg shadow transition flex items-center gap-2" title="Descargar PDF">
                <i class="fas fa-file-pdf fa-lg"></i> <span class="hidden sm:inline">PDF</span>
            </button>
        </div>

    </div>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 mb-10">
    <div class="p-4 bg-orange-50 border-b border-orange-100 flex justify-between items-center">
        <h3 class="font-bold text-orange-800 flex items-center gap-2">
            <i class="fas fa-list"></i> Resultado de la Búsqueda
        </h3>
        <span class="text-xs font-semibold text-gray-500 bg-white px-2 py-1 rounded border" id="rangoPantalla">---</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse" id="tablaPantalla">
            <thead class="hidden md:table-header-group bg-gray-800 text-white text-sm uppercase">
                <tr>
                    <th class="py-3 px-4 rounded-tl-lg">Fecha</th>
                    <th class="py-3 px-4">Código</th>
                    <th class="py-3 px-4">Cliente</th>
                    <th class="py-3 px-4 text-center">Estado</th>
                    <th class="py-3 px-4 text-right rounded-tr-lg">Total</th>
                </tr>
            </thead>
            <tbody id="tbodyReporte" class="text-sm text-gray-700">
                <tr><td colspan="5" class="text-center py-8 text-gray-400">Seleccione un rango de fechas.</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div style="display: none;">
    <div id="plantillaPDF" style="width: 720px; font-family: 'Helvetica', 'Arial', sans-serif; color: #333; font-size: 12px; background: #fff; padding: 20px;">
        
        <table style="width: 100%; border-bottom: 2px solid #ea580c; padding-bottom: 10px; margin-bottom: 20px;">
            <tr>
                <td style="width: 60%; vertical-align: middle;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <img src="../assets/img/logo.png" style="height: 60px; width: auto; object-fit: contain;">
                        <div>
                            <h1 style="margin: 0; font-size: 24px; color: #111; text-transform: uppercase;">Informe de Ventas</h1>
                            <p style="margin: 2px 0 0 0; color: #666; font-size: 11px;">CASA LOLA - Hornos a Leña</p>
                        </div>
                    </div>
                </td>
                <td style="width: 40%; text-align: right; vertical-align: middle;">
                    <p style="margin: 0; font-size: 10px; color: #888; text-transform: uppercase; font-weight: bold;">Generado por:</p>
                    <p style="margin: 0 0 5px 0; font-size: 12px; font-weight: bold;"><?php echo $_SESSION['nombre_completo']; ?></p>
                    
                    <p style="margin: 0; font-size: 10px; color: #888; text-transform: uppercase; font-weight: bold;">Fecha de Emisión:</p>
                    <p style="margin: 0; font-size: 12px;"><?php echo date('d/m/Y H:i'); ?></p>
                </td>
            </tr>
        </table>

        <div style="background-color: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 6px; padding: 15px; margin-bottom: 25px;">
            <table style="width: 100%;">
                <tr>
                    <td style="vertical-align: middle;">
                        <p style="margin: 0 0 5px 0; font-size: 10px; text-transform: uppercase; color: #666; font-weight: bold;">Periodo del Reporte:</p>
                        <p id="pdfRango" style="margin: 0; font-size: 14px; font-weight: bold; color: #111;">---</p>
                    </td>
                    <td style="text-align: right; vertical-align: middle;">
                        <p style="margin: 0 0 5px 0; font-size: 10px; text-transform: uppercase; color: #666; font-weight: bold;">Total Recaudado:</p>
                        <p id="pdfTotalVenta" style="margin: 0; font-size: 28px; font-weight: 900; color: #16a34a;">$0.00</p>
                    </td>
                </tr>
            </table>
        </div>

        <div style="margin-bottom: 25px;">
            <div style="background-color: #1f2937; color: white; padding: 5px 10px; font-weight: bold; font-size: 11px; text-transform: uppercase; border-top-left-radius: 4px; border-top-right-radius: 4px; width: fit-content;">
                Resumen por Proteína
            </div>
            <table style="width: 100%; border-collapse: collapse; border-top: 2px solid #1f2937;">
                <thead>
                    <tr style="background-color: #ffedd5; color: #9a3412;">
                        <th style="text-align: left; padding: 8px; font-size: 11px; border-bottom: 1px solid #fed7aa;">PRODUCTO / PROTEÍNA</th>
                        <th style="text-align: right; padding: 8px; font-size: 11px; border-bottom: 1px solid #fed7aa;">CANTIDAD VENDIDA</th>
                    </tr>
                </thead>
                <tbody id="pdfTablaProteinas">
                    </tbody>
            </table>
        </div>

        <div>
            <div style="background-color: #1f2937; color: white; padding: 5px 10px; font-weight: bold; font-size: 11px; text-transform: uppercase; border-top-left-radius: 4px; border-top-right-radius: 4px; width: fit-content;">
                Detalle de Pedidos
            </div>
            <table style="width: 100%; border-collapse: collapse; border-top: 2px solid #1f2937;">
                <thead>
                    <tr style="border-bottom: 1px solid #d1d5db; color: #6b7280;">
                        <th style="text-align: left; padding: 6px; font-size: 10px; width: 15%;">FECHA</th>
                        <th style="text-align: left; padding: 6px; font-size: 10px; width: 15%;">CÓDIGO</th>
                        <th style="text-align: left; padding: 6px; font-size: 10px; width: 40%;">CLIENTE</th>
                        <th style="text-align: center; padding: 6px; font-size: 10px; width: 15%;">ESTADO</th>
                        <th style="text-align: right; padding: 6px; font-size: 10px; width: 15%;">MONTO</th>
                    </tr>
                </thead>
                <tbody id="pdfTablaDetalles">
                    </tbody>
            </table>
        </div>

        <div style="margin-top: 40px; border-top: 1px solid #e5e7eb; padding-top: 10px; text-align: center; color: #9ca3af; font-size: 9px;">
            <p>Este documento es un reporte interno generado automáticamente por el sistema de Casa Lola.</p>
        </div>
    </div>
</div>

<script src="../assets/js/reportes.js"></script>

<?php include '../partials/footer.php'; ?>