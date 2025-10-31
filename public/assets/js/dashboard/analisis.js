/**
 * ⚡ DASHBOARD DE ANÁLISIS - SISTEMA DE BOTICA
 * Funcionalidad completa para gráficos dinámicos y estadísticas
 */

(function() {
    'use strict';
    
    // Variables globales
    let currentChart = null;
    let categoriasChart = null;
    
    // 🚀 DATOS DINÁMICOS DESDE EL SERVIDOR
    window.chartData = window.chartData || {
        ventas: [],
        totalIngresos: 0,
        cambioVentas: 0
    };
    
    /**
     * ⚡ Optimización de la página
     */
    function optimizePage() {
        // Activar animaciones de cards súper rápidas
        const cards = document.querySelectorAll('.card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.05}s`;
        });
        
        // Optimizar imágenes para carga súper rápida
        const images = document.querySelectorAll('img');
        images.forEach((img, index) => {
            if (index < 8) {
                img.loading = 'eager';
                img.decoding = 'sync';
            }
            img.style.willChange = 'transform';
        });
        
        console.log('📊 Análisis - Optimización súper rápida aplicada');
    }
    
    /**
     * ⚡ Configuración del gráfico de categorías
     */
    function setupCategoriasChart(categoriasData = null) {
        const chartContainer = document.getElementById('categorias-chart-analisis');
        const chartLoading = document.getElementById('categorias-loading');
        
        if (!chartContainer) return;
        
        // Mostrar loading
        if (chartLoading) {
            chartLoading.style.display = 'flex';
            chartLoading.style.opacity = '1';
        }
        
        // Destruir gráfico anterior si existe
        if (categoriasChart) {
            categoriasChart.destroy();
            categoriasChart = null;
        }
        
        // Usar datos proporcionados o datos globales
        const datosCategorias = categoriasData || window.categoriasMasVendidas || [];
        
        if (window.ApexCharts && datosCategorias.length > 0) {
            const labels = datosCategorias.map(item => item.categoria);
            const data = datosCategorias.map(item => parseFloat(item.cantidad_vendida) || 0);
            
            const options = {
                series: data,
                chart: {
                    type: 'donut',
                    height: 280,
                    toolbar: {
                        show: false
                    }
                },
                labels: labels,
                colors: ['#487FFF', '#4ADE80', '#F59E0B', '#EF4444', '#8B5CF6'],
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return Math.round(val) + '%';
                    },
                    style: {
                        fontSize: '12px',
                        fontWeight: '600'
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '14px',
                                    fontWeight: '600'
                                },
                                value: {
                                    show: true,
                                    fontSize: '16px',
                                    fontWeight: '700',
                                    formatter: function (val) {
                                        return val + ' unidades';
                                    }
                                },
                                total: {
                                    show: true,
                                    showAlways: false,
                                    label: 'Total',
                                    fontSize: '14px',
                                    fontWeight: '600',
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0) + ' unidades';
                                    }
                                }
                            }
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    fontSize: '12px'
                },
                tooltip: {
                    y: {
                        formatter: function(value) {
                            return value + ' unidades vendidas';
                        }
                    }
                }
            };
            
            categoriasChart = new ApexCharts(chartContainer, options);
            categoriasChart.render().then(() => {
                if (chartLoading) {
                    chartLoading.style.opacity = '0';
                    setTimeout(() => chartLoading.style.display = 'none', 100);
                }
                console.log('📊 Gráfico de Categorías cargado correctamente');
            });
        } else {
            // Mostrar mensaje cuando no hay datos
            chartContainer.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500"><p>No hay datos de categorías disponibles</p></div>';
            if (chartLoading) {
                chartLoading.style.opacity = '0';
                setTimeout(() => chartLoading.style.display = 'none', 100);
            }
        }
    }
    
    /**
     * ⚡ Configuración y renderizado del gráfico
     */
    function setupChart(ventasData = null) {
        const chartContainer = document.getElementById('ventas-chart-analisis');
        const chartLoading = document.getElementById('chart-loading');
        
        if (!chartContainer) return;
        
        // Mostrar loading
        if (chartLoading) {
            chartLoading.style.display = 'flex';
            chartLoading.style.opacity = '1';
        }
        
        // Destruir gráfico anterior si existe
        if (currentChart) {
            currentChart.destroy();
            currentChart = null;
        }
        
        // Usar datos proporcionados o datos globales
        const datosVentas = ventasData || window.chartData.ventas;
        
        if (window.ApexCharts && datosVentas) {
            // Configurar datos del gráfico
            const labels = datosVentas.map(item => item.fecha);
            const data = datosVentas.map(item => parseFloat(item.total) || 0);
            
            // Calcular valores máximo y mínimo para el eje Y
            const maxValue = Math.max(...data);
            const minValue = Math.min(...data);
            const range = maxValue - minValue;
            const step = Math.ceil(range / 5) || 10; // Dividir en 5 pasos
            
            const options = {
                series: [{
                    name: "Ventas del periodo",
                    data: data
                }],
                chart: {
                    height: 280,
                    type: 'line',
                    toolbar: {
                        show: false
                    },
                    zoom: {
                        enabled: false
                    },
                    dropShadow: {
                        enabled: true,
                        top: 6,
                        left: 0,
                        blur: 4,
                        color: "#000",
                        opacity: 0.1,
                    },
                    offsetX: 10,
                    offsetY: 0,
                    parentHeightOffset: 0
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    colors: ['#487FFF'],
                    width: 3
                },
                markers: {
                    size: 0,
                    strokeWidth: 3,
                    hover: {
                        size: 8
                    }
                },
                tooltip: {
                    enabled: true,
                    x: {
                        show: true,
                    },
                    y: {
                        formatter: function(value) {
                            return 'S/. ' + value.toFixed(2);
                        }
                    },
                    z: {
                        show: false,
                    }
                },
                grid: {
                    row: {
                        colors: ['transparent', 'transparent'],
                        opacity: 0.5
                    },
                    borderColor: '#D1D5DB',
                    strokeDashArray: 3,
                    padding: {
                        left: 60,
                        right: 25,
                        bottom: 40
                    }
                },
                yaxis: {
                    min: minValue > 0 ? Math.max(0, minValue - step) : 0,
                    max: maxValue + step,
                    tickAmount: 5,
                    labels: {
                        formatter: function (value) {
                            if (value >= 1000) {
                                return "S/. " + (value / 1000).toFixed(1) + "k";
                            }
                            return "S/. " + value.toFixed(0);
                        },
                        style: {
                            fontSize: "13px",
                            fontWeight: "500",
                            colors: ['#374151']
                        },
                        offsetX: 25,
                        align: 'right'
                    },
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                xaxis: {
                    categories: labels,
                    tooltip: {
                        enabled: false
                    },
                    labels: {
                        formatter: function (value) {
                            return value;
                        },
                        style: {
                            fontSize: "13px",
                            fontWeight: "500",
                            colors: ['#374151']
                        },
                        rotate: labels.length > 8 ? -45 : 0,
                        rotateAlways: false,
                        maxHeight: 80
                    },
                    axisBorder: {
                        show: false
                    },
                    crosshairs: {
                        show: true,
                        width: 20,
                        stroke: {
                            width: 0
                        },
                        fill: {
                            type: 'solid',
                            color: '#487FFF40',
                        }
                    }
                }
            };
            
            currentChart = new ApexCharts(chartContainer, options);
            currentChart.render().then(() => {
                if (chartLoading) {
                    chartLoading.style.opacity = '0';
                    setTimeout(() => chartLoading.style.display = 'none', 100);
                }
                console.log('📊 Gráfico de Análisis único cargado correctamente');
            });
        } else {
            // Fallback si no hay ApexCharts
            setTimeout(() => {
                if (chartLoading) {
                    chartLoading.style.opacity = '0';
                    setTimeout(() => chartLoading.style.display = 'none', 100);
                }
            }, 1500);
        }
    }
    
    /**
     * ⚡ Función para cambiar período dinámicamente
     */
    function cambiarPeriodo(periodo) {
        const chartLoading = document.getElementById('chart-loading');
        
        // Mostrar loading
        if (chartLoading) {
            chartLoading.style.display = 'flex';
            chartLoading.style.opacity = '1';
        }
        
        // Obtener la URL base correcta
        const currentUrl = window.location.href.split('?')[0];
        const url = currentUrl + `?periodo=${periodo}`;
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Actualizar gráfico principal
            setupChart(data.ventasPorDia);
            
            // Actualizar gráfico de categorías
            setupCategoriasChart(data.categoriasMasVendidas);
            
            // Actualizar métricas
            const totalElement = document.querySelector('.estadisticas-total');
            const ventasElement = document.querySelector('.estadisticas-ventas');
            const promedioElement = document.querySelector('.estadisticas-promedio');
            
            if (totalElement) {
                totalElement.textContent = 'S/. ' + data.totalVentas.toFixed(2);
            }
            
            if (ventasElement) {
                ventasElement.innerHTML = data.totalCantidad + ' ventas <iconify-icon icon="bxs:up-arrow" class="text-xs"></iconify-icon>';
            }
            
            if (promedioElement) {
                promedioElement.textContent = '+ S/. ' + data.promedioDiario.toFixed(2) + ' Por día';
            }
            
            // Actualizar título del período
            const tituloPeriodo = document.querySelector('.titulo-periodo');
            if (tituloPeriodo && data.tituloPeriodo) {
                tituloPeriodo.textContent = data.tituloPeriodo;
                tituloPeriodo.parentElement.style.display = 'block';
            } else if (tituloPeriodo) {
                tituloPeriodo.parentElement.style.display = 'none';
            }
            
            // Actualizar porcentaje de cambio
            const cambioElement = document.querySelector('.cambio-ventas');
            if (cambioElement && data.cambiosComparativos) {
                const porcentaje = data.cambiosComparativos.porcentaje_cambio || 0;
                const etiqueta = data.cambiosComparativos.etiqueta || 'vs anterior';
                const icono = porcentaje >= 0 ? 'bxs:up-arrow' : 'bxs:down-arrow';
                const color = porcentaje >= 0 ? 'text-success-600' : 'text-danger-600';
                const signo = porcentaje >= 0 ? '+' : '';
                
                cambioElement.innerHTML = `
                    <span class="inline-flex items-center gap-1 ${color}">
                        <iconify-icon icon="${icono}" class="text-xs"></iconify-icon> 
                        ${signo}${porcentaje.toFixed(1)}%
                    </span>
                    ${etiqueta}
                `;
            }
        })
        .catch(error => {
            console.error('Error al cargar datos:', error);
            if (chartLoading) {
                chartLoading.style.opacity = '0';
                setTimeout(() => chartLoading.style.display = 'none', 100);
            }
        });
    }
    
    /**
     * ⚡ Inicialización de la aplicación
     */
    function initAnalisisDashboard() {
        optimizePage();
        
        // Configurar event listener para el selector de período
        const periodoSelector = document.getElementById('periodo-selector');
        if (periodoSelector) {
            periodoSelector.addEventListener('change', function() {
                cambiarPeriodo(this.value);
            });
        }
        
        // Esperar a que ApexCharts se cargue completamente
        const checkApexCharts = () => {
            if (window.ApexCharts) {
                setupChart();
                setupCategoriasChart();
            } else {
                setTimeout(checkApexCharts, 100);
            }
        };
        setTimeout(checkApexCharts, 200);
    }
    
    /**
     * ⚡ Ejecutar cuando el DOM esté listo
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAnalisisDashboard);
    } else {
        initAnalisisDashboard();
    }
    
    // Exponer funciones globalmente si es necesario
    window.AnalisisDashboard = {
        cambiarPeriodo: cambiarPeriodo,
        setupChart: setupChart
    };
    
})(); 