@extends('layout.layout')
@php
    $title = 'Historial de Ventas';
    $subTitle = 'Registro completo de ventas realizadas';
    $script = '
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="' . asset('assets/js/ventas/historial.js') . '?v=' . time() . '"></script>
    ';
@endphp

<head>
    <title>Historial de Ventas</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/ventas/ventas.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

@push('head')
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<style>
/* Estilos mejorados para historial de ventas */
.historial-venta-number {
    font-weight: 600;
    color: #374151;
    font-size: 0.95rem;
}

.historial-sunat-code {
    font-size: 0.8rem;
    color: #6b7280;
    margin-top: 2px;
}

.historial-price-total {
    font-weight: 700;
    color: #059669;
    font-size: 1.1rem;
}

.historial-user-simple {
    font-weight: 500;
    color: #374151;
}

.historial-actions-improved {
    display: flex;
    gap: 8px;
    align-items: center;
}

.action-btn-primary, .action-btn-secondary {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 8px 12px;
    border-radius: 8px;
    border: none;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    opacity: 1 !important;
    visibility: visible !important;
}

.action-btn-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
}

.action-btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(59, 130, 246, 0.4);
}

.action-btn-secondary {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: white;
    box-shadow: 0 2px 4px rgba(5, 150, 105, 0.3);
}

.action-btn-secondary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(5, 150, 105, 0.4);
}

.action-btn-primary .material-icons,
.action-btn-secondary .material-icons {
    font-size: 16px;
}

/* Asegurar que los botones siempre sean visibles */
.historial-actions-improved button {
    opacity: 1 !important;
    visibility: visible !important;
}

/* Indicador de estado devuelto */
.estado-devuelto {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    color: #dc2626;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 12px;
    border: 1px solid #fca5a5;
    margin-left: 8px;
}

.estado-devuelto .material-icons {
    font-size: 14px;
}

/* Indicador de estado parcialmente devuelto */
.estado-parcialmente-devuelto {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: linear-gradient(135deg, #fef3e2 0%, #fed7aa 100%);
    color: #ea580c;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 12px;
    border: 1px solid #fdba74;
    margin-left: 8px;
}

.estado-parcialmente-devuelto .material-icons {
    font-size: 14px;
}

/* Estilo para filas de ventas devueltas */
.venta-devuelta {
    background-color: #fef2f2 !important;
    border-left: 4px solid #dc2626 !important;
}

/* Estilo para filas de ventas parcialmente devueltas */
.venta-parcialmente-devuelta {
    background-color: #fef3e2 !important;
    border-left: 4px solid #ea580c !important;
}

/* Información de devolución en el historial */
.devolucion-info {
    font-size: 0.8rem;
    color: #6b7280;
    margin-top: 2px;
    display: flex;
    flex-direction: column;
    gap: 1px;
}

.devolucion-info .devolucion-detalle {
    display: flex;
    align-items: center;
    gap: 4px;
}

.devolucion-info .material-icons {
    font-size: 12px;
}

.monto-devuelto {
    color: #dc2626;
    font-weight: 600;
}

.productos-devueltos {
    color: #ea580c;
    font-weight: 500;
}

/* Estilos para el modal de detalle de venta */
.swal-popup-detail {
    border-radius: 16px !important;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15) !important;
}

.swal-popup-detail .material-icons {
    font-size: 18px;
    vertical-align: middle;
}

/* Mejorar botones en devoluciones */
.historial-actions button,
.action-btn-primary,
.action-btn-secondary {
    opacity: 1 !important;
    visibility: visible !important;
    display: inline-flex !important;
}

/* Estilo especial para botones de devoluciones */
.devoluciones-actions button {
    opacity: 1 !important;
    visibility: visible !important;
    background: transparent !important;
    border: 1px solid !important;
    border-radius: 8px !important;
    padding: 8px 16px !important;
    font-weight: 500 !important;
    transition: all 0.2s ease !important;
}

.btn-buscar-venta {
    border-color: #3b82f6 !important;
    color: #3b82f6 !important;
}

.btn-buscar-venta:hover {
    background: #3b82f6 !important;
    color: white !important;
}

.btn-procesar-devolucion {
    border-color: #059669 !important;
    color: #059669 !important;
}

.btn-procesar-devolucion:hover {
    background: #059669 !important;
    color: white !important;
}

/* Estados de devolución */
.estado-devuelto, .estado-parcialmente-devuelto {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-left: 8px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.estado-devuelto {
    background: #fee2e2;
    color: #dc2626;
}

.estado-parcialmente-devuelto {
    background: #fef3c7;
    color: #d97706;
}

/* Venta completamente devuelta - Efecto tachado */
.venta-devuelta-completa {
    opacity: 0.7;
    position: relative;
}

.venta-devuelta-completa::after {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    top: 50%;
    height: 2px;
    background: #dc2626;
    transform: translateY(-50%);
    opacity: 0.8;
    z-index: 1;
}

.venta-devuelta-completa td {
    text-decoration: line-through;
    color: #9ca3af !important;
}

.venta-devuelta-completa .historial-price-total {
    text-decoration: line-through;
    color: #9ca3af !important;
}

/* Venta parcialmente devuelta */
.venta-parcialmente-devuelta {
    background: #fffbeb;
    border-left: 4px solid #f59e0b;
}

/* Asegurar que el botón Ver no se tache nunca */
.venta-devuelta-completa .historial-actions-improved button,
.venta-parcialmente-devuelta .historial-actions-improved button {
    text-decoration: none !important;
    opacity: 1 !important;
}

.venta-devuelta-completa td:not(:last-child) {
    text-decoration: line-through;
    opacity: 0.6;
}

.venta-parcialmente-devuelta td {
    opacity: 0.9;
}

/* Excepciones para elementos que no deben tacharse */
.venta-devuelta-completa .historial-badge,
.venta-devuelta-completa .historial-sale-info span,
.venta-devuelta-completa .historial-price-total span {
    text-decoration: none !important;
}
</style>
@endpush

@section('content')

<div class="grid grid-cols-12">
    <div class="col-span-12">

        <!-- Estadísticas Mejoradas con Degradados Bonitos -->
        <div class="historial-stats-grid">
            <div class="historial-stat-card historial-stat-red-improved">
                <div class="historial-stat-content">
                    <div class="historial-stat-label">Ventas Hoy</div>
                    <div class="historial-stat-value">{{ $estadisticas['ventas_hoy'] ?? 0 }}</div>
                    <div class="historial-stat-change historial-stat-change-positive">
                        <iconify-icon icon="solar:arrow-up-bold"></iconify-icon>
                        {{ $estadisticas['cambio_respecto_ayer'] >= 0 ? '+' : '' }}{{ $estadisticas['cambio_respecto_ayer'] ?? 0 }} Respecto a ayer
                    </div>
                </div>
                <div class="historial-stat-icon">
                    <iconify-icon icon="solar:cart-check-bold-duotone"></iconify-icon>
                </div>
            </div>
            
            <div class="historial-stat-card historial-stat-orange-improved">
                <div class="historial-stat-content">
                    <div class="historial-stat-label">Este Mes</div>
                    <div class="historial-stat-value">{{ $estadisticas['ventas_mes'] ?? 0 }}</div>
                    <div class="historial-stat-change historial-stat-change-positive">
                        <iconify-icon icon="solar:arrow-up-bold"></iconify-icon>
                        {{ $estadisticas['cambio_mes'] >= 0 ? '+' : '' }}{{ $estadisticas['cambio_mes'] ?? 0 }} Ventas del mes
                    </div>
                </div>
                <div class="historial-stat-icon">
                    <iconify-icon icon="solar:calendar-mark-bold-duotone"></iconify-icon>
                </div>
            </div>
            
            <div class="historial-stat-card historial-stat-blue-improved">
                <div class="historial-stat-content">
                    <div class="historial-stat-label">Productos Vendidos</div>
                    <div class="historial-stat-value">{{ $estadisticas['productos_vendidos'] ?? 0 }}</div>
                    <div class="historial-stat-change historial-stat-change-positive">
                        <iconify-icon icon="solar:arrow-up-bold"></iconify-icon>
                        Unidades vendidas hoy
                    </div>
                </div>
                <div class="historial-stat-icon">
                    <iconify-icon icon="solar:bag-smile-bold-duotone"></iconify-icon>
                </div>
            </div>
            
            <div class="historial-stat-card historial-stat-green-improved">
                <div class="historial-stat-content">
                    <div class="historial-stat-label">Ingresos Hoy</div>
                    <div class="historial-stat-value">S/ {{ number_format($estadisticas['ingresos_hoy'] ?? 0, 2) }}</div>
                    <div class="historial-stat-change historial-stat-change-positive">
                        <iconify-icon icon="solar:arrow-up-bold"></iconify-icon>
                        S/ {{ number_format($estadisticas['ingresos_mes'] ?? 0, 0) }} Este mes
                    </div>
                </div>
                <div class="historial-stat-icon">
                    <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
                </div>
            </div>
        </div>

        <!-- Filtros Mejorados Sin Etiquetas -->
        <div class="historial-table-container-improved">
            <div class="historial-table-header-improved">
                <div class="historial-filters-layout-improved">
                    <!-- Filtros a la izquierda -->
                    <div class="historial-filters-left-improved">
                        <form method="GET" action="{{ route('ventas.historial') }}" id="filtrosForm">
                            <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                                <input type="text" 
                                       name="search"
                                       value="{{ request('search') }}"
                                       class="historial-input-clean" 
                                       placeholder="Buscar ventas..." 
                                       id="searchHistorial">
                                
                                <div class="historial-select-with-label">
                                    <span class="historial-inline-label">Método</span>
                                    <div class="historial-select-wrapper-clean">
                                        <select class="historial-select-clean" name="metodo_pago" id="filtroMetodo">
                                            <option value="">Todos</option>
                                            <option value="efectivo" {{ request('metodo_pago') == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                            <option value="tarjeta" {{ request('metodo_pago') == 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                                            <option value="yape" {{ request('metodo_pago') == 'yape' ? 'selected' : '' }}>Yape</option>
                                        </select>
                                        <div class="historial-select-arrow-clean">
                                            <iconify-icon icon="solar:alt-arrow-down-bold"></iconify-icon>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="historial-select-with-label">
                                    <span class="historial-inline-label">Comprobante</span>
                                    <div class="historial-select-wrapper-clean">
                                        <select class="historial-select-clean" name="tipo_comprobante" id="filtroComprobante">
                                            <option value="">Todos</option>
                                            <option value="boleta" {{ request('tipo_comprobante') == 'boleta' ? 'selected' : '' }}>Boleta</option>
                                            <option value="ticket" {{ request('tipo_comprobante') == 'ticket' ? 'selected' : '' }}>Ticket</option>
                                        </select>
                                        <div class="historial-select-arrow-clean">
                                            <iconify-icon icon="solar:alt-arrow-down-bold"></iconify-icon>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="historial-select-with-label">
                                    <span class="historial-inline-label">Usuario</span>
                                    <div class="historial-select-wrapper-clean">
                                        <select class="historial-select-clean" name="usuario_id" id="filtroUsuario">
                                            <option value="">Todos</option>
                                            @foreach($usuarios as $usuario)
                                                <option value="{{ $usuario->id }}" {{ request('usuario_id') == $usuario->id ? 'selected' : '' }}>
                                                    {{ $usuario->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="historial-select-arrow-clean">
                                            <iconify-icon icon="solar:alt-arrow-down-bold"></iconify-icon>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Botón a la derecha -->
                    <div class="historial-filters-right">
                        <a href="{{ route('punto-venta.index') }}" class="historial-btn-nueva-entrada">
                            <iconify-icon icon="solar:add-circle-bold-duotone"></iconify-icon>
                            Nueva Venta
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="historial-table-wrapper-improved">
                <table class="historial-table" id="tablaHistorial">
                    <thead>
                        <tr>
                            <th>Fecha y Hora</th>
                            <th>N° Venta</th>
                            <th>Productos</th>
                            <th>Cliente</th>
                            <th>Método de Pago</th>
                            <th>Comprobante</th>
                            <th>Total</th>
                            <th>Usuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ventas as $venta)
                            <tr class="historial-row @if($venta->estado === 'devuelta') venta-devuelta-completa @elseif($venta->estado === 'parcialmente_devuelta') venta-parcialmente-devuelta @endif">
                                <td>
                                    <div class="historial-date">
                                        {{ $venta->fecha_venta ? $venta->fecha_venta->format('d/m/Y') : $venta->created_at->format('d/m/Y') }}
                                    </div>
                                    <div class="historial-time">
                                        {{ $venta->fecha_venta ? $venta->fecha_venta->format('g:i A') : $venta->created_at->format('g:i A') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="historial-sale-info">
                                        <div class="historial-sale-number">
                                            {{ $venta->numero_venta }}
                                            @if($venta->estado === 'devuelta')
                                                <span style="background: #fee2e2; color: #dc2626; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; margin-left: 4px;">
                                                    ✓ DEVUELTA
                                                </span>
                                            @elseif($venta->estado === 'parcialmente_devuelta')
                                                <span style="background: #fef3c7; color: #d97706; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; margin-left: 4px;">
                                                    ◐ PARCIAL
                                                </span>
                                            @endif
                                        </div>
                                        @if($venta->tiene_devoluciones)
                                            <div style="font-size: 0.75rem; color: #d97706; margin-top: 2px;">
                                                <iconify-icon icon="solar:return-bold-duotone"></iconify-icon>
                                                {{ $venta->cantidad_productos_devueltos }} productos devueltos
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            <td>
                                <div class="historial-quantity-container">
                                    <span class="historial-badge historial-badge-success">
                                        {{ $venta->detalles->sum('cantidad') }} unidades
                                    </span>
                                    <div class="historial-stock-change">
                                        {{ $venta->detalles->count() }} productos diferentes
                                    </div>
                                    
                                    @if($venta->tiene_devoluciones && $venta->cantidad_productos_devueltos > 0)
                                        <div class="historial-stock-change" style="color: #dc2626; font-weight: 500;">
                                            <i class="material-icons" style="font-size: 12px;">remove_circle</i>
                                            {{ $venta->cantidad_productos_devueltos }} devueltas
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($venta->cliente_razon_social)
                                    <div class="historial-provider-name">{{ $venta->cliente_razon_social }}</div>
                                    @if($venta->cliente_numero_documento)
                                        <div class="historial-provider-commercial">{{ $venta->cliente_tipo_documento }}: {{ $venta->cliente_numero_documento }}</div>
                                    @endif
                                @else
                                    <span class="historial-badge historial-badge-gray">Sin datos</span>
                                @endif
                            </td>
                            <td>
                                <div class="historial-payment-method">
                                    @if($venta->metodo_pago == 'efectivo')
                                        <span class="historial-badge historial-badge-success">
                                            <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
                                            Efectivo
                                        </span>
                                        @if($venta->vuelto > 0)
                                            <div class="historial-payment-details">
                                                Vuelto: S/ {{ number_format($venta->vuelto, 2) }}
                                            </div>
                                        @endif
                                    @elseif($venta->metodo_pago == 'tarjeta')
                                        <span class="historial-badge historial-badge-info">
                                            <iconify-icon icon="solar:card-bold-duotone"></iconify-icon>
                                            Tarjeta
                                        </span>
                                    @elseif($venta->metodo_pago == 'yape')
                                        <span class="historial-badge historial-badge-warning">
                                            <iconify-icon icon="solar:phone-bold-duotone"></iconify-icon>
                                            Yape
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($venta->tipo_comprobante == 'boleta')
                                    <span class="historial-badge historial-badge-info">
                                        <iconify-icon icon="solar:document-text-bold-duotone"></iconify-icon>
                                        Boleta Electrónica
                                    </span>
                                @else
                                    <span class="historial-badge historial-badge-gray">
                                        <iconify-icon icon="solar:ticket-bold-duotone"></iconify-icon>
                                        Sin Comprobante
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="historial-price-total">
                                    @if($venta->estado === 'devuelta')
                                        <div style="position: relative;">
                                            <span style="text-decoration: line-through; color: #9ca3af; font-size: 0.875rem;">S/ {{ number_format($venta->total, 2) }}</span>
                                            <br>
                                            <span style="color: #dc2626; font-weight: 600; font-size: 1.1rem;">S/ 0.00</span>
                                            <span style="background: #fee2e2; color: #dc2626; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; margin-left: 4px;">
                                                DEVUELTO
                                            </span>
                                        </div>
                                    @elseif($venta->tiene_devoluciones)
                                        <div style="position: relative;">
                                            <span style="text-decoration: line-through; color: #9ca3af; font-size: 0.875rem;">S/ {{ number_format($venta->total, 2) }}</span>
                                            <br>
                                            <span style="color: #059669; font-weight: 600;">S/ {{ number_format($venta->total_actual, 2) }}</span>
                                            <span style="background: #fef3c7; color: #d97706; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; margin-left: 4px;">
                                                -S/ {{ number_format($venta->monto_total_devuelto, 2) }}
                                            </span>
                                        </div>
                                    @else
                                        S/ {{ number_format($venta->total, 2) }}
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="historial-user-simple">{{ $venta->usuario->name ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <div class="historial-actions-improved">
                                    <button class="action-btn-primary" 
                                            onclick="mostrarDetalleVenta({{ $venta->id }})"
                                            title="Ver detalle de venta">
                                        <i class="material-icons">visibility</i>
                                        Ver
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9">
                                <div class="historial-empty-improved">
                                    <div class="historial-empty-icon">
                                        <iconify-icon icon="solar:cart-bold-duotone"></iconify-icon>
                                    </div>
                                    <h3>No hay ventas registradas</h3>
                                    <p>Aún no se han registrado ventas en el sistema</p>
                                    <div class="historial-empty-actions">
                                        <a href="{{ route('punto-venta.index') }}" class="historial-btn-primary-small">
                                            <iconify-icon icon="solar:add-circle-bold-duotone"></iconify-icon>
                                            Realizar Primera Venta
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                
                <!-- Paginación Mejorada -->
                @if($ventas->hasPages())
                <div class="historial-pagination-improved">
                    <!-- Información de paginación -->
                    <div class="historial-pagination-info">
                        <p class="text-sm text-gray-700">
                            Mostrando 
                            <span class="font-medium">{{ $ventas->firstItem() }}</span>
                            a 
                            <span class="font-medium">{{ $ventas->lastItem() }}</span>
                            de 
                            <span class="font-medium">{{ $ventas->total() }}</span>
                            ventas
                        </p>
                    </div>
                    
                    <!-- Controles de paginación -->
                    <div class="historial-pagination-controls">
                        {{-- Botón Primera página --}}
                        @if ($ventas->onFirstPage())
                            <span class="historial-pagination-btn historial-pagination-btn-disabled">
                                Primera
                            </span>
                        @else
                            <a href="{{ $ventas->url(1) }}" class="historial-pagination-btn">
                                Primera
                            </a>
                        @endif
                        
                        {{-- Botón Anterior --}}
                        @if ($ventas->onFirstPage())
                            <span class="historial-pagination-btn historial-pagination-btn-disabled">
                                ‹ Anterior
                            </span>
                        @else
                            <a href="{{ $ventas->previousPageUrl() }}" class="historial-pagination-btn">
                                ‹ Anterior
                            </a>
                        @endif
                        
                        {{-- Números de página --}}
                        @foreach ($ventas->getUrlRange(max(1, $ventas->currentPage() - 2), min($ventas->lastPage(), $ventas->currentPage() + 2)) as $page => $url)
                            @if ($page == $ventas->currentPage())
                                <span class="historial-pagination-btn historial-pagination-btn-current">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" class="historial-pagination-btn">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                        
                        {{-- Botón Siguiente --}}
                        @if ($ventas->hasMorePages())
                            <a href="{{ $ventas->nextPageUrl() }}" class="historial-pagination-btn">
                                Siguiente ›
                            </a>
                        @else
                            <span class="historial-pagination-btn historial-pagination-btn-disabled">
                                Siguiente ›
                            </span>
                        @endif
                        
                        {{-- Botón Última página --}}
                        @if ($ventas->hasMorePages())
                            <a href="{{ $ventas->url($ventas->lastPage()) }}" class="historial-pagination-btn">
                                Última
                            </a>
                        @else
                            <span class="historial-pagination-btn historial-pagination-btn-disabled">
                                Última
                            </span>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection




