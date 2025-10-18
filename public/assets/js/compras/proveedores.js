/**
 * 🏢 GESTIÓN DE PROVEEDORES - BOTICA SAN ANTONIO
 * JavaScript para manejo completo de proveedores
 */

class ProveedoresManager {
    constructor() {
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.verificarSweetAlert();
        console.log('✅ ProveedoresManager iniciado correctamente');
    }
    
    setupEventListeners() {
        // Filtros en tiempo real
        const searchInput = document.getElementById('buscarProveedor');
        if (searchInput) {
            searchInput.addEventListener('input', () => this.aplicarFiltros());
        }
        
        const estadoSelect = document.getElementById('filtroEstado');
        if (estadoSelect) {
            estadoSelect.addEventListener('change', () => this.aplicarFiltros());
        }
    }
    
    verificarSweetAlert() {
        if (typeof Swal === 'undefined') {
            console.error('❌ SweetAlert2 no está cargado');
            alert('Error: SweetAlert2 no está disponible. Recarga la página.');
            return false;
        }
        console.log('✅ SweetAlert2 disponible');
        return true;
    }
    
    /**
     * Abrir modal para agregar nuevo proveedor
     */
    abrirModalAgregar() {
        if (!this.verificarSweetAlert()) return;
        
        Swal.fire({
            title: '<div style="background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; padding: 1rem; margin: -1rem -1rem 1rem -1rem; border-radius: 8px;"><i class="fas fa-plus-circle"></i> Nuevo Proveedor</div>',
            html: `
                <div style="text-align: left; padding: 1rem;">
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">
                            <i class="fas fa-building" style="color: #3b82f6; margin-right: 0.5rem;"></i>
                            Razón Social *
                        </label>
                        <input type="text" id="new_razon_social" style="width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem;" placeholder="Ej: Distribuidora Médica S.A.C.">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">
                            <i class="fas fa-store" style="color: #3b82f6; margin-right: 0.5rem;"></i>
                            Nombre Comercial
                        </label>
                        <input type="text" id="new_nombre_comercial" style="width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem;" placeholder="Ej: Dismesa">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">
                            <i class="fas fa-id-card" style="color: #3b82f6; margin-right: 0.5rem;"></i>
                            RUC
                        </label>
                        <input type="text" id="new_ruc" maxlength="11" style="width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem;" placeholder="20123456789">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">
                            <i class="fas fa-phone" style="color: #3b82f6; margin-right: 0.5rem;"></i>
                            Teléfono
                        </label>
                        <input type="text" id="new_telefono" style="width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem;" placeholder="01-1234567">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">
                            <i class="fas fa-envelope" style="color: #3b82f6; margin-right: 0.5rem;"></i>
                            Email
                        </label>
                        <input type="email" id="new_email" style="width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem;" placeholder="contacto@empresa.com">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">
                            <i class="fas fa-map-marker-alt" style="color: #3b82f6; margin-right: 0.5rem;"></i>
                            Dirección
                        </label>
                        <textarea id="new_direccion" rows="2" style="width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem; resize: none;" placeholder="Dirección completa"></textarea>
                    </div>
                </div>
            `,
            showCancelButton: true,
            showDenyButton: false,
            showCloseButton: false,
            confirmButtonText: 'Guardar Proveedor',
            cancelButtonText: 'Cancelar',
            denyButtonText: '',
            width: '600px',
            customClass: {
                confirmButton: 'swal2-confirm-blue',
                cancelButton: 'swal2-cancel-gray'
            },
            buttonsStyling: false,
            didOpen: () => {
                // Eliminar botones no deseados
                setTimeout(() => {
                    const buttons = document.querySelectorAll('.swal2-deny, .swal2-close');
                    buttons.forEach(btn => btn.remove());
                }, 100);
                
                const style = document.createElement('style');
                style.textContent = `
                    .swal2-deny, .swal2-close {
                        display: none !important;
                        visibility: hidden !important;
                    }
                    .swal2-confirm-blue {
                        background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
                        color: white !important;
                        border: none !important;
                        padding: 0.75rem 1.5rem !important;
                        border-radius: 8px !important;
                        font-weight: 600 !important;
                        margin: 0 0.5rem !important;
                    }
                    .swal2-cancel-gray {
                        background: linear-gradient(135deg, #6b7280, #4b5563) !important;
                        color: white !important;
                        border: none !important;
                        padding: 0.75rem 1.5rem !important;
                        border-radius: 8px !important;
                        font-weight: 600 !important;
                        margin: 0 0.5rem !important;
                    }
                `;
                document.head.appendChild(style);
            }
        }).then((result) => {
            if (result.isConfirmed) {
                this.guardarProveedor();
            }
        });
    }
    
    /**
     * Guardar nuevo proveedor
     */
    guardarProveedor() {
        const formData = {
            razon_social: document.getElementById('new_razon_social').value.trim(),
            nombre_comercial: document.getElementById('new_nombre_comercial').value.trim(),
            ruc: document.getElementById('new_ruc').value.trim(),
            telefono: document.getElementById('new_telefono').value.trim(),
            email: document.getElementById('new_email').value.trim(),
            direccion: document.getElementById('new_direccion').value.trim()
        };
        
        // Validaciones
        if (!formData.razon_social) {
            Swal.fire({
                icon: 'error',
                title: 'Campo requerido',
                text: 'La razón social es obligatoria'
            });
            return;
        }
        
        if (formData.ruc && (formData.ruc.length !== 11 || !/^\d+$/.test(formData.ruc))) {
            Swal.fire({
                icon: 'error',
                title: 'RUC inválido',
                text: 'El RUC debe tener exactamente 11 dígitos numéricos'
            });
            return;
        }
        
        // Mostrar loading
        Swal.fire({
            title: 'Guardando proveedor...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });
        
        // Enviar datos
        fetch('/compras/proveedores/guardar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Proveedor guardado!',
                    text: `${formData.razon_social} ha sido registrado exitosamente`,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                let errorMessage = 'Error al guardar el proveedor';
                if (data.errors) {
                    errorMessage = Object.values(data.errors).flat().join('\n');
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error al guardar',
                    text: errorMessage
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor'
            });
        });
    }
    
    /**
     * Ver detalles del proveedor
     */
    verProveedor(id) {
        if (!this.verificarSweetAlert()) return;
        
        Swal.fire({
            title: 'Cargando información...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });
        
        fetch(`/api/compras/proveedor/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const proveedor = data.data;
                Swal.fire({
                    title: '<div style="background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 1rem; margin: -1rem -1rem 1rem -1rem; border-radius: 8px;"><i class="fas fa-eye"></i> Información del Proveedor</div>',
                    html: `
                        <div style="text-align: left; padding: 1.5rem;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div>
                                    <label style="font-weight: 600; color: #374151; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">
                                        <i class="fas fa-building" style="margin-right: 0.5rem; color: #10b981;"></i>
                                        Razón Social
                                    </label>
                                    <div style="background: #f9fafb; padding: 0.75rem; border-radius: 8px; border: 1px solid #e5e7eb;">
                                        ${proveedor.razon_social || 'No especificado'}
                                    </div>
                                </div>
                                <div>
                                    <label style="font-weight: 600; color: #374151; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">
                                        <i class="fas fa-store" style="margin-right: 0.5rem; color: #10b981;"></i>
                                        Nombre Comercial
                                    </label>
                                    <div style="background: #f9fafb; padding: 0.75rem; border-radius: 8px; border: 1px solid #e5e7eb;">
                                        ${proveedor.nombre_comercial || 'No especificado'}
                                    </div>
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div>
                                    <label style="font-weight: 600; color: #374151; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">
                                        <i class="fas fa-id-card" style="margin-right: 0.5rem; color: #10b981;"></i>
                                        RUC
                                    </label>
                                    <div style="background: #f9fafb; padding: 0.75rem; border-radius: 8px; border: 1px solid #e5e7eb;">
                                        ${proveedor.ruc || 'No especificado'}
                                    </div>
                                </div>
                                <div>
                                    <label style="font-weight: 600; color: #374151; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">
                                        <i class="fas fa-toggle-${proveedor.estado === 'activo' ? 'on' : 'off'}" style="margin-right: 0.5rem; color: ${proveedor.estado === 'activo' ? '#10b981' : '#ef4444'};"></i>
                                        Estado
                                    </label>
                                    <div style="background: #f9fafb; padding: 0.75rem; border-radius: 8px; border: 1px solid #e5e7eb;">
                                        <span style="color: ${proveedor.estado === 'activo' ? '#10b981' : '#ef4444'}; font-weight: 600;">
                                            ${proveedor.estado === 'activo' ? 'Activo' : 'Inactivo'}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div>
                                    <label style="font-weight: 600; color: #374151; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">
                                        <i class="fas fa-phone" style="margin-right: 0.5rem; color: #10b981;"></i>
                                        Teléfono
                                    </label>
                                    <div style="background: #f9fafb; padding: 0.75rem; border-radius: 8px; border: 1px solid #e5e7eb;">
                                        ${proveedor.telefono || 'No especificado'}
                                    </div>
                                </div>
                                <div>
                                    <label style="font-weight: 600; color: #374151; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">
                                        <i class="fas fa-envelope" style="margin-right: 0.5rem; color: #10b981;"></i>
                                        Email
                                    </label>
                                    <div style="background: #f9fafb; padding: 0.75rem; border-radius: 8px; border: 1px solid #e5e7eb;">
                                        ${proveedor.email || 'No especificado'}
                                    </div>
                                </div>
                            </div>
                            <div style="margin-bottom: 1.5rem;">
                                <label style="font-weight: 600; color: #374151; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">
                                    <i class="fas fa-map-marker-alt" style="margin-right: 0.5rem; color: #10b981;"></i>
                                    Dirección
                                </label>
                                <div style="background: #f9fafb; padding: 0.75rem; border-radius: 8px; border: 1px solid #e5e7eb;">
                                    ${proveedor.direccion || 'No especificado'}
                                </div>
                            </div>
                        </div>
                    `,
                    confirmButtonText: 'Cerrar',
                    width: '700px',
                    showCancelButton: false,
                    showDenyButton: false,
                    showCloseButton: false,
                    cancelButtonText: '',
                    denyButtonText: '',
                    customClass: {
                        confirmButton: 'swal2-confirm-green'
                    },
                    buttonsStyling: false,
                    didOpen: () => {
                        // Eliminar botones no deseados
                        setTimeout(() => {
                            const buttons = document.querySelectorAll('.swal2-deny, .swal2-close, .swal2-cancel');
                            buttons.forEach(btn => btn.remove());
                        }, 100);
                        
                        const style = document.createElement('style');
                        style.textContent = `
                            .swal2-deny, .swal2-close, .swal2-cancel {
                                display: none !important;
                                visibility: hidden !important;
                            }
                            .swal2-confirm-green {
                                background: linear-gradient(135deg, #10b981, #059669) !important;
                                color: white !important;
                                border: none !important;
                                padding: 0.75rem 1.5rem !important;
                                border-radius: 8px !important;
                                font-weight: 600 !important;
                            }
                        `;
                        document.head.appendChild(style);
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar la información del proveedor'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor'
            });
        });
    }
    
    /**
     * Editar proveedor
     */
    editarProveedor(id) {
        if (!this.verificarSweetAlert()) return;
        
        Swal.fire({
            title: 'Cargando información...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });
        
        fetch(`/api/compras/proveedor/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const proveedor = data.data;
                this.mostrarModalEditar(proveedor);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar la información del proveedor'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor'
            });
        });
    }
    
    /**
     * Mostrar modal de edición
     */
    mostrarModalEditar(proveedor) {
        Swal.fire({
            title: '<div style="background: linear-gradient(135deg, #e53e3e, #dc2626); color: white; padding: 1rem; margin: -1rem -1rem 1rem -1rem; border-radius: 8px;"><i class="fas fa-edit"></i> Editar Proveedor</div>',
            html: `
                <div style="text-align: left; padding: 1rem;">
                    <input type="hidden" id="edit_proveedor_id" value="${proveedor.id}">
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">
                            <i class="fas fa-building" style="color: #e53e3e; margin-right: 0.5rem;"></i>
                            Razón Social *
                        </label>
                        <input type="text" id="edit_razon_social" value="${proveedor.razon_social || ''}" style="width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem;">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">
                            <i class="fas fa-store" style="color: #e53e3e; margin-right: 0.5rem;"></i>
                            Nombre Comercial
                        </label>
                        <input type="text" id="edit_nombre_comercial" value="${proveedor.nombre_comercial || ''}" style="width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem;">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">
                            <i class="fas fa-id-card" style="color: #e53e3e; margin-right: 0.5rem;"></i>
                            RUC
                        </label>
                        <input type="text" id="edit_ruc" value="${proveedor.ruc || ''}" maxlength="11" style="width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem;">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">
                            <i class="fas fa-phone" style="color: #e53e3e; margin-right: 0.5rem;"></i>
                            Teléfono
                        </label>
                        <input type="text" id="edit_telefono" value="${proveedor.telefono || ''}" style="width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem;">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">
                            <i class="fas fa-envelope" style="color: #e53e3e; margin-right: 0.5rem;"></i>
                            Email
                        </label>
                        <input type="email" id="edit_email" value="${proveedor.email || ''}" style="width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem;">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">
                            <i class="fas fa-map-marker-alt" style="color: #e53e3e; margin-right: 0.5rem;"></i>
                            Dirección
                        </label>
                        <textarea id="edit_direccion" rows="2" style="width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem; resize: none;">${proveedor.direccion || ''}</textarea>
                    </div>
                </div>
            `,
            showCancelButton: true,
            showDenyButton: false,
            showCloseButton: false,
            confirmButtonText: 'Guardar Cambios',
            cancelButtonText: 'Cancelar',
            denyButtonText: '',
            width: '600px',
            customClass: {
                confirmButton: 'swal2-confirm-red',
                cancelButton: 'swal2-cancel-gray'
            },
            buttonsStyling: false,
            allowOutsideClick: false,
            didOpen: () => {
                // Eliminar botones no deseados
                setTimeout(() => {
                    const buttons = document.querySelectorAll('.swal2-deny, .swal2-close');
                    buttons.forEach(btn => btn.remove());
                }, 100);
                
                const style = document.createElement('style');
                style.textContent = `
                    .swal2-deny, .swal2-close {
                        display: none !important;
                        visibility: hidden !important;
                    }
                    .swal2-confirm-red {
                        background: linear-gradient(135deg, #e53e3e, #dc2626) !important;
                        color: white !important;
                        border: none !important;
                        padding: 0.75rem 1.5rem !important;
                        border-radius: 8px !important;
                        font-weight: 600 !important;
                        margin: 0 0.5rem !important;
                    }
                    .swal2-cancel-gray {
                        background: linear-gradient(135deg, #6b7280, #4b5563) !important;
                        color: white !important;
                        border: none !important;
                        padding: 0.75rem 1.5rem !important;
                        border-radius: 8px !important;
                        font-weight: 600 !important;
                        margin: 0 0.5rem !important;
                    }
                `;
                document.head.appendChild(style);
            }
        }).then((result) => {
            if (result.isConfirmed) {
                this.actualizarProveedor();
            }
        });
    }
    
    /**
     * Actualizar proveedor
     */
    actualizarProveedor() {
        const id = document.getElementById('edit_proveedor_id').value;
        const formData = {
            razon_social: document.getElementById('edit_razon_social').value.trim(),
            nombre_comercial: document.getElementById('edit_nombre_comercial').value.trim(),
            ruc: document.getElementById('edit_ruc').value.trim(),
            telefono: document.getElementById('edit_telefono').value.trim(),
            email: document.getElementById('edit_email').value.trim(),
            direccion: document.getElementById('edit_direccion').value.trim()
        };
        
        // Validaciones
        if (!formData.razon_social) {
            Swal.fire({
                icon: 'error',
                title: 'Campo requerido',
                text: 'La razón social es obligatoria'
            });
            return;
        }
        
        if (formData.ruc && (formData.ruc.length !== 11 || !/^\d+$/.test(formData.ruc))) {
            Swal.fire({
                icon: 'error',
                title: 'RUC inválido',
                text: 'El RUC debe tener exactamente 11 dígitos numéricos'
            });
            return;
        }
        
        // Mostrar loading
        Swal.fire({
            title: 'Actualizando proveedor...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });
        
        // Enviar datos
        fetch(`/compras/proveedores/${id}/actualizar`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Proveedor actualizado!',
                    text: `${formData.razon_social} ha sido actualizado exitosamente`,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                let errorMessage = 'Error al actualizar el proveedor';
                if (data.errors) {
                    errorMessage = Object.values(data.errors).flat().join('\n');
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error al actualizar',
                    text: errorMessage
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor'
            });
        });
    }
    
    /**
     * Cambiar estado del proveedor
     */
    cambiarEstado(id, nuevoEstado) {
        if (!this.verificarSweetAlert()) return;
        
        const accion = nuevoEstado === 'activo' ? 'activar' : 'desactivar';
        const colorHeader = nuevoEstado === 'activo' ? '#10b981' : '#e53e3e';
        const colorSecondary = nuevoEstado === 'activo' ? '#059669' : '#dc2626';
        const icono = nuevoEstado === 'activo' ? 'fas fa-check-circle' : 'fas fa-times-circle';
        const titulo = nuevoEstado === 'activo' ? 'Activar Proveedor' : 'Desactivar Proveedor';
        
        Swal.fire({
            title: `<div style="background: linear-gradient(135deg, ${colorHeader}, ${colorSecondary}); color: white; padding: 1rem; margin: -1rem -1rem 1rem -1rem; border-radius: 8px;"><i class="${icono}"></i> ${titulo}</div>`,
            html: `
                <div style="padding: 1rem;">
                    <div style="text-align: center; margin-bottom: 2rem;">
                        <p style="color: #374151; font-size: 1.1rem; margin-bottom: 1.5rem; font-weight: 500;">
                            ¿Está seguro que desea <strong style="color: ${colorHeader};">${accion}</strong> este proveedor?
                        </p>
                        <div style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); padding: 1.5rem; border-radius: 12px; border-left: 4px solid ${colorHeader};">
                            <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                                <i class="fas fa-info-circle" style="color: ${colorHeader}; font-size: 1.25rem; margin-top: 0.125rem;"></i>
                                <div style="text-align: left;">
                                    <h4 style="color: #374151; margin: 0 0 0.5rem 0; font-size: 0.95rem; font-weight: 600;">
                                        ${nuevoEstado === 'activo' ? 'Consecuencias de activar:' : 'Consecuencias de desactivar:'}
                                    </h4>
                                    <p style="color: #6b7280; font-size: 0.875rem; margin: 0; line-height: 1.5;">
                                        ${nuevoEstado === 'activo' 
                                            ? '• El proveedor estará disponible para nuevas compras<br>• Aparecerá en todas las listas de selección<br>• Podrá recibir órdenes de compra' 
                                            : '• No podrá realizar nuevas compras con este proveedor<br>• Se ocultará de las listas de selección<br>• Se mantendrá todo el historial de compras previas'}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `,
            showCancelButton: true,
            showDenyButton: false,
            showCloseButton: false,
            confirmButtonText: titulo,
            cancelButtonText: 'Cancelar',
            denyButtonText: '',
            width: '520px',
            customClass: {
                confirmButton: 'swal2-confirm-estado',
                cancelButton: 'swal2-cancel-gray'
            },
            buttonsStyling: false,
            allowOutsideClick: false,
            didOpen: () => {
                // Eliminar botones no deseados
                setTimeout(() => {
                    const buttons = document.querySelectorAll('.swal2-deny, .swal2-close');
                    buttons.forEach(btn => btn.remove());
                }, 100);
                
                const style = document.createElement('style');
                style.textContent = `
                    .swal2-deny, .swal2-close {
                        display: none !important;
                        visibility: hidden !important;
                    }
                    .swal2-confirm-estado {
                        background: linear-gradient(135deg, ${colorHeader}, ${colorSecondary}) !important;
                        color: white !important;
                        border: none !important;
                        padding: 0.75rem 1.5rem !important;
                        border-radius: 8px !important;
                        font-weight: 600 !important;
                        margin: 0 0.5rem !important;
                    }
                    .swal2-cancel-gray {
                        background: linear-gradient(135deg, #6b7280, #4b5563) !important;
                        color: white !important;
                        border: none !important;
                        padding: 0.75rem 1.5rem !important;
                        border-radius: 8px !important;
                        font-weight: 600 !important;
                        margin: 0 0.5rem !important;
                    }
                `;
                document.head.appendChild(style);
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loading
                Swal.fire({
                    title: `${accion.charAt(0).toUpperCase() + accion.slice(1)}ando proveedor...`,
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => Swal.showLoading()
                });
                
                // Enviar cambio de estado
                fetch(`/compras/proveedores/${id}/cambiar-estado`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: `¡Proveedor ${nuevoEstado === 'activo' ? 'activado' : 'desactivado'}!`,
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Error al cambiar el estado'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor'
                    });
                });
            }
        });
    }
    
    /**
     * Aplicar filtros de búsqueda
     */
    aplicarFiltros() {
        const searchTerm = document.getElementById('buscarProveedor').value.toLowerCase();
        const selectedEstado = document.getElementById('filtroEstado').value;
        const dataRows = document.querySelectorAll('#tablaProveedores tbody tr.proveedores-data-row');
        const noResultsRow = document.getElementById('noResultsRow');
        const noResultsText = document.getElementById('noResultsText');
        
        let visibleRows = 0;
        let filtroAplicado = false;
        
        dataRows.forEach(row => {
            let mostrarFila = true;
            
            if (searchTerm) {
                filtroAplicado = true;
                const text = row.textContent.toLowerCase();
                if (!text.includes(searchTerm)) {
                    mostrarFila = false;
                }
            }
            
            if (selectedEstado && mostrarFila) {
                filtroAplicado = true;
                const estadoCell = row.querySelector('td:nth-child(5)');
                if (estadoCell) {
                    const estadoText = estadoCell.textContent.toLowerCase();
                    if (selectedEstado === 'activo' && !estadoText.includes('activo')) {
                        mostrarFila = false;
                    } else if (selectedEstado === 'inactivo' && !estadoText.includes('inactivo')) {
                        mostrarFila = false;
                    }
                }
            }
            
            if (mostrarFila) {
                row.style.display = '';
                visibleRows++;
            } else {
                row.style.display = 'none';
            }
        });
        
        if (visibleRows === 0 && filtroAplicado) {
            let mensaje = 'No hay proveedores que coincidan con los criterios de búsqueda';
            
            if (searchTerm && selectedEstado) {
                mensaje = `No se encontraron proveedores "${searchTerm}" con estado ${selectedEstado}`;
            } else if (searchTerm) {
                mensaje = `No se encontraron resultados para "${searchTerm}"`;
            } else if (selectedEstado) {
                mensaje = `No hay proveedores con estado ${selectedEstado}`;
            }
            
            noResultsText.textContent = mensaje;
            noResultsRow.style.display = 'table-row';
        } else {
            noResultsRow.style.display = 'none';
        }
    }
    
    /**
     * Limpiar todos los filtros
     */
    limpiarTodosFiltros() {
        document.getElementById('buscarProveedor').value = '';
        document.getElementById('filtroEstado').value = '';
        document.getElementById('perPageSelect').value = '10';
        
        const dataRows = document.querySelectorAll('#tablaProveedores tbody tr.proveedores-data-row');
        const noResultsRow = document.getElementById('noResultsRow');
        
        dataRows.forEach(row => {
            row.style.display = '';
        });
        
        noResultsRow.style.display = 'none';
    }
}

// Instanciar el manager cuando el DOM esté listo
let proveedoresManager;

document.addEventListener('DOMContentLoaded', function() {
    proveedoresManager = new ProveedoresManager();
});

// Funciones globales para mantener compatibilidad con los onclick en HTML
window.abrirModalAgregar = function() {
    if (proveedoresManager) {
        proveedoresManager.abrirModalAgregar();
    }
};

window.verProveedor = function(id) {
    if (proveedoresManager) {
        proveedoresManager.verProveedor(id);
    }
};

window.editarProveedor = function(id) {
    if (proveedoresManager) {
        proveedoresManager.editarProveedor(id);
    }
};

window.cambiarEstado = function(id, estado) {
    if (proveedoresManager) {
        proveedoresManager.cambiarEstado(id, estado);
    }
};

window.limpiarTodosFiltros = function() {
    if (proveedoresManager) {
        proveedoresManager.limpiarTodosFiltros();
    }
};

console.log('✅ Script de proveedores cargado correctamente'); 