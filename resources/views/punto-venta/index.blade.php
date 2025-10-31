@extends('layout.layout')
@php
    $title='Nueva Venta';
    $subTitle = '';
@endphp

@section('content')
<div class="pos-container">
    <!-- CONTENIDO PRINCIPAL -->
    <div class="pos-main">
        <!-- PANEL IZQUIERDO - PRODUCTOS -->
        <div class="pos-left-panel">
            <!-- BÚSQUEDA Y FILTROS -->
            <div class="pos-search-section">
                <div class="pos-search-container">
                    <div class="pos-search-bar">
                        <iconify-icon icon="solar:magnifer-bold-duotone" class="pos-search-icon"></iconify-icon>
                        <input type="text" 
                               id="buscarProductos" 
                               class="pos-search-input" 
                               placeholder="Buscar producto por nombre..."
                               autocomplete="off">
                        <button class="pos-search-clear" onclick="limpiarBusqueda()" style="display: none;">
                            <iconify-icon icon="solar:close-circle-bold"></iconify-icon>
                        </button>
                    </div>
                    
                    <!-- FILTROS CON CONTADORES -->
                    <div class="pos-filters-buttons">
                        <button class="pos-filter-btn active" data-filtro="" onclick="cambiarFiltro('', this)">
                            <iconify-icon icon="solar:list-bold-duotone"></iconify-icon>
                            <span>Todos</span>
                            <span class="filter-counter" id="contadorTodos">0</span>
                        </button>
                        <button class="pos-filter-btn" data-filtro="por-vencer" onclick="cambiarFiltro('por-vencer', this)">
                            <iconify-icon icon="solar:calendar-bold-duotone"></iconify-icon>
                            <span>Por vencer</span>
                            <span class="filter-counter" id="contadorPorVencer">0</span>
                        </button>
                        <!-- Botón de alternativas eliminado -->
                    </div>
                </div>
                

            </div>

            <!-- PRODUCTOS MÁS VENDIDOS / RESULTADOS -->
            <div class="pos-productos-section">
                <div class="pos-productos-header">
                    <h3 class="pos-productos-title" id="productosTitulo">
                        <iconify-icon icon="solar:crown-bold-duotone"></iconify-icon>
                        Top 10 Productos Más Vendidos
                    </h3>
                    <div class="pos-productos-count">
                        <span id="productosCount">Cargando...</span>
                    </div>
                </div>
                
                <!-- GRID DE PRODUCTOS -->
                <div id="productosGrid" class="pos-productos-grid">
                    <!-- Los productos se cargarán aquí -->
                </div>
                
                <!-- Sección de alternativas eliminada -->
            </div>
        </div>

        <!-- PANEL DERECHO - RESUMEN Y CARRITO -->
        <div class="pos-right-panel">
            <div class="pos-resumen-container">
                <!-- CARRITO COMPACTO FARMACIA -->
                <div class="pos-carrito-farmacia">
                    <div class="pos-carrito-header-compacto">
                        <h3><iconify-icon icon="solar:cart-large-2-bold-duotone"></iconify-icon> Carrito <span id="contadorProductos">(0)</span></h3>
                        <button class="pos-btn-limpiar-header" onclick="limpiarCarrito()" style="display: none;">
                            <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                        </button>
                    </div>
                    
                    <div id="carritoProductos" class="pos-carrito-lista-farmacia">
                        <div class="pos-carrito-vacio">
                            <iconify-icon icon="solar:medical-kit-bold-duotone"></iconify-icon>
                            <p>Sin productos</p>
                        </div>
                    </div>
                </div>

                <!-- RESUMEN COMPACTO -->
                <div class="pos-resumen-compacto">
                    <!-- TOGGLE AZUL DE DESCUENTO - ANTES DEL SUBTOTAL -->
                    <div class="pos-descuento-toggle-azul">
                        <label class="pos-toggle-switch">
                            <input type="checkbox" id="conDescuento" onchange="toggleDescuento()">
                            <span class="pos-toggle-slider"></span>
                            <span class="pos-toggle-text">Descuento</span>
                        </label>
                    </div>

                    <!-- SECCIÓN DE DESCUENTO INTEGRADA - DESPUÉS DEL TOGGLE -->
                    <div class="pos-descuento-inline" id="seccionDescuento" style="display: none !important; visibility: hidden; height: 0; margin: 0; padding: 0; overflow: hidden;">
                        <div class="descuento-controles">
                            <div class="descuento-tipo-selector">
                                <label class="descuento-radio">
                                    <input type="radio" name="tipoDescuento" value="porcentaje" checked onchange="cambiarTipoDescuentoInline('porcentaje')">
                                    <span>%</span>
                                </label>
                                <label class="descuento-radio">
                                    <input type="radio" name="tipoDescuento" value="monto" onchange="cambiarTipoDescuentoInline('monto')">
                                    <span>S/.</span>
                                </label>
                            </div>
                            <div class="descuento-input-inline">
                                <input type="number" 
                                       id="descuentoInlineInput" 
                                       class="descuento-inline-campo"
                                       placeholder="0" 
                                       min="0" 
                                       max="100"
                                       step="0.01"
                                       oninput="aplicarDescuentoInline()">
                                <span class="descuento-simbolo" id="descuentoSimbolo">%</span>
                            </div>
                            <button class="btn-quitar-descuento" onclick="quitarDescuento()" title="Quitar descuento">
                                <iconify-icon icon="solar:close-circle-bold"></iconify-icon>
                            </button>
                        </div>
                    </div>

                    <div class="pos-totales-farmacia">
                        <div class="pos-total-fila">
                            <span>Subtotal:</span>
                            <span id="subtotalVenta">S/. 0.00</span>
                        </div>
                        <div class="pos-total-fila" id="descuentoRow" style="display: none;">
                            <span>Descuento <span id="descuentoPorcentaje">(0%)</span>:</span>
                            <span id="descuentoVenta" style="color: #dc2626;">-S/. 0.00</span>
                        </div>
                        <div class="pos-total-fila">
                            <span>IGV <span id="igvPorcentaje">(0%)</span>:</span>
                            <span id="igvVenta">S/. 0.00</span>
                        </div>
                        <div class="pos-total-fila pos-total-principal">
                            <span>TOTAL:</span>
                            <span id="totalVenta">S/. 0.00</span>
                        </div>
                    </div>

                    <!-- PAGO RÁPIDO -->
                    <div class="pos-pago-rapido">
                        <div class="pos-metodos-compactos">
                            <button class="pos-metodo-rapido active" data-metodo="efectivo">
                                <iconify-icon icon="solar:money-bag-bold"></iconify-icon>
                                Efectivo
                            </button>
                            <button class="pos-metodo-rapido" data-metodo="tarjeta">
                                <iconify-icon icon="solar:card-bold"></iconify-icon>
                                Tarjeta
                            </button>
                            <button class="pos-metodo-rapido" data-metodo="yape">
                                <iconify-icon icon="solar:smartphone-bold"></iconify-icon>
                                Yape
                            </button>
                        </div>

                        <div id="pagoEfectivoRapido" class="pos-efectivo-rapido">
                            <div class="pos-efectivo-fila">
                                <input type="number" 
                                       id="efectivoRecibido" 
                                       class="pos-efectivo-input" 
                                       placeholder="Efectivo recibido"
                                       step="0.01"
                                       min="0">
                                <div class="pos-vuelto-display">
                                    <span>Vuelto: <strong id="vueltoCalculado">S/. 0.00</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BOTONES DE COMPROBANTES -->
                    <div class="pos-botones-comprobantes">
                        <button id="btnBoleta" 
                                class="pos-btn-comprobante pos-btn-boleta" 
                                onclick="procesarVentaConTipo('boleta')"
                                disabled>
                            <iconify-icon icon="solar:document-text-bold-duotone"></iconify-icon>
                            <span>BOLETA</span>
                        </button>
                        
                        <button id="btnTicket" 
                                class="pos-btn-comprobante pos-btn-ticket" 
                                onclick="procesarVentaConTipo('ticket')"
                                disabled>
                            <iconify-icon icon="solar:ticket-bold-duotone"></iconify-icon>
                            <span>TICKET</span>
                        </button>
                        

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- LOADING OVERLAY - OCULTO POR DEFECTO -->
<div id="posLoading" class="pos-loading" style="display: none; opacity: 0; visibility: hidden;">
    <div class="pos-loading-content">
        <div class="pos-spinner"></div>
        <p>Cargando productos...</p>
    </div>
</div>


@endsection

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<style>
/* Estilos personalizados para SweetAlert */
.swal-popup-custom {
    border-radius: 20px !important;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25) !important;
}

.swal-btn-confirm, .swal-btn-always-visible {
    border-radius: 10px !important;
    padding: 12px 24px !important;
    font-weight: 600 !important;
    box-shadow: 0 4px 14px 0 rgba(16, 185, 129, 0.4) !important;
    transition: all 0.3s ease !important;
    opacity: 1 !important;
    visibility: visible !important;
}

.swal-btn-confirm:hover, .swal-btn-always-visible:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 20px 0 rgba(16, 185, 129, 0.6) !important;
}

.swal-btn-deny {
    border-radius: 10px !important;
    padding: 12px 24px !important;
    font-weight: 600 !important;
    box-shadow: 0 4px 14px 0 rgba(220, 38, 38, 0.4) !important;
    transition: all 0.3s ease !important;
    opacity: 1 !important;
    visibility: visible !important;
}

.swal-btn-deny:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 20px 0 rgba(220, 38, 38, 0.6) !important;
}

.swal-btn-cancel {
    border-radius: 10px !important;
    padding: 12px 24px !important;
    font-weight: 600 !important;
    box-shadow: 0 4px 14px 0 rgba(107, 114, 128, 0.4) !important;
    transition: all 0.3s ease !important;
    opacity: 1 !important;
    visibility: visible !important;
}

.swal-btn-cancel:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 20px 0 rgba(107, 114, 128, 0.6) !important;
}

/* Asegurar que todos los botones de SweetAlert sean siempre visibles */
.swal2-actions button {
    opacity: 1 !important;
    visibility: visible !important;
}

/* Animación de pulso para elementos destacados */
@keyframes pulse-success {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.pulse-success {
    animation: pulse-success 1s ease-in-out;
}
</style>
@endpush

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/punto-venta/pos-profesional.css') }}">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/punto-venta/pos-profesional.js') }}"></script>
@endpush