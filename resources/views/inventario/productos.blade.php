@extends('layout.layout')
@php
    $title='Gestión de Productos';
    $subTitle = 'Lista de Productos';
    $script='
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
        <script src="' . asset("assets/js/inventario/eliminar.js") . '"></script>
        <script src="' . asset("assets/js/inventario/validaciones-tiempo-real.js") . '"></script>
        <script src="' . asset("assets/js/inventario/listaproductos.js") . '"></script>
    ';
    use Carbon\Carbon;
@endphp
<head>
    <title>Lista de productos</title>
    <!-- Vite removido para evitar errores de conexión -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/inventario/tablas.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/inventario/crud.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/inventario/modal_agregar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/inventario/modal_ver.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/inventario/modal_editar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/inventario/validaciones-tiempo-real.css') }}?v={{ time() }}">
    <!-- Estilos modernos para badges de ubicación -->
    <link rel="stylesheet" href="{{ asset('assets/css/ubicacion/productos/tablas.css') }}?v={{ time() }}">
    <!-- Estilos para múltiples ubicaciones -->
    <link rel="stylesheet" href="{{ asset('assets/css/inventario/ubicaciones-multiples.css') }}?v={{ time() }}">
    <!-- FontAwesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Script para mantener sidebar expandido -->
    <script>
        // Solución simple: mantener sidebar siempre expandido
        function forzarSidebarExpandido() {
            // Ejecutar inmediatamente al cargar
            setTimeout(function() {
                const body = document.body;
                const html = document.documentElement;
                
                // Remover cualquier clase que colapse el sidebar
                const clasesToRemove = [
                    'sidebar-collapsed', 'sidebar-minimize', 'sidebar-close', 
                    'sidebar-mini', 'collapsed', 'minimize'
                ];
                
                clasesToRemove.forEach(className => {
                    body.classList.remove(className);
                    html.classList.remove(className);
                });
                
                // Forzar clases de expansión si existen
                const classesToAdd = ['sidebar-open', 'sidebar-expand', 'expanded'];
                classesToAdd.forEach(className => {
                    body.classList.add(className);
                });
                
                console.log('✅ Sidebar forzado a expandido');
            }, 10);
        }
        
        // Ejecutar inmediatamente
        forzarSidebarExpandido();
        
        // Ejecutar cuando el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', forzarSidebarExpandido);
        } else {
            forzarSidebarExpandido();
        }
        
        // Ejecutar después de que la página se cargue completamente
        window.addEventListener('load', forzarSidebarExpandido);
    </script>
    <!-- Estilos adicionales para paginación -->
    <style>
        .pagination-btn:hover {
            background: #f3f4f6 !important;
            border-color: #9ca3af !important;
        }
        .pagination-btn.current:hover {
            background: #4f46e5 !important;
        }
        .pagination-btn.disabled:hover {
            background: #f9fafb !important;
            color: #9ca3af !important;
            cursor: not-allowed !important;
        }
    </style>
    <!-- Scripts moved to section for better organization -->
</head>

@section('content')

    <!-- Modal de Detalles -->
    <div id="modalDetalles" class="modal-overlay fixed inset-0 hidden items-center justify-center z-50 bg-black bg-opacity-50">
        <div class="modal-container max-h-[95vh] overflow-y-auto w-[95%] max-w-7xl mx-4 bg-white rounded-lg shadow-2xl">
            <!-- Header -->
            <div class="modal-header-detail">
                <h3 class="modal-title-detail">
                    <iconify-icon icon="heroicons:eye-solid" class="text-xl"></iconify-icon>
                    Detalles del Producto
                </h3>
                <button class="modal-close-detail" onclick="cerrarModalDetalles()">
                    <iconify-icon icon="heroicons:x-mark-solid" class="text-lg"></iconify-icon>
                </button>
            </div>

            <!-- Content -->
            <div class="modal-content-details p-8">
                <div class="details-grid-container gap-12">

                    <!-- Columna Izquierda: Imagen y Datos Principales -->
                    <div class="details-col">
                        <!-- Imagen y Nombre -->
                        <div class="detail-section product-main-info mb-8">
                            <div class="product-image-container mb-6">
                                <img id="modal-imagen" src="" alt="Producto">
                                <div class="product-icon-overlay">
                                    <i class="fas fa-pills"></i>
                                    <span class="product-icon-fallback">💊</span>
                                </div>
                            </div>
                            <h3 id="modal-nombre" class="text-3xl font-bold text-gray-800 mb-3"></h3>
                            <p id="modal-concentracion" class="text-lg text-gray-500"></p>
                        </div>
                        
                        <!-- Información General -->
                        <div class="detail-section mb-8">
                            <h4 class="detail-section-title mb-6">
                                <iconify-icon icon="heroicons:identification-solid"></iconify-icon>
                                Información General
                            </h4>
                            <div class="space-y-6">
                                <div class="detail-item-grid py-3">
                                    <span class="detail-label text-lg">Categoría</span>
                                    <span class="detail-value text-lg" id="modal-categoria"></span>
                                </div>
                                <div class="detail-item-grid py-3">
                                    <span class="detail-label text-lg">Marca</span>
                                    <span class="detail-value text-lg" id="modal-marca"></span>
                                </div>
                                <div class="detail-item-grid py-3">
                                    <span class="detail-label text-lg">Proveedor</span>
                                    <span class="detail-value text-lg" id="modal-proveedor"></span>
                                </div>
                                <div class="detail-item-grid py-3">
                                    <span class="detail-label text-lg">Presentación</span>
                                    <span class="detail-value text-lg" id="modal-presentacion"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="detail-section mb-8">
                            <h4 class="detail-section-title mb-6">
                                <iconify-icon icon="heroicons:check-badge-solid"></iconify-icon>
                                Estado Actual
                            </h4>
                            <div id="modal-estado-container" class="py-4">
                                <span id="modal-estado" class="detail-value-lg text-xl"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha: Resto de la Información -->
                    <div class="details-col">
                        <!-- Códigos y Lote -->
                         <div class="detail-section mb-8">
                            <h4 class="detail-section-title mb-6">
                                <iconify-icon icon="heroicons:qr-code-solid"></iconify-icon>
                                Códigos y Lote
                            </h4>
                            <div class="codes-cards-grid gap-8">
                                <div class="detail-card p-6">
                                    <span class="detail-label text-lg">ID</span>
                                    <span class="detail-value-lg text-xl" id="modal-id"></span>
                                </div>
                                <div class="detail-card p-6">
                                    <span class="detail-label text-lg">Código de Barras</span>
                                    <span class="detail-value-lg text-xl" id="modal-codigo-barras"></span>
                                </div>
                                <div class="detail-card p-6">
                                    <span class="detail-label text-lg">Lote</span>
                                    <span class="detail-value-lg text-xl" id="modal-lote"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Stock y Precios -->
                        <div class="detail-section mb-8">
                            <h4 class="detail-section-title mb-6">
                                <iconify-icon icon="heroicons:banknotes-solid"></iconify-icon>
                                Stock y Precios
                            </h4>
                            <div class="stock-dates-cards-grid gap-8">
                                <div class="detail-card p-6">
                                    <span class="detail-label text-lg">Stock Actual</span>
                                    <span class="detail-value-lg text-xl" id="modal-stock"></span>
                                </div>
                                <div class="detail-card p-6">
                                    <span class="detail-label text-lg">Stock Mínimo</span>
                                    <span class="detail-value-lg text-xl" id="modal-stock-min"></span>
                                </div>
                                <div class="detail-card p-6">
                                    <span class="detail-label text-lg">Precio Compra</span>
                                    <span class="detail-value-lg text-xl" id="modal-precio-compra"></span>
                                </div>
                                <div class="detail-card p-6">
                                    <span class="detail-label text-lg">Precio Venta</span>
                                    <span class="detail-value-lg text-xl" id="modal-precio-venta"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Información de Lotes -->
                        <div class="detail-section mb-8">
                            <h4 class="detail-section-title mb-6">
                                <iconify-icon icon="solar:box-minimalistic-bold-duotone"></iconify-icon>
                                Lotes Disponibles
                            </h4>
                            <div id="modal-lotes-container" class="space-y-4">
                                <!-- Los lotes se cargarán dinámicamente aquí -->
                                <div class="text-center py-8">
                                    <iconify-icon icon="solar:box-linear" class="text-4xl text-gray-400 mb-2"></iconify-icon>
                                    <p class="text-gray-500">Cargando información de lotes...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Fechas y Ubicación -->
                        <div class="detail-section">
                             <h4 class="detail-section-title mb-6">
                                <iconify-icon icon="heroicons:calendar-days-solid"></iconify-icon>
                                Fechas y Ubicación
                            </h4>
                             <div class="stock-dates-cards-grid gap-8">
                                <div class="detail-card p-6">
                                    <span class="detail-label text-lg">Fabricación</span>
                                    <span class="detail-value-lg text-xl" id="modal-fecha-fab"></span>
                                </div>
                                <div class="detail-card p-6">
                                    <span class="detail-label text-lg">Vencimiento</span>
                                    <span class="detail-value-lg text-xl" id="modal-fecha-ven"></span>
                                </div>
                                <div class="detail-card ubicacion-card p-6">
                                    <span class="detail-label text-lg">Ubicación</span>
                                    <span class="detail-value-lg text-xl" id="modal-ubicacion"></span>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Modal Editar Producto -->
    <div id="modalEditar" class="modal-overlay fixed inset-0 hidden items-center justify-center z-50">
        <div class="modal-container bg-white w-11/12 md:max-w-3xl mx-auto rounded-lg shadow-lg z-50 overflow-y-auto">
            <div class="modal-header">
                <h3 class="text-xl font-semibold flex items-center gap-3">
                    <iconify-icon icon="lucide:edit"></iconify-icon>
                    Editar Producto
                </h3>
                <button class="modal-close-edit text-2xl font-bold">&times;</button>
            </div>
        
            <div class="modal-content p-6">
                <form id="formEditarProducto" class="space-y-6" enctype="multipart/form-data" novalidate>
                    <input type="hidden" id="edit-producto-id" name="producto_id">
        
                    <!-- Sección: Información Principal -->
                    <div class="form-section">
                        <h4 class="form-section-title">
                            <iconify-icon icon="heroicons:identification-solid"></iconify-icon>
                            Información Principal
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Producto</label>
                                <input type="text" name="nombre" id="edit-nombre" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                                <select name="categoria" id="edit-categoria" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="">Seleccionar</option>
                                    @if(isset($categorias))
                                        @foreach($categorias as $categoria)
                                            <option value="{{ $categoria->nombre }}">{{ $categoria->nombre }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                                <input type="text" name="marca" id="edit-marca" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Proveedor</label>
                                <select name="proveedor_id" id="edit-proveedor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">Seleccionar proveedor (opcional)</option>
                                    @if(isset($proveedores))
                                        @foreach($proveedores as $proveedor)
                                            <option value="{{ $proveedor->id }}">{{ $proveedor->razon_social }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
        
                    <!-- Sección: Detalles y Códigos -->
                    <div class="form-section">
                        <h4 class="form-section-title">
                            <iconify-icon icon="heroicons:document-text-solid"></iconify-icon>
                            Detalles y Códigos
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Presentación</label>
                                <select name="presentacion" id="edit-presentacion" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="">Seleccionar</option>
                                    @if(isset($presentaciones))
                                        @foreach($presentaciones as $presentacion)
                                            <option value="{{ $presentacion->nombre }}">{{ $presentacion->nombre }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Concentración</label>
                                <input type="text" name="concentracion" id="edit-concentracion" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Lote</label>
                                <input type="text" name="lote" id="edit-lote" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Código de Barras</label>
                                <input type="text" name="codigo_barras" id="edit-codigo_barras" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" maxlength="13" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección: Stock y Precios -->
                    <div class="form-section">
                        <h4 class="form-section-title">
                            <iconify-icon icon="heroicons:circle-stack-solid"></iconify-icon>
                            Stock y Precios
                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Stock Actual</label>
                                <input type="number" name="stock_actual" id="edit-stock_actual" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Stock Mínimo</label>
                                <input type="number" name="stock_minimo" id="edit-stock_minimo" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Precio Compra</label>
                                <input type="number" name="precio_compra" id="edit-precio_compra" min="0" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Precio Venta</label>
                                <input type="number" name="precio_venta" id="edit-precio_venta" min="0" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                        </div>
                    </div>
        
                    <!-- Sección: Fechas -->
                    <div class="form-section">
                        <h4 class="form-section-title">
                             <iconify-icon icon="heroicons:calendar-days-solid"></iconify-icon>
                            Fechas
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fabricación</label>
                                <input type="date" name="fecha_fabricacion" id="edit-fecha_fabricacion" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Vencimiento</label>
                                <input type="date" name="fecha_vencimiento" id="edit-fecha_vencimiento" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                        </div>
                    </div>
                    


                    
                     <!-- Sección: Imagen -->
                    <div class="form-section">
                        <h4 class="form-section-title">
                             <iconify-icon icon="heroicons:photo-solid"></iconify-icon>
                            Imagen del Producto
                        </h4>
                         <div class="image-upload-wrapper">
                             <input type="file" name="imagen" accept="image/*" id="edit-imagen-input">
                             <div class="upload-icon">
                                 <iconify-icon icon="heroicons:arrow-up-tray-solid"></iconify-icon>
                             </div>
                             <p class="upload-text">Haz clic para subir una nueva imagen</p>
                             <p class="upload-hint">PNG, JPG o GIF (Max. 2MB)</p>
                         </div>
                        <div id="edit-preview-container" class="mt-4 text-center" style="display: block;">
                            <img id="edit-preview-image" class="h-32 w-32 object-cover rounded-lg inline-block" src="" alt="Vista previa de la imagen">
                            <p class="text-sm text-gray-500 mt-2">Imagen actual</p>
                        </div>
                    </div>
        
                    <!-- Botones de acción -->
                    <div class="form-actions flex justify-end gap-4 pt-4">
                        <button type="button"
                                class="btn-cancel-edit px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors border border-gray-300">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="btn-save-edit px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2">
                            <iconify-icon icon="heroicons:check-circle-solid"></iconify-icon>
                            <span>Guardar Cambios</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <!-- Modal Agregar Producto -->
    <div id="modalAgregar" class="modal-overlay fixed inset-0 hidden items-center justify-center z-50">
        <div class="modal-container bg-white w-11/12 md:max-w-3xl mx-auto rounded-lg shadow-lg z-50 overflow-y-auto">
            <div class="modal-header">
                <h3 class="text-xl font-semibold flex items-center gap-3">
                    <iconify-icon icon="heroicons:plus-circle-solid"></iconify-icon>
                    Agregar Nuevo Producto
                </h3>
                <button class="modal-close text-2xl font-bold">&times;</button>
            </div>
        
            <div class="modal-content p-6">
                <form id="formAgregarProducto" class="space-y-6" enctype="multipart/form-data" novalidate>
                    @csrf
        
                    <!-- Sección: Información Principal -->
                    <div class="form-section">
                        <h4 class="form-section-title">
                            <iconify-icon icon="heroicons:identification-solid"></iconify-icon>
                            Información Principal
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Producto <span class="text-red-500">*</span></label>
                                <input type="text" name="nombre" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Ej: Paracetamol 500mg" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Categoría <span class="text-red-500">*</span></label>
                                <select name="categoria" id="add-categoria" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="">Seleccionar</option>
                                    @if(isset($categorias))
                                        @foreach($categorias as $categoria)
                                            <option value="{{ $categoria->nombre }}">{{ $categoria->nombre }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Marca <span class="text-red-500">*</span></label>
                                <input type="text" name="marca" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Ej: Genfar" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Proveedor <span class="text-red-500">*</span></label>
                                <select name="proveedor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="">Seleccionar proveedor</option>
                                    @if(isset($proveedores))
                                        @foreach($proveedores as $proveedor)
                                            <option value="{{ $proveedor->id }}">{{ $proveedor->razon_social }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
        
                    <!-- Sección: Detalles del Producto -->
                    <div class="form-section">
                        <h4 class="form-section-title">
                            <iconify-icon icon="heroicons:document-text-solid"></iconify-icon>
                            Detalles del Producto
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Presentación <span class="text-red-500">*</span></label>
                                <select name="presentacion" id="add-presentacion" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="">Seleccionar</option>
                                    @if(isset($presentaciones))
                                        @foreach($presentaciones as $presentacion)
                                            <option value="{{ $presentacion->nombre }}">{{ $presentacion->nombre }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Concentración <span class="text-red-500">*</span></label>
                                <input type="text" name="concentracion" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Ej: 500mg" required>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Código de Barras <span class="text-red-500">*</span></label>
                                <input type="text" name="codigo_barras" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" maxlength="13" required>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Información del Lote -->
                    <div class="form-section">
                        <h4 class="form-section-title">
                            <iconify-icon icon="heroicons:cube-solid"></iconify-icon>
                            Información del Lote
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Número de Lote <span class="text-red-500">*</span></label>
                                <input type="text" name="numero_lote" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Ej: LOT2024001" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Fabricación <span class="text-red-500">*</span></label>
                                <input type="date" name="fecha_fabricacion" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Vencimiento <span class="text-red-500">*</span></label>
                                <input type="date" name="fecha_vencimiento" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Temperatura de Almacenamiento</label>
                                <select name="temperatura_almacenamiento" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="ambiente">Temperatura Ambiente</option>
                                    <option value="refrigerado">Refrigerado (2-8°C)</option>
                                    <option value="congelado">Congelado (-18°C)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Registro Sanitario</label>
                                <input type="text" name="registro_sanitario" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Ej: EE-12345-20">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fabricante</label>
                                <input type="text" name="fabricante" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Ej: Laboratorios ABC">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">País de Origen</label>
                                <input type="text" name="pais_origen" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Ej: Perú">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                                <textarea name="observaciones" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Observaciones adicionales del lote"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección: Stock y Precios del Lote -->
                    <div class="form-section">
                        <h4 class="form-section-title">
                            <iconify-icon icon="heroicons:circle-stack-solid"></iconify-icon>
                            Stock y Precios del Lote
                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad Inicial <span class="text-red-500">*</span></label>
                                <input type="number" name="cantidad_inicial" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                <p class="text-xs text-gray-500 mt-1">Cantidad que ingresa al inventario</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Stock Mínimo (Producto) <span class="text-red-500">*</span></label>
                                <input type="number" name="stock_minimo" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                <p class="text-xs text-gray-500 mt-1">Alerta cuando el total esté por debajo</p>
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Precio Compra <span class="text-red-500">*</span></label>
                                <input type="number" name="precio_compra" min="0" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                <p class="text-xs text-gray-500 mt-1">Precio de compra de este lote</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Precio Venta <span class="text-red-500">*</span></label>
                                <input type="number" name="precio_venta" min="0" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                <p class="text-xs text-gray-500 mt-1">Precio de venta al público</p>
                            </div>
                        </div>
                    </div>
                    
                     <!-- Sección: Imagen -->
                    <div class="form-section">
                        <h4 class="form-section-title">
                             <iconify-icon icon="heroicons:photo-solid"></iconify-icon>
                            Imagen del Producto
                        </h4>
                         <div class="image-upload-wrapper">
                             <input type="file" name="imagen" accept="image/*" id="imagen-input">
                             <p class="upload-text">Haz clic para subir una imagen</p>
                             <p class="upload-hint">PNG, JPG o GIF (Max. 2MB)</p>
                         </div>
                        <div id="preview-container" class="mt-4 hidden text-center">
                            <img id="preview-image" class="h-32 w-32 object-cover rounded-lg inline-block" src="" alt="Vista previa de la imagen">
                        </div>
                    </div>
        
                    <!-- Botones de acción -->
                    <div class="form-actions flex justify-end gap-4 pt-4">
                        <button type="button"
                                class="btn-cancel px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors border border-gray-300">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="btn-save px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            Guardar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    


    <div class="grid grid-cols-12">
        <div class="col-span-12">
            <div class="card border-0 overflow-hidden">
                <div class="card-header">
                    <h6 class="card-title mb-0 text-lg">Lista de Productos</h6>
                </div>
                <div class="card-body">
                    <!-- Barra de herramientas -->
                    <div class="filtros-bar">
                        <form method="GET" action="{{ route('inventario.productos') }}" id="filtrosForm" style="display: none;">
                            <input type="hidden" name="search" id="hiddenSearch" value="{{ $search }}">
                            <input type="hidden" name="estado" id="hiddenEstado" value="{{ $estado }}">
                            <input type="hidden" name="per_page" id="hiddenPerPage" value="{{ $perPage }}">
                        </form>
                        
                        <input type="search" 
                            id="searchInput" 
                            placeholder="Buscar..." 
                            value="{{ $search }}"
                            class="input-buscar" />
                        <label for="filterEstado">Estado:</label>
                        <select id="filterEstado" class="select-estado">
                            <option value="todos" {{ $estado == 'todos' ? 'selected' : '' }}>Todos</option>
                            <option value="Normal" {{ $estado == 'Normal' ? 'selected' : '' }}>Normal</option>
                            <option value="Bajo stock" {{ $estado == 'Bajo stock' ? 'selected' : '' }}>Bajo stock</option>
                            <option value="Por vencer" {{ $estado == 'Por vencer' ? 'selected' : '' }}>Por Vencer</option>
                            <option value="Vencido" {{ $estado == 'Vencido' ? 'selected' : '' }}>Vencido</option>
                            <option value="Agotado" {{ $estado == 'Agotado' ? 'selected' : '' }}>Agotado</option>
                        </select>
                        <label for="perPageSelect">Mostrar:</label>
                        <select id="perPageSelect" class="select-estado">
                            <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <div style="flex:1"></div>
                        <button id="btnExcel" class="btn-excel">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Excel
                        </button>
                        <button id="btnPDF" class="btn-pdf">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            PDF
                        </button>
                        <button id="btnAgregarProducto" class="btn-agregar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Agregar Producto
                        </button>
                    </div>

                      
                    @if ($productos->isEmpty())
                        <div class="text-center py-8">
                            <div class="flex flex-col items-center justify-center p-6">
                                <h3 class="text-xl font-medium text-gray-800 mt-4">No se encontraron productos</h3>
                                <p class="text-base text-gray-500 text-center mt-2">
                                    Aún no hay productos registrados. ¡Haz clic en "Agregar Producto" para comenzar!
                                </p>
                            </div>
                        </div>
                    @else
                        <table id="selection-table" class="table-productos min-w-full divide-y divide-gray-200">
                            <thead class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500">
                                    <tr>
                                    <th scope="col" class="text-neutral-800">
                                        <div class="flex items-center gap-2 justify-center">
                                            ID
                                            <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4"/>
                                            </svg>
                                        </div>
                                    </th>
                                    <th scope="col" class="text-neutral-800">
                                        <div class="flex items-center gap-2">
                                            Producto
                                            <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4"/>
                                            </svg>
                                        </div>
                                    </th>
                                    <th scope="col" class="text-neutral-800">
                                        <div class="flex items-center gap-2">
                                            Fecha Vencimiento
                                            <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4"/>
                                            </svg>
                                        </div>
                                    </th>
                                    <th scope="col" class="text-neutral-800">
                                        <div class="flex items-center gap-2">
                                            Precio
                                            <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4"/>
                                            </svg>
                                        </div>
                                    </th>
                                    <th scope="col" class="text-neutral-800">
                                        <div class="flex items-center gap-2">
                                            Stock
                                            <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4"/>
                                            </svg>
                                        </div>
                                    </th>
                                    <th scope="col" class="text-neutral-800">
                                        <div class="flex items-center gap-2">
                                            Ubicación Almacén
                                            <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4"/>
                                            </svg>
                                        </div>
                                    </th>
                                    <th scope="col" class="text-neutral-800">
                                        <div class="flex items-center gap-2">
                                            Estado
                                            <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4"/>
                                            </svg>
                                        </div>
                                    </th>
                                    <th scope="col" class="text-neutral-800">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-300">
                                @foreach($productos as $index => $producto)
                                @php
                                    // Usar el estado de la base de datos
                                    $estado = $producto->estado ?? 'Normal';
                                    $estadoClass = '';
                                    
                                    // Asignar clases CSS según el estado
                                    switch($estado) {
                                        case 'Vencido':
                                            $estadoClass = 'estado-vencido';
                                            break;
                                        case 'Por vencer':
                                            $estadoClass = 'estado-por-vencer';
                                            break;
                                        case 'Bajo stock':
                                            $estadoClass = 'estado-bajo-stock';
                                            break;
                                        case 'Agotado':
                                            $estadoClass = 'estado-agotado';
                                            break;
                                        default:
                                            $estadoClass = 'estado-normal';
                                            break;
                                    }
                                @endphp
                                <tr data-id="{{ $producto->id }}">
                                    <td class="text-xs text-center">{{ ($productos->currentPage() - 1) * $productos->perPage() + $index + 1 }}</td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $producto->imagen_url }}" 
                                                alt="{{ $producto->nombre }}"
                                                class="w-10 h-10 rounded-lg object-cover shadow-sm border border-gray-200 bg-white"
                                                style="padding: 2px; margin-right: 8px;"
                                                onerror="this.onerror=null; this.src='{{ asset('assets/images/default-product.svg') }}';">
                                            <div>
                                                <h6 class="text-base font-semibold text-gray-800 leading-tight">{{ $producto->nombre }}</h6>
                                                <span class="text-secondary">{{ $producto->concentracion }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center font-medium" style="color: #333;">{{ $producto->fecha_vencimiento_solo_fecha ?? 'N/A' }}</td>
                                    <td class="text-center font-semibold" style="color: #333;">S/ {{ number_format($producto->precio_venta, 2) }}</td>
                                    <td class="text-center">
                                        <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                            @if($producto->stock_actual <= $producto->stock_minimo) 
                                                bg-red-100 text-red-800 border border-red-200
                                            @elseif($producto->stock_actual <= ($producto->stock_minimo * 2))
                                                bg-yellow-100 text-yellow-800 border border-yellow-200
                                            @else 
                                                bg-green-100 text-green-800 border border-green-200
                                            @endif">
                                            {{ $producto->stock_actual ?? 0 }} unidades
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($producto->total_ubicaciones > 1)
                                            <!-- Múltiples ubicaciones -->
                                            <div class="ubicacion-badge multiple" onclick="mostrarDetallesUbicaciones({{ $producto->id }}, '{{ $producto->nombre }}', {{ json_encode($producto->ubicaciones_detalle) }})">
                                                <div class="ubicacion-icon">
                                                    <iconify-icon icon="solar:buildings-2-bold-duotone"></iconify-icon>
                                                </div>
                                                <div class="ubicacion-info">
                                                    <span class="ubicacion-texto">{{ $producto->total_ubicaciones }} Ubicaciones</span>
                                                    <span class="ubicacion-subtexto">Ver detalles</span>
                                                </div>
                                            </div>
                                        @elseif($producto->total_ubicaciones == 1 && !$producto->tiene_stock_sin_ubicar)
                                            <!-- Una sola ubicación, todo el stock ubicado -->
                                            <div class="ubicacion-badge ubicado" onclick="mostrarDetallesUbicaciones({{ $producto->id }}, '{{ $producto->nombre }}', {{ json_encode($producto->ubicaciones_detalle) }})">
                                                <div class="ubicacion-icon">
                                                    <iconify-icon icon="solar:map-point-bold-duotone"></iconify-icon>
                                                </div>
                                                <div class="ubicacion-info">
                                                    <span class="ubicacion-texto">{{ $producto->ubicaciones_detalle->first()['ubicacion_completa'] ?? 'Ubicado' }}</span>
                                                </div>
                                            </div>
                                        @elseif($producto->total_ubicaciones >= 1 && $producto->tiene_stock_sin_ubicar)
                                            <!-- Parcialmente ubicado -->
                                            <div class="ubicacion-badge multiple" onclick="mostrarDetallesUbicaciones({{ $producto->id }}, '{{ $producto->nombre }}', {{ json_encode($producto->ubicaciones_detalle) }})">
                                                <div class="ubicacion-icon">
                                                    <iconify-icon icon="solar:box-minimalistic-bold-duotone"></iconify-icon>
                                                </div>
                                                <div class="ubicacion-info">
                                                    <span class="ubicacion-texto">Parcialmente ubicado</span>
                                                    <span class="ubicacion-subtexto">{{ $producto->stock_sin_ubicar }} sin ubicar</span>
                                                </div>
                                            </div>
                                        @else
                                            <!-- Sin ubicaciones -->
                                            <div class="ubicacion-badge sin-ubicar">
                                                <div class="ubicacion-icon">
                                                    <iconify-icon icon="solar:question-circle-bold-duotone"></iconify-icon>
                                                </div>
                                                <div class="ubicacion-info">
                                                    <span class="ubicacion-texto">Sin ubicar</span>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="estado-badge {{ $estadoClass }}">
                                            {{ $estado }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="javascript:void(0)" 
                                        onclick="abrirModalDetalles({{ $producto->id }})"
                                        class="btn-view w-8 h-8 bg-primary-50 text-primary-600 rounded-full inline-flex items-center justify-center"
                                        title="Ver detalles">
                                            <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                                        </a>
                                        <a href="javascript:void(0)" 
                                        onclick="abrirModalEdicion({{ $producto->id }})"
                                        class="btn-edit w-8 h-8 bg-success-100 text-success-600 rounded-full inline-flex items-center justify-center"
                                        title="Editar producto">
                                            <iconify-icon icon="lucide:edit"></iconify-icon>
                                        </a>
                                        <a href="javascript:void(0)" 
                                            onclick="eliminarProducto({{ $producto->id }})"
                                            class="w-8 h-8 bg-danger-100 text-danger-600 rounded-full inline-flex items-center justify-center"
                                            title="Eliminar producto">
                                            <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                                                <!-- Paginación Mejorada -->
                        @if($productos->hasPages())
                        <div class="historial-pagination-improved" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-top: 1px solid #e5e7eb; background: white;">
                            <!-- Información de paginación -->
                            <div class="historial-pagination-info">
                                <p class="text-sm text-gray-700">
                                    Mostrando 
                                    <span class="font-medium">{{ $productos->firstItem() }}</span>
                                    a 
                                    <span class="font-medium">{{ $productos->lastItem() }}</span>
                                    de 
                                    <span class="font-medium">{{ $productos->total() }}</span>
                                    productos
                                </p>
                            </div>
                            
                            <!-- Controles de paginación -->
                            <div class="historial-pagination-controls" style="display: flex; gap: 4px;">
                                {{-- Botón Primera página --}}
                                @if ($productos->onFirstPage())
                                    <span class="pagination-btn disabled" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; color: #9ca3af; background: #f9fafb; cursor: not-allowed; font-size: 14px;">
                                        Primera
                                    </span>
                                @else
                                    <a href="{{ $productos->url(1) }}" class="pagination-btn" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; color: #374151; background: white; text-decoration: none; font-size: 14px; transition: all 0.2s;">
                                        Primera
                                    </a>
                                @endif
                                
                                {{-- Botón Anterior --}}
                                @if ($productos->onFirstPage())
                                    <span class="pagination-btn disabled" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; color: #9ca3af; background: #f9fafb; cursor: not-allowed; font-size: 14px;">
                                        ‹ Anterior
                                    </span>
                                @else
                                    <a href="{{ $productos->previousPageUrl() }}" class="pagination-btn" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; color: #374151; background: white; text-decoration: none; font-size: 14px; transition: all 0.2s;">
                                        ‹ Anterior
                                    </a>
                                @endif
                                
                                {{-- Números de página --}}
                                @foreach ($productos->getUrlRange(max(1, $productos->currentPage() - 2), min($productos->lastPage(), $productos->currentPage() + 2)) as $page => $url)
                                    @if ($page == $productos->currentPage())
                                        <span class="pagination-btn current" style="padding: 8px 12px; border: 1px solid #6366f1; border-radius: 6px; color: white; background: #6366f1; font-weight: 600; font-size: 14px;">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <a href="{{ $url }}" class="pagination-btn" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; color: #374151; background: white; text-decoration: none; font-size: 14px; transition: all 0.2s;">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endforeach
                                
                                {{-- Botón Siguiente --}}
                                @if ($productos->hasMorePages())
                                    <a href="{{ $productos->nextPageUrl() }}" class="pagination-btn" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; color: #374151; background: white; text-decoration: none; font-size: 14px; transition: all 0.2s;">
                                        Siguiente ›
                                    </a>
                                @else
                                    <span class="pagination-btn disabled" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; color: #9ca3af; background: #f9fafb; cursor: not-allowed; font-size: 14px;">
                                        Siguiente ›
                                    </span>
                                @endif
                                
                                {{-- Botón Última página --}}
                                @if ($productos->hasMorePages())
                                    <a href="{{ $productos->url($productos->lastPage()) }}" class="pagination-btn" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; color: #374151; background: white; text-decoration: none; font-size: 14px; transition: all 0.2s;">
                                        Última
                                    </a>
                                @else
                                    <span class="pagination-btn disabled" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; color: #9ca3af; background: #f9fafb; cursor: not-allowed; font-size: 14px;">
                                        Última
                                    </span>
                                @endif
                            </div>
                        </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection

<!-- JavaScript inline para garantizar que las funciones estén disponibles -->
<script>
// Función global para verificar si SweetAlert2 está disponible
function verificarSwal() {
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 no está cargado');
        return false;
    }
    return true;
}

// Función eliminarProducto se carga desde eliminar.js

// Función auxiliar para productos
function obtenerProductoDeLaFila(row) {
  return {
    id: row.getAttribute('data-id'),
    nombre: row.querySelector('td:nth-child(2) h6')?.textContent.trim() || '',
    categoria: row.querySelector('td:nth-child(3)')?.textContent.trim() || '',
    marca: '',
    presentacion: '',
    concentracion: row.querySelector('td:nth-child(2) span')?.textContent.trim() || '',
    lote: '',
    codigo_barras: '',
    stock_actual: '',
    stock_minimo: '',
    precio_compra: '',
    precio_venta: '',
    fecha_fabricacion: '',
    fecha_vencimiento: row.querySelector('td:nth-child(4)')?.textContent.trim() || '',
    ubicacion: '',
    imagen: ''
  };
}

// Función auxiliar para renderizado
function renderCategorias(categorias) {
    let html = '';
    categorias.forEach(cat => {
        html += `
            <tr>
                <td>${cat.id}</td>
                <td>${cat.nombre}</td>
                <td>${cat.descripcion || ''}</td>
                <td>${cat.productos_count || 0}</td>
                <td><!-- Acciones --></td>
            </tr>
        `;
    });
    const tbody = document.getElementById('categorias-tbody');
    if (tbody) {
        tbody.innerHTML = html;
}
}

// Función global para cerrar modal de detalles
function cerrarModalDetalles() {
    const modal = document.getElementById('modalDetalles');
    if (modal) {
        modal.classList.add('hidden');
        modal.style.display = 'none';
    }
}

// Función global para abrir modal de detalles
async function abrirModalDetalles(productoId) {
    try {
        console.log('🔍 Abriendo detalles del producto:', productoId);
        
        // Mostrar modal con loading
        const modal = document.getElementById('modalDetalles');
        if (!modal) {
            console.error('❌ Modal de detalles no encontrado');
            return;
        }
        
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        
        // Mostrar loading en el contenido
        const lotesContainer = document.getElementById('modal-lotes-container');
        if (lotesContainer) {
            lotesContainer.innerHTML = `
                <div class="text-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto mb-4"></div>
                    <p class="text-gray-500">Cargando información de lotes...</p>
                </div>
            `;
        } else {
            console.error('❌ Contenedor de lotes no encontrado');
        }
        
        // Construir URL de la API
        const apiUrl = `/api/productos/${productoId}/detalles`;
        console.log('🌐 URL de la API:', apiUrl);
        
        // Obtener datos del producto desde la API
        console.log('📡 Realizando petición a la API...');
        const response = await fetch(apiUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        });
        
        console.log('📡 Respuesta recibida:', response.status, response.statusText);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('❌ Error en la respuesta:', errorText);
            throw new Error(`Error ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        console.log('📦 Datos completos recibidos:', data);
        
        if (!data.success) {
            throw new Error(data.message || 'Error en la respuesta de la API');
        }
        
        const producto = data.producto;
        const lotes = data.lotes || [];
        
        console.log('📦 Datos del producto:', producto);
        console.log('📋 Lotes encontrados:', lotes.length, 'lotes');
        
        // Llenar datos básicos del producto
        document.getElementById('modal-id').textContent = producto.id || 'N/A';
        document.getElementById('modal-nombre').textContent = producto.nombre || 'Sin nombre';
        document.getElementById('modal-concentracion').textContent = producto.concentracion || 'Sin concentración';
        document.getElementById('modal-categoria').textContent = producto.categoria || 'Sin categoría';
        document.getElementById('modal-marca').textContent = producto.marca || 'Sin marca';
        document.getElementById('modal-proveedor').textContent = producto.proveedor || 'Sin proveedor';
        document.getElementById('modal-presentacion').textContent = producto.presentacion || 'Sin presentación';
        document.getElementById('modal-codigo-barras').textContent = producto.codigo_barras || 'Sin código';
        document.getElementById('modal-lote').textContent = producto.lote || 'Sin lote';
        document.getElementById('modal-stock').textContent = producto.stock_actual || '0';
        document.getElementById('modal-stock-min').textContent = producto.stock_minimo || '0';
        document.getElementById('modal-precio-compra').textContent = `S/ ${producto.precio_compra || '0.00'}`;
        document.getElementById('modal-precio-venta').textContent = `S/ ${producto.precio_venta || '0.00'}`;
        document.getElementById('modal-fecha-fab').textContent = producto.fecha_fabricacion || 'N/A';
        document.getElementById('modal-fecha-ven').textContent = producto.fecha_vencimiento || 'N/A';
        document.getElementById('modal-ubicacion').textContent = producto.ubicacion || 'Sin ubicación';
        
        // Estado del producto
        const estadoElement = document.getElementById('modal-estado');
        if (estadoElement) {
            estadoElement.textContent = producto.estado || 'Normal';
            estadoElement.className = `detail-value-lg text-xl estado-${(producto.estado || 'normal').toLowerCase().replace(' ', '-')}`;
        }
        
        // Imagen del producto
        const imagenElement = document.getElementById('modal-imagen');
        if (imagenElement) {
            imagenElement.src = producto.imagen || '/assets/images/default-product.png';
            imagenElement.alt = producto.nombre || 'Producto';
        }
        
        // Mostrar información de lotes
        mostrarLotesEnModal(lotes);
        
    } catch (error) {
        console.error('❌ Error al cargar detalles del producto:', error);
        
        // Mostrar error en el modal de lotes
        const lotesContainer = document.getElementById('modal-lotes-container');
        if (lotesContainer) {
            lotesContainer.innerHTML = `
                <div class="text-center py-8">
                    <iconify-icon icon="heroicons:exclamation-triangle" class="text-4xl text-red-400 mb-2"></iconify-icon>
                    <p class="text-red-500">Error al cargar la información de lotes</p>
                    <p class="text-gray-400 text-sm">Intenta nuevamente más tarde</p>
                </div>
            `;
        }
        
        // Mostrar notificación de error
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron cargar los detalles del producto',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        }
    }
}

// Función para mostrar lotes en el modal
function mostrarLotesEnModal(lotes) {
    const lotesContainer = document.getElementById('modal-lotes-container');
    if (!lotesContainer) return;
    
    if (!lotes || lotes.length === 0) {
        lotesContainer.innerHTML = `
            <div class="text-center py-8">
                <iconify-icon icon="solar:box-linear" class="text-4xl text-gray-400 mb-2"></iconify-icon>
                <p class="text-gray-500">No hay lotes registrados para este producto</p>
                <p class="text-gray-400 text-sm">Los lotes se crean automáticamente al registrar entradas de mercadería</p>
            </div>
        `;
        return;
    }
    
    let html = `
        <div class="mb-4 p-4 bg-indigo-50 rounded-lg border border-indigo-200">
            <div class="flex items-center gap-2 mb-2">
                <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="text-indigo-600 text-xl"></iconify-icon>
                <h5 class="font-semibold text-indigo-800">Resumen de Lotes</h5>
            </div>
            <div class="grid grid-cols-3 gap-4 text-sm">
                <div class="text-center">
                    <div class="font-bold text-lg text-indigo-600">${lotes.length}</div>
                    <div class="text-gray-600">Lotes totales</div>
                </div>
                <div class="text-center">
                    <div class="font-bold text-lg text-green-600">${lotes.reduce((sum, lote) => sum + (parseInt(lote.stock_actual) || 0), 0)}</div>
                    <div class="text-gray-600">Stock total</div>
                </div>
                <div class="text-center">
                    <div class="font-bold text-lg text-orange-600">${lotes.filter(lote => {
                        if (!lote.fecha_vencimiento) return false;
                        const fechaVenc = new Date(lote.fecha_vencimiento);
                        const hoy = new Date();
                        const diasParaVencer = Math.ceil((fechaVenc - hoy) / (1000 * 60 * 60 * 24));
                        return diasParaVencer <= 30 && diasParaVencer > 0;
                    }).length}</div>
                    <div class="text-gray-600">Por vencer</div>
                </div>
            </div>
        </div>
        
        <div class="space-y-3">
    `;
    
    // Ordenar lotes por fecha de vencimiento (más próximos primero, nulos al final)
    lotes.sort((a, b) => {
        if (!a.fecha_vencimiento && !b.fecha_vencimiento) return 0;
        if (!a.fecha_vencimiento) return 1;
        if (!b.fecha_vencimiento) return -1;
        return new Date(a.fecha_vencimiento) - new Date(b.fecha_vencimiento);
    });
    
    lotes.forEach((lote, index) => {
        const fechaVenc = lote.fecha_vencimiento ? new Date(lote.fecha_vencimiento) : null;
        const fechaFab = lote.fecha_ingreso ? new Date(lote.fecha_ingreso) : null;
        const hoy = new Date();
        const diasParaVencer = fechaVenc ? Math.ceil((fechaVenc - hoy) / (1000 * 60 * 60 * 24)) : null;
        
        // Determinar estado del lote
        let estadoLote = 'normal';
        let estadoTexto = 'Normal';
        let estadoColor = 'bg-green-100 text-green-800';
        
        if (fechaVenc && fechaVenc < hoy) {
            estadoLote = 'vencido';
            estadoTexto = 'Vencido';
            estadoColor = 'bg-red-100 text-red-800';
        } else if (fechaVenc && diasParaVencer <= 30) {
            estadoLote = 'por-vencer';
            estadoTexto = 'Por vencer';
            estadoColor = 'bg-orange-100 text-orange-800';
        } else if (parseInt(lote.stock_actual) <= 5) {
            estadoLote = 'bajo-stock';
            estadoTexto = 'Bajo stock';
            estadoColor = 'bg-yellow-100 text-yellow-800';
        }
        
        html += `
            <div class="lote-card p-4 border rounded-lg ${estadoLote === 'vencido' ? 'border-red-200 bg-red-50' : estadoLote === 'por-vencer' ? 'border-orange-200 bg-orange-50' : 'border-gray-200 bg-white'} hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex items-center gap-2">
                        <iconify-icon icon="solar:tag-bold-duotone" class="text-indigo-600"></iconify-icon>
                        <h6 class="font-semibold text-gray-800">Lote: ${lote.lote || 'Sin lote'}</h6>
                    </div>
                    <span class="px-2 py-1 rounded-full text-xs font-medium ${estadoColor}">
                        ${estadoTexto}
                    </span>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <div class="text-gray-500 mb-1">Stock Actual</div>
                        <div class="font-semibold text-lg ${parseInt(lote.stock_actual) > 0 ? 'text-green-600' : 'text-red-600'}">
                            ${lote.stock_actual || 0} unidades
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-500 mb-1">Fecha Ingreso</div>
                        <div class="font-medium">
                            ${fechaFab ? fechaFab.toLocaleDateString('es-ES') : 'N/A'}
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-500 mb-1">Vencimiento</div>
                        <div class="font-medium ${estadoLote === 'vencido' ? 'text-red-600' : estadoLote === 'por-vencer' ? 'text-orange-600' : 'text-gray-800'}">
                            ${fechaVenc ? fechaVenc.toLocaleDateString('es-ES') : 'Sin fecha'}
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-500 mb-1">Días restantes</div>
                        <div class="font-medium ${diasParaVencer !== null && diasParaVencer < 0 ? 'text-red-600' : diasParaVencer !== null && diasParaVencer <= 30 ? 'text-orange-600' : 'text-green-600'}">
                            ${diasParaVencer === null ? 'Sin fecha' : diasParaVencer < 0 ? 'Vencido' : `${diasParaVencer} días`}
                        </div>
                    </div>
                </div>
                
                ${lote.ubicacion ? `
                    <div class="mt-3 pt-3 border-t border-gray-200">
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <iconify-icon icon="solar:home-2-bold-duotone" class="text-indigo-500"></iconify-icon>
                            <span>Ubicación: ${lote.ubicacion}</span>
                        </div>
                    </div>
                ` : ''}
            </div>
        `;
    });
    
    html += '</div>';
    lotesContainer.innerHTML = html;
}

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado - Inicializando funciones de productos');
    
    // Verificar que SweetAlert2 esté cargado
    setTimeout(() => {
        if (typeof Swal !== 'undefined') {
            console.log('✓ SweetAlert2 cargado correctamente');
        } else {
            console.error('✗ SweetAlert2 no está disponible');
        }
    }, 100);
    
    // Event listeners para modal de detalles
    const modalDetalles = document.getElementById('modalDetalles');
    if (modalDetalles) {
        // Click fuera del modal para cerrar
        modalDetalles.addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalDetalles();
            }
        });
        
        // Escape key para cerrar
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modalDetalles.classList.contains('hidden')) {
                cerrarModalDetalles();
            }
        });
    }
    
    // Función para actualizar y enviar el formulario con AJAX
    function updateAndSubmitForm() {
        loadProducts();
    }
    
    // Función para cargar productos con AJAX
    async function loadProducts() {
        try {
            const searchInput = document.getElementById('searchInput');
            const filterEstado = document.getElementById('filterEstado');
            const perPageSelect = document.getElementById('perPageSelect');
            
            const search = searchInput ? searchInput.value : '';
            const estado = filterEstado ? filterEstado.value : 'todos';
            const perPage = perPageSelect ? perPageSelect.value : 10;
            
            // Mostrar loading
            const tableContainer = document.querySelector('.card-body');
            if (tableContainer) {
                tableContainer.innerHTML = `
                    <div class="flex justify-center items-center py-12">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                        <span class="ml-2 text-gray-600">Cargando productos...</span>
                    </div>
                `;
            }
            
            // Construir URL con parámetros
            const url = new URL('{{ route("inventario.productos.ajax") }}', window.location.origin);
            url.searchParams.append('search', search);
            url.searchParams.append('estado', estado);
            url.searchParams.append('per_page', perPage);
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            
            const data = await response.json();
            
            // Renderizar la tabla con los nuevos datos
            renderProductTable(data, search, estado, perPage);
            
        } catch (error) {
            console.error('Error al cargar productos:', error);
            const tableContainer = document.querySelector('.card-body');
            if (tableContainer) {
                tableContainer.innerHTML = `
                    <div class="text-center py-12">
                        <div class="flex flex-col items-center justify-center">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                                <svg class="h-8 w-8 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Error al cargar productos</h3>
                            <p class="text-sm text-gray-500 mb-4">Hubo un problema al cargar los productos. Por favor, intenta nuevamente.</p>
                            <button onclick="loadProducts()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Reintentar
                            </button>
                        </div>
                    </div>
                `;
            }
        }
    }
    

    
    // Los event listeners se configuran en setupFilterEventListeners()
    // para evitar duplicación cuando se re-renderiza la tabla
     

     
     // Función para renderizar la tabla de productos
     function renderProductTable(productos, search, estado, perPage) {
         const tableContainer = document.querySelector('.card-body');
         if (!tableContainer) return;
         
         // Actualizar solo los valores de los controles existentes sin recrearlos
         const existingSearchInput = document.getElementById('searchInput');
         const existingFilterEstado = document.getElementById('filterEstado');
         const existingPerPageSelect = document.getElementById('perPageSelect');
         
         if (existingSearchInput) {
             existingSearchInput.value = search;
         }
         if (existingFilterEstado) {
             existingFilterEstado.value = estado;
         }
         if (existingPerPageSelect) {
             existingPerPageSelect.value = perPage;
         }
         
         // Actualizar URL sin recargar la página
         updateURL(search, estado, perPage, productos.current_page || 1);
         
         let html = `
             <!-- Barra de herramientas -->
             <div class="filtros-bar">
                 <form method="GET" action="{{ route('inventario.productos') }}" id="filtrosForm" style="display: none;">
                     <input type="hidden" name="search" id="hiddenSearch" value="${search}">
                     <input type="hidden" name="estado" id="hiddenEstado" value="${estado}">
                     <input type="hidden" name="per_page" id="hiddenPerPage" value="${perPage}">
                 </form>
                 
                 <input type="search" 
                     id="searchInput" 
                     placeholder="Buscar..." 
                     value="${search}"
                     class="input-buscar" />
                 <label for="filterEstado">Estado:</label>
                 <select id="filterEstado" class="select-estado">
                     <option value="todos" ${estado === 'todos' ? 'selected' : ''}>Todos</option>
                     <option value="Normal" ${estado === 'Normal' ? 'selected' : ''}>Normal</option>
                     <option value="Bajo stock" ${estado === 'Bajo stock' ? 'selected' : ''}>Bajo stock</option>
                     <option value="Por vencer" ${estado === 'Por vencer' ? 'selected' : ''}>Por Vencer</option>
                     <option value="Vencido" ${estado === 'Vencido' ? 'selected' : ''}>Vencido</option>
                     <option value="Agotado" ${estado === 'Agotado' ? 'selected' : ''}>Agotado</option>
                 </select>
                 <label for="perPageSelect">Mostrar:</label>
                 <select id="perPageSelect" class="select-estado">
                     <option value="5" ${perPage == 5 ? 'selected' : ''}>5 productos</option>
                     <option value="10" ${perPage == 10 ? 'selected' : ''}>10 productos</option>
                     <option value="25" ${perPage == 25 ? 'selected' : ''}>25 productos</option>
                     <option value="50" ${perPage == 50 ? 'selected' : ''}>50 productos</option>
                     <option value="100" ${perPage == 100 ? 'selected' : ''}>100 productos</option>
                 </select>
                 <div style="flex:1"></div>
                 <button id="btnExcel" class="btn-excel">
                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                     </svg>
                     Excel
                 </button>
                 <button id="btnPDF" class="btn-pdf">
                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                     </svg>
                     PDF
                 </button>
                 <button id="btnAgregarProducto" class="btn-agregar">
                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                     </svg>
                     Agregar Producto
                 </button>
             </div>
         `;
         
         if (productos.data.length === 0) {
             html += `
                 <div class="text-center py-12">
                     <div class="flex flex-col items-center justify-center">
                         <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 mb-4">
                             <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                             </svg>
                         </div>
                         <h3 class="text-lg font-medium text-gray-900 mb-2">
                             ${search ? `No se encontraron productos para "${search}"` : 'No hay productos registrados'}
                         </h3>
                         <p class="text-sm text-gray-500 max-w-sm mx-auto">
                             ${search ? 'Intenta con otros términos de búsqueda o verifica la ortografía.' : 'Aún no hay productos en el inventario. ¡Haz clic en "Agregar Producto" para comenzar!'}
                         </p>
                         ${search ? `
                             <button onclick="limpiarBusqueda()" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                 <svg class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                 </svg>
                                 Limpiar búsqueda
                             </button>
                         ` : ''}
                     </div>
                 </div>
             `;
         } else {
             html += `
                 <table id="selection-table" class="table-productos min-w-full divide-y divide-gray-200">
                     <thead class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500">
                         <tr>
                             <th scope="col" class="text-neutral-800">
                                 <div class="flex items-center gap-2 justify-center">
                                     ID
                                     <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                         <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4"/>
                                     </svg>
                                 </div>
                             </th>
                             <th scope="col" class="text-neutral-800">
                                 <div class="flex items-center gap-2">
                                     Producto
                                     <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                         <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4"/>
                                     </svg>
                                 </div>
                             </th>
                             <th scope="col" class="text-neutral-800">
                                 <div class="flex items-center gap-2">
                                     Fecha Vencimiento
                                     <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                         <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4"/>
                                     </svg>
                                 </div>
                             </th>
                             <th scope="col" class="text-neutral-800">
                                 <div class="flex items-center gap-2">
                                     Precio
                                     <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                         <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4"/>
                                     </svg>
                                 </div>
                             </th>
                             <th scope="col" class="text-neutral-800">
                                 <div class="flex items-center gap-2">
                                     Stock
                                     <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                         <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4"/>
                                     </svg>
                                 </div>
                             </th>
                             <th scope="col" class="text-neutral-800">
                                 <div class="flex items-center gap-2">
                                     Ubicación Almacén
                                     <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                         <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4"/>
                                     </svg>
                                 </div>
                             </th>
                             <th scope="col" class="text-neutral-800">
                                 <div class="flex items-center gap-2">
                                     Estado
                                     <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                         <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4"/>
                                     </svg>
                                 </div>
                             </th>
                             <th scope="col" class="text-neutral-800">Acción</th>
                         </tr>
                     </thead>
                     <tbody class="bg-white divide-y divide-gray-300">
             `;
             
             productos.data.forEach((producto, index) => {
                 const numeroFila = (productos.current_page - 1) * productos.per_page + index + 1;
                 
                 // Usar el estado que viene de la base de datos
                 const estado = producto.estado || 'Normal';
                 let estadoClass = '';
                 
                 // Asignar clase CSS según el estado
                 switch(estado) {
                     case 'Vencido':
                         estadoClass = 'estado-vencido';
                         break;
                     case 'Por vencer':
                         estadoClass = 'estado-por-vencer';
                         break;
                     case 'Bajo stock':
                         estadoClass = 'estado-bajo-stock';
                         break;
                     case 'Agotado':
                         estadoClass = 'estado-agotado';
                         break;
                     case 'Normal':
                     default:
                         estadoClass = 'estado-normal';
                         break;
                 }
                 
                 const fechaVencFormateada = producto.fecha_vencimiento ? new Date(producto.fecha_vencimiento).toLocaleDateString('es-ES') : 'N/A';
                 const defaultImageUrl = '{{ asset('assets/images/default-product.svg') }}';
                 const imagenUrl = producto.imagen_url || defaultImageUrl;
                 
                 html += `
                     <tr data-id="${producto.id}">
                         <td class="text-xs text-center">${numeroFila}</td>
                         <td>
                             <div class="flex items-center gap-3">
                                 <img src="${imagenUrl}" 
                                     alt="${producto.nombre}"
                                     class="w-10 h-10 rounded-lg object-cover shadow-sm border border-gray-200 bg-white"
                                     style="padding: 2px; margin-right: 8px;"
                                     onerror="this.onerror=null; this.src='${defaultImageUrl}';"> 
                                 <div>
                                     <h6 class="text-base font-semibold text-gray-800 leading-tight">${producto.nombre}</h6>
                                     <span class="text-secondary">${producto.concentracion || ''}</span>
                                 </div>
                             </div>
                         </td>
                         <td class="text-center font-medium" style="color: #333;">${fechaVencFormateada}</td>
                         <td class="text-center font-semibold" style="color: #333;">S/ ${parseFloat(producto.precio_venta).toFixed(2)}</td>
                         <td class="text-center">
                             ${(() => {
                                 const stock = producto.stock_actual || 0;
                                 const stockMinimo = producto.stock_minimo || 10;
                                 let colorClass = '';
                                 
                                 if (stock <= stockMinimo) {
                                     colorClass = 'bg-red-100 text-red-800 border border-red-200';
                                 } else if (stock <= (stockMinimo * 2)) {
                                     colorClass = 'bg-yellow-100 text-yellow-800 border border-yellow-200';
                                 } else {
                                     colorClass = 'bg-green-100 text-green-800 border border-green-200';
                                 }
                                 
                                 return `<div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${colorClass}">
                                     ${stock} unidades
                                 </div>`;
                             })()}
                         </td>
                         <td class="text-center">
                             ${(() => {
                                 const totalUbicaciones = producto.total_ubicaciones || 0;
                                 const ubicacionesDetalle = producto.ubicaciones_detalle || [];
                                 const tieneStockSinUbicar = producto.tiene_stock_sin_ubicar || false;
                                 const stockSinUbicar = producto.stock_sin_ubicar || 0;
                                 
                                 if (totalUbicaciones > 1) {
                                     return `<div class="ubicacion-badge multiple" onclick="mostrarDetallesUbicaciones(${producto.id}, '${producto.nombre}', ${JSON.stringify(ubicacionesDetalle).replace(/'/g, "\\'")})" style="cursor: pointer;">
                                         <div class="ubicacion-icon">
                                             <iconify-icon icon="solar:buildings-2-bold-duotone"></iconify-icon>
                                         </div>
                                         <div class="ubicacion-info">
                                             <span class="ubicacion-texto">${totalUbicaciones} Ubicaciones</span>
                                             <span class="ubicacion-subtexto">Ver detalles</span>
                                         </div>
                                     </div>`;
                                 } else if (totalUbicaciones === 1 && !tieneStockSinUbicar) {
                                     const primeraUbicacion = ubicacionesDetalle[0]?.ubicacion_completa || 'Ubicado';
                                     return `<div class="ubicacion-badge ubicado" onclick="mostrarDetallesUbicaciones(${producto.id}, '${producto.nombre}', ${JSON.stringify(ubicacionesDetalle).replace(/'/g, "\\'")})" style="cursor: pointer;">
                                         <div class="ubicacion-icon">
                                             <iconify-icon icon="solar:map-point-bold-duotone"></iconify-icon>
                                         </div>
                                         <div class="ubicacion-info">
                                             <span class="ubicacion-texto">${primeraUbicacion}</span>
                                         </div>
                                     </div>`;
                                 } else if (totalUbicaciones >= 1 && tieneStockSinUbicar) {
                                     return `<div class="ubicacion-badge multiple" onclick="mostrarDetallesUbicaciones(${producto.id}, '${producto.nombre}', ${JSON.stringify(ubicacionesDetalle).replace(/'/g, "\\'")})" style="cursor: pointer;">
                                         <div class="ubicacion-icon">
                                             <iconify-icon icon="solar:box-minimalistic-bold-duotone"></iconify-icon>
                                         </div>
                                         <div class="ubicacion-info">
                                             <span class="ubicacion-texto">Parcialmente ubicado</span>
                                             <span class="ubicacion-subtexto">${stockSinUbicar} sin ubicar</span>
                                         </div>
                                     </div>`;
                                 } else {
                                     return `<div class="ubicacion-badge sin-ubicar">
                                         <div class="ubicacion-icon">
                                             <iconify-icon icon="solar:question-circle-bold-duotone"></iconify-icon>
                                         </div>
                                         <div class="ubicacion-info">
                                             <span class="ubicacion-texto">Sin ubicar</span>
                                         </div>
                                     </div>`;
                                 }
                             })()}
                         </td>
                         <td class="text-center">
                             <span class="estado-badge ${estadoClass}">${estado}</span>
                         </td>
                         <td class="text-center">
                             <a href="javascript:void(0)" 
                             class="btn-view w-8 h-8 bg-primary-50 text-primary-600 rounded-full inline-flex items-center justify-center">
                                 <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                             </a>
                             <a href="javascript:void(0)" 
                             class="btn-edit w-8 h-8 bg-success-100 text-success-600 rounded-full inline-flex items-center justify-center">
                                 <iconify-icon icon="lucide:edit"></iconify-icon>
                             </a>
                             <a href="javascript:void(0)" 
                                 onclick="eliminarProducto(${producto.id})"
                                 class="w-8 h-8 bg-danger-100 text-danger-600 rounded-full inline-flex items-center justify-center">
                                 <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                             </a>
                         </td>
                     </tr>
                 `;
             });
             
             html += `
                     </tbody>
                 </table>
             `;
             
             // Agregar paginación profesional
             if (productos.data.length > 0) {
                 html += `
                     <div class="bg-white px-4 py-4 flex flex-col sm:flex-row items-center justify-between border-t border-gray-200 sm:px-6 space-y-3 sm:space-y-0">
                         <!-- Información de productos -->
                         <div class="flex-1 flex justify-center sm:justify-start">
                             <p class="text-sm text-gray-700">
                                 Mostrando 
                                 <span class="font-semibold text-indigo-600">${productos.from || 1}</span>
                                 a 
                                 <span class="font-semibold text-indigo-600">${productos.to || productos.data.length}</span>
                                 de 
                                 <span class="font-semibold text-indigo-600">${productos.total}</span>
                                 productos
                             </p>
                         </div>
                         
                         <!-- Navegación de páginas -->
                         ${productos.last_page > 1 ? `
                         <div class="flex items-center space-x-1">
                             <!-- Primera página -->
                             ${productos.current_page > 3 ? `
                                 <button onclick="changePage(1)" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-200">
                                     1
                                 </button>
                                 ${productos.current_page > 4 ? '<span class="px-2 py-2 text-gray-500">...</span>' : ''}
                             ` : ''}
                             
                             <!-- Botón Anterior -->
                             ${productos.current_page > 1 ? `
                                 <button onclick="changePage(${productos.current_page - 1})" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-200">
                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                     </svg>
                                 </button>
                             ` : `
                                 <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed">
                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                     </svg>
                                 </span>
                             `}
                             
                             <!-- Páginas cercanas -->
                             ${(() => {
                                 let pagesHtml = '';
                                 const startPage = Math.max(1, productos.current_page - 1);
                                 const endPage = Math.min(productos.last_page, productos.current_page + 1);
                                 
                                 for (let i = startPage; i <= endPage; i++) {
                                     if (i === productos.current_page) {
                                         pagesHtml += `<span class="px-4 py-2 text-sm font-bold text-white bg-indigo-600 border border-indigo-600 rounded-md shadow-sm">${i}</span>`;
                                     } else {
                                         pagesHtml += `<button onclick="changePage(${i})" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-200">${i}</button>`;
                                     }
                                 }
                                 return pagesHtml;
                             })()}
                             
                             <!-- Botón Siguiente -->
                             ${productos.current_page < productos.last_page ? `
                                 <button onclick="changePage(${productos.current_page + 1})" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-200">
                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                     </svg>
                                 </button>
                             ` : `
                                 <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed">
                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                     </svg>
                                 </span>
                             `}
                             
                             <!-- Última página -->
                             ${productos.current_page < productos.last_page - 2 ? `
                                 ${productos.current_page < productos.last_page - 3 ? '<span class="px-2 py-2 text-gray-500">...</span>' : ''}
                                 <button onclick="changePage(${productos.last_page})" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-200">
                                     ${productos.last_page}
                                 </button>
                             ` : ''}
                         </div>
                         ` : ''}
                         
                         <!-- Ir a página específica -->
                         ${productos.last_page > 5 ? `
                         <div class="flex items-center space-x-2">
                             <span class="text-sm text-gray-700">Ir a:</span>
                             <input type="number" 
                                    id="gotoPageInput" 
                                    min="1" 
                                    max="${productos.last_page}" 
                                    value="${productos.current_page}"
                                    class="w-16 px-2 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    onkeypress="if(event.key==='Enter') gotoPage()">
                             <button onclick="gotoPage()" class="px-3 py-1 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors duration-200">
                                 Ir
                             </button>
                         </div>
                         ` : ''}
                     </div>
                 `;
             }
         }
         
         tableContainer.innerHTML = html;
         
         // Reinicializar event listeners para los filtros
         setupFilterEventListeners();
     }
     
     // Función para limpiar la búsqueda
     function limpiarBusqueda() {
         const searchInput = document.getElementById('searchInput');
         if (searchInput) {
             searchInput.value = '';
         }
         
         // Actualizar la URL para remover el parámetro de búsqueda
         const url = new URL(window.location);
         url.searchParams.delete('search');
         window.history.pushState({}, '', url);
         
         // Recargar productos sin búsqueda
         loadProducts();
     }
     
     // Función para cambiar de página
     function changePage(page) {
         const perPageSelect = document.getElementById('perPageSelect');
         const estadoFilter = document.getElementById('filterEstado');
         const searchInput = document.getElementById('searchInput');
         
         const perPage = perPageSelect ? perPageSelect.value : 10;
         const estado = estadoFilter ? estadoFilter.value : 'todos';
         const search = searchInput ? searchInput.value : '';
         
         // Actualizar URL y cargar productos
         updateURL(search, estado, perPage, page);
         loadProductsWithPage(page);
     }
     
     // Función para ir a una página específica
     function gotoPage() {
         const gotoInput = document.getElementById('gotoPageInput');
         if (!gotoInput) return;
         
         const page = parseInt(gotoInput.value);
         const maxPage = parseInt(gotoInput.max);
         
         if (page >= 1 && page <= maxPage) {
             changePage(page);
         } else {
             gotoInput.value = 1;
             Swal.fire({
                 icon: 'warning',
                 title: 'Página inválida',
                 text: `Por favor ingresa un número entre 1 y ${maxPage}`,
                 timer: 2000,
                 showConfirmButton: false
             });
         }
     }
     
     // Función para actualizar URL
     function updateURL(search, estado, perPage, page = 1) {
         const url = new URL(window.location);
         
         if (search && search.trim() !== '') {
             url.searchParams.set('search', search.trim());
         } else {
             url.searchParams.delete('search');
         }
         
         if (estado && estado !== 'todos') {
             url.searchParams.set('estado', estado);
         } else {
             url.searchParams.delete('estado');
         }
         
         url.searchParams.set('per_page', perPage);
         
         if (page > 1) {
             url.searchParams.set('page', page);
         } else {
             url.searchParams.delete('page');
         }
         
         window.history.pushState({}, '', url);
     }
     
     // Función para cargar productos con página específica
     async function loadProductsWithPage(page) {
         try {
             const searchInput = document.getElementById('searchInput');
             const filterEstado = document.getElementById('filterEstado');
             const perPageSelect = document.getElementById('perPageSelect');
             
             const search = searchInput ? searchInput.value : '';
             const estado = filterEstado ? filterEstado.value : 'todos';
             const perPage = perPageSelect ? perPageSelect.value : 10;
             
             // Mostrar loading
             const tableContainer = document.querySelector('.card-body');
             if (tableContainer) {
                 const loadingHtml = `
                     <div class="flex justify-center items-center py-12">
                         <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                         <span class="ml-2 text-gray-600">Cargando página ${page}...</span>
                     </div>
                 `;
                 tableContainer.innerHTML = loadingHtml;
             }
             
             // Construir URL con parámetros
             const url = new URL('{{ route("inventario.productos.ajax") }}', window.location.origin);
             url.searchParams.append('search', search);
             url.searchParams.append('estado', estado);
             url.searchParams.append('per_page', perPage);
             url.searchParams.append('page', page);
             
             const response = await fetch(url, {
                 method: 'GET',
                 headers: {
                     'X-Requested-With': 'XMLHttpRequest',
                     'Accept': 'application/json'
                 }
             });
             
             if (!response.ok) {
                 throw new Error('Error en la respuesta del servidor');
             }
             
             const data = await response.json();
             
             // Renderizar la tabla con los nuevos datos
             renderProductTable(data, search, estado, perPage);
             
         } catch (error) {
             console.error('Error al cargar productos:', error);
             Swal.fire({
                 icon: 'error',
                 title: 'Error',
                 text: 'Hubo un problema al cargar la página. Por favor, intenta nuevamente.',
                 timer: 3000,
                 showConfirmButton: false
             });
         }
     }
     
     // Función para configurar event listeners de filtros
     function setupFilterEventListeners() {
         // Remover event listeners existentes para evitar duplicación
         const perPageSelect = document.getElementById('perPageSelect');
         const estadoFilter = document.getElementById('filterEstado');
         const searchInput = document.getElementById('searchInput');
         const btnAgregarProducto = document.getElementById('btnAgregarProducto');
         
         // Clonar elementos para remover todos los event listeners
         if (perPageSelect) {
             const newPerPageSelect = perPageSelect.cloneNode(true);
             perPageSelect.parentNode.replaceChild(newPerPageSelect, perPageSelect);
             newPerPageSelect.addEventListener('change', function() {
                 // Resetear a la página 1 cuando cambie la cantidad por página
                 updateURL(
                     document.getElementById('searchInput')?.value || '',
                     document.getElementById('filterEstado')?.value || 'todos',
                     this.value,
                     1
                 );
                 loadProducts();
             });
         }
         
         if (estadoFilter) {
             const newEstadoFilter = estadoFilter.cloneNode(true);
             estadoFilter.parentNode.replaceChild(newEstadoFilter, estadoFilter);
             newEstadoFilter.addEventListener('change', loadProducts);
         }
         
         if (searchInput) {
             const newSearchInput = searchInput.cloneNode(true);
             searchInput.parentNode.replaceChild(newSearchInput, searchInput);
             
             let searchTimeout;
             newSearchInput.addEventListener('input', function() {
                 clearTimeout(searchTimeout);
                 searchTimeout = setTimeout(() => {
                     loadProducts();
                 }, 300); // Espera 300ms después de que el usuario deje de escribir
             });
             
             newSearchInput.addEventListener('keypress', function(e) {
                 if (e.key === 'Enter') {
                     clearTimeout(searchTimeout);
                     loadProducts();
                 }
             });
         }
         
         // Event listener para botón agregar producto
         if (btnAgregarProducto) {
             const newBtnAgregarProducto = btnAgregarProducto.cloneNode(true);
             btnAgregarProducto.parentNode.replaceChild(newBtnAgregarProducto, btnAgregarProducto);
             newBtnAgregarProducto.addEventListener('click', function(e) {
                 e.preventDefault();
                 abrirModalAgregar();
             });
         }
     }
     
     // Configurar event listeners iniciales
     setupFilterEventListeners();
     
     // Función simple para mantener sidebar expandido SIEMPRE
     function mantenerSidebarExpandido() {
         const body = document.body;
         const html = document.documentElement;
         
         // Lista de clases que pueden colapsar el sidebar
         const clasesToRemove = [
             'sidebar-collapsed', 'sidebar-minimize', 'sidebar-close',
             'sidebar-mini', 'collapsed', 'minimize', 'sidebar-hidden'
         ];
         
         // Remover todas las clases que colapsan
         clasesToRemove.forEach(className => {
             body.classList.remove(className);
             html.classList.remove(className);
         });
         
         // Forzar que esté expandido
         body.classList.add('sidebar-open', 'sidebar-expand');
         
         console.log('🔄 Sidebar mantenido expandido');
     }
     
     // Ejecutar inmediatamente
     mantenerSidebarExpandido();
     
     // Ejecutar cada vez que se detecten cambios
     const observer = new MutationObserver(function(mutations) {
         let shouldCheck = false;
         mutations.forEach(function(mutation) {
             if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                 shouldCheck = true;
             }
         });
         if (shouldCheck) {
             setTimeout(mantenerSidebarExpandido, 5);
         }
     });
     
     // Observar cambios en body y html
     observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });
     observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
     
     // Ejecutar también antes de navegar
     window.addEventListener('beforeunload', function() {
         mantenerSidebarExpandido();
     });
     
     // ===============================================================
     // SISTEMA DE ACTUALIZACIÓN AUTOMÁTICA DE PRODUCTOS
     // ===============================================================
     
     // Escuchar eventos personalizados de actualización de productos
     window.addEventListener('productoActualizado', function(e) {
         console.log('🔄 Evento de producto actualizado recibido:', e.detail);
         // Recargar la lista de productos
         if (typeof loadProducts === 'function') {
             loadProducts();
         }
     });
     
     // Escuchar cambios en localStorage para comunicación entre ventanas/pestañas
     window.addEventListener('storage', function(e) {
         if (e.key === 'producto_actualizado' && e.newValue) {
             try {
                 const data = JSON.parse(e.newValue);
                 console.log('🔄 Actualización de producto detectada desde otra ventana:', data);
                 
                 // Verificar que el evento no sea muy antiguo (máximo 5 segundos)
                 const tiempoTranscurrido = Date.now() - data.timestamp;
                 if (tiempoTranscurrido < 5000) {
                     // Recargar la lista de productos
                     if (typeof loadProducts === 'function') {
                         loadProducts();
                     }
                 }
             } catch (error) {
                 console.error('Error al procesar evento de actualización:', error);
             }
         }
     });
     
     // ===============================================================
     // FUNCIONES DE EXPORTACIÓN
     // ===============================================================
     
     // Event listeners para botones de exportación
     document.getElementById('btnExcel').addEventListener('click', function() {
         exportarExcel();
     });
     
     document.getElementById('btnPDF').addEventListener('click', function() {
         exportarPDF();
     });
     
     // Función para exportar a Excel
     async function exportarExcel() {
         console.log('📊 Exportando a Excel...');
         
         // Obtener datos actuales de la tabla
         const productos = await obtenerDatosTabla();
         
         if (productos.length === 0) {
             Swal.fire({
                 icon: 'warning',
                 title: 'Sin datos',
                 text: 'No hay productos para exportar',
                 confirmButtonText: 'Entendido'
             });
             return;
         }
         
         // Preparar datos para Excel
         const datosExcel = productos.map(producto => ({
             'Nombre': producto.nombre,
             'Concentración': producto.concentracion || 'N/A',
             'Marca': producto.marca || 'N/A',
             'Stock': producto.stock_actual,
             'Precio Venta': `S/ ${parseFloat(producto.precio_venta || 0).toFixed(2)}`,
             'Fecha Vencimiento': producto.fecha_vencimiento || 'N/A',
             'Categoría': producto.categoria || 'N/A',
             'Ubicación': producto.ubicacion || 'Sin ubicación'
         }));
         
         // Crear libro de Excel
         const wb = XLSX.utils.book_new();
         const ws = XLSX.utils.json_to_sheet(datosExcel);
         
         // Ajustar ancho de columnas
         const colWidths = [
             {wch: 25}, // Nombre
             {wch: 15}, // Concentración
             {wch: 15}, // Marca
             {wch: 12}, // Stock
             {wch: 15}, // Precio Venta
             {wch: 15}, // Fecha Vencimiento
             {wch: 15}, // Categoría
             {wch: 15}  // Ubicación
         ];
         ws['!cols'] = colWidths;
         
         XLSX.utils.book_append_sheet(wb, ws, 'Productos');
         
         // Generar nombre de archivo con fecha
         const fecha = new Date().toISOString().split('T')[0];
         const nombreArchivo = `productos_${fecha}.xlsx`;
         
         // Descargar archivo
         XLSX.writeFile(wb, nombreArchivo);
         
         Swal.fire({
             icon: 'success',
             title: 'Exportación exitosa',
             text: `Archivo ${nombreArchivo} descargado correctamente`,
             confirmButtonText: 'Entendido'
         });
     }
     
     // Función para exportar a PDF
     async function exportarPDF() {
         console.log('📄 Exportando a PDF...');
         
         // Obtener datos actuales de la tabla
         const productos = await obtenerDatosTabla();
         
         if (productos.length === 0) {
             Swal.fire({
                 icon: 'warning',
                 title: 'Sin datos',
                 text: 'No hay productos para exportar',
                 confirmButtonText: 'Entendido'
             });
             return;
         }
         
         // Crear documento PDF
         const { jsPDF } = window.jspdf;
         const doc = new jsPDF('l', 'mm', 'a4'); // Orientación horizontal
         
         // Título del documento
         doc.setFontSize(16);
         doc.setFont('helvetica', 'bold');
         doc.text('Lista de Productos', 14, 15);
         
         // Fecha de generación
         doc.setFontSize(10);
         doc.setFont('helvetica', 'normal');
         const fechaActual = new Date().toLocaleDateString('es-PE');
         doc.text(`Fecha de generación: ${fechaActual}`, 14, 22);
         
         // Preparar datos para la tabla
         const columnas = [
             'Nombre', 'Concentración', 'Marca', 'Stock', 'Precio Venta', 
             'Fecha Venc.', 'Categoría', 'Ubicación'
         ];
         
         const filas = productos.map(producto => [
             producto.nombre.length > 20 ? producto.nombre.substring(0, 20) + '...' : producto.nombre,
             producto.concentracion || 'N/A',
             producto.marca || 'N/A',
             producto.stock_actual,
             `S/ ${parseFloat(producto.precio_venta || 0).toFixed(2)}`,
             producto.fecha_vencimiento || 'N/A',
             producto.categoria || 'N/A',
             producto.ubicacion || 'Sin ubicación'
         ]);
         
         // Crear tabla
         doc.autoTable({
             head: [columnas],
             body: filas,
             startY: 30,
             styles: {
                 fontSize: 8,
                 cellPadding: 2
             },
             headStyles: {
                 fillColor: [185, 41, 41],
                 textColor: 255,
                 fontStyle: 'bold'
             },
             alternateRowStyles: {
                 fillColor: [245, 245, 245]
             },
             margin: { top: 30, right: 14, bottom: 20, left: 14 }
         });
         
         // Generar nombre de archivo con fecha
         const fecha = new Date().toISOString().split('T')[0];
         const nombreArchivo = `productos_${fecha}.pdf`;
         
         // Descargar archivo
         doc.save(nombreArchivo);
         
         Swal.fire({
             icon: 'success',
             title: 'Exportación exitosa',
             text: `Archivo ${nombreArchivo} descargado correctamente`,
             confirmButtonText: 'Entendido'
         });
     }
     
     // Función para obtener datos actuales de la tabla
     async function obtenerDatosTabla() {
         const filas = document.querySelectorAll('tbody.bg-white tr[data-id]');
         const productos = [];
         
         console.log('🔍 Buscando filas de productos...', filas.length);
         
         for (const fila of filas) {
             const productId = fila.getAttribute('data-id');
             if (productId) {
                 try {
                     // Obtener datos completos del producto vía API
                     const response = await fetch(`/inventario/categoria/api/producto/${productId}`);
                     const data = await response.json();
                     
                     if (data.success && data.data) {
                         const producto = data.data;
                         console.log('✅ Datos del producto obtenidos:', producto);
                         productos.push({
                             codigo_barras: producto.codigo_barras || 'N/A',
                             nombre: producto.nombre || 'N/A',
                             concentracion: producto.concentracion || 'N/A',
                             marca: producto.marca || 'N/A',
                             lote: producto.lote || 'N/A',
                             stock_actual: producto.stock_actual || '0',
                             stock_minimo: producto.stock_minimo || '10',
                             precio_compra: producto.precio_compra || '0.00',
                             precio_venta: producto.precio_venta || '0.00',
                             fecha_fabricacion: producto.fecha_fabricacion || 'N/A',
                             fecha_vencimiento: producto.fecha_vencimiento || 'N/A',
                             categoria: producto.categoria || 'N/A',
                             presentacion: producto.presentacion || 'N/A',
                             proveedor: producto.proveedor || 'N/A',
                             ubicacion: producto.ubicacion || producto.ubicacion_almacen || 'Sin ubicación'
                         });
                     } else {
                         console.error('❌ Respuesta de API inválida:', data);
                     }
                 } catch (error) {
                     console.error(`❌ Error al obtener datos del producto ${productId}:`, error);
                     // Fallback a datos básicos de la tabla
                     const celdas = fila.querySelectorAll('td');
                     if (celdas.length >= 6) {
                         const nombreElement = celdas[1].querySelector('h6');
                         const concentracionElement = celdas[1].querySelector('.text-secondary');
                         const fechaVencimiento = celdas[2].textContent.trim();
                         const precioVenta = celdas[3].textContent.replace('S/ ', '').trim();
                         const ubicacionElement = celdas[4].querySelector('.ubicacion-texto');
                         
                         productos.push({
                             codigo_barras: 'N/A',
                             nombre: nombreElement ? nombreElement.textContent.trim() : 'N/A',
                             concentracion: concentracionElement ? concentracionElement.textContent.trim() : 'N/A',
                             marca: 'N/A',
                             lote: 'N/A',
                             stock_actual: '0',
                             stock_minimo: '10',
                             precio_compra: '0.00',
                             precio_venta: precioVenta,
                             fecha_fabricacion: 'N/A',
                             fecha_vencimiento: fechaVencimiento,
                             categoria: 'N/A',
                             presentacion: 'N/A',
                             proveedor: 'N/A',
                             ubicacion: ubicacionElement ? ubicacionElement.textContent.trim() : 'Sin ubicación'
                         });
                     }
                 }
             }
         }
         
         console.log('📊 Total productos encontrados:', productos.length);
         return productos;
     }
     
     // Función para determinar el estado del producto como texto
     function determinarEstadoTexto(producto) {
         const hoy = new Date();
         const fechaVencimiento = new Date(producto.fecha_vencimiento);
         const diasParaVencer = Math.ceil((fechaVencimiento - hoy) / (1000 * 60 * 60 * 24));
         
         if (fechaVencimiento < hoy) {
             return 'Vencido';
         } else if (diasParaVencer <= 30) {
             return 'Por vencer';
         } else if (parseInt(producto.stock_actual) <= parseInt(producto.stock_minimo)) {
             return 'Bajo stock';
         } else {
             return 'Normal';
         }
     }

     // ===================================
     // FUNCIONES PARA MÚLTIPLES UBICACIONES
     // ===================================
     
     // Hacer la función global para que sea accesible desde los eventos onclick
     window.mostrarDetallesUbicaciones = function(productoId, nombreProducto, ubicacionesDetalle) {
         console.log('📍 Mostrando detalles de ubicaciones:', { productoId, nombreProducto, ubicacionesDetalle });
         
         try {
             // Actualizar título del modal
             const tituloElement = document.getElementById('modalUbicacionesTitulo');
             if (tituloElement) {
                 tituloElement.textContent = `Ubicaciones de ${nombreProducto}`;
             }
             
             // Limpiar contenido anterior
             const listaUbicaciones = document.getElementById('listaUbicaciones');
             if (!listaUbicaciones) {
                 console.error('❌ No se encontró el elemento listaUbicaciones');
                 return;
             }
             
             listaUbicaciones.innerHTML = '';
             
             // Verificar si hay ubicaciones
             if (!ubicacionesDetalle || ubicacionesDetalle.length === 0) {
                 listaUbicaciones.innerHTML = `
                     <div class="text-center py-8">
                         <iconify-icon icon="solar:box-linear" class="text-6xl text-gray-400 mb-4"></iconify-icon>
                         <p class="text-gray-500 text-lg">Este producto no tiene ubicaciones asignadas</p>
                         <p class="text-gray-400 text-sm">Puedes asignar ubicaciones desde el módulo de almacén</p>
                     </div>
                 `;
             } else {
                 // Obtener información del producto para calcular stock sin ubicar
                 fetch(`/api/productos/${productoId}/informacion-stock`)
                     .then(response => response.json())
                     .then(data => {
                         if (data.success) {
                             const producto = data.producto;
                             const stockUbicado = ubicacionesDetalle.reduce((sum, ub) => sum + (ub.cantidad || 0), 0);
                             const stockSinUbicar = producto.stock_sin_ubicar || 0;
                             const totalUbicaciones = ubicacionesDetalle.length;
                             
                             // Generar HTML para cada ubicación
                             let htmlUbicaciones = `
                                 <div class="mb-4">
                                     <h4 class="text-lg font-semibold text-gray-800 mb-3">
                                         📦 Total: ${producto.stock_actual} unidades
                                     </h4>
                                     <div class="grid grid-cols-2 gap-4 mb-4" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                                         <div style="background: #f0f9ff; padding: 12px; border-radius: 8px; border-left: 4px solid #0ea5e9;">
                                             <div style="font-size: 12px; color: #0369a1; font-weight: 600;">UBICADO</div>
                                             <div style="font-size: 18px; font-weight: 700; color: #0c4a6e;">${stockUbicado} unidades</div>
                                             <div style="font-size: 11px; color: #0369a1;">${totalUbicaciones} ${totalUbicaciones === 1 ? 'ubicación' : 'ubicaciones'}</div>
                                         </div>
                                         ${stockSinUbicar > 0 ? `
                                         <div style="background: #fef2f2; padding: 12px; border-radius: 8px; border-left: 4px solid #ef4444;">
                                             <div style="font-size: 12px; color: #dc2626; font-weight: 600;">SIN UBICAR</div>
                                             <div style="font-size: 18px; font-weight: 700; color: #991b1b;">${stockSinUbicar} unidades</div>
                                             <div style="font-size: 11px; color: #dc2626;">Pendiente de ubicar</div>
                                         </div>
                                         ` : `
                                         <div style="background: #f0fdf4; padding: 12px; border-radius: 8px; border-left: 4px solid #22c55e;">
                                             <div style="font-size: 12px; color: #16a34a; font-weight: 600;">COMPLETAMENTE UBICADO</div>
                                             <div style="font-size: 18px; font-weight: 700; color: #15803d;">✓</div>
                                             <div style="font-size: 11px; color: #16a34a;">Todo el stock ubicado</div>
                                         </div>
                                         `}
                                     </div>
                                 </div>
                             `;
                             
                             // Continuar con el resto de ubicaciones
                             ubicacionesDetalle.forEach((ubicacion, index) => {
                                 const fechaVencimiento = ubicacion.fecha_vencimiento 
                                     ? new Date(ubicacion.fecha_vencimiento).toLocaleDateString('es-ES')
                                     : 'Sin fecha';
                                 
                                 const lote = ubicacion.lote || 'Sin lote';
                                 
                                 htmlUbicaciones += `
                                     <div class="ubicacion-item" style="margin-bottom: 12px;">
                                         <div class="ubicacion-item-info">
                                             <div class="ubicacion-item-nombre" style="display: flex; align-items: center; font-weight: 600; color: #1f2937; font-size: 15px; margin-bottom: 8px;">
                                                 <iconify-icon icon="solar:home-2-bold-duotone" style="margin-right: 8px; color: #6366f1;"></iconify-icon>
                                                 ${ubicacion.ubicacion_completa || 'Ubicación no especificada'}
                                                 ${ubicacion.tiene_multiples_lotes ? '<span style="background: #f59e0b; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; margin-left: 8px;">Múltiples lotes</span>' : ''}
                                             </div>
                                             ${ubicacion.tiene_multiples_lotes ? 
                                                 // Mostrar todos los lotes si hay múltiples
                                                 ubicacion.lotes_detalle.map(loteDetalle => {
                                                     const fechaLote = loteDetalle.fecha_vencimiento 
                                                         ? new Date(loteDetalle.fecha_vencimiento).toLocaleDateString('es-ES')
                                                         : 'Sin fecha';
                                                     return `
                                                         <div class="lote-detalle" style="margin-left: 20px; margin-bottom: 6px; padding: 8px; background: #f8fafc; border-radius: 6px; border-left: 3px solid #6366f1;">
                                                             <div style="display: flex; justify-content: space-between; align-items: center;">
                                                                 <div style="font-size: 12px; color: #6b7280;">
                                                                     <span style="display: flex; align-items: center; margin-bottom: 2px;">
                                                                         <iconify-icon icon="solar:tag-bold-duotone" style="margin-right: 4px; color: #10b981;"></iconify-icon> 
                                                                         Lote: ${loteDetalle.lote || 'Sin lote'}
                                                                     </span>
                                                                     <span style="display: flex; align-items: center;">
                                                                         <iconify-icon icon="solar:calendar-bold-duotone" style="margin-right: 4px; color: #f59e0b;"></iconify-icon> 
                                                                         Vence: ${fechaLote}
                                                                     </span>
                                                                 </div>
                                                                 <div style="background: #6366f1; color: white; padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 11px;">
                                                                     ${loteDetalle.cantidad} und.
                                                                 </div>
                                                             </div>
                                                         </div>
                                                     `;
                                                 }).join('') :
                                                 // Mostrar información simple si es un solo lote
                                                 `<div class="ubicacion-item-detalles" style="display: flex; gap: 20px; font-size: 13px; color: #6b7280;">
                                                     <span style="display: flex; align-items: center;">
                                                         <iconify-icon icon="solar:calendar-bold-duotone" style="margin-right: 4px; color: #f59e0b;"></iconify-icon> 
                                                         Vence: ${fechaVencimiento}
                                                     </span>
                                                     <span style="display: flex; align-items: center;">
                                                         <iconify-icon icon="solar:tag-bold-duotone" style="margin-right: 4px; color: #10b981;"></iconify-icon> 
                                                         Lote: ${lote}
                                                     </span>
                                                 </div>`
                                             }
                                         </div>
                                         ${!ubicacion.tiene_multiples_lotes ? 
                                             `<div class="ubicacion-item-cantidad" style="background: #6366f1; color: white; padding: 8px 12px; border-radius: 8px; font-weight: 700; font-size: 13px; min-width: 80px; text-align: center;">
                                                 ${ubicacion.cantidad || 0} ${(ubicacion.cantidad || 0) === 1 ? 'unidad' : 'unidades'}
                                             </div>` :
                                             `<div class="ubicacion-item-cantidad" style="background: #10b981; color: white; padding: 8px 12px; border-radius: 8px; font-weight: 700; font-size: 13px; min-width: 80px; text-align: center;">
                                                 Total: ${ubicacion.cantidad || 0} und.
                                             </div>`
                                         }
                                     </div>
                                 `;
                             });
                             
                             listaUbicaciones.innerHTML = htmlUbicaciones;
                         } else {
                             // Fallback si no se puede obtener la información del stock
                             let htmlUbicaciones = `
                                 <div class="mb-4">
                                     <h4 class="text-lg font-semibold text-gray-800 mb-3">
                                         📦 Total: ${ubicacionesDetalle.reduce((sum, ub) => sum + (ub.cantidad || 0), 0)} unidades en ${ubicacionesDetalle.length} ubicaciones
                                     </h4>
                                 </div>
                             `;
                             
                             ubicacionesDetalle.forEach((ubicacion, index) => {
                                 const fechaVencimiento = ubicacion.fecha_vencimiento 
                                     ? new Date(ubicacion.fecha_vencimiento).toLocaleDateString('es-ES')
                                     : 'Sin fecha';
                                 
                                 const lote = ubicacion.lote || 'Sin lote';
                                 
                                 htmlUbicaciones += `
                                     <div class="ubicacion-item" style="margin-bottom: 12px;">
                                         <div class="ubicacion-item-info">
                                             <div class="ubicacion-item-nombre" style="display: flex; align-items: center; font-weight: 600; color: #1f2937; font-size: 15px; margin-bottom: 8px;">
                                                 <iconify-icon icon="solar:home-2-bold-duotone" style="margin-right: 8px; color: #6366f1;"></iconify-icon>
                                                 ${ubicacion.ubicacion_completa || 'Ubicación no especificada'}
                                             </div>
                                             <div class="ubicacion-item-detalles" style="display: flex; gap: 20px; font-size: 13px; color: #6b7280;">
                                                 <span style="display: flex; align-items: center;">
                                                     <iconify-icon icon="solar:calendar-bold-duotone" style="margin-right: 4px; color: #f59e0b;"></iconify-icon> 
                                                     Vence: ${fechaVencimiento}
                                                 </span>
                                                 <span style="display: flex; align-items: center;">
                                                     <iconify-icon icon="solar:tag-bold-duotone" style="margin-right: 4px; color: #10b981;"></iconify-icon> 
                                                     Lote: ${lote}
                                                 </span>
                                             </div>
                                         </div>
                                         <div class="ubicacion-item-cantidad" style="background: #6366f1; color: white; padding: 8px 12px; border-radius: 8px; font-weight: 700; font-size: 13px; min-width: 80px; text-align: center;">
                                             ${ubicacion.cantidad || 0} ${(ubicacion.cantidad || 0) === 1 ? 'unidad' : 'unidades'}
                                         </div>
                                     </div>
                                 `;
                             });
                             
                             listaUbicaciones.innerHTML = htmlUbicaciones;
                         }
                     })
                     .catch(error => {
                         console.error('Error obteniendo información del stock:', error);
                         // Mostrar fallback en caso de error
                         let htmlUbicaciones = `
                             <div class="mb-4">
                                 <h4 class="text-lg font-semibold text-gray-800 mb-3">
                                     📦 Total: ${ubicacionesDetalle.reduce((sum, ub) => sum + (ub.cantidad || 0), 0)} unidades en ${ubicacionesDetalle.length} ubicaciones
                                 </h4>
                             </div>
                         `;
                         
                         ubicacionesDetalle.forEach((ubicacion, index) => {
                             const fechaVencimiento = ubicacion.fecha_vencimiento 
                                 ? new Date(ubicacion.fecha_vencimiento).toLocaleDateString('es-ES')
                                 : 'Sin fecha';
                             
                             const lote = ubicacion.lote || 'Sin lote';
                             
                             htmlUbicaciones += `
                                 <div class="ubicacion-item" style="margin-bottom: 12px;">
                                     <div class="ubicacion-item-info">
                                         <div class="ubicacion-item-nombre" style="display: flex; align-items: center; font-weight: 600; color: #1f2937; font-size: 15px; margin-bottom: 8px;">
                                             <iconify-icon icon="solar:home-2-bold-duotone" style="margin-right: 8px; color: #6366f1;"></iconify-icon>
                                             ${ubicacion.ubicacion_completa || 'Ubicación no especificada'}
                                         </div>
                                         <div class="ubicacion-item-detalles" style="display: flex; gap: 20px; font-size: 13px; color: #6b7280;">
                                             <span style="display: flex; align-items: center;">
                                                 <iconify-icon icon="solar:calendar-bold-duotone" style="margin-right: 4px; color: #f59e0b;"></iconify-icon> 
                                                 Vence: ${fechaVencimiento}
                                             </span>
                                             <span style="display: flex; align-items: center;">
                                                 <iconify-icon icon="solar:tag-bold-duotone" style="margin-right: 4px; color: #10b981;"></iconify-icon> 
                                                 Lote: ${lote}
                                             </span>
                                         </div>
                                     </div>
                                     <div class="ubicacion-item-cantidad" style="background: #6366f1; color: white; padding: 8px 12px; border-radius: 8px; font-weight: 700; font-size: 13px; min-width: 80px; text-align: center;">
                                         ${ubicacion.cantidad || 0} ${(ubicacion.cantidad || 0) === 1 ? 'unidad' : 'unidades'}
                                     </div>
                                 </div>
                             `;
                         });
                         
                         listaUbicaciones.innerHTML = htmlUbicaciones;
                     });
             }
             
             // Mostrar modal
             const modal = document.getElementById('modalUbicaciones');
             if (modal) {
                 modal.style.display = 'flex';
                 document.body.style.overflow = 'hidden'; // Prevenir scroll del body
                 console.log('✅ Modal mostrado correctamente');
             } else {
                 console.error('❌ No se encontró el modal modalUbicaciones');
             }
             
         } catch (error) {
             console.error('❌ Error mostrando modal:', error);
             alert('Error al mostrar los detalles de ubicaciones');
         }
     };
     
     // Hacer la función de cerrar global también
     window.cerrarModalUbicaciones = function() {
         const modal = document.getElementById('modalUbicaciones');
         if (modal) {
             modal.style.display = 'none';
             document.body.style.overflow = 'auto'; // Restaurar scroll del body
             console.log('✅ Modal cerrado');
         }
     };
     
     // Configurar eventos del modal cuando el DOM esté listo
     document.addEventListener('DOMContentLoaded', function() {
         // Cerrar modal al hacer click fuera de él
         const modal = document.getElementById('modalUbicaciones');
         if (modal) {
             modal.addEventListener('click', function(e) {
                 if (e.target === this) {
                     cerrarModalUbicaciones();
                 }
             });
         }
         
         // Cerrar modal con tecla Escape
         document.addEventListener('keydown', function(e) {
             if (e.key === 'Escape') {
                 cerrarModalUbicaciones();
             }
         });
         
         console.log('✅ Eventos del modal configurados');
     });
});
</script>

<!-- Modal de Detalles de Ubicaciones -->
<div id="modalUbicaciones" class="modal-ubicaciones-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.6); z-index: 9999; backdrop-filter: blur(4px);">
    <div class="modal-ubicaciones-container" style="background: white; border-radius: 16px; max-width: 700px; width: 90%; max-height: 85vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); margin: auto;">
        <!-- Header del Modal -->
        <div class="modal-ubicaciones-header" style="padding: 24px 28px 20px; border-bottom: 2px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; border-radius: 16px 16px 0 0;">
            <h3 class="modal-ubicaciones-title" style="font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 12px; margin: 0;">
                <iconify-icon icon="solar:buildings-2-bold-duotone" style="font-size: 24px;"></iconify-icon>
                <span id="modalUbicacionesTitulo">Ubicaciones del Producto</span>
            </h3>
            <button class="modal-ubicaciones-close" onclick="cerrarModalUbicaciones()" style="background: rgba(255, 255, 255, 0.2); border: none; color: white; cursor: pointer; padding: 8px; border-radius: 8px; transition: all 0.2s; font-size: 18px;">
                <iconify-icon icon="heroicons:x-mark-solid"></iconify-icon>
            </button>
        </div>

        <!-- Cuerpo del Modal -->
        <div class="modal-ubicaciones-body" style="padding: 28px;">
            <div id="listaUbicaciones" style="max-height: 400px; overflow-y: auto;">
                <!-- Las ubicaciones se cargarán dinámicamente aquí -->
                <div class="text-center py-8">
                    <iconify-icon icon="solar:box-linear" class="text-6xl text-gray-400 mb-4"></iconify-icon>
                    <p class="text-gray-500 text-lg">Cargando ubicaciones...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS y JS para validaciones del formulario -->
<link rel="stylesheet" href="{{ asset('assets/css/inventario/validaciones_agregar.css') }}">
<script src="{{ asset('assets/js/inventario/validaciones_agregar.js') }}"></script>

