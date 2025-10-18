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
        // Nombre: bloquear escritura completamente
        const nombreInput = formAgregarProducto.querySelector('input[name="nombre"]');
        if (nombreInput) {
            nombreInput.addEventListener('keypress', function(e) {
                e.preventDefault();
            });
            nombreInput.addEventListener('input', function(e) {
                e.preventDefault();
            });
        }

        // Marca: solo letras y espacios
        const marcaInput = formAgregarProducto.querySelector('input[name="marca"]');
        if (marcaInput) {
            marcaInput.addEventListener('keypress', function(e) {
                if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]$/.test(e.key)) {
                    e.preventDefault();
                }
            });
            marcaInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
            });
        }

        // Concentración: bloquear escritura completamente
        const concentracionInput = formAgregarProducto.querySelector('input[name="concentracion"]');
        if (concentracionInput) {
            concentracionInput.addEventListener('keypress', function(e) {
                e.preventDefault();
            });
            concentracionInput.addEventListener('input', function(e) {
                e.preventDefault();
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

        // Stock: solo números, sin ceros iniciales
        ['stock_actual', 'stock_minimo'].forEach(name => {
            const stockInput = formAgregarProducto.querySelector(`input[name="${name}"]`);
            if (stockInput) {
                stockInput.addEventListener('keypress', function(e) {
                    if ((e.key === '0' && this.value.length === 0) || !/^[0-9]$/.test(e.key)) {
                        e.preventDefault();
                    }
                    if (this.value.length >= 5) {
                        e.preventDefault();
                    }
                });
                stockInput.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '').slice(0, 5);
                });
            }
        });

        // Precios: solo números decimales, máximo dos decimales
        ['precio_compra', 'precio_venta'].forEach(name => {
            const precioInput = formAgregarProducto.querySelector(`input[name="${name}"]`);
            if (precioInput) {
                precioInput.addEventListener('keypress', function(e) {
                    if ((e.key === '.' && this.value.length === 0) || !/^[0-9\.]$/.test(e.key)) {
                        e.preventDefault();
                    }
                    if (e.key === '.' && this.value.includes('.')) {
                        e.preventDefault();
                    }
                    if (e.key === '0' && this.value.length === 0) {
                        e.preventDefault();
                    }
                    if (this.value.length >= 8 && this.selectionStart === this.value.length) {
                        e.preventDefault();
                    }
                });
                precioInput.addEventListener('input', function(e) {
                    let val = this.value.replace(/[^0-9.]/g, '');
                    const parts = val.split('.');
                    if (parts.length > 2) {
                        val = parts[0] + '.' + parts.slice(1).join('');
                    }
                    if (parts[1]) {
                        parts[1] = parts[1].slice(0, 2);
                        val = parts[0] + '.' + parts[1];
                    }
                    val = val.slice(0, 8);
                    if (val.startsWith('.')) val = '';
                    if (/^0[0-9]+$/.test(val)) val = val.replace(/^0+/, '');
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
        });
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

    // Mostrar el modal de editar y cargar categorías
    const modalEditar = document.getElementById('modalEditar');
    if (modalEditar) {
        window.abrirModalEditarProducto = function(producto) {
            cargarCategoriasEnSelectEditar(producto.categoria);
            modalEditar.classList.remove('hidden');
            modalEditar.classList.add('flex');
        };
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