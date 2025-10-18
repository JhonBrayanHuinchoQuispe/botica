/**
 * JavaScript para Ver Perfil - Botica San Antonio
 * Funcionalidades básicas de la vista del perfil
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeProfileView();
});

// =================================================
// INICIALIZACIÓN
// =================================================
function initializeProfileView() {
    // Animaciones de entrada
    animateCards();
    
    // Event listeners para botones
    setupEventListeners();
    
    console.log('🎉 Vista de perfil cargada correctamente');
}

// =================================================
// ANIMACIONES
// =================================================
function animateCards() {
    const cards = document.querySelectorAll('.info-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease-out';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

// =================================================
// EVENT LISTENERS
// =================================================
function setupEventListeners() {
    // Botón cambiar foto
    const btnCambiarFoto = document.querySelector('.btn-cambiar-foto');
    if (btnCambiarFoto) {
        btnCambiarFoto.addEventListener('click', function() {
            document.getElementById('avatarInput').click();
        });
    }

    // Configuración rápida
    const configOptions = document.querySelectorAll('.config-option');
    configOptions.forEach(option => {
        option.addEventListener('click', function() {
            const action = this.onclick;
            if (action) {
                action.call(this);
            }
        });
    });
}

// =================================================
// FUNCIONES PARA CONFIGURACIÓN RÁPIDA
// =================================================
function cambiarPassword() {
    window.location.href = '/perfil/editar#cambiar-password';
}

function configurarNotificaciones() {
    window.location.href = '/perfil/editar#configuracion';
}

async function exportarDatos() {
    try {
        const response = await fetch('/perfil/exportar-datos', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Crear y descargar archivo
            const blob = new Blob([JSON.stringify(data.data, null, 2)], { type: 'application/json' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = data.filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            showSuccessMessage('Datos exportados correctamente');
        } else {
            showErrorMessage('Error al exportar los datos');
        }
    } catch (error) {
        showErrorMessage('Error al exportar los datos');
        console.error('Error:', error);
    }
}

// =================================================
// FUNCIONES DE AVATAR
// =================================================
async function cambiarAvatar() {
    const fileInput = document.getElementById('avatarInput');
    const file = fileInput.files[0];
    
    if (!file) return;
    
    // Validar tipo de archivo
    if (!file.type.startsWith('image/')) {
        showErrorMessage('Por favor selecciona una imagen válida');
        return;
    }
    
    // Validar tamaño (2MB)
    if (file.size > 2 * 1024 * 1024) {
        showErrorMessage('La imagen debe ser menor a 2MB');
        return;
    }
    
    const formData = new FormData();
    formData.append('avatar', file);
    
    try {
        showLoadingMessage('Subiendo avatar...');
        
        const response = await fetch('/perfil/subir-avatar', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Actualizar todas las imágenes de avatar en la página
            const avatarImages = document.querySelectorAll('.avatar-image img, .avatar-placeholder');
            avatarImages.forEach(img => {
                if (img.tagName === 'IMG') {
                    img.src = data.avatar_url;
                } else {
                    // Reemplazar placeholder con imagen
                    img.outerHTML = `<img src="${data.avatar_url}" alt="Avatar">`;
                }
            });
            
            showSuccessMessage('Avatar actualizado correctamente');
        } else {
            showErrorMessage('Error al subir el avatar');
        }
    } catch (error) {
        showErrorMessage('Error al subir el avatar');
        console.error('Error:', error);
    } finally {
        Swal.close();
    }
}

// =================================================
// FUNCIONES DE NOTIFICACIÓN
// =================================================
function showSuccessMessage(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: message,
            confirmButtonColor: '#48bb78',
            timer: 3000,
            timerProgressBar: true
        });
    } else {
        alert(message);
    }
}

function showErrorMessage(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            confirmButtonColor: '#e53e3e'
        });
    } else {
        alert('Error: ' + message);
    }
}

function showLoadingMessage(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: message,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }
}

// =================================================
// UTILIDADES
// =================================================
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showSuccessMessage('Copiado al portapapeles');
    }).catch(() => {
        showErrorMessage('Error al copiar al portapapeles');
    });
}
