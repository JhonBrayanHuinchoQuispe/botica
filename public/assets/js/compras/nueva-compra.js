document.addEventListener('DOMContentLoaded', function() {
    // Elementos del formulario
    const form = document.getElementById('formEntradaMercaderia');
    const buscarProductoInput = document.getElementById('buscar-producto');
    const productoIdInput = document.getElementById('producto-id');
    const resultadosContainer = document.getElementById('resultados-busqueda');
    const btnProcesar = document.getElementById('btn-procesar-entrada');
    const btnRegistrar = document.getElementById('btn-registrar-entrada');
    const btnLimpiarProducto = document.getElementById('btn-limpiar-producto');

    // Configuración de búsqueda
    let timeoutBusqueda = null;
    let productoSeleccionado = null;

    // Inicializar eventos
    if (buscarProductoInput) {
        buscarProductoInput.addEventListener('input', manejarBusquedaProducto);
        buscarProductoInput.addEventListener('focus', mostrarResultados);
    }

    if (btnLimpiarProducto) {
        btnLimpiarProducto.addEventListener('click', limpiarProductoSeleccionado);
    }

    if (form) {
        form.addEventListener('submit', procesarEntrada);
        // Agregar event listeners para validación en tiempo real
        form.addEventListener('input', validarFormulario);
        form.addEventListener('change', validarFormulario);
    }

    // Validación inicial
    validarFormulario();
    
    // Ocultar botón limpiar inicialmente
    ocultarBotonLimpiar();

    // Función para manejar la búsqueda de productos
    function manejarBusquedaProducto(e) {
        const termino = e.target.value.trim();
        
        // Mostrar/ocultar botón limpiar basado en si hay texto
        if (termino.length > 0) {
            mostrarBotonLimpiar();
        } else {
            ocultarBotonLimpiar();
        }
        
        // Limpiar timeout anterior
        if (timeoutBusqueda) {
            clearTimeout(timeoutBusqueda);
        }

        if (termino.length < 2) {
            ocultarResultados();
            limpiarSeleccion();
            return;
        }

        // Debounce para evitar muchas peticiones
        timeoutBusqueda = setTimeout(() => {
            buscarProductos(termino);
        }, 300);
    }

    // Función para buscar productos
    async function buscarProductos(termino) {
        try {
            console.log('Buscando productos con término:', termino);
            
            const response = await fetch(`/api/compras/buscar-productos?q=${encodeURIComponent(termino)}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            });

            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Error response:', errorText);
                throw new Error(`Error ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            console.log('Datos recibidos:', data);
            
            if (data.success === false) {
                throw new Error(data.message || 'Error en la búsqueda');
            }
            
            mostrarResultadosBusqueda(data.productos || []);
        } catch (error) {
            console.error('Error completo al buscar productos:', error);
            mostrarMensajeError(`Error al buscar productos: ${error.message}`);
        }
    }

    // Función para mostrar resultados de búsqueda con autocompletado inteligente
    function mostrarResultadosBusqueda(productos) {
        if (!resultadosContainer) return;

        if (productos.length === 0) {
            resultadosContainer.innerHTML = '<div class="compras-no-resultados">No se encontraron productos</div>';
            resultadosContainer.style.display = 'block';
            return;
        }

        // Guardar productos en sessionStorage para acceso posterior
        sessionStorage.setItem('ultimosBusquedaProductos', JSON.stringify(productos));

        const html = productos.map(producto => {
            // Determinar clases CSS según los estados aplicables
            let claseEstado = '';
            let iconosEstado = [];
            let textosEstado = [];

            // Procesar todos los estados aplicables
            if (producto.estados_aplicables && Array.isArray(producto.estados_aplicables)) {
                producto.estados_aplicables.forEach(estado => {
                    switch(estado.toLowerCase()) {
                        case 'agotado':
                            claseEstado += ' stock-agotado';
                            iconosEstado.push('⚠️');
                            textosEstado.push('Agotado');
                            break;
                        case 'bajo stock':
                            claseEstado += ' stock-bajo';
                            iconosEstado.push('⚡');
                            textosEstado.push('Bajo Stock');
                            break;
                        case 'por vencer':
                            claseEstado += ' proximo-vencimiento';
                            iconosEstado.push('⏰');
                            textosEstado.push('Por Vencer');
                            break;
                        case 'vencido':
                            claseEstado += ' vencido';
                            iconosEstado.push('❌');
                            textosEstado.push('Vencido');
                            break;
                        case 'normal':
                            // No agregar clases especiales para estado normal
                            break;
                    }
                });
            }

            // Unir iconos y textos
            const iconoEstado = iconosEstado.join(' ');
            const textoEstado = textosEstado.join(' - ');

            const esSugerido = producto.sugerido ? 'sugerido' : '';
            const iconoSugerido = producto.sugerido ? '⭐' : '';

            return `
                <div class="compras-resultado-item ${claseEstado} ${esSugerido}" data-producto-id="${producto.id}">
                    <div class="compras-resultado-info">
                        <div class="compras-resultado-nombre">
                            ${iconoSugerido} ${producto.nombre}
                            ${iconoEstado ? `<span class="estado-icono">${iconoEstado}</span>` : ''}
                        </div>
                        <div class="compras-resultado-detalles">
                            ${producto.presentacion} - ${producto.concentracion}
                            <span class="compras-stock ${claseEstado}">Stock: ${producto.stock_actual}</span>
                            ${producto.lote ? `<span class="compras-lote">Lote: ${producto.lote}</span>` : ''}
                        </div>
                        ${textoEstado ? `<div class="compras-estado-texto">${textoEstado}</div>` : ''}
                        <div class="compras-historial">Última entrada: ${producto.texto_ultima_entrada}</div>
                    </div>
                    <div class="compras-resultado-precio">S/. ${parseFloat(producto.precio_venta || 0).toFixed(2)}</div>
                </div>
            `;
        }).join('');

        resultadosContainer.innerHTML = html;
        resultadosContainer.style.display = 'block';

        // Agregar eventos de click a los resultados
        resultadosContainer.querySelectorAll('.compras-resultado-item').forEach(item => {
            item.addEventListener('click', () => seleccionarProducto(item));
        });
    }

    // Función para seleccionar un producto
    function seleccionarProducto(item) {
        const productoId = item.dataset.productoId;
        
        // Obtener datos adicionales del producto desde el dataset o buscar en los resultados
        const productos = JSON.parse(sessionStorage.getItem('ultimosBusquedaProductos') || '[]');
        const producto = productos.find(p => p.id == productoId);
        
        // Obtener el nombre limpio desde los datos originales (sin íconos ni espacios extra)
        const nombreProducto = producto ? producto.nombre : item.querySelector('.compras-resultado-nombre').textContent.replace(/⭐|⚠️|⚡|⏰/g, '').trim();
        const precioVenta = item.querySelector('.compras-resultado-precio').textContent.replace('S/. ', '');
        
        // Guardar producto seleccionado
        productoSeleccionado = {
            id: productoId,
            nombre: nombreProducto,
            precio_compra: producto ? producto.precio_compra : null,
            precio_venta: producto ? producto.precio_venta : precioVenta
        };

        // Actualizar campos con el nombre limpio
        buscarProductoInput.value = nombreProducto;
        productoIdInput.value = productoId;

        // Actualizar placeholders de precios
        const precioCompraInput = document.querySelector('input[name="precio_compra"]');
        const precioVentaInput = document.querySelector('input[name="precio_venta"]');
        
        if (precioCompraInput && productoSeleccionado.precio_compra) {
            precioCompraInput.placeholder = `Precio actual: S/. ${parseFloat(productoSeleccionado.precio_compra).toFixed(2)}`;
        }
        
        if (precioVentaInput && productoSeleccionado.precio_venta) {
            precioVentaInput.placeholder = `Precio actual: S/. ${parseFloat(productoSeleccionado.precio_venta).toFixed(2)}`;
        }

        // Ocultar resultados
        ocultarResultados();
        
        // Validar formulario después de seleccionar producto
        validarFormulario();
    }

    // Función para mostrar resultados
    function mostrarResultados() {
        if (resultadosContainer && resultadosContainer.innerHTML.trim()) {
            resultadosContainer.style.display = 'block';
        }
    }

    // Función para ocultar resultados
    function ocultarResultados() {
        if (resultadosContainer) {
            resultadosContainer.style.display = 'none';
        }
    }

    // Función para limpiar selección
    function limpiarSeleccion() {
        productoSeleccionado = null;
        if (productoIdInput) {
            productoIdInput.value = '';
        }
        
        // Resetear placeholders de precios (vacíos cuando no hay producto seleccionado)
        const precioCompraInput = document.querySelector('input[name="precio_compra"]');
        const precioVentaInput = document.querySelector('input[name="precio_venta"]');
        
        if (precioCompraInput) {
            precioCompraInput.placeholder = '';
        }
        
        if (precioVentaInput) {
            precioVentaInput.placeholder = '';
        }
        
        // Ocultar botón limpiar
        ocultarBotonLimpiar();
        
        // Validar formulario después de limpiar
        validarFormulario();
    }

    // Función para mostrar el botón limpiar
    function mostrarBotonLimpiar() {
        if (btnLimpiarProducto) {
            btnLimpiarProducto.style.display = 'flex';
        }
    }

    // Función para ocultar el botón limpiar
    function ocultarBotonLimpiar() {
        if (btnLimpiarProducto) {
            btnLimpiarProducto.style.display = 'none';
        }
    }

    // Función para limpiar producto seleccionado (botón X)
    function limpiarProductoSeleccionado() {
        // Limpiar el campo de búsqueda
        if (buscarProductoInput) {
            buscarProductoInput.value = '';
        }
        
        // Limpiar la selección
        limpiarSeleccion();
        
        // Ocultar resultados
        ocultarResultados();
        
        // Enfocar el campo de búsqueda
        if (buscarProductoInput) {
            buscarProductoInput.focus();
        }
    }

    // Función para validar el formulario en tiempo real
    function validarFormulario() {
        if (!btnRegistrar) return;

        // Obtener valores de los campos obligatorios
        const productoId = document.getElementById('producto-id')?.value || '';
        const proveedorId = document.querySelector('select[name="proveedor_id"]')?.value || '';
        const cantidad = document.querySelector('input[name="cantidad"]')?.value || '';
        const lote = document.querySelector('input[name="lote"]')?.value || '';

        // Verificar que todos los campos obligatorios estén completos
        const formularioValido = productoId.trim() !== '' && 
                                proveedorId.trim() !== '' && 
                                cantidad.trim() !== '' && 
                                parseFloat(cantidad) > 0 &&
                                lote.trim() !== '';

        // Habilitar o deshabilitar el botón
        btnRegistrar.disabled = !formularioValido;
    }

    // Función para procesar entrada de mercadería
    async function procesarEntrada(e) {
        e.preventDefault();

        if (!productoSeleccionado) {
            Swal.fire({
                icon: 'warning',
                title: 'Producto requerido',
                text: 'Debe seleccionar un producto válido'
            });
            return;
        }

        // Validaciones adicionales del frontend
        const cantidad = parseFloat(document.querySelector('input[name="cantidad"]')?.value || 0);
        const precioCompra = parseFloat(document.querySelector('input[name="precio_compra"]')?.value || 0);
        const precioVenta = parseFloat(document.querySelector('input[name="precio_venta"]')?.value || 0);
        const fechaVencimiento = document.querySelector('input[name="fecha_vencimiento"]')?.value;

        // Validar cantidad
        if (cantidad <= 0 || cantidad > 999999) {
            Swal.fire({
                icon: 'warning',
                title: 'Cantidad inválida',
                text: 'La cantidad debe ser mayor a 0 y menor a 999,999'
            });
            return;
        }

        // Validar precios si se proporcionan
        if (precioCompra > 0 && precioVenta > 0 && precioVenta <= precioCompra) {
            Swal.fire({
                icon: 'warning',
                title: 'Precios inválidos',
                text: 'El precio de venta debe ser mayor al precio de compra'
            });
            return;
        }

        // Validar fecha de vencimiento
        if (fechaVencimiento) {
            const fechaVenc = new Date(fechaVencimiento);
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            
            if (fechaVenc < hoy) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Fecha inválida',
                    text: 'La fecha de vencimiento no puede ser anterior a hoy'
                });
                return;
            }
        }

        // Mostrar loading
        Swal.fire({
            title: 'Procesando entrada...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const formData = new FormData(form);
            const token = document.querySelector('meta[name="csrf-token"]').content;

            const response = await fetch('/compras/procesar', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok && data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Entrada procesada!',
                    html: `
                        <div style="text-align: left;">
                            <p><strong>Producto:</strong> ${data.data.producto}</p>
                            <p><strong>Cantidad:</strong> ${data.data.cantidad} unidades</p>
                            <p><strong>Stock anterior:</strong> ${data.data.stock_anterior}</p>
                            <p><strong>Stock nuevo:</strong> ${data.data.stock_nuevo}</p>
                            <p><strong>Lote:</strong> ${data.data.lote}</p>
                            ${data.data.proveedor ? `<p><strong>Proveedor:</strong> ${data.data.proveedor}</p>` : ''}
                        </div>
                    `,
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    // Disparar evento personalizado para notificar actualización de productos
                    const evento = new CustomEvent('productoActualizado', {
                        detail: {
                            tipo: 'entrada_mercaderia',
                            producto_id: productoSeleccionado.id,
                            timestamp: Date.now()
                        }
                    });
                    window.dispatchEvent(evento);
                    
                    // También usar localStorage para comunicación entre ventanas/pestañas
                    localStorage.setItem('producto_actualizado', JSON.stringify({
                        tipo: 'entrada_mercaderia',
                        producto_id: productoSeleccionado.id,
                        timestamp: Date.now()
                    }));
                    
                    // Limpiar formulario
                    form.reset();
                    limpiarSeleccion();
                    buscarProductoInput.value = '';
                    ocultarResultados();
                });
            } else {
                // Manejar errores de validación del servidor
                if (response.status === 422 && data.errors) {
                    // Errores de validación específicos
                    const errores = Object.values(data.errors).flat();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Datos inválidos',
                        html: `<ul style="text-align: left;">${errores.map(error => `<li>${error}</li>`).join('')}</ul>`
                    });
                } else {
                    // Otros errores
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error al procesar la entrada'
                    });
                }
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor. Verifique su conexión a internet.'
            });
        }
    }

    // Función para mostrar mensaje de error
    function mostrarMensajeError(mensaje) {
        if (resultadosContainer) {
            resultadosContainer.innerHTML = `<div class="compras-error">${mensaje}</div>`;
            resultadosContainer.style.display = 'block';
        }
    }

    // Cerrar resultados al hacer click fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.compras-busqueda-container')) {
            ocultarResultados();
        }
    });

    // Manejar teclas en el input de búsqueda
    if (buscarProductoInput) {
        buscarProductoInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                ocultarResultados();
            }
        });
    }
});