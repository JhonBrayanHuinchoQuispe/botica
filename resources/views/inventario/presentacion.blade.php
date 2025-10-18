@extends('layout.layout')
@php
    $title = 'Gestión de Presentaciones';
    $subTitle = 'Lista de Presentaciones';
    $script = '<script src="' . asset('assets/js/inventario/presentacion/lista.js') . '"></script>';
    $script .= '<script src="' . asset('assets/js/inventario/presentacion/agregar.js') . '"></script>';
    $script .= '<script src="' . asset('assets/js/inventario/presentacion/editar.js') . '"></script>';
    $script .= '<script src="' . asset('assets/js/inventario/presentacion/eliminar.js') . '"></script>';
@endphp

<head>
    <title>Lista de Presentaciones</title>
    <link rel="stylesheet" href="{{ asset('assets/css/inventario/presentacion/lista.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/inventario/presentacion/agregar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/inventario/presentacion/editar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/inventario/presentacion/eliminar.css') }}">
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

@section('content')
<div class="grid grid-cols-12">
    <div class="col-span-12">
        <div class="card border-0 overflow-hidden">
            <div class="card-header flex items-center gap-3 bg-white rounded-t-lg shadow-sm mb-2">
                <iconify-icon icon="ion:ribbon" class="text-2xl text-primary-500"></iconify-icon>
                <h2 class="card-title mb-0 text-2xl font-bold text-neutral-800">Lista de Presentaciones</h2>
            </div>
            <div class="card-body">
                <div class="presentaciones-header mb-8 flex flex-wrap gap-4 items-center justify-between">
                    <div class="flex gap-3 flex-1 items-center">
                        <div class="input-buscar-presentacion-group" style="flex:1;max-width:350px;">
                            <iconify-icon icon="ion:search-outline" class="input-buscar-presentacion-icon"></iconify-icon>
                            <input type="search" id="buscarPresentacion" class="input-buscar-presentacion" placeholder="Buscar presentación...">
                        </div>
                        <div class="registros-por-pagina-group ml-2">
                            <select id="registrosPorPagina" class="registros-por-pagina-select">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="15">15</option>
                                <option value="20">20</option>
                            </select>
                            <span class="registros-label">Registros por página</span>
                        </div>
                    </div>
                    <button id="btnNuevaPresentacion" class="btn-nueva-presentacion flex items-center gap-2 ml-auto">
                        <iconify-icon icon="ic:round-add-circle" class="text-xl"></iconify-icon>
                        <span> Nueva Presentación</span>
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="tabla-presentaciones">
                        <thead>
                            <tr>
                                <th class="sortable" data-col="id">ID <span class="sort-icon"></span></th>
                                <th class="sortable" data-col="nombre">Nombre <span class="sort-icon"></span></th>
                                <th>Descripción</th>
                                <th class="sortable" data-col="productos">Productos <span class="sort-icon"></span></th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="presentaciones-tbody">
                            @if(isset($presentaciones) && $presentaciones->isNotEmpty())
                                @foreach($presentaciones as $presentacion)
                                    <tr data-id="{{ $presentacion->id }}">
                                        <td data-label="ID">{{ $presentacion->id }}</td>
                                        <td data-label="Nombre">{{ $presentacion->nombre }}</td>
                                        <td data-label="Descripción">{{ $presentacion->descripcion ?: '' }}</td>
                                        <td data-label="Productos">{{ $presentacion->productos_count ?? 0 }}</td>
                                        <td data-label="Acciones">
                                            <button class="tabla-btn edit" data-id="{{ $presentacion->id }}" title="Editar"><iconify-icon icon="lucide:edit"></iconify-icon></button>
                                            <button class="tabla-btn delete" data-id="{{ $presentacion->id }}" title="Eliminar"><iconify-icon icon="mingcute:delete-2-line"></iconify-icon></button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" style="text-align:center;padding:2rem;">No hay presentaciones registradas.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Agregar Presentación -->
<div id="modalAgregarPresentacion" class="modal-presentacion-overlay" style="display:none;">
    <div class="modal-presentacion-container">
        <div class="modal-presentacion-header">
            <iconify-icon icon="ic:round-add-circle"></iconify-icon>
            <span>Agregar Presentación</span>
        </div>
        <form id="formAgregarPresentacion" class="modal-presentacion-form">
            <label class="font-semibold">Nombre</label>
            <div class="input-icon-group">
                <iconify-icon icon="ion:ribbon-outline" class="input-icon"></iconify-icon>
                <input type="text" id="agregarPresentacionNombre" name="nombre" required maxlength="100" class="input-modal-presentacion" placeholder="Ej: Tabletas">
            </div>
            <label class="font-semibold">Descripción</label>
            <textarea id="agregarPresentacionDescripcion" name="descripcion" maxlength="255" class="input-modal-presentacion" placeholder="Ej: Caja con 20 tabletas"></textarea>
            <div class="modal-presentacion-actions">
                <button type="button" class="btn-modal-presentacion btn-cancelar" id="btnCancelarAgregarPresentacion">
                    <iconify-icon icon="ic:round-cancel"></iconify-icon> Cancelar
                </button>
                <button type="submit" class="btn-modal-presentacion btn-guardar" id="btnGuardarAgregarPresentacion">
                    <iconify-icon icon="ic:round-check-circle"></iconify-icon> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Presentación -->
<div id="modalEditarPresentacion" class="modal-presentacion-overlay" style="display:none;">
    <div class="modal-presentacion-container">
        <div class="modal-presentacion-header-edit">
            <iconify-icon icon="lucide:edit"></iconify-icon>
            <span>Editar Presentación</span>
        </div>
        <form id="formEditarPresentacion" class="modal-presentacion-form">
            <input type="hidden" id="editarPresentacionId" name="id">
            <label class="font-semibold">Nombre</label>
            <div class="input-icon-group">
                <iconify-icon icon="ion:ribbon-outline" class="input-icon"></iconify-icon>
                <input type="text" id="editarPresentacionNombre" name="nombre" required maxlength="100" class="input-modal-presentacion">
            </div>
            <label class="font-semibold">Descripción</label>
            <textarea id="editarPresentacionDescripcion" name="descripcion" maxlength="255" class="input-modal-presentacion"></textarea>
            <div class="modal-presentacion-actions">
                <button type="button" class="btn-modal-presentacion btn-cancelar-edit" id="btnCancelarEditarPresentacion">
                    <iconify-icon icon="ic:round-cancel"></iconify-icon> Cancelar
                </button>
                <button type="submit" class="btn-modal-presentacion btn-guardar-edit" id="btnGuardarEditarPresentacion">
                    <iconify-icon icon="ic:round-check-circle"></iconify-icon> Guardar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

<script>
    // Inyectar datos del servidor a JavaScript para una carga inicial rápida
    window.presentacionesIniciales = @json($presentaciones ?? []);
</script>