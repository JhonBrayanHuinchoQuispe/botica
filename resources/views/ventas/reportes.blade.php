@extends('layout.layout')
@php
    $title = 'Reportes de Ventas';
    $subTitle = 'Análisis y métricas de ventas';
    $script = '
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
        <script src="' . asset('assets/js/ventas/reportes.js') . '?v=' . time() . '"></script>
    ';
@endphp

<head>
    <title>Reportes de Ventas</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/ventas/ventas.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

@section('content')

<div class="grid grid-cols-12">
    <div class="col-span-12">
        
        <!-- Filtro de Período -->
        <div class="historial-table-container-improved" style="margin-bottom: 2rem;">
            <div class="historial-table-header-improved">
                <div class="historial-filters-layout-improved">
                    <div class="historial-filters-left-improved">
                        <form method="GET" action="{{ route('ventas.reportes') }}" id="filtroReporteForm">
                            <div style="display: flex; gap: 1rem; align-items: center;">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <span style="font-weight: 600; color: #374151;">📊 Período:</span>
                                    <select name="periodo" 
                                            class="historial-input-clean" 
                                            onchange="this.form.submit()"
                                            style="min-width: 150px;">
                                        <option value="dia" {{ $periodo == 'dia' ? 'selected' : '' }}>Hoy</option>
                                        <option value="semana" {{ $periodo == 'semana' ? 'selected' : '' }}>Esta Semana</option>
                                        <option value="mes" {{ $periodo == 'mes' ? 'selected' : '' }}>Este Mes</option>
                                        <option value="año" {{ $periodo == 'año' ? 'selected' : '' }}>Este Año</option>
                                    </select>
                                </div>
                                
                                <div style="color: #6b7280; font-size: 0.875rem;">
                                    <iconify-icon icon="solar:calendar-bold-duotone"></iconify-icon>
                                    {{ $datos['fecha_inicio']->format('d/m/Y') }} - {{ $datos['fecha_fin']->format('d/m/Y') }}
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="historial-filters-right">
                        <button onclick="exportarReporte()" class="historial-btn-nueva-entrada">
                            <iconify-icon icon="solar:download-bold-duotone"></iconify-icon>
                            Exportar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Métricas Principales -->
        <div class="reportes-stats-grid mb-4">
            <div class="reportes-stat-card reportes-stat-blue-gradient">
                <div class="reportes-stat-content">
                    <div class="reportes-stat-label">Total Ventas</div>
                    <div class="reportes-stat-value">{{ $datos['total_ventas'] }}</div>
                    <div class="reportes-stat-change">
                        <iconify-icon icon="bxs:up-arrow" class="text-xs"></iconify-icon>
                        Ventas realizadas
                    </div>
                </div>
                <div class="reportes-stat-icon">
                    <iconify-icon icon="solar:bag-smile-bold"></iconify-icon>
                </div>
            </div>
            
            <div class="reportes-stat-card reportes-stat-green-gradient">
                <div class="reportes-stat-content">
                    <div class="reportes-stat-label">Ingresos Totales</div>
                    <div class="reportes-stat-value">S/ {{ number_format($datos['total_ingresos'], 2) }}</div>
                    <div class="reportes-stat-change">
                        <iconify-icon icon="bxs:up-arrow" class="text-xs"></iconify-icon>
                        Ingresos generados
                    </div>
                </div>
                <div class="reportes-stat-icon">
                    <iconify-icon icon="solar:dollar-minimalistic-bold"></iconify-icon>
                </div>
            </div>
            
            <div class="reportes-stat-card reportes-stat-orange-gradient">
                <div class="reportes-stat-content">
                    <div class="reportes-stat-label">Ticket Promedio</div>
                    <div class="reportes-stat-value">S/ {{ number_format($datos['ticket_promedio'], 2) }}</div>
                    <div class="reportes-stat-change">
                        <iconify-icon icon="bxs:up-arrow" class="text-xs"></iconify-icon>
                        Por venta
                    </div>
                </div>
                <div class="reportes-stat-icon">
                    <iconify-icon icon="solar:calculator-bold"></iconify-icon>
                </div>
            </div>
            
            <div class="reportes-stat-card reportes-stat-purple-gradient">
                <div class="reportes-stat-content">
                    <div class="reportes-stat-label">Productos Vendidos</div>
                    <div class="reportes-stat-value">{{ $datos['productos_mas_vendidos']->sum('total_vendido') }}</div>
                    <div class="reportes-stat-change">
                        <iconify-icon icon="bxs:up-arrow" class="text-xs"></iconify-icon>
                        Unidades totales
                    </div>
                </div>
                <div class="reportes-stat-icon">
                    <iconify-icon icon="solar:box-bold"></iconify-icon>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="reportes-charts-grid mb-4">
            <div class="reportes-chart-container reportes-chart-main">
                <div class="reportes-chart-header">
                    <div class="reportes-chart-title">
                        <iconify-icon icon="solar:chart-2-bold" class="reportes-chart-icon"></iconify-icon>
                        <span>Ingresos por Día</span>
                    </div>
                    <div class="reportes-chart-subtitle">Evolución de ventas en el período</div>
                </div>
                <div class="reportes-chart-body">
                    <div id="chart-loading-ventas" class="reportes-chart-loading">
                        <div class="reportes-loading-spinner"></div>
                        <span>Cargando gráfico...</span>
                    </div>
                    <canvas id="ingresosChart" width="400" height="200"></canvas>
                </div>
            </div>
            
            <div class="reportes-chart-container reportes-chart-secondary">
                <div class="reportes-chart-header">
                    <div class="reportes-chart-title">
                        <iconify-icon icon="solar:pie-chart-2-bold" class="reportes-chart-icon"></iconify-icon>
                        <span>Métodos de Pago</span>
                    </div>
                    <div class="reportes-chart-subtitle">Distribución de pagos</div>
                </div>
                <div class="reportes-chart-body">
                    <div id="chart-loading-metodos" class="reportes-chart-loading">
                        <div class="reportes-loading-spinner"></div>
                        <span>Cargando gráfico...</span>
                    </div>
                    <canvas id="metodosChart" width="300" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Productos Más Vendidos -->
        <div class="reportes-table-container">
            <div class="reportes-table-header">
                <div class="reportes-table-title">
                    <iconify-icon icon="solar:crown-bold" class="reportes-table-icon"></iconify-icon>
                    <span>Top 10 Productos Más Vendidos</span>
                </div>
                <div class="reportes-table-subtitle">Productos con mayor rotación en el período</div>
            </div>
            <div class="reportes-table-body">
                @if($datos['productos_mas_vendidos']->count() > 0)
                    <div class="reportes-table-wrapper">
                        <table class="reportes-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Producto</th>
                                    <th class="text-center">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($datos['productos_mas_vendidos'] as $index => $item)
                                    <tr>
                                        <td>
                                            <div class="reportes-rank-badge reportes-rank-{{ $index < 3 ? 'top' : 'normal' }}">
                                                {{ $index + 1 }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="reportes-product-info">
                                                <div class="reportes-product-details">
                                                    <div class="reportes-product-name">{{ $item->producto->nombre }}</div>
                                                    <div class="reportes-product-code">{{ $item->producto->concentracion ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="reportes-quantity-badge">{{ $item->total_vendido }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="reportes-empty-state">
                        <iconify-icon icon="solar:box-bold" class="reportes-empty-icon"></iconify-icon>
                        <h6 class="reportes-empty-title">No hay productos vendidos</h6>
                        <p class="reportes-empty-text">No se encontraron productos vendidos en este período</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

<style>
/* Estilos adicionales para reportes */
.historial-ranking {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

/* Responsive para reportes */
@media (max-width: 1024px) {
    div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}

@media (max-width: 768px) {
    .historial-filters-left-improved div[style*="display: flex"] {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 0.5rem !important;
    }
    
    .historial-table th:nth-child(4),
    .historial-table td:nth-child(4) {
        display: none;
    }
}
</style>

<script>
// Datos para los gráficos
const datosIngresos = @json($datos['ingresos_por_dia']);
const datosMetodos = @json($datos['ventas_por_metodo']);

// Inicializar gráficos cuando cargue la página
document.addEventListener('DOMContentLoaded', function() {
    inicializarGraficos();
});

function inicializarGraficos() {
    // Gráfico de Ingresos por Día
    const ctxIngresos = document.getElementById('ingresosChart').getContext('2d');
    new Chart(ctxIngresos, {
        type: 'line',
        data: {
            labels: datosIngresos.map(item => item.fecha),
            datasets: [{
                label: 'Ingresos (S/)',
                data: datosIngresos.map(item => item.ingresos),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
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
                    ticks: {
                        callback: function(value) {
                            return 'S/ ' + value.toFixed(2);
                        }
                    }
                }
            }
        }
    });

    // Gráfico de Métodos de Pago
    const ctxMetodos = document.getElementById('metodosChart').getContext('2d');
    new Chart(ctxMetodos, {
        type: 'doughnut',
        data: {
            labels: datosMetodos.map(item => {
                switch(item.metodo_pago) {
                    case 'efectivo': return 'Efectivo';
                    case 'tarjeta': return 'Tarjeta';
                    case 'yape': return 'Yape';
                    default: return item.metodo_pago;
                }
            }),
            datasets: [{
                data: datosMetodos.map(item => item.total),
                backgroundColor: [
                    '#10b981',
                    '#3b82f6', 
                    '#f59e0b',
                    '#ef4444',
                    '#8b5cf6'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });
}

// La función exportarReporte() está definida en reportes.js
</script>
