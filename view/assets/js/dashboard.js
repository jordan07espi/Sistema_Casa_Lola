// Archivo: view/assets/js/dashboard.js

document.addEventListener('DOMContentLoaded', function() {
    // Referencias a los inputs
    const inputDesde = document.getElementById('dashDesde');
    const inputHasta = document.getElementById('dashHasta');
    const btnFiltrar = document.getElementById('btnFiltrar');

    // Carga inicial
    cargarDashboard();

    // Evento al hacer clic en el botón de recarga
    btnFiltrar.addEventListener('click', function() {
        // Animación simple de carga en el botón
        const icon = this.querySelector('i');
        icon.classList.add('fa-spin');
        
        cargarDashboard().finally(() => {
            setTimeout(() => icon.classList.remove('fa-spin'), 500);
        });
    });

    // Eventos al cambiar fecha (opcional: carga automática)
    inputDesde.addEventListener('change', cargarDashboard);
    inputHasta.addEventListener('change', cargarDashboard);
});

function cargarDashboard() {
    // Obtener valores de los inputs
    const desde = document.getElementById('dashDesde').value;
    const hasta = document.getElementById('dashHasta').value;

    const formData = new FormData();
    formData.append('action', 'obtener_datos');
    formData.append('desde', desde);
    formData.append('hasta', hasta);

    return fetch('../../controller/DashboardController.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // 1. Llenar KPIs
                // Efecto de contador simple (opcional) o asignación directa
                document.getElementById('kpiDinero').textContent = '$' + parseFloat(data.kpis.dinero).toFixed(2);
                document.getElementById('kpiPedidos').textContent = data.kpis.pedidos;
                document.getElementById('kpiProducto').textContent = data.kpis.top_producto;
                document.getElementById('kpiCantidadProducto').textContent = data.kpis.top_cantidad;

                // 2. Inicializar Gráficos (destruir anteriores si existen)
                actualizarGraficoVentas(data.graficos.ventas);
                actualizarGraficoEstados(data.graficos.estados);
            }
        })
        .catch(err => console.error("Error cargando dashboard:", err));
}

// Variables globales para instancias de gráficos
let chartVentasInstance = null;
let chartEstadosInstance = null;

function actualizarGraficoVentas(datos) {
    const ctx = document.getElementById('chartVentas').getContext('2d');
    
    // Si ya existe el gráfico, lo destruimos para redibujar
    if (chartVentasInstance) {
        chartVentasInstance.destroy();
    }

    const etiquetas = datos.map(d => {
        // Formato fecha simple DD/MM
        const f = new Date(d.fecha + 'T00:00:00');
        return f.getDate() + '/' + (f.getMonth() + 1);
    });
    const valores = datos.map(d => d.total_venta);

    chartVentasInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: etiquetas,
            datasets: [{
                label: 'Ventas ($)',
                data: valores,
                backgroundColor: 'rgba(59, 130, 246, 0.7)', // Azul moderno
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1,
                borderRadius: 6,
                barThickness: 30
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { 
                    beginAtZero: true,
                    grid: { borderDash: [2, 4] } 
                },
                x: {
                    grid: { display: false }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
}

function actualizarGraficoEstados(datos) {
    const ctx = document.getElementById('chartEstados').getContext('2d');

    if (chartEstadosInstance) {
        chartEstadosInstance.destroy();
    }

    const findCant = (est) => {
        const found = datos.find(d => d.estado === est);
        return found ? found.cantidad : 0;
    };

    const dataValues = [
        findCant('Entregado'), 
        findCant('Pendiente'), 
        findCant('Cancelado')
    ];

    chartEstadosInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Entregado', 'Pendiente', 'Cancelado'],
            datasets: [{
                data: dataValues,
                backgroundColor: [
                    '#10b981', // Emerald 500
                    '#f59e0b', // Amber 500
                    '#ef4444'  // Red 500
                ],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            },
            cutout: '70%' // Dona más fina
        }
    });
}