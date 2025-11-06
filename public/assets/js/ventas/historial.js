console.log('✅ Historial de Ventas - JavaScript cargado');

// Variables globales
let timeout;

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Inicializando Historial de Ventas');
    
    // Configurar eventos
    configurarFiltros();
    configurarBusqueda();
    configurarAcciones();
    
    console.log('✅ Historial de Ventas inicializado correctamente');
});

// Configurar filtros automáticos
function configurarFiltros() {
    const filtros = ['filtroMetodo', 'filtroComprobante', 'filtroUsuario'];
    
    filtros.forEach(filtroId => {
        const filtro = document.getElementById(filtroId);
        if (filtro) {
            filtro.addEventListener('change', function() {
                console.log(`📊 Filtro ${filtroId} cambiado a:`, this.value);
                document.getElementById('filtrosForm').submit();
            });
        }
    });
}

// Configurar búsqueda con delay
function configurarBusqueda() {
    const searchInput = document.getElementById('searchHistorial');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            const query = this.value;
            
            console.log('🔍 Búsqueda:', query);
            
            timeout = setTimeout(() => {
                if (query.length >= 3 || query.length === 0) {
                    document.getElementById('filtrosForm').submit();
                }
            }, 200); // Reducido de 500ms a 200ms para mayor velocidad
        });
        
        // Enter para buscar inmediato
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(timeout);
                document.getElementById('filtrosForm').submit();
            }
        });
    }
}

// Configurar acciones de los botones
function configurarAcciones() {
    // Botones de ver detalle
    document.querySelectorAll('[onclick^="verDetalleVenta"]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const ventaId = this.getAttribute('onclick').match(/\d+/)[0];
            mostrarDetalleVenta(ventaId);
        });
    });
    
    // Botones de imprimir
    document.querySelectorAll('[onclick^="imprimirComprobante"]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const ventaId = this.getAttribute('onclick').match(/\d+/)[0];
            imprimirComprobante(ventaId);
        });
    });
}

// Función mejorada para mostrar detalle de venta
function mostrarDetalleVenta(ventaId) {
    console.log('👁️ Mostrando detalle de venta:', ventaId);
    
    // Mostrar loading con diseño moderno
    Swal.fire({
        title: 'Cargando Detalle',
        html: `
            <div style="text-align: center; padding: 20px;">
                <div style="font-size: 2em; margin-bottom: 16px;">📋</div>
                <p style="color: #6b7280;">Obteniendo información de la venta...</p>
            </div>
        `,
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Hacer petición AJAX para obtener datos reales
    fetch(`/ventas/detalle/${ventaId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const venta = data.venta;
                mostrarModalDetalleVenta(venta);
            } else {
                throw new Error(data.message || 'Error al cargar detalle');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error al Cargar',
                text: 'No se pudo obtener el detalle de la venta',
                confirmButtonColor: '#dc2626'
            });
        });
}

// Modal mejorado con Material Icons y información de devoluciones
function mostrarModalDetalleVenta(venta) {
    const fechaFormateada = new Date(venta.fecha_venta || venta.created_at).toLocaleString('es-PE');
    
    // Estado de la venta con iconos
    let estadoHtml = '';
    if (venta.estado === 'devuelta') {
        estadoHtml = `
            <div style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border-radius: 8px; padding: 12px; margin-bottom: 16px; border: 1px solid #fca5a5;">
                <div style="display: flex; align-items: center; justify-content: center;">
                    <i class="material-icons" style="color: #dc2626; margin-right: 8px;">refresh</i>
                    <span style="color: #dc2626; font-weight: 700;">VENTA DEVUELTA COMPLETAMENTE</span>
                </div>
            </div>
        `;
    } else if (venta.estado === 'parcialmente_devuelta') {
        estadoHtml = `
            <div style="background: linear-gradient(135deg, #fef3e2 0%, #fed7aa 100%); border-radius: 8px; padding: 12px; margin-bottom: 16px; border: 1px solid #fdba74;">
                <div style="display: flex; align-items: center; justify-content: center;">
                    <i class="material-icons" style="color: #ea580c; margin-right: 8px;">history</i>
                    <span style="color: #ea580c; font-weight: 700;">VENTA PARCIALMENTE DEVUELTA</span>
                </div>
            </div>
        `;
    }

    // Información de descuentos si existen
    let descuentosHtml = '';
    if (venta.tiene_descuento) {
        descuentosHtml = `
            <div style="margin-bottom: 20px; background: #fef3e2; border-radius: 12px; padding: 16px; border: 1px solid #fdba74;">
                <div style="display: flex; align-items: center; margin-bottom: 12px;">
                    <i class="material-icons" style="color: #ea580c; margin-right: 8px;">local_offer</i>
                    <h4 style="margin: 0; color: #ea580c; font-weight: 600;">Información de Descuento</h4>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                    <div style="text-align: center; background: white; border-radius: 8px; padding: 12px;">
                        <div style="color: #ea580c; font-weight: 700; font-size: 1.2em;">S/ ${parseFloat(venta.descuento_monto || 0).toFixed(2)}</div>
                        <div style="color: #6b7280; font-size: 0.875rem;">Monto Descontado</div>
                    </div>
                    <div style="text-align: center; background: white; border-radius: 8px; padding: 12px;">
                        <div style="color: #ea580c; font-weight: 700; font-size: 1.2em;">${venta.descuento_porcentaje || 0}%</div>
                        <div style="color: #6b7280; font-size: 0.875rem;">Porcentaje</div>
                    </div>
                    <div style="text-align: center; background: white; border-radius: 8px; padding: 12px;">
                        <div style="color: #059669; font-weight: 700; font-size: 1.2em;">S/ ${parseFloat(venta.total).toFixed(2)}</div>
                        <div style="color: #6b7280; font-size: 0.875rem;">Total Final</div>
                    </div>
                </div>
                
                ${venta.descuento_razon ? `
                    <div style="background: white; border-radius: 8px; padding: 12px; border-left: 3px solid #ea580c;">
                        <div style="font-weight: 600; color: #374151; margin-bottom: 4px;">Motivo del Descuento:</div>
                        <div style="color: #6b7280; font-size: 0.875rem;">${venta.descuento_razon}</div>
                    </div>
                ` : ''}
            </div>
        `;
    }

    // Información de devoluciones si existen
    let devolucionesHtml = '';
    if (venta.tiene_devoluciones && venta.devoluciones && venta.devoluciones.length > 0) {
        devolucionesHtml = `
            <div style="margin-bottom: 20px; background: #f8fafc; border-radius: 12px; padding: 16px; border: 1px solid #e2e8f0;">
                <div style="display: flex; align-items: center; margin-bottom: 12px;">
                    <i class="material-icons" style="color: #dc2626; margin-right: 8px;">trending_down</i>
                    <h4 style="margin: 0; color: #dc2626; font-weight: 600;">Información de Devoluciones</h4>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                    <div style="text-align: center; background: white; border-radius: 8px; padding: 12px;">
                        <div style="color: #dc2626; font-weight: 700; font-size: 1.2em;">S/ ${parseFloat(venta.monto_total_devuelto || 0).toFixed(2)}</div>
                        <div style="color: #6b7280; font-size: 0.875rem;">Monto Devuelto</div>
                    </div>
                    <div style="text-align: center; background: white; border-radius: 8px; padding: 12px;">
                        <div style="color: #ea580c; font-weight: 700; font-size: 1.2em;">${venta.productos_afectados_por_devolucion || 0}</div>
                        <div style="color: #6b7280; font-size: 0.875rem;">Productos Afectados</div>
                    </div>
                    <div style="text-align: center; background: white; border-radius: 8px; padding: 12px;">
                        <div style="color: #059669; font-weight: 700; font-size: 1.2em;">S/ ${(parseFloat(venta.total) - parseFloat(venta.monto_total_devuelto || 0)).toFixed(2)}</div>
                        <div style="color: #6b7280; font-size: 0.875rem;">Monto Restante</div>
                    </div>
                </div>
                
                <div style="margin-top: 12px;">
                    <div style="font-weight: 600; color: #374151; margin-bottom: 8px;">Historial de Devoluciones:</div>
                    ${venta.devoluciones.map(devolucion => `
                        <div style="background: white; border-radius: 8px; padding: 12px; margin-bottom: 8px; border-left: 3px solid #dc2626;">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; color: #374151; margin-bottom: 4px;">${devolucion.producto.nombre}</div>
                                    <div style="font-size: 0.875rem; color: #6b7280;">
                                        <span>${devolucion.cantidad_devuelta} unidades</span> • 
                                        <span>${devolucion.motivo_formateado}</span> • 
                                        <span>${devolucion.fecha_devolucion}</span>
                                    </div>
                                    ${devolucion.observaciones ? `<div style="font-size: 0.8rem; color: #6b7280; margin-top: 4px; font-style: italic;">${devolucion.observaciones}</div>` : ''}
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-weight: 700; color: #dc2626;">-S/ ${parseFloat(devolucion.monto_devolucion).toFixed(2)}</div>
                                    <div style="font-size: 0.8rem; color: #6b7280;">por ${devolucion.usuario}</div>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    let productosHtml = '';
    if (venta.detalles && venta.detalles.length > 0) {
        productosHtml = venta.detalles.map(detalle => {
            const tieneDevolucion = detalle.tiene_devolucion || false;
            const cantidadDevuelta = detalle.cantidad_devuelta || 0;
            const cantidadRestante = detalle.cantidad_restante || detalle.cantidad;
            const devolucionCompleta = detalle.devolucion_completa || false;
            
            let estadoProducto = '';
            if (devolucionCompleta) {
                estadoProducto = '<span style="background: #fef2f2; color: #dc2626; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;"><i class="material-icons" style="font-size: 12px;">block</i> DEVUELTO</span>';
            } else if (tieneDevolucion) {
                estadoProducto = '<span style="background: #fef3e2; color: #ea580c; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;"><i class="material-icons" style="font-size: 12px;">history</i> PARCIAL</span>';
            }
            
            return `
                <div style="display: flex; justify-content: space-between; padding: 12px; background: ${devolucionCompleta ? '#fef2f2' : (tieneDevolucion ? '#fef3e2' : '#f9fafb')}; border-radius: 8px; margin-bottom: 8px; border-left: 3px solid ${devolucionCompleta ? '#dc2626' : (tieneDevolucion ? '#ea580c' : '#e5e7eb')};">
                    <div style="flex: 1;">
                        <div style="font-weight: 600; color: #374151; margin-bottom: 2px;">${detalle.producto.nombre}</div>
                        <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 4px;">${detalle.producto.concentracion || ''}</div>
                        ${estadoProducto}
                        ${tieneDevolucion ? `
                            <div style="font-size: 0.8rem; color: #6b7280; margin-top: 4px;">
                                Original: ${detalle.cantidad} • Devuelto: ${cantidadDevuelta} • Restante: ${cantidadRestante}
                            </div>
                        ` : ''}
                    </div>
                    <div style="text-align: center; min-width: 60px;">
                        <div style="font-weight: 600; color: ${devolucionCompleta ? '#dc2626' : '#059669'};">${cantidadRestante}</div>
                        <div style="font-size: 0.75rem; color: #6b7280;">unidades</div>
                        ${tieneDevolucion ? `<div style="font-size: 0.7rem; color: #dc2626; text-decoration: line-through;">-${cantidadDevuelta}</div>` : ''}
                    </div>
                    <div style="text-align: center; min-width: 80px;">
                        <div style="font-weight: 500; color: #374151;">S/. ${parseFloat(detalle.precio_unitario).toFixed(2)}</div>
                        <div style="font-size: 0.75rem; color: #6b7280;">c/u</div>
                    </div>
                    <div style="text-align: right; min-width: 80px;">
                        <div style="font-weight: 700; color: ${devolucionCompleta ? '#dc2626' : '#059669'};">S/. ${(cantidadRestante * detalle.precio_unitario).toFixed(2)}</div>
                        ${tieneDevolucion ? `<div style="font-size: 0.7rem; color: #dc2626; text-decoration: line-through;">-S/. ${(cantidadDevuelta * detalle.precio_unitario).toFixed(2)}</div>` : ''}
                    </div>
                </div>
            `;
        }).join('');
    }

        // Modificar el contenido si la venta está completamente devuelta
        let productosSection = '';
        let totalesSection = '';
        
        if (venta.estado === 'devuelta') {
            // Si está completamente devuelta, no mostrar "Productos (Estado Actual)"
            productosSection = '';
            totalesSection = '';
        } else {
            // Mostrar productos normalmente
            productosSection = `
                <!-- Productos -->
                <div style="margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; margin-bottom: 12px;">
                        <i class="material-icons" style="color: #059669; margin-right: 8px;">shopping_cart</i>
                        <h4 style="margin: 0; color: #059669; font-weight: 600;">Productos ${venta.tiene_devoluciones ? '(Estado Actual)' : 'Vendidos'}</h4>
                    </div>
                    ${productosHtml}
                </div>
            `;
            
            // Mostrar totales normalmente
            totalesSection = `
                <!-- Totales -->
                <div style="background: #f9fafb; border-radius: 12px; padding: 16px; border: 1px solid #e5e7eb;">
                    ${venta.tiene_descuento ? `
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="color: #6b7280;">Subtotal Original:</span>
                            <span style="font-weight: 500; color: #374151;">S/. ${parseFloat(venta.subtotal_original || 0).toFixed(2)}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="color: #dc2626;">Descuento ${venta.descuento_tipo === 'porcentaje' ? `(${venta.descuento_porcentaje}%)` : ''}:</span>
                            <span style="font-weight: 600; color: #dc2626;">-S/. ${parseFloat(venta.descuento_monto || 0).toFixed(2)}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                            <span style="color: #6b7280;">Subtotal con Descuento:</span>
                            <span style="font-weight: 500; color: #374151;">S/. ${parseFloat(venta.subtotal || 0).toFixed(2)}</span>
                        </div>
                    ` : `
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                            <span style="color: #6b7280;">Subtotal:</span>
                            <span style="font-weight: 500; color: #374151;">S/. ${parseFloat(venta.subtotal || 0).toFixed(2)}</span>
                        </div>
                    `}
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                        <span style="color: #6b7280;">IGV (18%):</span>
                        <span style="font-weight: 500; color: #374151;">S/. ${parseFloat(venta.igv || 0).toFixed(2)}</span>
                    </div>
                    ${venta.tiene_devoluciones ? `
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="color: #dc2626;">Monto Devuelto:</span>
                            <span style="font-weight: 600; color: #dc2626;">-S/. ${parseFloat(venta.monto_total_devuelto || 0).toFixed(2)}</span>
                        </div>
                    ` : ''}
                    <div style="display: flex; justify-content: space-between; padding-top: 12px; border-top: 2px solid #e5e7eb;">
                        <span style="font-weight: 700; color: #374151; font-size: 1.1em;">TOTAL ${venta.tiene_devoluciones ? 'ORIGINAL' : ''}:</span>
                        <span style="font-weight: 700; color: #dc2626; font-size: 1.2em;">S/. ${parseFloat(venta.total).toFixed(2)}</span>
                    </div>
                    ${venta.tiene_devoluciones ? `
                        <div style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 1px solid #e5e7eb; margin-top: 8px;">
                            <span style="font-weight: 700; color: #059669; font-size: 1.1em;">TOTAL ACTUAL:</span>
                            <span style="font-weight: 700; color: #059669; font-size: 1.2em;">S/. ${(parseFloat(venta.total) - parseFloat(venta.monto_total_devuelto || 0)).toFixed(2)}</span>
                        </div>
                    ` : ''}
                    
                    ${venta.metodo_pago === 'efectivo' && venta.vuelto > 0 ? `
                        <div style="background: #fef3c7; border-radius: 8px; padding: 8px; margin-top: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-weight: 600; color: #92400e;">💰 Vuelto:</span>
                                <span style="font-weight: 700; color: #92400e;">S/. ${parseFloat(venta.vuelto).toFixed(2)}</span>
                            </div>
                        </div>
                    ` : ''}
                </div>
            `;
        }
        
        Swal.fire({
        title: '<span style="color: #3b82f6; font-weight: 700;"><i class="material-icons" style="vertical-align: middle; margin-right: 8px;">receipt_long</i>Detalle de Venta</span>',
            html: `
            <div style="max-width: 850px; margin: 0 auto;">
                ${estadoHtml}
                
                <!-- Información Principal -->
                <div style="background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%); border-radius: 12px; padding: 20px; margin-bottom: 20px; border: 2px solid #3b82f6;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div>
                            <div style="display: flex; align-items: center; margin-bottom: 8px;">
                                <i class="material-icons" style="color: #3b82f6; margin-right: 8px;">confirmation_number</i>
                                <span style="font-weight: 600; color: #374151;">N° Venta</span>
                            </div>
                            <div style="font-family: monospace; font-weight: 700; color: #1e40af;">${venta.numero_venta}</div>
                        </div>
                        <div>
                            <div style="display: flex; align-items: center; margin-bottom: 8px;">
                                <i class="material-icons" style="color: #3b82f6; margin-right: 8px;">schedule</i>
                                <span style="font-weight: 600; color: #374151;">Fecha y Hora</span>
                            </div>
                            <div style="color: #1e40af; font-weight: 500;">${fechaFormateada}</div>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <div style="display: flex; align-items: center; margin-bottom: 8px;">
                                <i class="material-icons" style="color: #3b82f6; margin-right: 8px;">person</i>
                                <span style="font-weight: 600; color: #374151;">Atendido por</span>
                            </div>
                            <div style="color: #1e40af; font-weight: 500;">${venta.usuario ? venta.usuario.name : 'N/A'}</div>
                        </div>
                        <div>
                            <div style="display: flex; align-items: center; margin-bottom: 8px;">
                                <i class="material-icons" style="color: #3b82f6; margin-right: 8px;">${venta.tipo_comprobante === 'boleta' ? 'description' : 'receipt'}</i>
                                <span style="font-weight: 600; color: #374151;">Comprobante</span>
                            </div>
                            <div style="color: ${venta.tipo_comprobante === 'boleta' ? '#059669' : '#6b7280'}; font-weight: 500;">
                                ${venta.tipo_comprobante === 'boleta' ? 'Con Comprobante' : 'Sin Comprobante'}
                            </div>
                        </div>
                    </div>
                </div>

                ${devolucionesHtml}
                
                <!-- Sección de Descuentos -->
                ${venta.tiene_descuento ? `
                    <div style="background: linear-gradient(135deg, #fef3e2 0%, #fef7ed 100%); border-radius: 12px; padding: 20px; margin-bottom: 20px; border: 2px solid #f59e0b;">
                        <div style="display: flex; align-items: center; margin-bottom: 16px;">
                            <i class="material-icons" style="color: #f59e0b; margin-right: 8px; font-size: 24px;">local_offer</i>
                            <h4 style="margin: 0; color: #f59e0b; font-weight: 700;">Descuento Aplicado</h4>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div style="background: white; border-radius: 8px; padding: 12px; text-align: center;">
                                <div style="color: #dc2626; font-weight: 700; font-size: 1.4em;">
                                    ${venta.descuento_tipo === 'porcentaje' ? `${venta.descuento_porcentaje}%` : `S/. ${parseFloat(venta.descuento_monto || 0).toFixed(2)}`}
                                </div>
                                <div style="color: #6b7280; font-size: 0.875rem; font-weight: 500;">
                                    ${venta.descuento_tipo === 'porcentaje' ? 'Porcentaje' : 'Monto Fijo'}
                                </div>
                            </div>
                            <div style="background: white; border-radius: 8px; padding: 12px; text-align: center;">
                                <div style="color: #dc2626; font-weight: 700; font-size: 1.4em;">S/. ${parseFloat(venta.descuento_monto || 0).toFixed(2)}</div>
                                <div style="color: #6b7280; font-size: 0.875rem; font-weight: 500;">Ahorro Total</div>
                            </div>
                        </div>
                        
                        ${venta.descuento_razon ? `
                            <div style="background: white; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                                <div style="display: flex; align-items: center; margin-bottom: 8px;">
                                    <i class="material-icons" style="color: #6b7280; margin-right: 8px; font-size: 18px;">info</i>
                                    <span style="font-weight: 600; color: #374151;">Motivo del Descuento</span>
                                </div>
                                <div style="color: #6b7280; font-style: italic;">${venta.descuento_razon}</div>
                            </div>
                        ` : ''}
                        
                        ${venta.descuento_autorizado_por ? `
                            <div style="background: white; border-radius: 8px; padding: 12px;">
                                <div style="display: flex; align-items: center; margin-bottom: 8px;">
                                    <i class="material-icons" style="color: #6b7280; margin-right: 8px; font-size: 18px;">verified_user</i>
                                    <span style="font-weight: 600; color: #374151;">Autorizado por</span>
                                </div>
                                <div style="color: #059669; font-weight: 500;">${venta.descuento_autorizado_por}</div>
                            </div>
                        ` : ''}
                    </div>
                ` : ''}
                
                ${productosSection}
                ${totalesSection}
                </div>
            `,
            showCancelButton: false,
        confirmButtonText: '<i class="material-icons" style="margin-right: 4px;">check</i>Entendido',
        confirmButtonColor: '#059669',
        width: '900px',
        customClass: {
            popup: 'swal-popup-detail',
            confirmButton: 'btn-entendido-visible'
        }
        });
}

// Función para reimprimir comprobante
function reimprimirComprobante(ventaId) {
    console.log('🖨️ Reimprimiendo comprobante para venta:', ventaId);
    
    Swal.fire({
        icon: 'info',
        title: 'Reimprimiendo...',
        html: `
            <div style="text-align: center; padding: 20px;">
                <div style="font-size: 3em; margin-bottom: 16px;">🖨️</div>
                <p>Preparando comprobante para impresión...</p>
            </div>
        `,
        showConfirmButton: false,
        timer: 2000
    }).then(() => {
        // Aquí implementarás la lógica real de impresión
        window.open(`/ventas/imprimir/${ventaId}`, '_blank');
    });
}

// Imprimir comprobante
function imprimirComprobante(ventaId) {
    console.log('🖨️ Imprimir comprobante de venta:', ventaId);
    
    Swal.fire({
        title: '🖨️ Imprimir Comprobante',
        text: '¿Qué deseas hacer?',
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Ver PDF',
        denyButtonText: 'Descargar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#059669',
        denyButtonColor: '#dc2626'
    }).then((result) => {
        if (result.isConfirmed) {
            // Abrir PDF en nueva ventana
            window.open(`/ventas/${ventaId}/pdf`, '_blank');
            
            Swal.fire({
                title: '✅ PDF Abierto',
                text: 'El comprobante se abrió en una nueva ventana',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        } else if (result.isDenied) {
            // Descargar PDF
            const link = document.createElement('a');
            link.href = `/ventas/${ventaId}/pdf?download=1`;
            link.download = `venta_${ventaId}.pdf`;
            link.click();
            
            Swal.fire({
                title: '📥 Descargando...',
                text: 'El comprobante se está descargando',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
}

// Limpiar todos los filtros
function limpiarFiltros() {
    console.log('🧹 Limpiando filtros');
    
    // Limpiar campos
    document.getElementById('searchHistorial').value = '';
    document.getElementById('filtroMetodo').value = '';
    document.getElementById('filtroComprobante').value = '';
    document.getElementById('filtroUsuario').value = '';
    
    // Enviar formulario
    document.getElementById('filtrosForm').submit();
}

// Exportar datos (función futura)
function exportarVentas() {
    console.log('📊 Exportar ventas');
    
    Swal.fire({
        title: '📊 Exportar Ventas',
        text: '¿En qué formato deseas exportar?',
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Excel',
        denyButtonText: 'PDF',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#059669',
        denyButtonColor: '#dc2626'
    }).then((result) => {
        if (result.isConfirmed) {
            // Exportar a Excel
            window.location.href = '/ventas/export/excel';
        } else if (result.isDenied) {
            // Exportar a PDF
            window.location.href = '/ventas/export/pdf';
        }
    });
}

// Funciones de utilidad
function formatearFecha(fecha) {
    return new Date(fecha).toLocaleDateString('es-PE');
}

function formatearMoneda(cantidad) {
    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN'
    }).format(cantidad);
}

// Mostrar tooltip en hover
function mostrarTooltip(elemento, texto) {
    elemento.setAttribute('title', texto);
}

// Event listeners para efectos visuales
document.addEventListener('DOMContentLoaded', function() {
    // Efecto hover en filas de la tabla
    const filas = document.querySelectorAll('.historial-data-row');
    filas.forEach(fila => {
        fila.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
            this.style.transition = 'transform 0.2s ease';
        });
        
        fila.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
});

// Función para actualizar estadísticas en tiempo real (si es necesario)
function actualizarEstadisticas() {
    console.log('📈 Actualizando estadísticas...');
    
    // Aquí podrías hacer una petición AJAX para obtener estadísticas actualizadas
    // Por ahora solo mostramos un mensaje
    const toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
    
    toast.fire({
        icon: 'info',
        title: 'Estadísticas actualizadas'
    });
}

console.log('✅ Historial de Ventas - JavaScript completamente cargado');