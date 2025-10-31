@extends('layout.layout')
@php
    $title = 'Devoluciones';
    $subTitle = 'Gestión de devoluciones de productos';
    $script = '
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="' . asset('assets/js/ventas/devoluciones.js') . '?v=' . time() . '"></script>
    ';
@endphp

<head>
    <title>Devoluciones</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/ventas/ventas.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

@push('head')
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<style>
/* Asegurar que todos los botones sean siempre visibles */
.btn-buscar-venta,
.btn-procesar-devolucion,
.historial-actions button,
button,
.swal2-confirm,
.swal2-styled {
    opacity: 1 !important;
    visibility: visible !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
}

/* Estilo específico para botones de devoluciones */
.devoluciones-form button {
    opacity: 1 !important;
    visibility: visible !important;
    background: transparent !important;
    border: 1px solid !important;
    border-radius: 8px !important;
    padding: 8px 16px !important;
    font-weight: 500 !important;
    transition: all 0.2s ease !important;
    cursor: pointer !important;
}

/* Contenedor de búsqueda de devoluciones */
.devoluciones-search-container {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.devoluciones-search-container input {
    flex: 1;
    opacity: 1 !important;
    visibility: visible !important;
}

.devoluciones-search-container button {
    opacity: 1 !important;
    visibility: visible !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
}

.btn-buscar-venta {
    border-color: #3b82f6 !important;
    color: #3b82f6 !important;
    background: transparent !important;
    padding: 8px 16px !important;
    border-radius: 8px !important;
    font-weight: 500 !important;
    transition: all 0.2s ease !important;
}

.btn-buscar-venta:hover {
    background: #3b82f6 !important;
    color: white !important;
}

/* Información de venta mejorada */
.info-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e7eb;
}

.info-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #f3f4f6;
}

.info-header i {
    color: #3b82f6;
    font-size: 1.25rem;
}

.info-header h3 {
    margin: 0;
    color: #374151;
    font-weight: 600;
    font-size: 1.1rem;
}

.info-content {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem;
    border-radius: 8px;
    transition: background-color 0.2s ease;
}

.info-item:hover {
    background: #f9fafb;
}

.info-item i {
    color: #6b7280;
    font-size: 1.1rem;
    width: 24px;
    text-align: center;
}

.info-details {
    display: flex;
    flex-direction: column;
    flex: 1;
}

.info-label {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
}

.info-value {
    font-size: 0.9rem;
    color: #374151;
    font-weight: 600;
}

.total-item .info-value.total-value {
    color: #059669;
    font-size: 1rem;
    font-weight: 700;
}

.total-item i {
    color: #059669;
}

/* Botón de procesar devolución siempre visible */
#procesarDevolucionBtn {
    opacity: 1 !important;
    visibility: visible !important;
    background: #dc2626 !important;
    color: white !important;
    border: none !important;
    padding: 0.75rem 1.5rem !important;
    border-radius: 8px !important;
    font-weight: 600 !important;
    transition: all 0.2s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
}

#procesarDevolucionBtn:hover {
    background: #b91c1c !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3) !important;
}

/* Estilos personalizados para modales SweetAlert */
.swal-custom-popup {
    border-radius: 16px !important;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
}

.swal-custom-confirm {
    background: #dc2626 !important;
    border: none !important;
    border-radius: 8px !important;
    padding: 12px 24px !important;
    font-weight: 600 !important;
    transition: all 0.2s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.swal-custom-confirm:hover {
    background: #b91c1c !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3) !important;
}

.swal-custom-cancel {
    background: #6b7280 !important;
    border: none !important;
    border-radius: 8px !important;
    padding: 12px 24px !important;
    font-weight: 600 !important;
    transition: all 0.2s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.swal-custom-cancel:hover {
    background: #4b5563 !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3) !important;
}

.swal-success-popup {
    border-radius: 16px !important;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
}

.swal2-timer-progress-bar {
    background: #059669 !important;
}

/* Hacer que los iconos material en SweetAlert se vean bien */
.swal2-html-container .material-icons {
    font-family: 'Material Icons' !important;
    font-weight: normal !important;
    font-style: normal !important;
    font-size: inherit !important;
    line-height: 1 !important;
    letter-spacing: normal !important;
    text-transform: none !important;
    display: inline-block !important;
    white-space: nowrap !important;
    word-wrap: normal !important;
    direction: ltr !important;
    -webkit-font-feature-settings: 'liga' !important;
    -webkit-font-smoothing: antialiased !important;
}

/* Estilos compactos para el recibo */
.info-section-compact {
    background: white;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e7eb;
}

.info-row-compact {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 0.4rem 0;
}

.info-item-compact {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex: 1;
}

.info-item-compact i {
    color: #6b7280;
    font-size: 1rem;
}

.info-item-compact .info-label {
    color: #6b7280;
    font-size: 0.875rem;
}

.info-item-compact .info-value {
    color: #374151;
    font-weight: 600;
    font-size: 0.9rem;
    margin-left: 0.25rem;
}

.total-row-compact {
    border-top: 1px solid #e5e7eb;
    margin-top: 0.5rem;
    padding-top: 0.5rem;
}

.total-value-compact {
    color: #059669;
    font-weight: 700;
    font-size: 1.1rem;
}
</style>
@endpush

@section('content')

<div class="grid grid-cols-12">
    <div class="col-span-12">

        <!-- Estadísticas de Devoluciones -->
        <div class="historial-stats-grid">
            <div class="historial-stat-card historial-stat-red-improved">
                <div class="historial-stat-content">
                    <div class="historial-stat-label">Devoluciones Hoy</div>
                    <div class="historial-stat-value">{{ $estadisticas['devoluciones_hoy'] ?? 0 }}</div>
                    <div class="historial-stat-change historial-stat-change-positive">
                        <iconify-icon icon="solar:refresh-bold"></iconify-icon>
                        Devoluciones procesadas
                    </div>
                </div>
                <div class="historial-stat-icon">
                    <iconify-icon icon="solar:refresh-square-bold-duotone"></iconify-icon>
                </div>
            </div>
            
            <div class="historial-stat-card historial-stat-orange-improved">
                <div class="historial-stat-content">
                    <div class="historial-stat-label">Este Mes</div>
                    <div class="historial-stat-value">{{ $estadisticas['devoluciones_mes'] ?? 0 }}</div>
                    <div class="historial-stat-change historial-stat-change-positive">
                        <iconify-icon icon="solar:calendar-bold"></iconify-icon>
                        Devoluciones del mes
                    </div>
                </div>
                <div class="historial-stat-icon">
                    <iconify-icon icon="solar:calendar-mark-bold-duotone"></iconify-icon>
                </div>
            </div>
            
            <div class="historial-stat-card historial-stat-blue-improved">
                <div class="historial-stat-content">
                    <div class="historial-stat-label">Monto Devuelto Hoy</div>
                    <div class="historial-stat-value">S/ {{ number_format($estadisticas['monto_devuelto_hoy'] ?? 0, 2) }}</div>
                    <div class="historial-stat-change historial-stat-change-positive">
                        <iconify-icon icon="solar:money-bag-bold"></iconify-icon>
                        Reembolsos procesados
                    </div>
                </div>
                <div class="historial-stat-icon">
                    <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
                </div>
            </div>
            
            <div class="historial-stat-card historial-stat-green-improved">
                <div class="historial-stat-content">
                    <div class="historial-stat-label">Monto Total Mes</div>
                    <div class="historial-stat-value">S/ {{ number_format($estadisticas['monto_devuelto_mes'] ?? 0, 2) }}</div>
                    <div class="historial-stat-change historial-stat-change-positive">
                        <iconify-icon icon="solar:chart-bold"></iconify-icon>
                        Reembolsos del mes
                    </div>
                </div>
                <div class="historial-stat-icon">
                    <iconify-icon icon="solar:chart-square-bold-duotone"></iconify-icon>
                </div>
            </div>
        </div>

        <!-- Búsqueda de Venta -->
        <div class="historial-table-container-improved">
            <div class="historial-table-header-improved">
                <div class="historial-filters-layout-improved">
                    <div class="historial-filters-left-improved">
                        <form method="GET" action="{{ route('ventas.devoluciones') }}" id="buscarVentaForm">
                            <div class="devoluciones-search-container">
                                <input type="text" 
                                       id="numeroVenta"
                                       name="numero_venta" 
                                       value="{{ request('numero_venta') }}"
                                       class="form-control" 
                                       placeholder="Ingresa el número de venta..."
                                       style="opacity: 1 !important; visibility: visible !important;">
                                <button type="button" 
                                        id="buscarVenta"
                                        class="btn btn-buscar-venta"
                                        style="opacity: 1 !important; visibility: visible !important; display: inline-flex !important;">
                                    <i class="material-icons">search</i>
                                    Buscar Venta
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="historial-filters-right">
                        <a href="{{ route('ventas.historial') }}" class="historial-btn-secondary-small">
                            <iconify-icon icon="solar:history-bold-duotone"></iconify-icon>
                            Ver Historial
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="historial-table-wrapper-improved">
                @if($venta)
                    <!-- Información de la Venta Compacta -->
                    <div style="padding: 1.5rem; background: #f8fafc; border-bottom: 1px solid #e5e7eb;">
                        <div class="info-section-compact">
                            <div class="info-header" style="margin-bottom: 0.75rem;">
                                <i class="material-icons">receipt_long</i>
                                <h3>Información de Venta</h3>
                            </div>
                            
                            <!-- Primera fila: Número, Fecha y Usuario -->
                            <div class="info-row-compact">
                                <div class="info-item-compact">
                                    <i class="material-icons">confirmation_number</i>
                                    <span class="info-label">Número:</span>
                                    <span class="info-value">{{ $venta->numero_venta }}</span>
                                </div>
                                <div class="info-item-compact">
                                    <i class="material-icons">event</i>
                                    <span class="info-label">Fecha:</span>
                                    <span class="info-value">{{ $venta->fecha_venta ? $venta->fecha_venta->format('d/m/Y g:i A') : $venta->created_at->format('d/m/Y g:i A') }}</span>
                                </div>
                                <div class="info-item-compact">
                                    <i class="material-icons">person</i>
                                    <span class="info-label">Usuario:</span>
                                    <span class="info-value">{{ $venta->usuario->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                            
                            <!-- Segunda fila: Subtotal, IGV y Total -->
                            <div class="info-row-compact">
                                <div class="info-item-compact">
                                    <i class="material-icons">receipt</i>
                                    <span class="info-label">Subtotal:</span>
                                    <span class="info-value">S/ {{ number_format($venta->subtotal, 2) }}</span>
                                </div>
                                <div class="info-item-compact">
                                    <i class="material-icons">account_balance</i>
                                    <span class="info-label">IGV:</span>
                                    <span class="info-value">S/ {{ number_format($venta->iva, 2) }}</span>
                                </div>
                                <div class="info-item-compact">
                                    <i class="material-icons">monetization_on</i>
                                    <span class="info-label">Total:</span>
                                    <span class="info-value total-value-compact">S/ {{ number_format($venta->total, 2) }}</span>
                                </div>
                            </div>
                            
                            <!-- Tercera fila: Método de pago y Comprobante -->
                            <div class="info-row-compact total-row-compact">
                                <div class="info-item-compact">
                                    <i class="material-icons">credit_card</i>
                                    <span class="info-label">Método:</span>
                                    <span class="info-value">{{ ucfirst($venta->metodo_pago) }}</span>
                                </div>
                                <div class="info-item-compact">
                                    <i class="material-icons">description</i>
                                    <span class="info-label">Comprobante:</span>
                                    <span class="info-value">{{ ucfirst($venta->tipo_comprobante) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Productos de la Venta -->
                    <form id="devolucionForm" style="padding: 0;">
                        <input type="hidden" name="venta_id" value="{{ $venta->id }}">
                        
                        <table class="historial-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">
                                        <input type="checkbox" id="selectAll" style="width: 20px; height: 20px;">
                                    </th>
                                    <th>Producto</th>
                                    <th>Cantidad Vendida</th>
                                    <th>Precio Unitario</th>
                                    <th>Subtotal</th>
                                    <th>Cantidad a Devolver</th>
                                    <th>Motivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($venta->detalles as $detalle)
                                <tr class="historial-data-row producto-row" data-detalle-id="{{ $detalle->id }}">
                                    <td>
                                        <input type="checkbox" 
                                               class="producto-checkbox" 
                                               name="productos[{{ $loop->index }}][selected]"
                                               value="1"
                                               style="width: 20px; height: 20px;">
                                    </td>
                                    <td>
                                        <div class="historial-product-info" style="padding-left: 0;">
                                            <div class="historial-product-name">{{ $detalle->producto->nombre }}</div>
                                            @if($detalle->producto->concentracion)
                                                <div class="historial-product-code">{{ $detalle->producto->concentracion }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $cantidadDevuelta = $venta->devoluciones()
                                                ->where('venta_detalle_id', $detalle->id)
                                                ->sum('cantidad_devuelta');
                                            $cantidadDisponible = $detalle->cantidad - $cantidadDevuelta;
                                        @endphp
                                        
                                        <div style="text-align: center;">
                                            <span class="historial-badge historial-badge-info">{{ $detalle->cantidad }}</span>
                                            @if($cantidadDevuelta > 0)
                                                <br>
                                                <small style="color: #d97706; font-weight: 600; margin-top: 2px;">
                                                    Devueltas: {{ $cantidadDevuelta }}
                                                </small>
                                                <br>
                                                <small style="color: #059669; font-weight: 600;">
                                                    Disponibles: {{ $cantidadDisponible }}
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="historial-price">S/ {{ number_format($detalle->precio_unitario, 2) }}</div>
                                    </td>
                                    <td>
                                        <div class="historial-price">S/ {{ number_format($detalle->subtotal, 2) }}</div>
                                    </td>
                                    <td>
                                        <input type="hidden" name="productos[{{ $loop->index }}][detalle_id]" value="{{ $detalle->id }}">
                                        @if($cantidadDisponible > 0)
                                            <input type="number" 
                                                   name="productos[{{ $loop->index }}][cantidad_devolver]"
                                                   class="cantidad-devolver"
                                                   min="1" 
                                                   max="{{ $cantidadDisponible }}"
                                                   disabled
                                                   style="width: 80px; padding: 0.5rem; border: 2px solid #e5e7eb; border-radius: 6px; text-align: center;">
                                        @else
                                            <span style="color: #6b7280; font-style: italic; font-size: 0.875rem;">
                                                Ya devuelto completamente
                                            </span>
                                            <input type="hidden" name="productos[{{ $loop->index }}][cantidad_devolver]" value="0">
                                        @endif
                                    </td>
                                    <td>
                                        <select name="productos[{{ $loop->index }}][motivo]" 
                                                class="motivo-select"
                                                disabled
                                                style="width: 150px; padding: 0.5rem; border: 2px solid #e5e7eb; border-radius: 6px;">
                                            <option value="">Seleccionar...</option>
                                            <option value="defectuoso">Producto defectuoso</option>
                                            <option value="vencido">Producto vencido</option>
                                            <option value="equivocacion">Error en la venta</option>
                                            <option value="cliente_insatisfecho">Cliente insatisfecho</option>
                                            <option value="cambio_opinion">Cambio de opinión</option>
                                            <option value="otro">Otro motivo</option>
                                        </select>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                        <div style="padding: 2rem; background: #f8fafc; border-top: 1px solid #e5e7eb;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <p style="margin: 0; color: #6b7280; font-size: 0.875rem;">
                                        <iconify-icon icon="solar:info-circle-bold-duotone"></iconify-icon>
                                        Selecciona los productos a devolver y completa la información requerida
                                    </p>
                                </div>
                                <div style="display: flex; gap: 1rem;">
                                    <button type="button" 
                                            class="historial-btn-secondary-small" 
                                            onclick="limpiarSeleccion()">
                                        <iconify-icon icon="solar:refresh-bold-duotone"></iconify-icon>
                                        Limpiar
                                    </button>
                                    <button type="submit" 
                                            class="historial-btn-nueva-entrada"
                                            id="procesarDevolucionBtn"
                                            disabled>
                                        <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon>
                                        Procesar Devolución
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                @else
                    <!-- Estado sin búsqueda o sin resultados -->
                    <div class="historial-empty-improved">
                        <div class="historial-empty-icon">
                            <iconify-icon icon="solar:magnifer-zoom-out-bold-duotone"></iconify-icon>
                        </div>
                        
                        @if(request('numero_venta'))
                            <h3>Venta no encontrada</h3>
                            <p>No se encontró la venta con número <strong>{{ request('numero_venta') }}</strong></p>
                            <div style="font-size: 0.875rem; color: #6b7280; margin: 1rem 0;">
                                <p><strong>Posibles causas:</strong></p>
                                <ul style="text-align: left; margin: 0.5rem 0; padding-left: 1.5rem;">
                                    <li>El número de venta no existe</li>
                                    <li>La venta está en estado "cancelada"</li>
                                    <li>Error de escritura en el número</li>
                                </ul>
                                <p><strong>Sugerencias:</strong></p>
                                <ul style="text-align: left; margin: 0.5rem 0; padding-left: 1.5rem;">
                                    <li>Verifica el número completo de venta</li>
                                    <li>Consulta el historial de ventas para verificar</li>
                                    <li>Solo se pueden procesar devoluciones de ventas válidas</li>
                                </ul>
                            </div>
                        @else
                            <h3>Buscar venta para devolución</h3>
                            <p>Ingresa el número de venta en el campo de búsqueda para comenzar el proceso de devolución</p>
                        @endif
                        
                        <div class="historial-empty-actions">
                            <a href="{{ route('ventas.historial') }}" class="historial-btn-primary-small">
                                <iconify-icon icon="solar:list-bold-duotone"></iconify-icon>
                                Ver Historial de Ventas
                            </a>
                            @if(request('numero_venta'))
                                <button onclick="window.open('/debug-ventas', '_blank')" class="historial-btn-primary-small" style="background: #059669; border-color: #059669; margin-left: 0.5rem;">
                                    <iconify-icon icon="solar:database-bold-duotone"></iconify-icon>
                                    Ver Info Debug
                                </button>
                                <button onclick="window.open('/verificar-totales/{{ request('numero_venta') }}', '_blank')" class="historial-btn-primary-small" style="background: #7c3aed; border-color: #7c3aed; margin-left: 0.5rem;">
                                    <iconify-icon icon="solar:calculator-bold-duotone"></iconify-icon>
                                    Ver Totales
                                </button>
                                <button onclick="if(confirm('¿Resetear devoluciones de esta venta?')) { window.open('/reset-devoluciones/{{ request('numero_venta') }}', '_blank'); }" class="historial-btn-primary-small" style="background: #dc2626; border-color: #dc2626; margin-left: 0.5rem;">
                                    <iconify-icon icon="solar:restart-bold-duotone"></iconify-icon>
                                    Reset Test
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

<style>
/* Ocultar el segundo ícono de menú (toggle móvil) solo en Devoluciones */
.navbar-header .sidebar-mobile-toggle { display: none !important; }

/* Estilos adicionales para devoluciones */
.producto-row.selected {
    background: #f0f9ff !important;
    border-left: 4px solid #3b82f6;
}

.cantidad-devolver:enabled {
    border-color: #3b82f6 !important;
}

.motivo-select:enabled {
    border-color: #3b82f6 !important;
}

.producto-checkbox:checked {
    accent-color: #3b82f6;
}

#selectAll:checked {
    accent-color: #dc2626;
}

/* Responsive para devoluciones */
@media (max-width: 768px) {
    .historial-table th:nth-child(4),
    .historial-table td:nth-child(4),
    .historial-table th:nth-child(5),
    .historial-table td:nth-child(5) {
        display: none;
    }
    
    .cantidad-devolver {
        width: 60px !important;
    }
    
    .motivo-select {
        width: 120px !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.producto-checkbox');
    const procesarBtn = document.getElementById('procesarDevolucionBtn');
    
    // Seleccionar todos
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                toggleProductoInputs(checkbox);
            });
            updateProcessarBtn();
        });
    }
    
    // Checkboxes individuales
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            toggleProductoInputs(this);
            updateSelectAll();
            updateProcessarBtn();
        });
    });
    
    function toggleProductoInputs(checkbox) {
        const row = checkbox.closest('.producto-row');
        const cantidadInput = row.querySelector('.cantidad-devolver');
        const motivoSelect = row.querySelector('.motivo-select');
        
        if (checkbox.checked) {
            row.classList.add('selected');
            cantidadInput.disabled = false;
            cantidadInput.required = true;
            motivoSelect.disabled = false;
            motivoSelect.required = true;
            
            // Establecer cantidad por defecto
            if (!cantidadInput.value) {
                cantidadInput.value = cantidadInput.max;
            }
        } else {
            row.classList.remove('selected');
            cantidadInput.disabled = true;
            cantidadInput.required = false;
            cantidadInput.value = '';
            motivoSelect.disabled = true;
            motivoSelect.required = false;
            motivoSelect.value = '';
        }
    }
    
    function updateSelectAll() {
        const checkedCount = document.querySelectorAll('.producto-checkbox:checked').length;
        const totalCount = checkboxes.length;
        
        if (selectAll) {
            selectAll.checked = checkedCount === totalCount;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < totalCount;
        }
    }
    
    function updateProcessarBtn() {
        const checkedCount = document.querySelectorAll('.producto-checkbox:checked').length;
        if (procesarBtn) {
            procesarBtn.disabled = checkedCount === 0;
        }
    }
});

function limpiarSeleccion() {
    document.querySelectorAll('.producto-checkbox').forEach(checkbox => {
        checkbox.checked = false;
        checkbox.dispatchEvent(new Event('change'));
    });
}
</script>

{{-- Overlay de carga reutilizable (como en Presentación/Categoría) --}}
@include('components.loading-overlay', [
    'id' => 'loadingOverlay',
    'size' => 36,
    'inner' => 14,
    'label' => 'Cargando datos...'
])
