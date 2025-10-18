console.log('✅ Configuración del Sistema - JavaScript cargado');

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Inicializando Configuración del Sistema');
    
    // Configurar eventos
    configurarFormulario();
    configurarEventosAutorizacion();
    
    console.log('✅ Configuración del Sistema inicializada correctamente');
});

// Configurar el formulario principal
function configurarFormulario() {
    const form = document.getElementById('formConfiguracion');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            guardarConfiguracion();
        });
    }
}

// Configurar eventos de autorización de descuento
function configurarEventosAutorizacion() {
    const selectAutorizacion = document.getElementById('requiere_autorizacion_descuento');
    const inputMaxSinAutorizacion = document.getElementById('descuento_sin_autorizacion_max');
    
    if (selectAutorizacion && inputMaxSinAutorizacion) {
        selectAutorizacion.addEventListener('change', function() {
            inputMaxSinAutorizacion.disabled = this.value === '0';
            if (this.value === '0') {
                inputMaxSinAutorizacion.value = '0';
            }
        });
    }
}

// Guardar configuración
async function guardarConfiguracion() {
    const form = document.getElementById('formConfiguracion');
    const formData = new FormData(form);
    
    // Convertir valores booleanos
    const booleanFields = [
        'igv_habilitado', 
        'descuentos_habilitados', 
        'requiere_autorizacion_descuento', 
        'promociones_habilitadas',
        'imprimir_automatico'
    ];
    
    booleanFields.forEach(field => {
        formData.set(field, formData.get(field) === '1' ? true : false);
    });
    
    // Mostrar loading
    Swal.fire({
        title: 'Guardando Configuración',
        html: `
            <div style="text-align: center; padding: 20px;">
                <div style="font-size: 3em; margin-bottom: 16px;">⚙️</div>
                <p style="color: #6b7280;">Actualizando configuración del sistema...</p>
            </div>
        `,
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
    
    try {
        const response = await fetch('/admin/configuracion/actualizar', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        });
        
        const data = await response.json();
        
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Configuración Actualizada!',
                text: data.message,
                confirmButtonColor: '#059669',
                timer: 2000,
                timerProgressBar: true
            }).then(() => {
                // Recargar la página para mostrar los cambios
                window.location.reload();
            });
        } else {
            throw new Error(data.message || 'Error al guardar configuración');
        }
        
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Error al guardar la configuración',
            confirmButtonColor: '#dc2626'
        });
    }
}

// Función para restablecer valores predeterminados
function restablecerConfiguracion() {
    Swal.fire({
        title: '¿Restablecer Configuración?',
        text: "Se restablecerán todos los valores a su configuración predeterminada",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, restablecer',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.reload();
        }
    });
} 