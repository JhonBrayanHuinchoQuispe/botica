@extends('layouts.layout')
@php
    $title = 'Configuración de Impresoras';
    $subTitle = 'Gestión de impresoras del sistema';
    $script = '
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="' . asset('assets/js/admin/configuracion.js') . '?v=' . time() . '"></script>
    ';
@endphp

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-t-lg p-4">
                    <div class="flex items-center gap-2">
                        <iconify-icon icon="solar:printer-bold-duotone" class="text-2xl"></iconify-icon>
                        <h3 class="text-lg font-semibold">Configuración de Impresoras</h3>
                    </div>
                </div>
                <div class="card-body p-6">
                    <form id="formConfiguracionImpresoras" onsubmit="guardarConfiguracionImpresoras(event)">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Impresora Principal -->
                            <div>
                                <label class="form-label">Impresora Principal</label>
                                <input type="text" name="impresora_principal" id="impresora_principal" 
                                       class="form-control" 
                                       value="{{ $configuracion->impresora_principal ?? '' }}"
                                       placeholder="Nombre de la impresora principal">
                                <small class="text-muted">Impresora predeterminada para documentos generales</small>
                            </div>

                            <!-- Impresora de Tickets -->
                            <div>
                                <label class="form-label">Impresora de Tickets</label>
                                <input type="text" name="impresora_tickets" id="impresora_tickets" 
                                       class="form-control" 
                                       value="{{ $configuracion->impresora_tickets ?? '' }}"
                                       placeholder="Nombre de la impresora de tickets">
                                <small class="text-muted">Impresora específica para tickets de venta</small>
                            </div>

                            <!-- Impresora de Reportes -->
                            <div>
                                <label class="form-label">Impresora de Reportes</label>
                                <input type="text" name="impresora_reportes" id="impresora_reportes" 
                                       class="form-control" 
                                       value="{{ $configuracion->impresora_reportes ?? '' }}"
                                       placeholder="Nombre de la impresora de reportes">
                                <small class="text-muted">Impresora para reportes y documentos largos</small>
                            </div>

                            <!-- Impresión Automática -->
                            <div>
                                <label class="form-label">Impresión Automática</label>
                                <select name="imprimir_automatico" id="imprimir_automatico" class="form-control">
                                    <option value="1" {{ $configuracion->imprimir_automatico ? 'selected' : '' }}>Habilitado</option>
                                    <option value="0" {{ !$configuracion->imprimir_automatico ? 'selected' : '' }}>Deshabilitado</option>
                                </select>
                                <small class="text-muted">Imprimir automáticamente después de cada venta</small>
                            </div>

                            <!-- Copias de Ticket -->
                            <div>
                                <label class="form-label">Copias de Ticket</label>
                                <input type="number" name="copias_ticket" id="copias_ticket" 
                                       class="form-control" 
                                       value="{{ $configuracion->copias_ticket ?? 1 }}"
                                       min="1" max="5">
                                <small class="text-muted">Número de copias a imprimir por ticket</small>
                            </div>

                            <!-- Ancho del Papel -->
                            <div>
                                <label class="form-label">Ancho del Papel (mm)</label>
                                <select name="papel_ticket_ancho" id="papel_ticket_ancho" class="form-control">
                                    <option value="58" {{ ($configuracion->papel_ticket_ancho ?? 80) == 58 ? 'selected' : '' }}>58mm</option>
                                    <option value="80" {{ ($configuracion->papel_ticket_ancho ?? 80) == 80 ? 'selected' : '' }}>80mm</option>
                                </select>
                                <small class="text-muted">Ancho del papel para tickets</small>
                            </div>
                        </div>

                        <!-- Sección de Pruebas -->
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h4 class="text-md font-semibold mb-3">Pruebas de Impresión</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <button type="button" class="btn btn-outline-primary" onclick="probarImpresora('principal')">
                                    <iconify-icon icon="solar:printer-bold" class="mr-2"></iconify-icon>
                                    Probar Principal
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="probarImpresora('tickets')">
                                    <iconify-icon icon="solar:ticket-bold" class="mr-2"></iconify-icon>
                                    Probar Tickets
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="probarImpresora('reportes')">
                                    <iconify-icon icon="solar:document-bold" class="mr-2"></iconify-icon>
                                    Probar Reportes
                                </button>
                            </div>
                        </div>

                        <!-- Información de Estado -->
                        <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                            <h4 class="text-md font-semibold mb-3">Estado de las Impresoras</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="text-center">
                                    <div class="text-2xl mb-2">
                                        <iconify-icon icon="solar:printer-bold-duotone" class="text-blue-600"></iconify-icon>
                                    </div>
                                    <p class="text-sm font-medium">Principal</p>
                                    <span class="badge badge-success" id="estado-principal">Conectada</span>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl mb-2">
                                        <iconify-icon icon="solar:ticket-bold-duotone" class="text-purple-600"></iconify-icon>
                                    </div>
                                    <p class="text-sm font-medium">Tickets</p>
                                    <span class="badge badge-success" id="estado-tickets">Conectada</span>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl mb-2">
                                        <iconify-icon icon="solar:document-bold-duotone" class="text-green-600"></iconify-icon>
                                    </div>
                                    <p class="text-sm font-medium">Reportes</p>
                                    <span class="badge badge-success" id="estado-reportes">Conectada</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-6">
                            <a href="{{ route('admin.configuracion') }}" class="btn btn-secondary">
                                <iconify-icon icon="solar:arrow-left-bold" class="mr-2"></iconify-icon>
                                Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <iconify-icon icon="solar:diskette-bold" class="mr-2"></iconify-icon>
                                Guardar Configuración
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function guardarConfiguracionImpresoras(event) {
    event.preventDefault();
    
    const form = document.getElementById('formConfiguracionImpresoras');
    const formData = new FormData(form);
    
    try {
        const response = await fetch('{{ route("admin.configuracion.impresoras.actualizar") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: result.message,
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            throw new Error(result.message || 'Error al guardar la configuración');
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Error al guardar la configuración'
        });
    }
}

async function probarImpresora(tipo) {
    let impresora = '';
    
    switch(tipo) {
        case 'principal':
            impresora = document.getElementById('impresora_principal').value;
            break;
        case 'tickets':
            impresora = document.getElementById('impresora_tickets').value;
            break;
        case 'reportes':
            impresora = document.getElementById('impresora_reportes').value;
            break;
    }
    
    if (!impresora) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Por favor, configure primero el nombre de la impresora'
        });
        return;
    }
    
    try {
        const response = await fetch('{{ route("admin.configuracion.impresoras.probar") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ impresora: impresora })
        });
        
        const result = await response.json();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Prueba Exitosa!',
                text: result.message,
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            throw new Error(result.message || 'Error al probar la impresora');
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Error al probar la impresora'
        });
    }
}
</script>
@endsection