/* GESTIÓN DE ROLES Y PERMISOS - JAVASCRIPT */

// Variables globales
let currentRoleId = null;
let isEditMode = false;
let rolesData = [];

// Configuración CSRF
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Event Listeners al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    initializeRoleManagement();
});

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
    
    // Inicializar progress bar
    updateProgressBar();
    
    // Inicializar estado de botones (mostrar solo "Seleccionar Todo")
    updateControlButtonsVisibility(0);
    
    // Focus en primer campo
    setTimeout(() => {
        document.getElementById('name').focus();
    }, 100);
}

async function editRole(roleId) {
    try {
        const response = await fetch(`/admin/roles/${roleId}/edit`, {
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
    
    // Inicializar progress bar
    updateProgressBar();
            
            // Focus en primer campo
            setTimeout(() => {
                document.getElementById('name').focus();
            }, 100);
        } else {
            throw new Error(data.message || 'Error al cargar datos del rol');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error', 'No se pudieron cargar los datos del rol');
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
    document.getElementById('name').value = role.name || '';
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
        } else {
            throw new Error(data.message || 'Error al cargar datos del rol');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error', 'No se pudieron cargar los datos del rol');
    }
}

function duplicateRole(roleId) {
    // Implementar duplicación de rol
    showAlert('info', 'Función en desarrollo', 'La duplicación de roles estará disponible pronto');
}

async function deleteRole(roleId) {
    const result = await Swal.fire({
        title: '¿Eliminar rol?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });

    if (!result.isConfirmed) return;

    try {
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
        } else {
            throw new Error(data.message || 'Error al eliminar el rol');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error', error.message);
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
    const submitBtn = document.querySelector('.btn-save');
    const submitText = document.getElementById('saveButtonText');
    
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
        text: message,
        confirmButtonColor: '#e53e3e',
        confirmButtonText: 'Entendido'
    };
    
    switch (type) {
        case 'success':
            config.icon = 'success';
            break;
        case 'error':
            config.icon = 'error';
            break;
        case 'warning':
            config.icon = 'warning';
            break;
        default:
            config.icon = 'info';
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
    const nameField = document.getElementById('name');
    const displayNameField = document.getElementById('display_name');
    const selectedPermissions = document.querySelectorAll('.checkbox-permiso:checked');
    const progressBar = document.getElementById('roleProgressBar');
    
    if (!progressBar) return;
    
    let progress = 0;
    
    // 30% por nombre del sistema
    if (nameField && nameField.value.trim()) {
        progress += 30;
    }
    
    // 20% por nombre mostrado
    if (displayNameField && displayNameField.value.trim()) {
        progress += 20;
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
    // Crear modal dinámicamente
    const modalHtml = `
        <div id="viewRoleModal" class="modal-view-overlay">
            <div class="modal-view-container">
                <!-- Header -->
                <div class="modal-view-header">
                    <div class="header-content">
                        <div class="header-left">
                            <div class="header-icon ${role.is_protected ? 'icon-protected' : 'icon-normal'}">
                                <iconify-icon icon="${role.is_protected ? 'solar:crown-bold-duotone' : 'solar:shield-user-bold-duotone'}"></iconify-icon>
                            </div>
                            <div class="header-text">
                                <h3>${role.display_name}</h3>
                                <p>${role.is_protected ? 'Rol del Sistema (Protegido)' : 'Rol Personalizado'}</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" onclick="closeViewRoleModal()">
                            <iconify-icon icon="heroicons:x-mark"></iconify-icon>
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="modal-view-body">
                    <!-- Información Básica -->
                    <div class="info-section">
                        <div class="section-header">
                            <iconify-icon icon="solar:info-circle-bold-duotone"></iconify-icon>
                            <h4>Información del Rol</h4>
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Nombre del Sistema:</span>
                                <span class="info-value">${role.name}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Nombre Mostrado:</span>
                                <span class="info-value">${role.display_name}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Descripción:</span>
                                <span class="info-value">${role.description || 'Sin descripción'}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Color:</span>
                                <div class="color-preview" style="background-color: ${role.color}"></div>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Creado:</span>
                                <span class="info-value">${role.created_at}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Última modificación:</span>
                                <span class="info-value">${role.updated_at}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Estadísticas -->
                    <div class="stats-section">
                        <div class="section-header">
                            <iconify-icon icon="solar:chart-square-bold-duotone"></iconify-icon>
                            <h4>Estadísticas</h4>
                        </div>
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <iconify-icon icon="solar:shield-check-bold-duotone"></iconify-icon>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-number">${role.permissions_count}</div>
                                    <div class="stat-label">Permisos Asignados</div>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <iconify-icon icon="solar:users-group-rounded-bold-duotone"></iconify-icon>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-number">${role.users_count}</div>
                                    <div class="stat-label">${role.users_count === 1 ? 'Usuario' : 'Usuarios'}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Permisos -->
                    <div class="permissions-section">
                        <div class="section-header">
                            <iconify-icon icon="solar:shield-keyhole-bold-duotone"></iconify-icon>
                            <h4>Permisos Asignados (${role.permissions_count})</h4>
                        </div>
                        <div class="permissions-list">
                            ${role.permissions.map(permission => `
                                <div class="permission-item">
                                    <div class="permission-icon">
                                        <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon>
                                    </div>
                                    <div class="permission-content">
                                        <div class="permission-name">${permission.display_name}</div>
                                        <div class="permission-code">${permission.name}</div>
                                        ${permission.description ? `<div class="permission-desc">${permission.description}</div>` : ''}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>

                    <!-- Usuarios -->
                    ${role.users_count > 0 ? `
                        <div class="users-section">
                            <div class="section-header">
                                <iconify-icon icon="solar:users-group-rounded-bold-duotone"></iconify-icon>
                                <h4>Usuarios con este Rol (${role.users_count})</h4>
                            </div>
                            <div class="users-list">
                                ${role.users.map(user => `
                                    <div class="user-item">
                                        <div class="user-avatar">
                                            ${user.avatar ? 
                                                `<img src="${user.avatar}" alt="${user.name}">` : 
                                                `<div class="avatar-placeholder">${user.name.charAt(0).toUpperCase()}</div>`
                                            }
                                        </div>
                                        <div class="user-content">
                                            <div class="user-name">${user.name}</div>
                                            <div class="user-email">${user.email}</div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    ` : ''}
                </div>

                <!-- Footer -->
                <div class="modal-view-footer">
                    <button type="button" class="btn-secondary" onclick="closeViewRoleModal()">
                        <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon>
                        Entendido
                    </button>
                    ${!role.is_protected ? `
                        <button type="button" class="btn-primary" onclick="closeViewRoleModal(); editRole(${role.id});">
                            <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                            Editar Rol
                        </button>
                    ` : ''}
                </div>
            </div>
        </div>
    `;

    // Insertar modal en el DOM
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Mostrar modal con animación
    const modal = document.getElementById('viewRoleModal');
    modal.style.display = 'flex';
    
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);
    
    // Bloquear scroll del body
    document.body.style.overflow = 'hidden';
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
    document.getElementById('name').value = role.name || '';
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