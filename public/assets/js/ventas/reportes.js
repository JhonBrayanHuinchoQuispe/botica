console.log('✅ Reportes - JavaScript cargado');

// Variables globales
let chartIngresos, chartMetodos;
let datosReporte = {};

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Inicializando módulo de Reportes');
    
    // Configurar eventos adicionales
    configurarEventos();
    
    // Inicializar gráficos inmediatamente
    inicializarGraficos();
    
    console.log('✅ Reportes inicializado correctamente');
});

// Configurar eventos adicionales
function configurarEventos() {
    // Cambio automático de período
    const selectPeriodo = document.querySelector('select[name="periodo"]');
    if (selectPeriodo) {
        selectPeriodo.addEventListener('change', function() {
            mostrarCargandoPeriodo();
        });
    }
    
    // Atajos de teclado
    document.addEventListener('keydown', function(e) {
        // Ctrl + E para exportar
        if (e.ctrlKey && e.key === 'e') {
            e.preventDefault();
            exportarReporte();
        }
    });
}

// Mostrar loading al cambiar período y actualizar datos
function mostrarCargandoPeriodo() {
    const selectPeriodo = document.querySelector('select[name="periodo"]');
    const periodoSeleccionado = selectPeriodo ? selectPeriodo.value : 'hoy';
    
    Swal.fire({
        title: 'Actualizando reporte...',
        text: 'Obteniendo datos del período seleccionado',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Actualizar datos inmediatamente
    actualizarDatosPorPeriodo(periodoSeleccionado);
    Swal.close();
}

// Exportar reporte
function exportarReporte() {
    const periodo = document.querySelector('select[name="periodo"]').value;
    
    Swal.fire({
        title: '📊 Exportar Reporte',
        html: `
            <div style="text-align: left;">
                <p><strong>Período seleccionado:</strong> ${obtenerNombrePeriodo(periodo)}</p>
                <p>¿En qué formato deseas exportar el reporte?</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: '📊 Excel',
        denyButtonText: '📄 PDF',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#059669',
        denyButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280'
    }).then((result) => {
        if (result.isConfirmed) {
            exportarExcel(periodo);
        } else if (result.isDenied) {
            exportarPDF(periodo);
        }
    });
}

// Exportar a Excel
async function exportarExcel(periodo) {
    console.log('📊 Exportando a Excel...');
    
    Swal.fire({
        title: 'Preparando Excel...',
        text: 'Generando archivo de reporte',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
    
    try {
        // Obtener datos reales del backend
        const datos = await obtenerDatosReporte(periodo);
    
    // Crear workbook
    const wb = XLSX.utils.book_new();
    
    // Hoja de estadísticas principales
    const wsEstadisticas = XLSX.utils.aoa_to_sheet([
        ['REPORTE DE VENTAS - ' + obtenerNombrePeriodo(periodo).toUpperCase()],
        [''],
        ['ESTADÍSTICAS PRINCIPALES'],
        ['Métrica', 'Valor'],
        ['Total Ventas', datos.estadisticas.ventas],
        ['Productos Vendidos', datos.estadisticas.productos],
        ['Clientes Atendidos', datos.estadisticas.clientes],
        ['Ticket Promedio', datos.estadisticas.promedio],
        [''],
        ['MÉTODOS DE PAGO'],
        ['Método', 'Cantidad', 'Porcentaje'],
        ['Efectivo', datos.metodos[0], ((datos.metodos[0] / datos.metodos.reduce((a,b) => a+b, 0)) * 100).toFixed(1) + '%'],
        ['Tarjeta', datos.metodos[1], ((datos.metodos[1] / datos.metodos.reduce((a,b) => a+b, 0)) * 100).toFixed(1) + '%'],
        ['Yape', datos.metodos[2], ((datos.metodos[2] / datos.metodos.reduce((a,b) => a+b, 0)) * 100).toFixed(1) + '%']
    ]);
    
    XLSX.utils.book_append_sheet(wb, wsEstadisticas, 'Resumen');
    
    // Hoja de ingresos diarios
    const wsIngresos = XLSX.utils.aoa_to_sheet([
        ['INGRESOS POR DÍA'],
        [''],
        ['Día', 'Ingresos (S/)'],
        ...datos.ingresos.map((ingreso, index) => [`Día ${index + 1}`, `S/ ${ingreso.toLocaleString()}`])
    ]);
    
    XLSX.utils.book_append_sheet(wb, wsIngresos, 'Ingresos Diarios');
    
        // Generar archivo
        const nombreArchivo = `reporte_ventas_${periodo}_${new Date().toISOString().split('T')[0]}.xlsx`;
        XLSX.writeFile(wb, nombreArchivo);
        
        // Mostrar notificación inmediatamente
        Swal.fire({
            title: '✅ Excel Generado',
            text: `El archivo "${nombreArchivo}" se ha descargado exitosamente`,
            icon: 'success',
            confirmButtonColor: '#059669'
        });
        
    } catch (error) {
        console.error('❌ Error exportando Excel:', error);
        Swal.fire({
            title: 'Error',
            text: 'No se pudo generar el archivo Excel. Inténtalo de nuevo.',
            icon: 'error',
            confirmButtonColor: '#dc2626'
        });
    }
}

// Exportar a PDF
async function exportarPDF(periodo) {
    console.log('📄 Exportando a PDF...');
    
    Swal.fire({
        title: 'Preparando PDF...',
        text: 'Generando archivo de reporte',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
    
    try {
        // Obtener datos reales del backend
        const datos = await obtenerDatosReporte(periodo);
    
    // Configurar jsPDF
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    // Título
    doc.setFontSize(20);
    doc.setTextColor(40, 40, 40);
    doc.text('REPORTE DE VENTAS', 20, 30);
    
    doc.setFontSize(14);
    doc.text(`Período: ${obtenerNombrePeriodo(periodo)}`, 20, 45);
    doc.text(`Fecha de generación: ${new Date().toLocaleDateString()}`, 20, 55);
    
    // Estadísticas principales
    doc.setFontSize(16);
    doc.setTextColor(0, 100, 0);
    doc.text('ESTADÍSTICAS PRINCIPALES', 20, 75);
    
    doc.setFontSize(12);
    doc.setTextColor(40, 40, 40);
    doc.text(`Total Ventas: ${datos.estadisticas.ventas}`, 20, 90);
    doc.text(`Productos Vendidos: ${datos.estadisticas.productos}`, 20, 100);
    doc.text(`Clientes Atendidos: ${datos.estadisticas.clientes}`, 20, 110);
    doc.text(`Ticket Promedio: ${datos.estadisticas.promedio}`, 20, 120);
    
    // Métodos de pago
    doc.setFontSize(16);
    doc.setTextColor(0, 100, 0);
    doc.text('MÉTODOS DE PAGO', 20, 140);
    
    doc.setFontSize(12);
    doc.setTextColor(40, 40, 40);
    const totalMetodos = datos.metodos.reduce((a,b) => a+b, 0);
    doc.text(`Efectivo: ${datos.metodos[0]} (${((datos.metodos[0]/totalMetodos)*100).toFixed(1)}%)`, 20, 155);
    doc.text(`Tarjeta: ${datos.metodos[1]} (${((datos.metodos[1]/totalMetodos)*100).toFixed(1)}%)`, 20, 165);
    doc.text(`Yape: ${datos.metodos[2]} (${((datos.metodos[2]/totalMetodos)*100).toFixed(1)}%)`, 20, 175);
    
    // Ingresos por día (tabla)
    doc.setFontSize(16);
    doc.setTextColor(0, 100, 0);
    doc.text('INGRESOS POR DÍA', 20, 195);
    
    // Crear tabla de ingresos
    const tablaIngresos = datos.ingresos.map((ingreso, index) => [
        `Día ${index + 1}`,
        `S/ ${ingreso.toLocaleString()}`
    ]);
    
    doc.autoTable({
        head: [['Día', 'Ingresos']],
        body: tablaIngresos,
        startY: 205,
        theme: 'grid',
        headStyles: { fillColor: [0, 100, 0] },
        margin: { left: 20 }
    });
    
    // Generar archivo
    const nombreArchivo = `reporte_ventas_${periodo}_${new Date().toISOString().split('T')[0]}.pdf`;
    doc.save(nombreArchivo);
    
    // Mostrar notificación inmediatamente
    Swal.fire({
        title: '✅ PDF Generado',
        text: `El archivo "${nombreArchivo}" se ha descargado exitosamente`,
        icon: 'success',
        confirmButtonColor: '#dc2626'
    });
            
    } catch (error) {
        console.error('❌ Error exportando PDF:', error);
        Swal.fire({
            title: 'Error',
            text: 'No se pudo generar el archivo PDF. Inténtalo de nuevo.',
            icon: 'error',
            confirmButtonColor: '#dc2626'
        });
    }
}

// Obtener nombre legible del período
function obtenerNombrePeriodo(periodo) {
    const nombres = {
        'dia': 'Hoy',
        'semana': 'Esta Semana',
        'mes': 'Este Mes',
        'año': 'Este Año'
    };
    
    return nombres[periodo] || periodo;
}

// Funciones de formateo
function formatearMoneda(cantidad) {
    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN'
    }).format(cantidad);
}

function formatearPorcentaje(valor) {
    return new Intl.NumberFormat('es-PE', {
        style: 'percent',
        minimumFractionDigits: 1
    }).format(valor / 100);
}

function formatearNumero(numero) {
    return new Intl.NumberFormat('es-PE').format(numero);
}

// Inicializar gráficos
function inicializarGraficos() {
    console.log('📊 Inicializando gráficos...');
    
    // Ocultar elementos de carga
    const loadingVentas = document.getElementById('chart-loading-ventas');
    const loadingMetodos = document.getElementById('chart-loading-metodos');
    
    if (loadingVentas) {
        loadingVentas.style.display = 'none';
    }
    if (loadingMetodos) {
        loadingMetodos.style.display = 'none';
    }
    
    // Inicializar gráfico de ingresos
    inicializarGraficoIngresos();
    
    // Inicializar gráfico de métodos de pago
    inicializarGraficoMetodos();
}

// Gráfico de ingresos por día
function inicializarGraficoIngresos() {
    const ctx = document.getElementById('ingresosChart');
    if (!ctx) return;
    
    // Datos de ejemplo (en producción vendrían del servidor)
    const datos = {
        labels: ['13/08', '14/08', '15/08', '16/08', '17/08', '18/08', '19/08'],
        datasets: [{
            label: 'Ingresos (S/)',
            data: [0, 0, 0, 0, 0, 36, 72],
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#3b82f6',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 6
        }]
    };
    
    chartIngresos = new Chart(ctx, {
        type: 'line',
        data: datos,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f3f4f6'
                    },
                    ticks: {
                        callback: function(value) {
                            return 'S/ ' + value;
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// Gráfico de métodos de pago
function inicializarGraficoMetodos() {
    const ctx = document.getElementById('metodosChart');
    if (!ctx) return;
    
    // Datos de ejemplo (en producción vendrían del servidor)
    const datos = {
        labels: ['Efectivo', 'Tarjeta', 'Yape'],
        datasets: [{
            data: [60, 25, 15],
            backgroundColor: [
                '#10b981',
                '#3b82f6', 
                '#f59e0b'
            ],
            borderWidth: 0,
            hoverOffset: 4
        }]
    };
    
    chartMetodos = new Chart(ctx, {
        type: 'doughnut',
        data: datos,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map((label, i) => {
                                    const value = data.datasets[0].data[i];
                                    const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return {
                                        text: `${label}: ${value} (${percentage}%)`,
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        strokeStyle: data.datasets[0].backgroundColor[i],
                                        lineWidth: 0,
                                        pointStyle: 'circle'
                                    };
                                });
                            }
                            return [];
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

// Función para obtener datos del reporte según período desde el backend
async function obtenerDatosReporte(periodo) {
    try {
        console.log('🔄 Obteniendo datos reales del backend para período:', periodo);
        
        const response = await fetch(`/ventas/reportes/datos?periodo=${periodo}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('✅ Datos obtenidos del backend:', data);
        
        // Formatear datos para compatibilidad con gráficos
        const datosFormateados = {
            ingresos: data.ingresos_por_dia?.map(item => parseFloat(item.ingresos) || 0) || [],
            metodos: [
                data.ventas_por_metodo?.find(m => m.metodo_pago === 'efectivo')?.total || 0,
                data.ventas_por_metodo?.find(m => m.metodo_pago === 'tarjeta')?.total || 0,
                data.ventas_por_metodo?.find(m => m.metodo_pago === 'yape')?.total || 0
            ],
            estadisticas: {
                ventas: `S/ ${parseFloat(data.total_ingresos || 0).toLocaleString('es-PE', {minimumFractionDigits: 2})}`,
                productos: (data.productos_mas_vendidos?.reduce((sum, p) => sum + (p.total_vendido || 0), 0) || 0).toString(),
                clientes: data.total_ventas?.toString() || '0',
                promedio: `S/ ${parseFloat(data.ticket_promedio || 0).toLocaleString('es-PE', {minimumFractionDigits: 2})}`
            },
            productos_mas_vendidos: data.productos_mas_vendidos || []
        };
        
        return datosFormateados;
        
    } catch (error) {
        console.error('❌ Error obteniendo datos del reporte:', error);
        
        // Datos de fallback en caso de error
        return {
            ingresos: [0, 0, 0, 0, 0, 0, 0],
            metodos: [0, 0, 0],
            estadisticas: {
                ventas: 'S/ 0.00',
                productos: '0',
                clientes: '0',
                promedio: 'S/ 0.00'
            },
            productos_mas_vendidos: []
        };
    }
}

// Función para actualizar datos por período
async function actualizarDatosPorPeriodo(periodo) {
    console.log('🔄 Actualizando datos para período:', periodo);
    
    try {
        // Mostrar indicador de carga
        mostrarCargandoPeriodo();
        
        // Obtener datos del período desde el backend
        const datos = await obtenerDatosReporte(periodo);
        
        // Actualizar estadísticas principales
        actualizarEstadisticas(datos.estadisticas);
        
        // Actualizar gráficos
        actualizarGraficoIngresos(datos.ingresos);
        actualizarGraficoMetodos(datos.metodos);
        
        console.log('✅ Datos actualizados correctamente');
        
    } catch (error) {
        console.error('❌ Error actualizando datos:', error);
        
        // Mostrar mensaje de error al usuario
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron cargar los datos del reporte. Inténtalo de nuevo.',
                confirmButtonText: 'Entendido'
            });
        }
    }
}

// Actualizar estadísticas principales
function actualizarEstadisticas(estadisticas) {
    const elementos = {
        '.metric-value': ['ventas', 'productos', 'clientes', 'promedio']
    };
    
    const valores = Object.values(estadisticas);
    const metricValues = document.querySelectorAll('.metric-value');
    
    metricValues.forEach((elemento, index) => {
        if (valores[index]) {
            elemento.textContent = valores[index];
        }
    });
}

// Actualizar gráfico de ingresos
function actualizarGraficoIngresos(nuevosIngresos) {
    if (chartIngresos) {
        chartIngresos.data.datasets[0].data = nuevosIngresos;
        chartIngresos.update('active');
    }
}

// Actualizar gráfico de métodos de pago
function actualizarGraficoMetodos(nuevosMetodos) {
    if (chartMetodos) {
        chartMetodos.data.datasets[0].data = nuevosMetodos;
        chartMetodos.update('active');
    }
}

console.log('✅ Reportes - JavaScript completamente cargado');