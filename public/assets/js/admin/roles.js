/* GESTIÓN DE ROLES Y PERMISOS - JAVASCRIPT */

// Variables globales
let currentRoleId = null;
let isEditMode = false;
let rolesData = [];

// Configuración CSRF
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Event Listeners al cargar la página
// Evitar dobles toggles simultáneos
const togglingRoles = new Set();

document.addEventListener('DOMContentLoaded', function() {
    initializeRoleManagement();
});

// Helpers de preloader para esta vista
function showLoading(label = 'Cargando datos...') {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = 'flex';
        const textEl = overlay.querySelector('.loading-text');
        if (textEl) textEl.textContent = label;
    }
}
function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.style.display = 'none';
}

/* INICIALIZACIÓN */
function initializeRoleManagement() {
    setupEventListeners();
    loadRolesData();
    setupPermissionControls();
}

function setupEventListeners() {
    // Formulario de rol
    const roleForm = document.getElementById('roleForm');
    if (roleForm) {
        roleForm.addEventListener('submit', handleRoleFormSubmit);
    }

    // Filtros
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(filterRoles, 300));
    }

    // Cerrar modal con escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeRoleModal();
        }
    });

    // Cerrar modal al hacer clic fuera
    const modal = document.getElementById('roleModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeRoleModal();
            }
        });
    }

    // Color presets
    setupColorPresets();

    // Toggle de estado en la tabla de roles
    document.addEventListener('change', function(e) {
        const toggle = e.target.closest('.role-status-toggle');
        if (toggle) {
            const roleId = toggle.dataset.roleId;
            if (!roleId) return;
            if (togglingRoles.has(roleId)) return; // bloquear repetidos
            togglingRoles.add(roleId);
            const prevChecked = !toggle.checked; // estado previo antes del cambio
            toggle.disabled = true; // prevenir más clics mientras se procesa
            toggleRoleStatus(roleId, toggle, prevChecked);
        }
    });

    // Vincular comportamiento de Dashboard colapsado
    document.addEventListener('change', function(e) {
        const chk = e.target.closest('.permission-checkbox');
        if (!chk) return;
        const permName = chk.dataset.name || '';
        if (permName.startsWith('dashboard.')) {
            const hidden = document.querySelectorAll('.permission-checkbox[data-dashboard-hidden="true"]');
            hidden.forEach(h => { h.checked = chk.checked; });
        }
    });

    // Restringir config.* a Administrador/Dueño/Gerente
    const displayInput = document.getElementById('display_name');
    function updateConfigPermissionsAvailability() {
        const roleName = (displayInput?.value || '').toLowerCase().trim();
        const allowed = ['administrador', 'dueño', 'dueno', 'gerente'];
        const isAllowed = allowed.includes(roleName);
        document.querySelectorAll('.permission-checkbox').forEach(ch => {
            const name = (ch.dataset.name || '').toLowerCase();
            if (name.startsWith('config.')) {
                ch.disabled = !isAllowed;
                const card = ch.closest('.permiso-card');
                if (card) {
                    card.style.opacity = isAllowed ? '1' : '0.6';
                }
                if (!isAllowed) ch.checked = false;
            }
        });
    }
    if (displayInput) {
        displayInput.addEventListener('input', updateConfigPermissionsAvailability);
        setTimeout(updateConfigPermissionsAvailability, 200);
    }
}

/* GESTIÓN DE DATOS */
function loadRolesData() {
    const roleRows = document.querySelectorAll('.role-row');
    rolesData = Array.from(roleRows).map(row => {
        const roleData = {
            id: row.dataset.roleId,
            name: row.querySelector('.role-name')?.textContent.trim(),
            description: row.querySelector('.description-text')?.textContent.trim() || '',
            element: row
        };
        return roleData;
    });
}

/* FILTROS Y BÚSQUEDA */
function filterRoles() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';

    rolesData.forEach(role => {
        let shouldShow = true;

        // Filtro de búsqueda (buscar en nombre y descripción)
        if (searchTerm) {
            const searchableText = (role.name + ' ' + role.description).toLowerCase();
            shouldShow = shouldShow && searchableText.includes(searchTerm);
        }

        // Mostrar/ocultar fila
        role.element.style.display = shouldShow ? '' : 'none';
    });

    // Mostrar mensaje si no hay resultados
    updateEmptyState();
}

function updateEmptyState() {
    const visibleRoles = rolesData.filter(role => role.element.style.display !== 'none');
    const tbody = document.querySelector('.roles-table tbody');
    const existingEmptyState = tbody.querySelector('.filter-empty-state');

    if (visibleRoles.length === 0 && rolesData.length > 0) {
        if (!existingEmptyState) {
            const emptyRow = document.createElement('tr');
            emptyRow.className = 'filter-empty-state';
            emptyRow.innerHTML = `
                <td colspan="6" class="empty-state">
                    <div class="empty-content">
                        <iconify-icon icon="solar:shield-user-bold-duotone" class="empty-icon"></iconify-icon>
                        <h3>No se encontraron roles</h3>
                        <p>Intenta con otros términos de búsqueda</p>
                        <button type="button" class="btn-action-elegant btn-primary" onclick="clearFilters()">
                            <iconify-icon icon="solar:arrow-path-bold-duotone"></iconify-icon>
                            <span>Limpiar Filtros</span>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(emptyRow);
        }
    } else if (existingEmptyState) {
        existingEmptyState.remove();
    }
}

function clearFilters() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.value = '';
        filterRoles();
    }
}

/* MODAL DE ROL */
function openCreateRoleModal() {
    isEditMode = false;
    currentRoleId = null;
    
    // Resetear formulario
    resetRoleForm();
    
    // Configurar modal para crear
    document.getElementById('modalTitle').textContent = 'Crear Nuevo Rol';
    document.getElementById('modalIcon').setAttribute('icon', 'solar:shield-plus-bold-duotone');
    document.getElementById('saveButtonText').textContent = 'Crear Rol';
    
    // Mostrar modal profesional
    const modal = document.getElementById('roleModal');
    modal.classList.remove('hidden');
    // Asegurar tema rojo para crear
    const container = modal.querySelector('.modal-profesional-container');
    if (container) {
        container.classList.remove('tema-verde');
    }
    
    // Inicializar progress bar
    updateProgressBar();
    
    // Inicializar estado de botones (mostrar solo "Seleccionar Todo")
    updateControlButtonsVisibility(0);
    
    // Focus en primer campo
    setTimeout(() => {
        document.getElementById('display_name').focus();
    }, 100);
}

async function editRole(roleId) {
    try {
        showLoading('Cargando datos para editar...');
        const response = await fetch(`/admin/roles/${roleId}/editar`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();
        
        if (data.success) {
            if (data.role.is_protected) {
                showAlert('warning', 'Rol Protegido', 'Este rol no puede ser modificado');
                return;
            }
            
    isEditMode = true;
    currentRoleId = roleId;
    
    // Configurar modal para editar
    document.getElementById('modalTitle').textContent = 'Editar Rol';
    document.getElementById('modalIcon').setAttribute('icon', 'solar:shield-edit-bold-duotone');
    document.getElementById('saveButtonText').textContent = 'Actualizar Rol';
    
    // Cargar datos del rol
            populateRoleFormFromEdit(data.role);
    
    // Mostrar modal profesional
    const modal = document.getElementById('roleModal');
    modal.classList.remove('hidden');
    // Aplicar tema verde para editar
    const container = modal.querySelector('.modal-profesional-container');
    if (container) {
        container.classList.add('tema-verde');
    }
    
    // Inicializar progress bar
    updateProgressBar();
            
            // Focus en primer campo
            setTimeout(() => {
                document.getElementById('display_name').focus();
            }, 100);
            hideLoading();
        } else {
            throw new Error(data.message || 'Error al cargar datos del rol');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error', 'No se pudieron cargar los datos del rol');
        hideLoading();
    }
}

function closeRoleModal() {
    const modal = document.getElementById('roleModal');
    modal.classList.add('hidden');
    resetRoleForm();
    currentRoleId = null;
    isEditMode = false;
    
    // Resetear progress bar
    const progressBar = document.getElementById('roleProgressBar');
    if (progressBar) {
        progressBar.style.width = '0%';
    }
}

function resetRoleForm() {
    const form = document.getElementById('roleForm');
    form.reset();
    
    // Resetear color
    document.getElementById('color').value = '#e53e3e';
    
    // Limpiar errores de validación
    clearFormErrors();
    
    // Desmarcar permisos
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
    permissionCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // Actualizar contador
    updatePermissionsCounter();
}

/* CARGA DE DATOS DEL ROL */
async function loadRoleData(roleId) {
    try {
        showFormLoading(true);
        
        const response = await fetch(`/admin/roles/${roleId}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            }
        });

        const data = await response.json();
        
        if (data.success) {
            populateRoleForm(data.role);
        } else {
            throw new Error(data.message || 'Error al cargar datos del rol');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error', 'No se pudieron cargar los datos del rol');
    } finally {
        showFormLoading(false);
    }
}

function populateRoleForm(role) {
    // Datos básicos
    document.getElementById('roleId').value = role.id;
    document.getElementById('display_name').value = role.display_name || '';
    document.getElementById('description').value = role.description || '';
    document.getElementById('color').value = role.color || '#e53e3e';
    
    // Permisos
    if (role.permissions && role.permissions.length > 0) {
        const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
        permissionCheckboxes.forEach(checkbox => {
            const permissionId = checkbox.value;
            checkbox.checked = role.permissions.some(permission => permission.id == permissionId);
        });
    }
    
    // Actualizar contador
    updatePermissionsCounter();
}

/* ENVÍO DE FORMULARIO */
async function handleRoleFormSubmit(e) {
    e.preventDefault();
    
    if (!validateRoleForm()) {
        return;
    }
    
    const formData = new FormData(e.target);
    // Derivar 'name' (técnico) desde 'display_name' (visible)
    const displayName = document.getElementById('display_name')?.value || '';
    const slug = displayName
        .toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // quitar acentos
        .replace(/[^a-z0-9\s-]/g, '') // caracteres no permitidos
        .trim()
        .replace(/\s+/g, '-') // espacios por guiones
        .replace(/-+/g, '-');
    formData.append('name', slug);
    
    // Agregar permisos seleccionados
    const selectedPermissions = Array.from(document.querySelectorAll('.permission-checkbox:checked'))
        .map(checkbox => checkbox.value);
    
    if (selectedPermissions.length === 0) {
        showAlert('warning', 'Validación', 'Debe seleccionar al menos un permiso');
        return;
    }
    
    selectedPermissions.forEach(permissionId => {
        formData.append('permisos[]', permissionId);
    });
    
    try {
        showFormLoading(true);
        
        const url = isEditMode ? `/admin/roles/${currentRoleId}` : '/admin/roles';
        const method = isEditMode ? 'POST' : 'POST';
        
        if (isEditMode) {
            formData.append('_method', 'PUT');
        }
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData
        });

        const data = await response.json();
        
        if (data.success) {
            showAlert('success', '¡Éxito!', data.message);
            closeRoleModal();
            
            // Recargar la página para mostrar cambios
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            if (data.errors) {
                showFormErrors(data.errors);
            } else {
                throw new Error(data.message || 'Error al procesar la solicitud');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error', error.message || 'Error al procesar la solicitud');
    } finally {
        showFormLoading(false);
    }
}

/* VALIDACIÓN DEL FORMULARIO */
function validateRoleForm() {
    clearFormErrors();
    
    let isValid = true;
    const errors = {};
    
    // Validar nombre mostrado
    const displayName = document.getElementById('display_name').value.trim();
    if (!displayName) {
        errors.display_name = 'El nombre mostrado es requerido';
        isValid = false;
    }
    
    // Validar permisos
    const selectedPermissions = document.querySelectorAll('.permission-checkbox:checked');
    if (selectedPermissions.length === 0) {
        errors.permisos = 'Debe seleccionar al menos un permiso';
        isValid = false;
    }
    
    if (!isValid) {
        showFormErrors(errors);
    }
    
    return isValid;
}

function showFormErrors(errors) {
    Object.keys(errors).forEach(field => {
        const input = document.getElementById(field);
        if (input) {
            input.classList.add('border-red-500');
            
            // Crear mensaje de error
            const errorDiv = document.createElement('div');
            errorDiv.className = 'text-red-500 text-sm mt-1';
            errorDiv.textContent = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
            
            // Insertar después del input
            input.parentNode.appendChild(errorDiv);
        }
    });
}

function clearFormErrors() {
    // Remover clases de error
    const inputs = document.querySelectorAll('.form-input');
    inputs.forEach(input => {
        input.classList.remove('border-red-500');
    });
    
    // Remover mensajes de error
    const errorMessages = document.querySelectorAll('.text-red-500');
    errorMessages.forEach(msg => {
        if (msg.className.includes('text-sm mt-1')) {
            msg.remove();
        }
    });
}

/* GESTIÓN DE PERMISOS */
function setupPermissionControls() {
    // Checkboxes de módulos
    const moduleCheckboxes = document.querySelectorAll('.module-checkbox');
    moduleCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const module = this.dataset.module;
            const permissionCheckboxes = document.querySelectorAll(`.permission-checkbox[data-module="${module}"]`);
            
            permissionCheckboxes.forEach(permCheckbox => {
                permCheckbox.checked = this.checked;
            });
            
            updatePermissionsCounter();
        });
    });
    
    // Checkboxes de permisos individuales
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
    permissionCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updatePermissionsCounter();
            
            // Actualizar checkbox del módulo
            const module = this.dataset.module;
            const moduleCheckbox = document.querySelector(`.module-checkbox[data-module="${module}"]`);
            const modulePermissions = document.querySelectorAll(`.permission-checkbox[data-module="${module}"]`);
            const checkedModulePermissions = document.querySelectorAll(`.permission-checkbox[data-module="${module}"]:checked`);
            
            if (moduleCheckbox) {
                if (checkedModulePermissions.length === 0) {
                    moduleCheckbox.checked = false;
                    moduleCheckbox.indeterminate = false;
                } else if (checkedModulePermissions.length === modulePermissions.length) {
                    moduleCheckbox.checked = true;
                    moduleCheckbox.indeterminate = false;
                } else {
                    moduleCheckbox.checked = false;
                    moduleCheckbox.indeterminate = true;
                }
            }
        });
    });
}

function selectAllPermissions() {
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
    permissionCheckboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    
    const moduleCheckboxes = document.querySelectorAll('.module-checkbox');
    moduleCheckboxes.forEach(checkbox => {
        checkbox.checked = true;
        checkbox.indeterminate = false;
    });
    
    updatePermissionsCounter();
}

function deselectAllPermissions() {
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
    permissionCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    
    const moduleCheckboxes = document.querySelectorAll('.module-checkbox');
    moduleCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
        checkbox.indeterminate = false;
    });
    
    updatePermissionsCounter();
}

function updatePermissionsCounter() {
    const selectedPermissions = document.querySelectorAll('.permission-checkbox:checked');
    const counter = document.getElementById('selectedPermissionsCount');
    
    if (counter) {
        counter.textContent = selectedPermissions.length;
    }
    
    // También actualizar visibilidad de botones (para compatibilidad)
    updateControlButtonsVisibility(selectedPermissions.length);
}

/* COLOR PICKER */
function setupColorPresets() {
    const colorPresets = document.querySelectorAll('.color-preset');
    colorPresets.forEach(preset => {
        preset.addEventListener('click', function() {
            const color = this.dataset.color;
            document.getElementById('color').value = color;
        });
    });
}

/* FUNCIONES DE TABLA - Ya no necesitamos menús dropdown */
// Las acciones ahora son botones directos en la tabla

/* ACCIONES DE ROL */
async function viewRole(roleId) {
    try {
        showLoading('Cargando datos...');
        const response = await fetch(`/admin/roles/${roleId}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();
        
        if (data.success) {
            openViewRoleModal(data.role);
            hideLoading();
        } else {
            throw new Error(data.message || 'Error al cargar datos del rol');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error', 'No se pudieron cargar los datos del rol');
        hideLoading();
    }
}

function duplicateRole(roleId) {
    // Implementar duplicación de rol
    showAlert('info', 'Función en desarrollo', 'La duplicación de roles estará disponible pronto');
}

async function deleteRole(roleId) {
    // Cargar detalles del rol para mostrarlos en el modal de confirmación
    let roleDetails = null;
    try {
        showLoading('Cargando datos...');
        const resp = await fetch(`/admin/roles/${roleId}`, {
            method: 'GET',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await resp.json();
        if (data?.success) roleDetails = data.role;
    } catch (_) {}
    hideLoading();

    const name = roleDetails?.display_name || roleDetails?.name || 'Este rol';
    const users = roleDetails?.users_count ?? 0;
    const perms = roleDetails?.permissions_count ?? 0;
    const html = `
        <div class="swal2-role-summary">
            <div class="role-card">
                <div class="role-header">
                    <span class="role-color" style="background:${roleDetails?.color || '#e5e7eb'}"></span>
                    <div class="role-name">${name}</div>
                </div>
                <div class="role-stats">
                    <div class="stat-pill stat-perms">
                        <iconify-icon icon="solar:shield-check-bold-duotone"></iconify-icon>
                        <span class="stat-count">${perms}</span>
                        <span class="stat-label">permisos</span>
                    </div>
                    <div class="stat-pill stat-users">
                        <iconify-icon icon="solar:users-group-rounded-bold-duotone"></iconify-icon>
                        <span class="stat-count">${users}</span>
                        <span class="stat-label">${users === 1 ? 'usuario' : 'usuarios'}</span>
                    </div>
                </div>
            </div>
            <div class="swal2-warning-text">Esta acción eliminará permanentemente el rol y no podrá ser recuperado.</div>
        </div>`;

    const result = await Swal.fire({
        title: '¿Eliminar rol?',
        html,
        icon: 'warning',
        showCancelButton: true,
        buttonsStyling: false,
        focusConfirm: false,
        customClass: {
            popup: 'swal2-popup-roles',
            confirmButton: 'swal2-confirm-roles',
            cancelButton: 'swal2-cancel-roles'
        },
        confirmButtonText: 'Sí, eliminar rol',
        cancelButtonText: 'Cancelar'
    });

    if (!result.isConfirmed) return;

    try {
        showLoading('Eliminando rol...');
        const response = await fetch(`/admin/roles/${roleId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            }
        });

        const data = await response.json();
        
        if (data.success) {
            showAlert('success', '¡Éxito!', data.message);
            
            // Remover tarjeta
            setTimeout(() => {
                window.location.reload();
            }, 1500);
            hideLoading();
        } else {
            throw new Error(data.message || 'Error al eliminar el rol');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error', error.message);
        hideLoading();
    }
}

/* EXPORTACIÓN */
function exportRoles() {
    const visibleRoles = rolesData.filter(role => role.element.style.display !== 'none');
    
    if (visibleRoles.length === 0) {
        showAlert('warning', 'Sin datos', 'No hay roles para exportar');
        return;
    }
    
    showAlert('info', 'Función en desarrollo', 'La exportación de roles estará disponible pronto');
}

/* UTILIDADES */
function showFormLoading(show) {
    const submitBtn = document.querySelector('.btn-guardar') || document.querySelector('.btn-save');
    const submitText = document.getElementById('saveButtonText');

    if (!submitBtn || !submitText) {
        // Si no existe el botón o el texto (por cambios de plantilla), salimos sin error
        return;
    }

    if (show) {
        submitBtn.disabled = true;
        submitText.textContent = 'Procesando...';
        submitBtn.style.opacity = '0.7';
    } else {
        submitBtn.disabled = false;
        submitText.textContent = isEditMode ? 'Actualizar Rol' : 'Crear Rol';
        submitBtn.style.opacity = '1';
    }
}

function showAlert(type, title, message) {
    const config = {
        title: title,
        text: message
    };

    switch (type) {
        case 'success':
            config.icon = 'success';
            // Ocultar botón y cerrar automáticamente
            config.showConfirmButton = false;
            config.timer = 1500;
            break;
        case 'error':
            config.icon = 'error';
            config.confirmButtonColor = '#e53e3e';
            config.confirmButtonText = 'Entendido';
            break;
        case 'warning':
            config.icon = 'warning';
            config.confirmButtonColor = '#e53e3e';
            config.confirmButtonText = 'Entendido';
            break;
        default:
            config.icon = 'info';
            config.confirmButtonColor = '#e53e3e';
            config.confirmButtonText = 'Entendido';
    }

    Swal.fire(config);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/* Cambiar estado de rol (activo/inactivo) */
async function toggleRoleStatus(roleId, checkboxEl = null, prevChecked = null) {
    try {
        const response = await fetch(`/admin/roles/${roleId}/cambiar-estado`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        if (data.success) {
            // Actualizar UI sin recargar página
            const row = document.querySelector(`.role-row[data-role-id="${roleId}"]`);
            if (row) {
                // Toggle checkbox según nuevo estado
                const checkbox = checkboxEl || row.querySelector('.role-status-toggle');
                if (checkbox) checkbox.checked = !!data.is_active;

                // Actualizar badge de estado
                const statusCell = row.querySelector('.status-cell');
                if (statusCell) {
                    statusCell.innerHTML = data.is_active
                        ? `<span class="status-badge status-active"><iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon> Activo</span>`
                        : `<span class="status-badge status-inactive" style="background: linear-gradient(135deg, rgba(239,68,68,0.12) 0%, rgba(239,68,68,0.08) 100%); color: #dc2626; border-color: rgba(239,68,68,0.3); box-shadow: 0 2px 4px rgba(239,68,68,0.1);"><iconify-icon icon="solar:close-circle-bold-duotone"></iconify-icon> Inactivo</span>`;
                }
            }
            showAlert('success', 'Actualizado', data.message || 'Estado del rol actualizado');
        } else {
            throw new Error(data.message || 'No se pudo cambiar el estado');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error', error.message);
        // Revertir UI si falló
        const row = document.querySelector(`.role-row[data-role-id="${roleId}"]`);
        const checkbox = checkboxEl || (row ? row.querySelector('.role-status-toggle') : null);
        if (checkbox !== null && prevChecked !== null) {
            checkbox.checked = prevChecked;
        }
    } finally {
        // Rehabilitar control y liberar bloqueo
        if (checkboxEl) checkboxEl.disabled = false;
        togglingRoles.delete(roleId);
    }
}

// Seleccionar todos los permisos de un módulo (sin checkbox de encabezado)
function selectAllModulePermissions(moduleId) {
    const permissionCheckboxes = document.querySelectorAll(`.permission-checkbox[data-module="${moduleId}"]`);
    permissionCheckboxes.forEach(cb => { cb.checked = true; });
    updatePermissionCount();
}

/* MODAL DE PERMISOS DEL SISTEMA - ACTUALIZADO PARA NUEVO DISEÑO */
function openPermissionsModal() {
    const modal = document.getElementById('permissionsModal');
    if (!modal) return;
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Animación de entrada suave con nueva clase
    const container = modal.querySelector('.permisos-modal-container');
    if (container) {
        container.style.transform = 'scale(0.95)';
        container.style.opacity = '0';
        container.style.transition = 'all 0.2s ease';
        
        setTimeout(() => {
            container.style.transform = 'scale(1)';
            container.style.opacity = '1';
        }, 10);
    }
}

function closePermissionsModal() {
    const modal = document.getElementById('permissionsModal');
    if (!modal) return;
    
    const container = modal.querySelector('.permisos-modal-container');
    if (container) {
        container.style.transform = 'scale(0.95)';
        container.style.opacity = '0';
        
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }, 200);
    } else {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

// Event listener para cerrar modal de permisos al hacer clic fuera
document.addEventListener('click', function(event) {
    const permissionsModal = document.getElementById('permissionsModal');
    if (event.target === permissionsModal) {
        closePermissionsModal();
    }
});

// Event listener para cerrar modal de permisos con tecla Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const permissionsModal = document.getElementById('permissionsModal');
        if (permissionsModal && !permissionsModal.classList.contains('hidden')) {
            closePermissionsModal();
        }
    }
}); 

/* ==============================================
   FUNCIONES PROFESIONALES PARA EL NUEVO DISEÑO MODAL
   ============================================== */

// Progress Bar para el modal profesional
function updateProgressBar() {
    const displayNameField = document.getElementById('display_name');
    const selectedPermissions = document.querySelectorAll('.checkbox-permiso:checked');
    const progressBar = document.getElementById('roleProgressBar');
    
    if (!progressBar) return;
    
    let progress = 0;
    
    // 50% por nombre del rol mostrado
    if (displayNameField && displayNameField.value.trim()) {
        progress += 50;
    }
    
    // 50% por permisos seleccionados
    if (selectedPermissions.length > 0) {
        progress += 50;
    }
    
    progressBar.style.width = progress + '%';
}

// Función específica para toggle de módulos (nueva clase CSS)
function toggleModulePermissions(moduleId) {
    const moduleCheckbox = document.getElementById(`module_${moduleId}`);
    const permissionCheckboxes = document.querySelectorAll(`.checkbox-permiso[data-module="${moduleId}"]`);
    
    if (moduleCheckbox && permissionCheckboxes.length > 0) {
        const isChecked = moduleCheckbox.checked;
        
        permissionCheckboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
            // Fallback para navegadores sin soporte :has()
            const card = checkbox.closest('.permiso-card');
            if (card) {
                if (isChecked) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
            }
        });
        
        updatePermissionCount();
        updateProgressBar();
    }
}

// Actualizar contador de permisos (funciona con nuevas clases CSS)
function updatePermissionCount() {
    const selectedPermissions = document.querySelectorAll('.checkbox-permiso:checked');
    const counter = document.getElementById('selectedPermissionsCount');
    
    if (counter) {
        counter.textContent = selectedPermissions.length;
    }
    
    // También actualizar los contadores de módulos individuales
    updateModuleCounters();
    
    // Actualizar visibilidad de botones de control
    updateControlButtonsVisibility(selectedPermissions.length);
}

// Función para manejar la visibilidad de los botones de control
function updateControlButtonsVisibility(selectedCount) {
    const selectAllBtn = document.querySelector('.btn-seleccionar');
    const deselectAllBtn = document.querySelector('.btn-deseleccionar');
    
    if (selectAllBtn && deselectAllBtn) {
        if (selectedCount === 0) {
            // No hay nada seleccionado: mostrar solo "Seleccionar Todo"
            selectAllBtn.classList.remove('oculto');
            deselectAllBtn.classList.add('oculto');
        } else {
            // Hay algo seleccionado: mostrar solo "Deseleccionar Todo"
            selectAllBtn.classList.add('oculto');
            deselectAllBtn.classList.remove('oculto');
        }
    }
}

// Actualizar contadores de módulos individuales
function updateModuleCounters() {
    const modules = document.querySelectorAll('.modulo-permiso-card');
    
    modules.forEach(moduleCard => {
        const moduleCheckbox = moduleCard.querySelector('.checkbox-modulo');
        const moduleId = moduleCheckbox?.dataset.module;
        if (!moduleId) return;
        
        const allPermissions = moduleCard.querySelectorAll('.checkbox-permiso');
        const selectedPermissions = moduleCard.querySelectorAll('.checkbox-permiso:checked');
        
        // Actualizar estado del checkbox del módulo
        if (moduleCheckbox) {
            if (selectedPermissions.length === 0) {
                moduleCheckbox.checked = false;
                moduleCheckbox.indeterminate = false;
            } else if (selectedPermissions.length === allPermissions.length) {
                moduleCheckbox.checked = true;
                moduleCheckbox.indeterminate = false;
            } else {
                moduleCheckbox.checked = false;
                moduleCheckbox.indeterminate = true;
            }
        }
    });
}

// Setup para color presets del nuevo diseño
function setupColorPresetsRol() {
    const colorPresets = document.querySelectorAll('.color-preset-rol');
    const colorInput = document.getElementById('color');
    
    colorPresets.forEach(preset => {
        preset.addEventListener('click', function() {
            const color = this.dataset.color;
            if (colorInput) {
                colorInput.value = color;
            }
        });
    });
}

// Inicializar eventos del modal profesional
function initializeProfessionalModal() {
    // Setup para color presets del nuevo diseño
    setupColorPresetsRol();
    
    // Eventos para actualizar progress bar
    const nameField = document.getElementById('name');
    const displayNameField = document.getElementById('display_name');
    
    if (nameField) {
        nameField.addEventListener('input', updateProgressBar);
    }
    
    if (displayNameField) {
        displayNameField.addEventListener('input', updateProgressBar);
    }
    
    // Eventos para checkboxes de permisos (nuevas clases)
    const permissionCheckboxes = document.querySelectorAll('.checkbox-permiso');
    permissionCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Fallback para navegadores sin soporte :has()
            const card = this.closest('.permiso-card');
            if (card) {
                if (this.checked) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
            }
            
            updatePermissionCount();
            updateProgressBar();
        });
    });
    
    // Eventos para botones de seleccionar/deseleccionar todo
    const selectAllBtn = document.querySelector('.btn-seleccionar');
    const deselectAllBtn = document.querySelector('.btn-deseleccionar');
    
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            selectAllPermissionsProfessional();
            updatePermissionCount();
            updateProgressBar();
        });
    }
    
    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function() {
            deselectAllPermissionsProfessional();
            updatePermissionCount();
            updateProgressBar();
        });
    }
}

// Funciones de selección actualizadas para el nuevo diseño
function selectAllPermissionsProfessional() {
    const permissionCheckboxes = document.querySelectorAll('.checkbox-permiso');
    const moduleCheckboxes = document.querySelectorAll('.checkbox-modulo');
    
    permissionCheckboxes.forEach(checkbox => {
        checkbox.checked = true;
        // Fallback para navegadores sin soporte :has()
        const card = checkbox.closest('.permiso-card');
        if (card) {
            card.classList.add('selected');
        }
    });
    
    moduleCheckboxes.forEach(checkbox => {
        checkbox.checked = true;
        checkbox.indeterminate = false;
    });
}

function deselectAllPermissionsProfessional() {
    const permissionCheckboxes = document.querySelectorAll('.checkbox-permiso');
    const moduleCheckboxes = document.querySelectorAll('.checkbox-modulo');
    
    permissionCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
        // Fallback para navegadores sin soporte :has()
        const card = checkbox.closest('.permiso-card');
        if (card) {
            card.classList.remove('selected');
        }
    });
    
    moduleCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
        checkbox.indeterminate = false;
    });
}

// Actualizar resetRoleForm para funcionar con las nuevas clases
function resetRoleFormProfessional() {
    const form = document.getElementById('roleForm');
    form.reset();
    
    // Resetear color
    const colorInput = document.getElementById('color');
    if (colorInput) {
        colorInput.value = '#e53e3e';
    }
    
    // Limpiar errores de validación
    clearFormErrors();
    
    // Desmarcar permisos (nuevas clases CSS)
    const permissionCheckboxes = document.querySelectorAll('.checkbox-permiso');
    permissionCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
        // Fallback para navegadores sin soporte :has()
        const card = checkbox.closest('.permiso-card');
        if (card) {
            card.classList.remove('selected');
        }
    });
    
    const moduleCheckboxes = document.querySelectorAll('.checkbox-modulo');
    moduleCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
        checkbox.indeterminate = false;
    });
    
    // Actualizar contadores y botones
    updatePermissionCount();
    updateProgressBar();
    
    // Asegurar que se muestre solo el botón "Seleccionar Todo"
    updateControlButtonsVisibility(0);
}

// Actualizar función de validación para nuevas clases
function validateRoleFormProfessional() {
    clearFormErrors();
    
    let isValid = true;
    const errors = {};
    
    // Validar nombre del sistema
    const name = document.getElementById('name').value.trim();
    if (!name) {
        errors.name = 'El nombre del sistema es requerido';
        isValid = false;
    } else if (!/^[a-z0-9-]+$/.test(name)) {
        errors.name = 'Solo letras minúsculas, números y guiones';
        isValid = false;
    }
    
    // Validar nombre mostrado
    const displayName = document.getElementById('display_name').value.trim();
    if (!displayName) {
        errors.display_name = 'El nombre mostrado es requerido';
        isValid = false;
    }
    
    // Validar permisos (nuevas clases)
    const selectedPermissions = document.querySelectorAll('.checkbox-permiso:checked');
    if (selectedPermissions.length === 0) {
        errors.permisos = 'Debe seleccionar al menos un permiso';
        isValid = false;
    }
    
    if (!isValid) {
        showFormErrors(errors);
    }
    
    return isValid;
}

// Actualizar populate form para nuevas clases
function populateRoleFormProfessional(role) {
    // Datos básicos
    document.getElementById('roleId').value = role.id;
    document.getElementById('name').value = role.name || '';
    document.getElementById('display_name').value = role.display_name || '';
    document.getElementById('description').value = role.description || '';
    document.getElementById('color').value = role.color || '#e53e3e';
    
    // Permisos (nuevas clases CSS)
    let selectedCount = 0;
    if (role.permissions && role.permissions.length > 0) {
        const permissionCheckboxes = document.querySelectorAll('.checkbox-permiso');
        permissionCheckboxes.forEach(checkbox => {
            const permissionId = checkbox.value;
            const isChecked = role.permissions.some(permission => permission.id == permissionId);
            checkbox.checked = isChecked;
            if (isChecked) selectedCount++;
            
            // Fallback para navegadores sin soporte :has()
            const card = checkbox.closest('.permiso-card');
            if (card) {
                if (isChecked) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
            }
        });
    }
    
    // Actualizar contadores y botones
    updatePermissionCount();
    updateProgressBar();
    
    // Actualizar visibilidad de botones según permisos cargados
    updateControlButtonsVisibility(selectedCount);
}

// Override de funciones principales para usar las versiones profesionales
// Esto asegura compatibilidad con las nuevas clases CSS

// Actualizar la inicialización
document.addEventListener('DOMContentLoaded', function() {
    initializeRoleManagement();
    
    // Inicializar modal profesional
    setTimeout(initializeProfessionalModal, 100);
});

// Override de selectAllPermissions para usar ambas versiones (compatibilidad)
const originalSelectAll = selectAllPermissions;
selectAllPermissions = function() {
    // Intentar con las clases nuevas primero
    const newCheckboxes = document.querySelectorAll('.checkbox-permiso');
    if (newCheckboxes.length > 0) {
        selectAllPermissionsProfessional();
        updatePermissionCount();
        updateProgressBar();
    } else {
        // Fallback a clases antiguas
        originalSelectAll();
        updatePermissionsCounter();
    }
};

// Override de deselectAllPermissions
const originalDeselectAll = deselectAllPermissions;
deselectAllPermissions = function() {
    // Intentar con las clases nuevas primero
    const newCheckboxes = document.querySelectorAll('.checkbox-permiso');
    if (newCheckboxes.length > 0) {
        deselectAllPermissionsProfessional();
        updatePermissionCount();
        updateProgressBar();
    } else {
        // Fallback a clases antiguas
        originalDeselectAll();
        updatePermissionsCounter();
    }
};

// Override de resetRoleForm
const originalResetForm = resetRoleForm;
resetRoleForm = function() {
    // Intentar con las nuevas clases primero
    const newCheckboxes = document.querySelectorAll('.checkbox-permiso');
    if (newCheckboxes.length > 0) {
        resetRoleFormProfessional();
    } else {
        // Fallback a versión original
        originalResetForm();
    }
};

/* MODAL DE VISTA DE ROL */
function openViewRoleModal(role) {
    // Modal profesional (tema azul) para vista de rol
    const modalHtml = `
        <div id="viewRoleModal" class="modal-profesional">
            <div class="modal-profesional-container tema-azul">
                <div class="header-profesional">
                    <div class="header-content">
                        <div class="header-left">
                            <div class="header-icon ${role.is_protected ? 'icon-protected' : 'icon-normal'}">
                                <iconify-icon icon="${role.is_protected ? 'solar:crown-bold-duotone' : 'solar:shield-user-bold-duotone'}"></iconify-icon>
                            </div>
                            <div class="header-text">
                                <h3>Información del Rol</h3>
                                <p>${role.is_protected ? 'Rol del Sistema (Protegido)' : 'Rol Personalizado'}</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" onclick="closeViewRoleModal()">
                            <iconify-icon icon="heroicons:x-mark"></iconify-icon>
                        </button>
                    </div>
                </div>

                <div class="modal-content-profesional">
                    <div class="seccion-form seccion-azul">
                        <div class="seccion-header">
                            <div class="seccion-icon icon-azul">
                                <iconify-icon icon="solar:info-circle-bold-duotone"></iconify-icon>
                            </div>
                            <div class="seccion-titulo">
                                <h3>Información del Rol</h3>
                                <p>Datos básicos y configuración</p>
                            </div>
                        </div>
                        <div class="grid-campos columnas-2">
                            <div class="campo-grupo">
                                <label class="campo-label"><iconify-icon icon="solar:code-bold-duotone" class="label-icon"></iconify-icon> Nombre del Sistema</label>
                                <div class="field-pill">${role.name}</div>
                            </div>
                            <div class="campo-grupo">
                                <label class="campo-label"><iconify-icon icon="solar:eye-bold-duotone" class="label-icon"></iconify-icon> Nombre Mostrado</label>
                                <div class="field-pill">${role.display_name}</div>
                            </div>
                            <div class="campo-grupo campo-completo">
                                <label class="campo-label"><iconify-icon icon="solar:document-text-bold-duotone" class="label-icon"></iconify-icon> Descripción</label>
                                <div class="field-pill">${role.description || 'Sin descripción'}</div>
                            </div>
                            <div class="campo-grupo">
                                <label class="campo-label"><iconify-icon icon="solar:pallete-bold-duotone" class="label-icon"></iconify-icon> Color</label>
                                <div class="field-pill"><span style="display:inline-block;width:16px;height:16px;border-radius:4px;background:${role.color};margin-right:8px"></span>${role.color || '-'}</div>
                            </div>
                            <div class="campo-grupo">
                                <label class="campo-label"><iconify-icon icon="solar:calendar-bold-duotone" class="label-icon"></iconify-icon> Creado</label>
                                <div class="field-pill">${role.created_at}</div>
                            </div>
                            <div class="campo-grupo">
                                <label class="campo-label"><iconify-icon icon="solar:calendar-bold-duotone" class="label-icon"></iconify-icon> Última modificación</label>
                                <div class="field-pill">${role.updated_at}</div>
                            </div>
                        </div>
                    </div>

                    <div class="seccion-form seccion-morado">
                        <div class="seccion-header">
                            <div class="seccion-icon icon-morado"><iconify-icon icon="solar:chart-square-bold-duotone"></iconify-icon></div>
                            <div class="seccion-titulo"><h3>Estadísticas</h3><p>Resumen del rol</p></div>
                        </div>
                        <div class="stats-grid">
                            <div class="stat-card"><div class="stat-icon"><iconify-icon icon="solar:shield-check-bold-duotone"></iconify-icon></div><div class="stat-content"><div class="stat-number">${role.permissions_count}</div><div class="stat-label">Permisos Asignados</div></div></div>
                            <div class="stat-card"><div class="stat-icon"><iconify-icon icon="solar:users-group-rounded-bold-duotone"></iconify-icon></div><div class="stat-content"><div class="stat-number">${role.users_count}</div><div class="stat-label">${role.users_count === 1 ? 'Usuario' : 'Usuarios'}</div></div></div>
                        </div>
                    </div>

                    <div class="seccion-form seccion-azul">
                        <div class="seccion-header">
                            <div class="seccion-icon icon-azul"><iconify-icon icon="solar:shield-keyhole-bold-duotone"></iconify-icon></div>
                            <div class="seccion-titulo"><h3>Permisos Asignados (${role.permissions_count})</h3><p>Detalle de permisos del rol</p></div>
                        </div>
                        <div class="permissions-list">
                            ${formatPermissionsForView(role.permissions)}
                        </div>
                    </div>
                </div>

                <div class="footer-profesional">
                    <div class="footer-botones">
                        <button type="button" class="btn-cancelar" onclick="closeViewRoleModal()"><iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon> Entendido</button>
                        ${!role.is_protected ? `<button type="button" class="btn-guardar" onclick="closeViewRoleModal(); editRole(${role.id})"><iconify-icon icon="solar:pen-bold-duotone"></iconify-icon> Editar Rol</button>` : ''}
                    </div>
                </div>
            </div>
        </div>`;

    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = document.getElementById('viewRoleModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

// Formatear permisos para la vista con etiquetas en español y colapso de dashboard
function formatPermissionsForView(permissions = []) {
    const permLabels = {
        // Dashboard (colapsado a uno)
        'dashboard.access': 'Dashboard',
        // Ventas
        'ventas.view': 'Ver ventas',
        'ventas.create': 'Crear venta',
        'ventas.edit': 'Editar venta',
        'ventas.delete': 'Eliminar venta',
        'ventas.reports': 'Reportes de ventas',
        'ventas.devoluciones': 'Devoluciones de ventas',
        'ventas.clientes': 'Clientes de ventas',
        // Inventario
        'inventario.view': 'Ver inventario',
        'inventario.create': 'Crear inventario',
        'inventario.edit': 'Editar inventario',
        'inventario.delete': 'Eliminar inventario',
        // Productos
        'productos.view': 'Ver productos',
        'productos.create': 'Crear producto',
        'productos.edit': 'Editar producto',
        'productos.delete': 'Eliminar producto',
        // Usuarios
        'usuarios.view': 'Ver usuarios',
        'usuarios.create': 'Crear usuario',
        'usuarios.edit': 'Editar usuario',
        'usuarios.delete': 'Eliminar usuario',
        'usuarios.activate': 'Activar usuario',
        'usuarios.roles': 'Roles de usuario',
        // Compras
        'compras.view': 'Ver compras',
        'compras.create': 'Crear compra',
        'compras.edit': 'Editar compra',
        'compras.delete': 'Eliminar compra',
    };

    let dashboardShown = false;
    const items = [];

    permissions.forEach(permission => {
        const name = permission.name || '';
        let label = permLabels[name] || permission.display_name || name.replace(/\./g, ' ');

        if (name.startsWith('dashboard.')) {
            if (dashboardShown) return; // mostramos Dashboard una sola vez
            label = 'Dashboard';
            dashboardShown = true;
        }

        items.push(`
            <div class="permission-item">
                <div class="permission-icon"><iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon></div>
                <div class="permission-content">
                    <div class="permission-name">${label}</div>
                    <div class="permission-label">${name}</div>
                </div>
            </div>
        `);
    });

    return items.join('');
}

function closeViewRoleModal() {
    const modal = document.getElementById('viewRoleModal');
    if (modal) {
        modal.classList.remove('show');
        
        setTimeout(() => {
            modal.remove();
            document.body.style.overflow = 'auto';
        }, 300);
    }
}

/* FUNCIÓN PARA POBLAR FORMULARIO DE EDICIÓN */
function populateRoleFormFromEdit(role) {
    // Datos básicos
    document.getElementById('roleId').value = role.id;
    document.getElementById('display_name').value = role.display_name || '';
    document.getElementById('description').value = role.description || '';
    document.getElementById('color').value = role.color || '#e53e3e';
    
    // Limpiar todos los checkboxes primero
    const allCheckboxes = document.querySelectorAll('.checkbox-permiso, .permission-checkbox');
    allCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
        const card = checkbox.closest('.permiso-card');
        if (card) {
            card.classList.remove('selected');
        }
    });
    
    // Marcar permisos seleccionados
    if (role.permissions && role.permissions.length > 0) {
        role.permissions.forEach(permissionId => {
            // Intentar con las nuevas clases primero
            let checkbox = document.querySelector(`.checkbox-permiso[value="${permissionId}"]`);
            if (!checkbox) {
                // Fallback a clases antiguas
                checkbox = document.querySelector(`.permission-checkbox[value="${permissionId}"]`);
            }
            
            if (checkbox) {
                checkbox.checked = true;
                const card = checkbox.closest('.permiso-card');
                if (card) {
                    card.classList.add('selected');
                }
            }
        });
    }
    
    // Actualizar contadores y estado
    updatePermissionCount();
    updateProgressBar();
    updateControlButtonsVisibility(role.permissions ? role.permissions.length : 0);
}