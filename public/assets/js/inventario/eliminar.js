// Hacer la función global
window.eliminarProducto = function(id) {
    console.log('🗑️ Función eliminarProducto llamada con ID:', id);
    
    // Verificar que SweetAlert2 esté disponible
    if (typeof Swal === 'undefined') {
        console.error('❌ SweetAlert2 no está disponible');
        alert('Error: Sistema no inicializado correctamente');
        return;
    }
    // Obtener el nombre del producto y sus detalles
    const row = document.querySelector(`tr[data-id="${id}"]`);
    if (!row) {
        console.error('No se encontró la fila del producto con ID:', id);
        return;
    }
    
    // Obtener datos del producto de la fila
    const nombreElement = row.querySelector('td:nth-child(2) h6');
    const nombreProducto = nombreElement ? nombreElement.textContent.trim() : 'Producto';
    
    const estadoElement = row.querySelector('td:nth-child(7) span'); // Columna de estado
    const estado = estadoElement ? estadoElement.textContent.trim() : 'Normal';
    
    const precioElement = row.querySelector('td:nth-child(4)'); // Columna de precio
    const precio = precioElement ? precioElement.textContent.trim() : 'N/A';

    Swal.fire({
        title: '¿Eliminar producto?',
        html: `
            <div class="text-center">
                <p class="mb-2 text-gray-600">Estás a punto de eliminar el producto:</p>
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <p class="font-semibold text-lg text-gray-800">${nombreProducto}</p>
                    <div class="flex items-center justify-center gap-4 mt-2 text-sm text-gray-600">
                        <span>Precio: ${precio}</span>
                        <span class="px-2 py-1 rounded ${
                            estado.toLowerCase() === 'normal' ? 'bg-success-100 text-success-700' : 
                            estado.toLowerCase() === 'bajo stock' ? 'bg-warning-100 text-warning-700' : 
                            'bg-danger-100 text-danger-700'
                        }">${estado}</span>
                    </div>
                </div>
                <p class="text-sm text-gray-600">Esta acción eliminará permanentemente el producto del inventario y no podrá ser recuperado.</p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#F3F4F6',
        confirmButtonText: '<span class="text-white">Sí, eliminar producto</span>',
        cancelButtonText: '<span class="text-gray-700">Cancelar</span>',
        customClass: {
            popup: 'swal2-popup-custom',
            confirmButton: 'swal2-confirm-custom',
            cancelButton: 'swal2-cancel-custom',
            htmlContainer: 'swal2-html-custom'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            
            // Mostrar loading
            Swal.fire({
                title: 'Eliminando producto...',
                text: 'Por favor, espere...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`/inventario/producto/eliminar/${id}`, {  // Ruta correcta según web.php
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.needsRefresh) {
                        // Recargar la página si los IDs fueron reordenados
                        Swal.fire({
                            icon: 'success',
                            title: '¡Eliminado correctamente!',
                            text: `${nombreProducto} ha sido eliminado del inventario.`,
                            showConfirmButton: false,
                            timer: 1500,
                            timerProgressBar: true
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        // Eliminar la fila con animación
                        const row = document.querySelector(`tr[data-id="${id}"]`);
                        if (row) {
                            row.style.transition = 'all 0.3s ease-out';
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(20px)';
                            setTimeout(() => {
                                row.remove();
                                actualizarContador();
                                reordenarIds(); // Nueva función para reordenar IDs visualmente
                            }, 300);
                        }

                        Swal.fire({
                            icon: 'success',
                            title: '¡Eliminado correctamente!',
                            text: `${nombreProducto} ha sido eliminado del inventario.`,
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                    }
                } else {
                    throw new Error(data.message || 'Error al eliminar el producto');
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error al eliminar',
                    text: error.message || 'Hubo un problema al intentar eliminar el producto.',
                    icon: 'error',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#dc2626'
                });
            });
        }
    });
}

// Función para actualizar el contador de registros
function actualizarContador() {
    const totalRows = document.querySelectorAll('tbody tr').length;
    const counter = document.querySelector('.datatable-info');
    if (counter) {
        // Eliminar cualquier asignación a counter.textContent con mensajes de conteo de productos
    }
}

// Nueva función para reordenar IDs visualmente
function reordenarIds() {
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach((row, index) => {
        const idCell = row.querySelector('td:first-child');
        if (idCell) {
            idCell.textContent = (index + 1).toString();
        }
    });
}

// Confirmar que el script se cargó correctamente
console.log('✅ Script eliminar.js cargado correctamente');
console.log('🗑️ Función eliminarProducto disponible:', typeof window.eliminarProducto);