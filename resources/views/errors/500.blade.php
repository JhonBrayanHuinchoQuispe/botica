@extends('layout.layout')

@php
    $title = 'Error del Servidor';
    $subTitle = 'Error 500';
@endphp

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto text-center">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="mb-6">
                <iconify-icon icon="solar:danger-triangle-bold-duotone" class="text-6xl text-red-500 mb-4"></iconify-icon>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Error del Servidor</h1>
                <p class="text-gray-600">{{ $message ?? 'Ha ocurrido un error interno del servidor.' }}</p>
            </div>
            
            @if(isset($error_details) && $error_details)
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <h3 class="text-sm font-medium text-red-800 mb-2">Detalles del error:</h3>
                    <p class="text-sm text-red-700 font-mono">{{ $error_details }}</p>
                </div>
            @endif
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button onclick="window.history.back()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors">
                    <iconify-icon icon="solar:arrow-left-bold" class="mr-2"></iconify-icon>
                    Volver
                </button>
                <a href="{{ route('dashboard') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors">
                    <iconify-icon icon="solar:home-bold" class="mr-2"></iconify-icon>
                    Ir al Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection