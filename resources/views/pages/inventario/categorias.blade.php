@extends('layouts.layout')
@php
    $title = 'Gestión de Categorías';
    $subTitle = 'Lista de Categorías';
    $script = '<script src="' . asset('assets/js/inventario/categoria/lista.js') . '"></script>';
    $script .= '<script src="' . asset('assets/js/inventario/categoria/agregar.js') . '"></script>';
    $script .= '<script src="' . asset('assets/js/inventario/categoria/editar.js') . '"></script>';
    $script .= '<script src="' . asset('assets/js/inventario/categoria/eliminar.js') . '"></script>';
@endphp

<head>
    <title>Lista de Categorías</title>
    <link rel="stylesheet" href="{{ asset('assets/css/inventario/categoria/lista.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/inventario/categoria/agregar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/inventario/categoria/editar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/inventario/categoria/eliminar.css') }}">
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

@section('content')
<div class="grid grid-cols-12">
    <div class="col-span-12">
        <div class="card border-0 overflow-hidden">
            <div class="card-header flex items-center gap-3 bg-white rounded-t-lg shadow-sm mb-2">
                <iconify-icon icon="fluent:apps-list-detail-24-filled" class="text-2xl text-primary-500"></iconify-icon>
                <h2 class="card-title mb-0 text-2xl font-bold text-neutral-800">Lista de Categorías</h2>
            </div>
            <div class="card-body">
                <div class="categorias-header mb-8 flex flex-wrap gap-4 items-center justify-between">
                    <div class="flex gap-3 flex-1 items-center">
                        <div class="input-buscar-categoria-group" style="flex:1;max-width:350px;">
                            <iconify-icon icon="ion:search-outline" class="input-buscar-categoria-icon"></iconify-icon>
                            <input type="search" id="buscarCategoria" class="input-buscar-categoria" placeholder="Buscar categoría...">
                        </div>
                        <div class="registros-por-pagina-group ml-2">
                            <select id="registrosPorPagina" class="registros-por-pagina-select">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="15">15</option>
                                <option value="20">20</option>
                                <option value="30">30</option>
                                <option value="40">40</option>
                                <option value="50">50</option>
                            </select>
                            <span class="registros-label">Registros por página</span>
                        </div>
                    </div>
                    <button id="btnNuevaCategoria" class="btn-nueva-categoria flex items-center gap-2 ml-auto">
                        <iconify-icon icon="ic:round-add-circle" class="text-xl"></iconify-icon>
                        <span> Nueva Categoría</span>
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="tabla-categorias">
                        <thead>
                            <tr>
                                <th class="sortable" data-col="id">ID <span class="sort-icon" id="sortIconId"></span></th>
                                <th class="sortable" data-col="nombre">Nombre <span class="sort-icon" id="sortIconNombre"></span></th>
                                <th>Descripción</th>
                                <th class="sortable" data-col="productos">Productos <span class="sort-icon" id="sortIconProductos"></span></th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="categorias-tbody">
                            @if(isset($categorias) && $categorias->isNotEmpty())
                                @foreach($categorias as $categoria)
                                    <tr data-id="{{ $categoria->id }}">
                                        <td data-label="ID">{{ $categoria->id }}</td>
                                        <td data-label="Nombre">{{ $categoria->nombre }}</td>
                                        <td data-label="Descripción">{{ $categoria->descripcion ?: '' }}</td>
                                        <td data-label="Productos">{{ $categoria->productos_count ?? 0 }}</td>
                                        <td data-label="Acciones">
                                            <button class="tabla-btn edit" data-id="{{ $categoria->id }}" title="Editar"><iconify-icon icon="lucide:edit"></iconify-icon></button>
                                            <button class="tabla-btn delete" data-id="{{ $categoria->id }}" title="Eliminar"><iconify-icon icon="mingcute:delete-2-line"></iconify-icon></button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" style="text-align:center;padding:2rem;">No hay categorías registradas.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Agregar Categoría -->
<div id="modalAgregarCategoria" class="modal-categoria-overlay" style="display:none;">
    <div class="modal-categoria-container">
        <div class="modal-categoria-header">
            <iconify-icon icon="ic:round-add-circle"></iconify-icon>
            <span>Agregar Categoría</span>
        </div>
        <form id="formAgregarCategoria" class="modal-categoria-form">
            <label class="font-semibold">Nombre</label>
            <div class="input-icon-group">
                <iconify-icon icon="mdi:label-outline" class="input-icon"></iconify-icon>
                <input type="text" id="agregarCategoriaNombre" name="nombre" required maxlength="100" class="input-modal-categoria input-with-icon" placeholder="Ej: Analgésicos">
            </div>
            <label class="font-semibold">Descripción</label>
            <div style="margin-bottom: 1.25rem;">
                <textarea id="agregarCategoriaDescripcion" name="descripcion" maxlength="255" class="input-modal-categoria" placeholder="Ej: Para aliviar el dolor" style="padding: 0.8rem 1rem;"></textarea>
            </div>
            <div class="modal-categoria-actions">
                <button type="button" class="btn-modal-categoria btn-cancelar" id="btnCancelarAgregarCategoria">
                    <iconify-icon icon="ic:round-cancel"></iconify-icon> Cancelar
                </button>
                <button type="submit" class="btn-modal-categoria btn-guardar" id="btnGuardarAgregarCategoria">
                    <iconify-icon icon="ic:round-check-circle"></iconify-icon> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Categoría -->
<div id="modalEditarCategoria" class="modal-categoria-overlay" style="display:none;">
    <div class="modal-categoria-container">
        <div class="modal-categoria-header-edit">
            <iconify-icon icon="lucide:edit"></iconify-icon>
            <span>Editar Categoría</span>
        </div>
        <form id="formEditarCategoria" class="modal-categoria-form">
            <input type="hidden" id="editarCategoriaId" name="id">
            <label class="font-semibold">Nombre</label>
            <div class="input-icon-group">
                <iconify-icon icon="mdi:label-outline" class="input-icon"></iconify-icon>
                <input type="text" id="editarCategoriaNombre" name="nombre" required maxlength="100" class="input-modal-categoria input-with-icon">
            </div>
            <label class="font-semibold">Descripción</label>
            <div style="margin-bottom: 1.25rem;">
                <textarea id="editarCategoriaDescripcion" name="descripcion" maxlength="255" class="input-modal-categoria" style="padding: 0.8rem 1rem;"></textarea>
            </div>
            <div class="modal-categoria-actions">
                <button type="button" class="btn-modal-categoria btn-cancelar-edit" id="btnCancelarEditarCategoria">
                    <iconify-icon icon="ic:round-cancel"></iconify-icon> Cancelar
                </button>
                <button type="submit" class="btn-modal-categoria btn-guardar-edit" id="btnGuardarEditarCategoria">
                    <iconify-icon icon="ic:round-check-circle"></iconify-icon> Guardar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

<script>
    // Inyectar datos del servidor a JavaScript para una carga inicial rápida
    window.categoriasIniciales = @json($categorias ?? []);
</script>