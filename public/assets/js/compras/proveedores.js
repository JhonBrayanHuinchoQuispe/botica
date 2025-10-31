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

        // Toggle de estado (activo/inactivo)
        document.addEventListener('change', (e) => {
            const toggle = e.target.closest('.proveedor-status-toggle');
            if (!toggle) return;
            const proveedorId = toggle.dataset.proveedorId;
            if (!proveedorId) return;
            // Bloquear mientras procesa
            toggle.disabled = true;
            const fila = document.querySelector(`.proveedores-data-row[data-proveedor-id="${proveedorId}"]`);
            const estadoPrevio = toggle.checked ? 'inactivo' : 'activo'; // antes del cambio
            this.toggleEstadoProveedor(proveedorId, toggle, fila, estadoPrevio);
        });
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
            razon_social: document.getElementById('prov_razon_social')?.value.trim() || '',
            nombre_comercial: document.getElementById('prov_nombre_comercial')?.value.trim() || '',
            ruc: document.getElementById('prov_ruc')?.value.trim() || '',
            telefono: document.getElementById('prov_telefono')?.value.trim() || '',
            email: document.getElementById('prov_email')?.value.trim() || '',
            direccion: document.getElementById('prov_direccion')?.value.trim() || ''
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
        
        // Ocultar el modal mientras se procesa
        try { closeProveedorModal(); } catch(e) {}
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
        const id = document.getElementById('prov_id')?.value;
        const formData = {
            razon_social: document.getElementById('prov_razon_social')?.value.trim(),
            nombre_comercial: document.getElementById('prov_nombre_comercial')?.value.trim(),
            ruc: document.getElementById('prov_ruc')?.value.trim(),
            telefono: document.getElementById('prov_telefono')?.value.trim(),
            email: document.getElementById('prov_email')?.value.trim(),
            direccion: document.getElementById('prov_direccion')?.value.trim()
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
        
        // Ocultar el modal mientras se procesa
        try { closeProveedorModal(); } catch(e) {}
        // Mostrar loading
        Swal.fire({
            title: 'Actualizando proveedor...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });
        
        // Enviar datos
        fetch(`/compras/proveedores/${id}`, {
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
     * Toggle rápido de estado desde el switch
     */
    toggleEstadoProveedor(id, toggleEl, filaEl, estadoPrevio) {
        fetch(`/compras/proveedores/${id}/cambiar-estado`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) throw new Error(data.message || 'No se pudo cambiar estado');
            const nuevoEstado = (data.data && data.data.estado) ? data.data.estado : (toggleEl.checked ? 'activo' : 'inactivo');
            // Ajustar toggle según backend
            toggleEl.checked = nuevoEstado === 'activo';
            // Actualizar badge
            if (filaEl) {
                const estadoBadge = filaEl.querySelector('.estado-badge');
                if (estadoBadge) {
                    if (nuevoEstado === 'activo') {
                        estadoBadge.classList.remove('proveedores-badge-secondary');
                        estadoBadge.classList.add('proveedores-badge-success');
                        estadoBadge.innerHTML = '<iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon> Activo';
                        filaEl.classList.remove('opacity-75');
                    } else {
                        estadoBadge.classList.remove('proveedores-badge-success');
                        estadoBadge.classList.add('proveedores-badge-secondary');
                        estadoBadge.innerHTML = '<iconify-icon icon="solar:close-circle-bold-duotone"></iconify-icon> Inactivo';
                        filaEl.classList.add('opacity-75');
                    }
                }
            }
        })
        .catch(err => {
            console.error(err);
            // Revertir toggle
            toggleEl.checked = (estadoPrevio === 'activo');
            Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'No se pudo cambiar estado del proveedor' });
        })
        .finally(() => {
            toggleEl.disabled = false;
        });
    }
    
    /**
     * Eliminar proveedor
     */
    eliminarProveedor(id) {
        if (!this.verificarSweetAlert()) return;
        
        // Obtener datos del proveedor desde la fila de la tabla
        const fila = document.querySelector(`tr.proveedores-data-row[data-proveedor-id="${id}"]`);
        if (!fila) {
            console.error('No se encontró la fila del proveedor');
            return;
        }
        
        const razonSocial = fila.querySelector('td:nth-child(2)')?.textContent.trim() || '';
        const ruc = fila.querySelector('td:nth-child(3)')?.textContent.trim() || '';
        const contactoCell = fila.querySelector('td:nth-child(4)');
        let telefono = '';
        let email = '';
        if (contactoCell) {
            const items = contactoCell.querySelectorAll('.proveedores-contact-item');
            if (items[0]) telefono = items[0].textContent.trim();
            if (items[1]) email = items[1].textContent.trim();
        }
        
        Swal.fire({
            title: '¿Eliminar proveedor?',
            html: `
                <div style="text-align: left; padding: 1rem;">
                    <p style="margin-bottom: 1rem; color: #dc2626; font-weight: 600;">
                        Esta acción eliminará permanentemente el proveedor y no se puede deshacer.
                    </p>
                    <div style="background: #f9fafb; padding: 1rem; border-radius: 8px; border-left: 4px solid #3b82f6;">
                        <p style="margin: 0.25rem 0;"><strong>Razón Social:</strong> ${razonSocial}</p>
                        ${ruc ? `<p style=\"margin: 0.25rem 0;\"><strong>RUC:</strong> ${ruc}</p>` : ''}
                        ${telefono ? `<p style=\"margin: 0.25rem 0;\"><strong>Teléfono:</strong> ${telefono}</p>` : ''}
                        ${email ? `<p style=\"margin: 0.25rem 0;\"><strong>Email:</strong> ${email}</p>` : ''}
                        ${!telefono && !email ? `<p style=\"margin: 0.25rem 0; color:#6b7280;\"><strong>Contacto:</strong> Sin contacto</p>` : ''}
                    </div>
                    <p style="margin-top: 1rem; color: #6b7280; font-size: 0.9rem;">
                        Si el proveedor tiene registros asociados, no podrá ser eliminado.
                    </p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            showDenyButton: false,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            buttonsStyling: false,
            didOpen: () => {
                const c = Swal.getConfirmButton();
                const k = Swal.getCancelButton();
                const d = Swal.getDenyButton();
                // Eliminar el botón "No" si por alguna configuración global aparece
                if (d) { d.remove(); }
                if (c) {
                    c.style.opacity = '1';
                    c.style.visibility = 'visible';
                    c.style.backgroundColor = '#dc2626';
                    c.style.color = '#fff';
                    c.style.border = 'none';
                    c.style.borderRadius = '8px';
                    c.style.padding = '0.625rem 1rem';
                    c.style.fontWeight = '600';
                    c.style.boxShadow = 'none';
                    c.style.cursor = 'pointer';
                    c.style.marginRight = '0.75rem';
                }
                if (k) {
                    k.style.opacity = '1';
                    k.style.visibility = 'visible';
                    k.style.backgroundColor = '#6b7280';
                    k.style.color = '#fff';
                    k.style.border = 'none';
                    k.style.borderRadius = '8px';
                    k.style.padding = '0.625rem 1rem';
                    k.style.fontWeight = '600';
                    k.style.boxShadow = 'none';
                    k.style.cursor = 'pointer';
                    k.style.marginLeft = '0.75rem';
                }
                const actions = Swal.getActions();
                if (actions) {
                    actions.style.gap = '0.75rem';
                    actions.style.marginTop = '1rem';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loader
                Swal.fire({
                    title: 'Eliminando proveedor...',
                    html: '<div class="spinner-border text-danger" role="status"><span class="sr-only">Cargando...</span></div>',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'swal2-popup-custom'
                    }
                });
                
                // Realizar petición DELETE
                fetch(`/compras/proveedores/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '¡Eliminado!',
                            text: data.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false,
                            customClass: {
                                popup: 'swal2-popup-custom'
                            }
                        }).then(() => {
                            // Recargar la página para actualizar la lista
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message,
                            icon: 'error',
                            confirmButtonText: 'Entendido',
                            customClass: {
                                popup: 'swal2-popup-custom',
                                confirmButton: 'swal2-confirm-custom'
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Ocurrió un error al eliminar el proveedor',
                        icon: 'error',
                        confirmButtonText: 'Entendido',
                        customClass: {
                            popup: 'swal2-popup-custom',
                            confirmButton: 'swal2-confirm-custom'
                        }
                    });
                });
            }
        });
    }
    
    /**
     * Aplicar filtros de búsqueda y estado
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
    // Abrir modal profesional (tema rojo)
    if (document.getElementById('proveedorModal')) return;
    openCreateProveedorModal();
};

window.verProveedor = function(id) {
    if (document.getElementById('proveedorModal')) return;
    Swal.fire({ title: 'Cargando información...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });
    fetch(`/api/compras/proveedor/${id}`)
      .then(r => r.json())
      .then(d => { Swal.close(); if (d.success) openViewProveedorModal(d.data); else Swal.fire({icon:'error', title:'Error', text:d.message || 'No se pudo cargar proveedor'}); })
      .catch(_ => { Swal.close(); Swal.fire({icon:'error', title:'Error de conexión', text:'No se pudo conectar con el servidor'}); });
};

window.editarProveedor = function(id) {
    if (document.getElementById('proveedorModal')) return;
    Swal.fire({ title: 'Cargando información...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });
    fetch(`/api/compras/proveedor/${id}`)
      .then(r => r.json())
      .then(d => { Swal.close(); if (d.success) openEditProveedorModal(d.data); else Swal.fire({icon:'error', title:'Error', text:d.message || 'No se pudo cargar proveedor'}); })
      .catch(_ => { Swal.close(); Swal.fire({icon:'error', title:'Error de conexión', text:'No se pudo conectar con el servidor'}); });
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

window.eliminarProveedor = function(id) {
    if (proveedoresManager) {
        proveedoresManager.eliminarProveedor(id);
    }
};

console.log('✅ Script de proveedores cargado correctamente');

// ================= Modal profesional reutilizado (roles) =================
function closeProveedorModal() {
    const m = document.getElementById('proveedorModal');
    if (m) m.remove();
    document.body.style.overflow = '';
}

function openCreateProveedorModal() {
    const html = `
    <div id="proveedorModal" class="modal-profesional">
      <div class="modal-profesional-container tema-rojo">
        <div class="header-profesional">
          <div class="header-content">
            <div class="header-left">
              <div class="header-icon icon-normal"><iconify-icon icon="solar:add-circle-bold-duotone"></iconify-icon></div>
              <div class="header-text"><h3>Nuevo Proveedor</h3></div>
            </div>
            <button type="button" class="btn-close" onclick="closeProveedorModal()"><iconify-icon icon="heroicons:x-mark"></iconify-icon></button>
          </div>
        </div>
        <div class="modal-content-profesional">
          <div class="seccion-form seccion-azul">
            <div class="seccion-header"><div class="seccion-icon icon-azul"><iconify-icon icon="solar:info-circle-bold-duotone"></iconify-icon></div><div class="seccion-titulo"><h3>Información del Proveedor</h3><p>Datos básicos</p></div></div>
            <div class="grid-campos columnas-2">
              <div class="campo-grupo campo-completo"><label class="campo-label"><iconify-icon icon="solar:buildings-bold-duotone" class="label-icon"></iconify-icon> Razón Social *</label><input type="text" id="prov_razon_social" class="campo-input" placeholder="Ej: Distribuidora Médica S.A.C."></div>
              <div class="campo-grupo campo-completo"><label class="campo-label"><iconify-icon icon="solar:store-2-bold-duotone" class="label-icon"></iconify-icon> Nombre Comercial</label><input type="text" id="prov_nombre_comercial" class="campo-input" placeholder="Ej: Dismesa"></div>
              <div class="campo-grupo"><label class="campo-label"><iconify-icon icon="solar:id-card-bold-duotone" class="label-icon"></iconify-icon> RUC</label><input type="text" id="prov_ruc" maxlength="11" class="campo-input" placeholder="20123456789"></div>
              <div class="campo-grupo"><label class="campo-label"><iconify-icon icon="solar:phone-bold-duotone" class="label-icon"></iconify-icon> Teléfono</label><input type="text" id="prov_telefono" class="campo-input" placeholder="01-1234567"></div>
              <div class="campo-grupo campo-completo"><label class="campo-label"><iconify-icon icon="solar:letter-bold-duotone" class="label-icon"></iconify-icon> Email</label><input type="email" id="prov_email" class="campo-input" placeholder="contacto@empresa.com"></div>
              <div class="campo-grupo campo-completo"><label class="campo-label"><iconify-icon icon="solar:map-point-bold-duotone" class="label-icon"></iconify-icon> Dirección</label><textarea id="prov_direccion" rows="3" class="campo-input" style="min-height: 80px;" placeholder="Dirección completa"></textarea></div>
            </div>
          </div>
        </div>
        <div class="footer-profesional"><div class="footer-botones"><button type="button" class="btn-cancelar" onclick="closeProveedorModal()">Cancelar</button><button type="button" class="btn-guardar" id="btnGuardarProveedor"><iconify-icon icon="solar:disk-bold-duotone"></iconify-icon> Guardar Proveedor</button></div></div>
      </div>
    </div>`;
    document.body.insertAdjacentHTML('beforeend', html);
    document.body.style.overflow = 'hidden';
    document.getElementById('btnGuardarProveedor').addEventListener('click', () => {
        if (!proveedoresManager) proveedoresManager = new ProveedoresManager();
        proveedoresManager.guardarProveedor();
    });
}

function openEditProveedorModal(p) {
    const html = `
    <div id="proveedorModal" class="modal-profesional">
      <div class="modal-profesional-container tema-verde">
        <div class="header-profesional"><div class="header-content"><div class="header-left"><div class="header-icon icon-normal"><iconify-icon icon="solar:pen-bold-duotone"></iconify-icon></div><div class="header-text"><h3>Editar Proveedor</h3></div></div><button type="button" class="btn-close" onclick="closeProveedorModal()"><iconify-icon icon="heroicons:x-mark"></iconify-icon></button></div></div>
        <div class="modal-content-profesional">
          <div class="seccion-form seccion-azul">
            <div class="seccion-header"><div class="seccion-icon icon-azul"><iconify-icon icon="solar:info-circle-bold-duotone"></iconify-icon></div><div class="seccion-titulo"><h3>Información del Proveedor</h3><p>Datos básicos</p></div></div>
            <div class="grid-campos columnas-2">
              <input type="hidden" id="prov_id" value="${p.id}">
              <div class="campo-grupo campo-completo"><label class="campo-label">Razón Social *</label><input type="text" id="prov_razon_social" class="campo-input" value="${p.razon_social||''}"></div>
              <div class="campo-grupo campo-completo"><label class="campo-label">Nombre Comercial</label><input type="text" id="prov_nombre_comercial" class="campo-input" value="${p.nombre_comercial||''}"></div>
              <div class="campo-grupo"><label class="campo-label">RUC</label><input type="text" id="prov_ruc" maxlength="11" class="campo-input" value="${p.ruc||''}"></div>
              <div class="campo-grupo"><label class="campo-label">Teléfono</label><input type="text" id="prov_telefono" class="campo-input" value="${p.telefono||''}"></div>
              <div class="campo-grupo campo-completo"><label class="campo-label">Email</label><input type="email" id="prov_email" class="campo-input" value="${p.email||''}"></div>
              <div class="campo-grupo campo-completo"><label class="campo-label">Dirección</label><textarea id="prov_direccion" rows="3" class="campo-input" style="min-height: 80px;">${p.direccion||''}</textarea></div>
            </div>
          </div>
        </div>
        <div class="footer-profesional"><div class="footer-botones"><button type="button" class="btn-cancelar" onclick="closeProveedorModal()">Cancelar</button><button type="button" class="btn-guardar" id="btnActualizarProveedor"><iconify-icon icon="solar:disk-bold-duotone"></iconify-icon> Actualizar Proveedor</button></div></div>
      </div>
    </div>`;
    document.body.insertAdjacentHTML('beforeend', html);
    document.body.style.overflow = 'hidden';
    document.getElementById('btnActualizarProveedor').addEventListener('click', () => {
        if (!proveedoresManager) proveedoresManager = new ProveedoresManager();
        proveedoresManager.actualizarProveedor();
    });
}

function openViewProveedorModal(p) {
    const html = `
    <div id="proveedorModal" class="modal-profesional">
      <div class="modal-profesional-container tema-azul">
        <div class="header-profesional"><div class="header-content"><div class="header-left"><div class="header-icon icon-normal"><iconify-icon icon="solar:eye-bold-duotone"></iconify-icon></div><div class="header-text"><h3>Información del Proveedor</h3></div></div><button type="button" class="btn-close" onclick="closeProveedorModal()"><iconify-icon icon="heroicons:x-mark"></iconify-icon></button></div></div>
        <div class="modal-content-profesional">
          <div class="seccion-form seccion-azul">
            <div class="seccion-header"><div class="seccion-icon icon-azul"><iconify-icon icon="solar:info-circle-bold-duotone"></iconify-icon></div><div class="seccion-titulo"><h3>Datos del Proveedor</h3><p>Resumen</p></div></div>
            <div class="grid-campos columnas-2">
              <div class="campo-grupo campo-completo"><label class="campo-label">Razón Social</label><div class="field-pill">${p.razon_social||'-'}</div></div>
              <div class="campo-grupo campo-completo"><label class="campo-label">Nombre Comercial</label><div class="field-pill">${p.nombre_comercial||'-'}</div></div>
              <div class="campo-grupo"><label class="campo-label">RUC</label><div class="field-pill">${p.ruc||'-'}</div></div>
              <div class="campo-grupo"><label class="campo-label">Teléfono</label><div class="field-pill">${p.telefono||'-'}</div></div>
              <div class="campo-grupo campo-completo"><label class="campo-label">Email</label><div class="field-pill">${p.email||'-'}</div></div>
              <div class="campo-grupo campo-completo"><label class="campo-label">Dirección</label><div class="field-pill">${p.direccion||'-'}</div></div>
            </div>
          </div>
        </div>
        <div class="footer-profesional"><div class="footer-botones"><button type="button" class="btn-cancelar" onclick="closeProveedorModal()">Entendido</button><button type="button" class="btn-guardar" onclick="closeProveedorModal(); editarProveedor(${p.id});">Editar</button></div></div>
      </div>
    </div>`;
    document.body.insertAdjacentHTML('beforeend', html);
    document.body.style.overflow = 'hidden';
}