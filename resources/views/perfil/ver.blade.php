@extends('layout.layout')

@php
    $title = 'Perfil de Usuario';
    $subTitle = 'Ver Información del Perfil';
    $script = '
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="' . asset('assets/js/perfil/ver.js') . '"></script>
    ';
@endphp

<head>
    <title>Perfil de Usuario - {{ $user->name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/perfil/ver.css') }}?v={{ time() }}">
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
</head>

@section('content')

<div class="grid grid-cols-12">
    <div class="col-span-12">
<div class="perfil-container">
            <!-- Layout Principal de Dos Columnas -->
            <div class="ver-perfil-layout">
                <!-- Panel Izquierdo - Información del Usuario -->
                <div class="perfil-info-panel">
                    <!-- Header de Usuario -->
                    <div class="usuario-header">
                        <div class="avatar-section">
                            <div class="avatar-preview">
                        @if($user->avatar)
                            <img src="{{ $user->avatar_url }}" alt="Avatar de {{ $user->name }}">
                        @else
                            <div class="avatar-placeholder">
                                        @php
                                            $names = explode(' ', $user->name);
                                            $initials = '';
                                            foreach($names as $name) {
                                                $initials .= strtoupper(substr($name, 0, 1));
                                                if(strlen($initials) >= 2) break;
                                            }
                                        @endphp
                                        {{ $initials }}
                            </div>
                        @endif
                    <div class="avatar-status online"></div>
                            </div>
                </div>
                
                        <div class="usuario-info">
                            <h1 class="nombre-usuario">{{ $user->name }}</h1>
                            <p class="email-usuario">{{ $user->email }}</p>
                            <div class="badges-usuario">
                        <span class="badge-role">
                                    <iconify-icon icon="heroicons:user-solid"></iconify-icon>
                                    Usuario
                        </span>
                        <span class="badge-status activo">
                            <iconify-icon icon="heroicons:check-circle-solid"></iconify-icon>
                            Activo
                        </span>
                    </div>
                </div>
                        
                        <div class="usuario-acciones">
                            <a href="{{ route('perfil.editar') }}" class="btn-editar-perfil">
                                <iconify-icon icon="heroicons:pencil-solid"></iconify-icon>
                                Editar Perfil
                            </a>
                        </div>
            </div>
            
                    <!-- Información Detallada -->
                    <div class="info-detallada">
                        <h3>
                            <iconify-icon icon="heroicons:identification-solid"></iconify-icon>
                            Información Personal
                        </h3>
                        <div class="info-lista">
                            <div class="info-item">
                                <span class="info-label">Nombre completo:</span>
                                <span class="info-value">{{ $user->name }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Email:</span>
                                <span class="info-value">{{ $user->email }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Teléfono:</span>
                                <span class="info-value">{{ $user->telefono ?? 'No especificado' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Cargo:</span>
                                <span class="info-value">{{ $user->cargo ?? 'No especificado' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Dirección:</span>
                                <span class="info-value">{{ $user->direccion ?? 'No especificada' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Miembro desde:</span>
                                <span class="info-value">{{ $user->created_at->format('d/m/Y') }}</span>
                            </div>
            </div>
        </div>
    </div>

                <!-- Panel Derecho - Tarjetas de Información -->
                <div class="informacion-panel">
        <!-- Información Personal -->
        <div class="info-card">
            <div class="card-header">
                <iconify-icon icon="heroicons:user-solid"></iconify-icon>
                <h3>Información Personal</h3>
            </div>
            <div class="card-content">
                <div class="info-row">
                    <span class="info-label">Nombre completo</span>
                    <span class="info-value">{{ $user->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Correo electrónico</span>
                    <span class="info-value">{{ $user->email }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Teléfono</span>
                    <span class="info-value">{{ $user->telefono ?? 'No especificado' }}</span>
                </div>
                            <div class="info-row">
                                <span class="info-label">Cargo</span>
                                <span class="info-value">{{ $user->cargo ?? 'No especificado' }}</span>
                            </div>
                <div class="info-row">
                    <span class="info-label">Fecha de registro</span>
                    <span class="info-value">{{ $user->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Seguridad de la Cuenta -->
        <div class="info-card">
            <div class="card-header">
                <iconify-icon icon="heroicons:shield-check-solid"></iconify-icon>
                <h3>Seguridad de la Cuenta</h3>
            </div>
            <div class="card-content">
                <div class="info-row">
                    <span class="info-label">Estado de verificación</span>
                    <span class="info-value">
                        @if($user->email_verified_at)
                            <span class="status-badge verificado">
                                <iconify-icon icon="heroicons:check-badge-solid"></iconify-icon>
                                Verificado
                            </span>
                        @else
                            <span class="status-badge no-verificado">
                                <iconify-icon icon="heroicons:exclamation-triangle-solid"></iconify-icon>
                                No verificado
                            </span>
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Último acceso</span>
                    <span class="info-value">{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Primer acceso' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Autenticación 2FA</span>
                    <span class="info-value">
                        <span class="status-badge no-activo">
                            <iconify-icon icon="heroicons:x-circle-solid"></iconify-icon>
                            No configurado
                        </span>
                    </span>
                </div>
            </div>
        </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
