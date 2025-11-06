@extends('layouts.app')

@section('title', 'Logs del Sistema')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Logs del Sistema</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Esta sección mostrará los logs del sistema para monitoreo y debugging.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <h5>Logs Disponibles</h5>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Laravel Log
                                    <span class="badge badge-primary badge-pill">Activo</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Error Log
                                    <span class="badge badge-warning badge-pill">Monitoreo</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Access Log
                                    <span class="badge badge-success badge-pill">Disponible</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h5>Acciones</h5>
                        <button class="btn btn-primary" onclick="alert('Funcionalidad en desarrollo')">
                            <i class="fas fa-eye"></i> Ver Logs
                        </button>
                        <button class="btn btn-warning" onclick="alert('Funcionalidad en desarrollo')">
                            <i class="fas fa-download"></i> Descargar Logs
                        </button>
                        <button class="btn btn-danger" onclick="alert('Funcionalidad en desarrollo')">
                            <i class="fas fa-trash"></i> Limpiar Logs
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Funcionalidad adicional para logs
    console.log('Página de logs cargada');
</script>
@endpush