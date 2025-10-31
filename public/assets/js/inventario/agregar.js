document.addEventListener('DOMContentLoaded', function() {
    // Obtener elementos
    const filterEstado = document.getElementById('filterEstado');
    const table = document.getElementById('selection-table');
    const formAgregarProducto = document.getElementById('formAgregarProducto');
    
    // Inicializar filtrado
    if (filterEstado) {
        filterEstado.addEventListener('change', function() {
            const selectedEstado = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            let visibleCount = 0;

            rows.forEach(row => {
                const estadoCell = row.querySelector('td:nth-child(5) span');
                if (!estadoCell) return;

                const estadoText = estadoCell.textContent.toLowerCase().trim();
                let showRow = false;

                if (selectedEstado === 'todos') {
                    showRow = true;
                } else {
                    // Manejar casos especiales
                    switch(selectedEstado) {
                        case 'bajo_stock':
                            showRow = estadoText === 'bajo stock';
                            break;
                        case 'por_vencer':
                            showRow = estadoText === 'por vencer';
                            break;
                        default:
                            showRow = estadoText === selectedEstado;
                            break;
                    }
                }

                row.style.display = showRow ? '' : 'none';
                if (showRow) visibleCount++;
            });

            // Actualizar contador
            updateFilterCounter(visibleCount, rows.length, selectedEstado);
        });
    }

    // --- VALIDACIONES BÁSICAS EN TIEMPO REAL ---
    if (formAgregarProducto) {
        // Nombre: permitir letras, números, espacios y caracteres especiales comunes
        const nombreInput = formAgregarProducto.querySelector('input[name="nombre"]');
        if (nombreInput) {
            nombreInput.addEventListener('keypress', function(e) {
                // Permitir letras, números, espacios y caracteres especiales comunes para medicamentos
                if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\-\.\(\)\+\/]$/.test(e.key)) {
                    e.preventDefault();
                }
            });
            nombreInput.addEventListener('input', function(e) {
                // Limpiar caracteres no permitidos pero permitir escritura
                this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\-\.\(\)\+\/]/g, '');
            });
        }

        // Marca: solo letras, números y espacios
        const marcaInput = formAgregarProducto.querySelector('input[name="marca"]');
        if (marcaInput) {
            marcaInput.addEventListener('keypress', function(e) {
                if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s]$/.test(e.key)) {
                    e.preventDefault();
                }
            });
            marcaInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s]/g, '');
            });
        }

        // Concentración: permitir letras, números y caracteres comunes para concentraciones
        const concentracionInput = formAgregarProducto.querySelector('input[name="concentracion"]');
        if (concentracionInput) {
            concentracionInput.addEventListener('keypress', function(e) {
                // Permitir letras, números, espacios y caracteres para concentraciones (mg, ml, %, etc.)
                if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\-\.\%\/]$/.test(e.key)) {
                    e.preventDefault();
                }
            });
            concentracionInput.addEventListener('input', function(e) {
                // Limpiar caracteres no permitidos pero permitir escritura
                this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\-\.\%\/]/g, '');
            });
        }

        // Lote: solo letras, números, espacios, guiones y puntos
        const loteInput = formAgregarProducto.querySelector('input[name="lote"]');
        if (loteInput) {
            loteInput.addEventListener('keypress', function(e) {
                if (!/^[a-zA-Z0-9\s\-.]$/.test(e.key)) {
                    e.preventDefault();
                }
            });
            loteInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^a-zA-Z0-9\s\-.]/g, '');
            });
        }

        // Código de barras: solo números, máximo 13 dígitos
        const codBarrasInput = formAgregarProducto.querySelector('input[name="codigo_barras"]');
        if (codBarrasInput) {
            codBarrasInput.addEventListener('keypress', function(e) {
                if (!/^[0-9]$/.test(e.key) || this.value.length >= 13) {
                    e.preventDefault();
                }
            });
            codBarrasInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13);
            });
        }

        // Stock: solo números enteros
        ['stock_actual', 'stock_minimo'].forEach(name => {
            const stockInput = formAgregarProducto.querySelector(`input[name="${name}"]`);
            if (stockInput) {
                stockInput.addEventListener('keypress', function(e) {
                    // Solo permitir números
                    if (!/^[0-9]$/.test(e.key)) {
                        e.preventDefault();
                    }
                    // Limitar longitud
                    if (this.value.length >= 6) {
                        e.preventDefault();
                    }
                });
                stockInput.addEventListener('input', function(e) {
                    // Solo números, sin límite de ceros iniciales (permitir 0)
                    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
                });
            }
        });

        // Precios: solo números decimales, máximo dos decimales
        ['precio_compra', 'precio_venta'].forEach(name => {
            const precioInput = formAgregarProducto.querySelector(`input[name="${name}"]`);
            if (precioInput) {
                precioInput.addEventListener('keypress', function(e) {
                    // Permitir números, punto decimal y teclas de control
                    if (!/^[0-9\.]$/.test(e.key)) {
                        e.preventDefault();
                    }
                    // Solo permitir un punto decimal
                    if (e.key === '.' && this.value.includes('.')) {
                        e.preventDefault();
                    }
                    // Limitar longitud total
                    if (this.value.length >= 10 && this.selectionStart === this.value.length) {
                        e.preventDefault();
                    }
                });
                precioInput.addEventListener('input', function(e) {
                    let val = this.value.replace(/[^0-9.]/g, '');
                    
                    // Manejar múltiples puntos decimales
                    const parts = val.split('.');
                    if (parts.length > 2) {
                        val = parts[0] + '.' + parts.slice(1).join('');
                    }
                    
                    // Limitar decimales a 2 dígitos
                    if (parts[1] && parts[1].length > 2) {
                        parts[1] = parts[1].slice(0, 2);
                        val = parts[0] + '.' + parts[1];
                    }
                    
                    // Limitar longitud total
                    val = val.slice(0, 10);
                    
                    // No permitir que empiece con punto
                    if (val.startsWith('.')) {
                        val = '0' + val;
                    }
                    
                    this.value = val;
                });
            }
        });

        // Fechas: configurar fecha mínima para vencimiento
        const fechaFabInput = formAgregarProducto.querySelector('input[name="fecha_fabricacion"]');
        const fechaVenInput = formAgregarProducto.querySelector('input[name="fecha_vencimiento"]');
        
        if (fechaFabInput && fechaVenInput) {
            fechaFabInput.addEventListener('input', function() {
                if (this.value) {
                    fechaVenInput.min = this.value;
                } else {
                    fechaVenInput.removeAttribute('min');
                }
            });
        }
    }

    // --- VALIDACIONES BÁSICAS EN TIEMPO REAL PARA MODAL DE EDICIÓN ---
    const formEditarProducto = document.getElementById('formEditarProducto');
    if (formEditarProducto) {
        // Nombre: bloquear escritura completamente
        const nombreEditInput = formEditarProducto.querySelector('input[name="nombre"]');
        if (nombreEditInput) {
            nombreEditInput.addEventListener('keypress', function(e) {
                e.preventDefault();
            });
            nombreEditInput.addEventListener('input', function(e) {
                e.preventDefault();
            });
        }

        // Concentración: bloquear escritura completamente
        const concentracionEditInput = formEditarProducto.querySelector('#edit-concentracion');
        if (concentracionEditInput) {
            concentracionEditInput.addEventListener('keypress', function(e) {
                e.preventDefault();
            });
            concentracionEditInput.addEventListener('input', function(e) {
                e.preventDefault();
            });
        }

        // Lote: solo letras, números, espacios, guiones y puntos
        const loteEditInput = formEditarProducto.querySelector('input[name="lote"]');
        if (loteEditInput) {
            loteEditInput.addEventListener('keypress', function(e) {
                if (!/^[a-zA-Z0-9\s\-.]$/.test(e.key)) {
                    e.preventDefault();
                }
            });
            loteEditInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^a-zA-Z0-9\s\-.]/g, '');
            });
        }
    }

    // Cargar categorías dinámicamente en el modal de agregar producto
    function cargarCategoriasEnSelect() {
        fetch('/inventario/categoria/api/all')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const select = document.querySelector('#modalAgregar select[name="categoria"]');
                    if (select) {
                        select.innerHTML = '<option value="">Seleccionar</option>';
                        data.data.forEach(cat => {
                            select.innerHTML += `<option value="${cat.nombre}">${cat.nombre}</option>`;
                        });
                    }
                }
            });
    }

    // Mostrar el modal y cargar categorías
    const btnAgregarProducto = document.getElementById('btnAgregarProducto');
    const modalAgregar = document.getElementById('modalAgregar');
    if (btnAgregarProducto && modalAgregar) {
        btnAgregarProducto.addEventListener('click', function() {
            cargarCategoriasEnSelect();
            modalAgregar.classList.remove('hidden');
            modalAgregar.classList.add('flex');
            modalAgregar.style.display = 'flex';
            document.body.classList.add('modal-open');
        });
        // Cerrar modal agregar correctamente
        const btnCloseAdd = document.getElementById('closeAgregar');
        const btnCancelAdd = document.getElementById('btnCancelarAgregar');
        const closeAdd = ()=>{ modalAgregar.classList.add('hidden'); modalAgregar.classList.remove('flex'); modalAgregar.style.display = 'none'; document.body.classList.remove('modal-open'); };
        if (btnCloseAdd) btnCloseAdd.addEventListener('click', closeAdd);
        if (btnCancelAdd) btnCancelAdd.addEventListener('click', closeAdd);
    }

    // Cargar categorías dinámicamente en el modal de editar producto
    function cargarCategoriasEnSelectEditar(valorSeleccionado = '') {
        fetch('/inventario/categoria/api/all')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const select = document.querySelector('#modalEditar select[name="categoria"]');
                    if (select) {
                        select.innerHTML = '<option value="">Seleccionar</option>';
                        data.data.forEach(cat => {
                            select.innerHTML += `<option value="${cat.nombre}"${cat.nombre === valorSeleccionado ? ' selected' : ''}>${cat.nombre}</option>`;
                        });
                    }
                }
            });
    }

    // Helpers para editar: cargar selects
    async function cargarPresentacionesEnSelectEditar(valorSeleccionado = '') {
        try {
            const res = await fetch('/inventario/presentacion/api');
            const data = await res.json();
            if (data.success) {
                const select = document.querySelector('#modalEditar select[name="presentacion"]');
                if (select) {
                    select.innerHTML = '<option value="">Seleccionar</option>';
                    data.data.forEach(p => {
                        select.innerHTML += `<option value="${p.nombre}"${p.nombre === valorSeleccionado ? ' selected' : ''}>${p.nombre}</option>`;
                    });
                }
            }
        } catch(e) { console.error(e); }
    }

    async function cargarProveedoresEnSelectEditar(valorSeleccionado = '') {
        try {
            const res = await fetch('/api/compras/buscar-proveedores');
            const data = await res.json();
            if (data.success) {
                const select = document.querySelector('#modalEditar select[name="proveedor_id"]');
                if (select) {
                    select.innerHTML = '<option value="">Seleccionar</option>';
                    data.data.forEach(pr => {
                        select.innerHTML += `<option value="${pr.id}"${String(pr.id) === String(valorSeleccionado) ? ' selected' : ''}>${pr.nombre || pr.razon_social || 'Proveedor'}</option>`;
                    });
                }
            }
        } catch(e) { console.error(e); }
    }

    // Mostrar el modal de editar y cargar categorías
    const modalEditar = document.getElementById('modalEditar');
    if (modalEditar) {
        window.abrirModalEditarProducto = function(producto) {
            // ID oculto
            const campoId = document.getElementById('edit-producto-id');
            if (campoId) campoId.value = producto.id;

            // Inputs
            const campos = {
                'edit-nombre': producto.nombre,
                'edit-concentracion': producto.concentracion,
                'edit-marca': producto.marca,
                'edit-lote': producto.lote,
                'edit-codigo_barras': producto.codigo_barras,
                'edit-stock_actual': producto.stock_actual,
                'edit-stock_minimo': producto.stock_minimo,
                'edit-precio_compra': producto.precio_compra,
                'edit-precio_venta': producto.precio_venta,
                'edit-fecha_fabricacion': producto.fecha_fabricacion || '',
                'edit-fecha_vencimiento': producto.fecha_vencimiento || ''
            };
            Object.entries(campos).forEach(([id, val]) => { const el = document.getElementById(id); if (el) el.value = val ?? ''; });

            // Selects
            cargarCategoriasEnSelectEditar(producto.categoria);
            cargarPresentacionesEnSelectEditar(producto.presentacion);
            cargarProveedoresEnSelectEditar(producto.proveedor_id);

            // Imagen
            const prev = document.getElementById('edit-preview-container');
            const img = document.getElementById('edit-preview-image');
            if (prev && img) {
                prev.style.display = 'block';
                img.src = producto.imagen_url || '/assets/images/default-product.svg';
                img.onerror = function(){ this.src = '/assets/images/default-product.svg'; };
            }

            modalEditar.classList.remove('hidden');
            modalEditar.classList.add('flex');
            modalEditar.style.display = 'flex';
            document.body.classList.add('modal-open');
        };
        // Cerrar modal editar correctamente
        const btnCloseEdit = document.getElementById('closeEditar');
        const btnCancelEdit = document.getElementById('btnCancelarEditar');
        const closeEdit = ()=>{ modalEditar.classList.add('hidden'); modalEditar.classList.remove('flex'); modalEditar.style.display = 'none'; document.body.classList.remove('modal-open'); };
        if (btnCloseEdit) btnCloseEdit.addEventListener('click', closeEdit);
        if (btnCancelEdit) btnCancelEdit.addEventListener('click', closeEdit);
    }
});

function updateFilterCounter(visible, total, estado) {
    const estadoLabels = {
        'todos': 'Todos',
        'normal': 'Normal',
        'bajo_stock': 'Bajo stock',
        'por_vencer': 'Por vencer',
        'vencido': 'Vencido'
    };

    // Crear o actualizar el contador
    let counter = document.getElementById('filter-counter');
    if (!counter) {
        counter = document.createElement('div');
        counter.id = 'filter-counter';
        counter.className = 'text-sm text-gray-600 mt-2';
        const filterContainer = document.getElementById('filterEstado').parentNode;
        filterContainer.appendChild(counter);
    }
}