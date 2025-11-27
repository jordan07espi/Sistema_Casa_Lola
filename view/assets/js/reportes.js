// Archivo: view/assets/js/reportes.js

// Variables globales para almacenar datos
let datosReporte = null;

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formReporte');
    
    // Generar reporte inicial
    generarReporte();

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        generarReporte();
    });
});

function generarReporte() {
    const formData = new FormData(document.getElementById('formReporte'));
    formData.append('action', 'generar');

    const tbody = document.getElementById('tbodyReporte');
    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-8"><i class="fas fa-spinner fa-spin fa-2x text-orange-500"></i></td></tr>';

    fetch('../../controller/ReporteController.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                datosReporte = data; // Guardamos para exportar
                renderizarPantalla(data);
                prepararPlantillaPDF(data);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => console.error(err));
}

// 1. Renderizar lo que se ve en el navegador (Tabla simple)
function renderizarPantalla(data) {
    const tbody = document.getElementById('tbodyReporte');
    const rangoTxt = document.getElementById('rangoPantalla');
    
    // Texto de fechas
    const d = document.getElementById('fechaDesde').value;
    const h = document.getElementById('fechaHasta').value;
    rangoTxt.textContent = `${d} al ${h}`;

    tbody.innerHTML = '';

    if (data.lista.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-8 text-gray-500">No se encontraron ventas en este periodo.</td></tr>';
        return;
    }

    data.lista.forEach(item => {
        let badge = '';
        if(item.estado === 'Entregado') badge = 'bg-green-100 text-green-800';
        else if(item.estado === 'Pendiente') badge = 'bg-yellow-100 text-yellow-800';
        else badge = 'bg-red-100 text-red-800';

        tbody.innerHTML += `
            <tr class="border-b hover:bg-gray-50 transition">
                <td class="py-2 px-4 text-gray-600">${item.fecha_entrega}</td>
                <td class="py-2 px-4 font-mono font-bold text-gray-800">#${sanitizeHTML(item.codigo_pedido)}</td>
                <td class="py-2 px-4 uppercase font-medium">${sanitizeHTML(item.cliente)}</td>
                <td class="py-2 px-4 text-center"><span class="px-2 py-0.5 rounded text-xs font-bold ${badge}">${item.estado}</span></td>
                <td class="py-2 px-4 text-right font-bold text-gray-900">$${parseFloat(item.total).toFixed(2)}</td>
            </tr>
        `;
    });
}

// 2. Llenar la plantilla oculta para el PDF
// Archivo: view/assets/js/reportes.js

function prepararPlantillaPDF(data) {
    // A. Encabezado y KPI
    const d = document.getElementById('fechaDesde').value;
    const h = document.getElementById('fechaHasta').value;
    document.getElementById('pdfRango').textContent = `Del ${d} al ${h}`;
    document.getElementById('pdfTotalVenta').textContent = '$' + parseFloat(data.kpis.dinero).toFixed(2);

    // B. Tabla de Proteínas (Resumen)
    const tbodyProt = document.getElementById('pdfTablaProteinas');
    tbodyProt.innerHTML = '';
    
    if (data.proteinas.length > 0) {
        data.proteinas.forEach(prot => {
            tbodyProt.innerHTML += `
                <tr style="border-bottom: 1px solid #f3f4f6;">
                    <td style="padding: 8px; font-weight: bold; color: #374151;">${prot.nombre_producto}</td>
                    <td style="padding: 8px; text-align: right; font-family: monospace; background-color: #f9fafb;">${prot.cantidad_total} unds.</td>
                </tr>
            `;
        });
    } else {
        tbodyProt.innerHTML = '<tr><td colspan="2" style="padding: 8px; color: #9ca3af; font-style: italic;">Sin datos de proteínas.</td></tr>';
    }

    // C. Tabla Detallada
    const tbodyDet = document.getElementById('pdfTablaDetalles');
    tbodyDet.innerHTML = '';
    
    data.lista.forEach((item, index) => {
        // Estilo alternado (zebra) manual para asegurar compatibilidad
        const bgColor = index % 2 === 0 ? '#ffffff' : '#f9fafb';
        
        tbodyDet.innerHTML += `
            <tr style="background-color: ${bgColor}; border-bottom: 1px solid #e5e7eb;">
                <td style="padding: 6px; color: #374151;">${item.fecha_entrega}</td>
                <td style="padding: 6px; font-family: monospace; font-weight: bold;">#${item.codigo_pedido}</td>
                <td style="padding: 6px; text-transform: uppercase;">${sanitizeHTML(item.cliente).substring(0, 30)}</td>
                <td style="padding: 6px; text-align: center; font-size: 9px;">${item.estado}</td>
                <td style="padding: 6px; text-align: right; font-weight: bold;">$${parseFloat(item.total).toFixed(2)}</td>
            </tr>
        `;
    });
}

function exportarPDF() {
    if (!datosReporte || datosReporte.lista.length === 0) {
        alert("No hay datos para exportar.");
        return;
    }

    const elemento = document.getElementById('plantillaPDF');
    
    // Configuración optimizada para A4
    const opt = {
        margin:       [10, 10, 10, 10], // Margen en mm (Arriba, Izq, Abajo, Der)
        filename:     `Reporte_CasaLola_${new Date().toISOString().slice(0,10)}.pdf`,
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { 
            scale: 2, // Mejora la resolución
            useCORS: true, // Importante para cargar el logo
            scrollY: 0 // Evita desplazamientos extraños
        },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    // Hacer visible el elemento temporalmente para que html2canvas pueda capturarlo
    elemento.parentNode.style.display = 'block';
    
    html2pdf().set(opt).from(elemento).save().then(() => {
        // Volver a ocultar al terminar
        elemento.parentNode.style.display = 'none';
    }).catch(err => {
        console.error("Error generando PDF:", err);
        alert("Hubo un error al generar el PDF. Revise la consola.");
        elemento.parentNode.style.display = 'none';
    });
}

function exportarPDF() {
    if (!datosReporte || datosReporte.lista.length === 0) {
        alert("No hay datos para exportar.");
        return;
    }

    const elemento = document.getElementById('plantillaPDF');
    // Hacemos visible el elemento temporalmente o usamos el clon
    // html2pdf puede renderizar elementos display:none si se le pasa el elemento directo
    
    const opt = {
        margin:       [10, 10, 10, 10], // Márgenes (mm) top, left, bottom, right
        filename:     `Reporte_CasaLola_${new Date().toISOString().slice(0,10)}.pdf`,
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true }, // useCORS importante para imágenes locales a veces
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    // Forzamos mostrar el div para que html2pdf lo capture bien (a veces falla si está hidden)
    elemento.parentNode.style.display = 'block';
    
    html2pdf().set(opt).from(elemento).save().then(() => {
        // Volver a ocultar
        elemento.parentNode.style.display = 'none';
    });
}

function exportarExcel() {
    const tabla = document.getElementById('tablaPantalla');
    const wb = XLSX.utils.table_to_book(tabla, {sheet: "Ventas"});
    const fecha = document.getElementById('fechaDesde').value;
    XLSX.writeFile(wb, `Reporte_Ventas_${fecha}.xlsx`);
}