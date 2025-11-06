document.addEventListener('DOMContentLoaded', () => {
  const tbody = document.getElementById('productos-botica-tbody');
  const info = document.getElementById('productos-botica-pagination-info');
  const perPageEl = document.getElementById('mostrarBotica');
  const estadoEl = document.getElementById('estadoBotica');
  const searchEl = document.getElementById('buscarProductoBotica');
  const clearBtn = document.getElementById('clearBuscarProductoBotica');

  let page = 1;

  // Loading overlay (reutiliza el componente común)
  const loadingOverlay = document.getElementById('loadingOverlay');
  function showLoading(label = 'Cargando datos...') {
    if (loadingOverlay) {
      loadingOverlay.style.display = 'flex';
      const textEl = loadingOverlay.querySelector('.loading-text');
      if (textEl) textEl.textContent = label;
    }
  }
  function hideLoading() {
    if (loadingOverlay) loadingOverlay.style.display = 'none';
  }

  function getEstadoTooltip(prod) {
    // Prioridad: vencido
    const esVencido = (prod.estado === 'Vencido');
    // Derivar agotado cuando stock = 0 (si no está vencido)
    const esAgotado = !esVencido && Number(prod.stock_actual || 0) <= 0;
    const estadoBase = prod.estado || 'Normal';
    const estado = esVencido ? 'Vencido' : (esAgotado ? 'Agotado' : estadoBase);
    if (estado === 'Bajo stock') {
      const s = Number(prod.stock_actual || 0);
      const m = Number(prod.stock_minimo || 0);
      if (m > 0 && s <= m) return `Stock bajo: ${s} por debajo del mínimo ${m}`;
      return 'Stock bajo';
    }
    if (estado === 'Por vencer') {
      const dias = calcDiasA(expiraEn(prod.fecha_vencimiento));
      if (dias !== null) return `Vence en ${dias} días`;
      return 'Producto próximo a vencer';
    }
    if (estado === 'Vencido') {
      const dias = calcDiasDesde(expiraEn(prod.fecha_vencimiento));
      if (dias !== null) return `Venció hace ${dias} días`;
      return 'Producto vencido';
    }
    if (estado === 'Agotado') {
      return 'Sin stock disponible';
    }
    return null;
  }

  function expiraEn(fechaStr) {
    if (!fechaStr) return null;
    const d = new Date(fechaStr);
    if (isNaN(d.getTime())) return null;
    return d;
  }
  function calcDiasA(date) { if (!date) return null; const ms = date.getTime() - Date.now(); return Math.max(0, Math.round(ms/86400000)); }
  function calcDiasDesde(date) { if (!date) return null; const ms = Date.now() - date.getTime(); return Math.max(0, Math.round(ms/86400000)); }

  function estadoBadge(prod) {
    // Calcular estado final con prioridad correcta
    const esVencido = (prod.estado === 'Vencido');
    const esAgotado = !esVencido && Number(prod.stock_actual || 0) <= 0;
    const estadoBase = prod.estado || 'Normal';
    const estado = esVencido ? 'Vencido' : (esAgotado ? 'Agotado' : estadoBase);
    const map = {
      'Normal': 'estado-normal',
      'Bajo stock': 'estado-bajo-stock',
      'Por vencer': 'estado-por-vencer',
      'Vencido': 'estado-vencido',
      'Agotado': 'estado-agotado'
    };
    const cls = map[estado] || 'estado-normal';
    const tooltip = getEstadoTooltip(prod);
    const tooltipAttr = tooltip && estado !== 'Normal' ? ` data-tooltip="${tooltip}"` : '';
    return `<span class="estado-badge ${cls}"${tooltipAttr}>${estado}</span>`;
  }

  function formatFecha(str) {
    if (!str) return 'N/A';
    const d = new Date(str);
    if (isNaN(d.getTime())) return 'N/A';
    return d.toLocaleDateString('es-PE');
  }

  function chipStock(stock, minimo) {
    const s = Number(stock || 0);
    const m = Number(minimo || 0);
    let cls = 'chip bg-green-100 text-green-800 border border-green-200';
    if (s <= m) cls = 'chip bg-red-100 text-red-800 border border-red-200';
    else if (s <= m * 2) cls = 'chip bg-yellow-100 text-yellow-800 border border-yellow-200';
    return `<div class="${cls}">${s} uni</div>`;
  }

  function ubicacionBadge(prod) {
    const total = prod.total_ubicaciones || 0;
    const sin = prod.tiene_stock_sin_ubicar || false;
    const sinCant = prod.stock_sin_ubicar || 0;
    if (total > 1) return `<div class="ubicacion-badge multiple"><iconify-icon icon="solar:buildings-2-bold-duotone"></iconify-icon><span>${total} ubicaciones</span></div>`;
    if (total === 1 && !sin) return `<div class="ubicacion-badge ubicado"><iconify-icon icon="solar:map-point-bold-duotone"></iconify-icon><span>Ubicado</span></div>`;
    if (total >= 1 && sin) return `<div class="ubicacion-badge multiple"><iconify-icon icon="solar:box-minimalistic-bold-duotone"></iconify-icon><span>Parcial (${sinCant} sin ubicar)</span></div>`;
    return `<div class="ubicacion-badge sin-ubicar"><iconify-icon icon="mdi:map-marker-off-outline"></iconify-icon><span>Sin ubicar</span></div>`;
  }

  async function load() {
    const perPage = perPageEl.value || 10;
    const estado = estadoEl.value || 'todos';
    const search = (searchEl.value || '').trim();

    const url = new URL(window.APP_PRODUCTS_AJAX || '/inventario/productos/ajax', window.location.origin);
    url.searchParams.append('search', search);
    url.searchParams.append('estado', estado);
    url.searchParams.append('per_page', perPage);
    url.searchParams.append('page', page);

    // Mostrar skeleton de carga
    const skeleton = document.getElementById('productosBoticaSkeleton');
    if (skeleton) {
      skeleton.style.display = 'block';
      skeleton.innerHTML = Array.from({length: 6}).map(()=>
        `<div class="skeleton-row">
           <span class="skeleton-dot"></span>
           <span class="skeleton-bar medium"></span>
           <span class="skeleton-bar medium"></span>
           <span class="skeleton-bar short"></span>
           <span class="skeleton-bar actions"></span>
        </div>`
      ).join('');
    }
    tbody.innerHTML = '';
    try {
      console.log('Haciendo fetch...');
      const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
      console.log('Respuesta recibida:', res.status, res.ok);
      
      if (!res.ok) throw new Error('Error al cargar productos');
      const data = await res.json();
      console.log('Datos recibidos:', data);
      
      // Guardar última respuesta para exportar
      window.boticaLastResponse = data;
      window.boticaLastProducts = Array.isArray(data.data) ? data.data : [];
      render(data);
    } catch (e) {
      console.error('Error en load():', e);
      tbody.innerHTML = `<tr><td colspan="8" style="padding:24px;text-align:center;color:#dc2626;">No se pudo cargar productos</td></tr>`;
      console.error(e);
    } finally {
      if (skeleton) skeleton.style.display = 'none';
    }
  }

  // Exponer función de recarga para uso externo (guardar/editar)
  window.loadProducts = load;

  function formatMoney(n) {
    const num = Number(n || 0);
    return num.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function render(resp) {
    const productos = resp.data || [];
    const defaultImageUrl = window.APP_DEFAULT_IMAGE || '/assets/images/default-product.svg';
    if (!productos.length) {
      tbody.innerHTML = `<tr><td colspan="8" style="padding:24px;text-align:center;color:#64748b;">No se encontraron productos</td></tr>`;
    } else {
      console.log('Renderizando', productos.length, 'productos');
      tbody.innerHTML = productos.map((p, idx) => {
        const img = p.imagen_url || defaultImageUrl;
        return `<tr data-id="${p.id}">
          <td>
            <div class="flex items-center gap-3">
              <img data-src="${img}" src="${defaultImageUrl}" width="40" height="40" loading="lazy" decoding="async" fetchpriority="${idx < 6 ? 'high' : 'low'}" class="w-10 h-10 rounded-lg object-cover shadow-sm border border-gray-200 bg-white img-loading" onerror="this.src='${defaultImageUrl}'"/>
              <div>
                <h6 class="text-base font-semibold text-gray-800 leading-tight">${p.nombre}</h6>
                <span class="text-secondary">${p.concentracion || ''}</span>
              </div>
            </div>
          </td>
          <td class="text-left">${p.categoria || '-'}</td>
          <td class="text-left price-cell">
            <div class="flex flex-col">
              <span class="pv"><iconify-icon icon="tabler:arrow-up" class="price-icon"></iconify-icon> P. Venta: S/ ${formatMoney(p.precio_venta)}</span>
              <span class="pc"><iconify-icon icon="tabler:arrow-down" class="price-icon"></iconify-icon> P. Compra: S/ ${formatMoney(p.precio_compra)}</span>
            </div>
          </td>
          <td class="text-center">${chipStock(p.stock_actual, p.stock_minimo)}</td>
          <td class="text-center">${ubicacionBadge(p)}</td>
          <td class="text-center">${estadoBadge(p)}</td>
          <td class="text-center acciones-cell">
            <button class="btn-view" data-id="${p.id}" title="Ver detalles"><iconify-icon icon="iconamoon:eye-light"></iconify-icon></button>
            <button class="btn-edit" data-id="${p.id}" title="Editar"><iconify-icon icon="lucide:edit"></iconify-icon></button>
            <button class="btn-delete" data-id="${p.id}" title="Eliminar"><iconify-icon icon="mingcute:delete-2-line"></iconify-icon></button>
          </td>
        </tr>`;
      }).join('');
      initLazyImages();
    }
    info.textContent = `Mostrando ${resp.from || 0} a ${resp.to || 0} de ${resp.total || 0} productos`;

    // Render paginación estilo historial
    const controls = document.getElementById('productos-botica-pagination-controls');
    if (controls) {
      const current = Number(resp.current_page || 1);
      const last = Number(resp.last_page || 1);
      const isFirst = current <= 1;
      const isLast = current >= last;
      const btn = (label, disabled, action) => {
        if (disabled) return `<span class="historial-pagination-btn historial-pagination-btn-disabled">${label}</span>`;
        return `<button class="historial-pagination-btn" data-action="${action}">${label}</button>`;
      };
      const currentBtn = `<span class="historial-pagination-btn historial-pagination-btn-current">${current}</span>`;
      controls.innerHTML = [
        btn('Primera', isFirst, 'first'),
        btn('‹ Anterior', isFirst, 'prev'),
        currentBtn,
        btn('Siguiente ›', isLast, 'next'),
        btn('Última', isLast, 'last')
      ].join('');
      controls.querySelectorAll('button.historial-pagination-btn').forEach(el => {
        el.addEventListener('click', () => {
          const action = el.dataset.action;
          if (action === 'first') page = 1;
          else if (action === 'prev') page = Math.max(1, current - 1);
          else if (action === 'next') page = Math.min(last, current + 1);
          else if (action === 'last') page = last;
          load();
        });
      });
    }

    // Wire up actions via delegation (robusto ante re-render)
    tbody.addEventListener('click', (e) => {
      const btnView = e.target.closest('.btn-view');
      if (btnView) {
        const id = btnView.dataset.id || btnView.closest('tr')?.dataset.id;
        if (id) abrirDetalles(id);
        return;
      }
      const btnEdit = e.target.closest('.btn-edit');
      if (btnEdit) {
        const id = btnEdit.dataset.id || btnEdit.closest('tr')?.dataset.id;
        if (id) abrirModalEdicion(id); else console.error('No se encontró el id del producto para editar');
        return;
      }
      const btnDelete = e.target.closest('.btn-delete');
      if (btnDelete) {
        const id = btnDelete.dataset.id || btnDelete.closest('tr')?.dataset.id;
        if (id) eliminarProductoBotica(id);
      }
    });
  }

  function initLazyImages() {
    const imgs = tbody.querySelectorAll('img[data-src]');
    const defaultImg = window.APP_DEFAULT_IMAGE || '/assets/images/default-product.svg';
    const load = (img) => {
      const src = img.getAttribute('data-src');
      if (!src || img.dataset.loaded) return;
      img.onload = () => { img.classList.remove('img-loading'); img.dataset.loaded = '1'; };
      img.onerror = () => { img.src = defaultImg; img.classList.remove('img-loading'); img.dataset.loaded = '1'; };
      img.src = src;
    };
    if ('IntersectionObserver' in window) {
      const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
          if (e.isIntersecting) {
            load(e.target);
            io.unobserve(e.target);
          }
        });
      }, { root: null, rootMargin: '120px', threshold: 0.1 });
      imgs.forEach((img, i) => {
        io.observe(img);
        // Pre-cargar primeros visibles para percepción rápida
        if (i < 6) { load(img); io.unobserve(img); }
      });
    } else {
      imgs.forEach(load);
    }
  }

  // Forzar cierre de cualquier modal previo antes de abrir otro
  function resetModals() {
    const ids = ['modalEditar','modalDetallesBotica','modalAgregar'];
    ids.forEach((id)=>{
      const m = document.getElementById(id);
      if (m) { m.style.display='none'; m.classList.add('hidden'); m.classList.remove('flex'); }
    });
    document.body.classList.remove('modal-open');
  }

  async function abrirDetalles(id) {
    try {
      resetModals();
      showLoading('Cargando datos...');
      const res = await fetch(`/inventario/producto/${id}`);
      const data = await res.json();
      if (!data.success) throw new Error('No se pudo obtener detalles');
      const p = data.data;
      // Resolver proveedor: si no viene nombre, intentar obtenerlo por ID
      let proveedorNombre = p.proveedor || '';
      if (!proveedorNombre && p.proveedor_id) {
        try {
          const rp = await fetch(`/api/compras/proveedor/${p.proveedor_id}`);
          const dp = await rp.json();
          if (dp.success && dp.data) {
            proveedorNombre = dp.data.razon_social || dp.data.nombre_comercial || dp.data.ruc || '';
          }
        } catch (_) {}
      }
      // Poblar campos del modal detalles (lectura)
      const map = {
        'det-nombre': p.nombre,
        'det-marca': p.marca,
        'det-categoria': p.categoria,
        'det-presentacion': p.presentacion,
        'det-concentracion': p.concentracion,
        'det-lote': p.lote,
        'det-codigo_barras': p.codigo_barras,
        'det-proveedor': proveedorNombre,
        'det-stock_actual': p.stock_actual,
        'det-stock_minimo': p.stock_minimo,
        'det-precio_compra': typeof p.precio_compra === 'number' ? p.precio_compra.toFixed(2) : p.precio_compra,
        'det-precio_venta': typeof p.precio_venta === 'number' ? p.precio_venta.toFixed(2) : p.precio_venta,
        'det-fecha_fabricacion': formatFecha(p.fecha_fabricacion),
        'det-fecha_vencimiento': formatFecha(p.fecha_vencimiento)
      };
      Object.entries(map).forEach(([id,val])=>{ const el=document.getElementById(id); if (el) el.value = val || ''; });
      const prev = document.getElementById('det-preview-container');
      const img = document.getElementById('det-preview-image');
      if (prev && img) { prev.style.display='block'; img.src = p.imagen_url || '/assets/images/default-product.svg'; img.onerror = function(){ this.src='/assets/images/default-product.svg'; } }
      mostrarModal();
    } catch (e) {
      console.error(e);
      Swal.fire('Error', 'No se pudo cargar el producto', 'error');
    } finally { hideLoading(); }
  }

function mostrarModal() {
  const modal = document.getElementById('modalDetallesBotica');
  modal.style.display = 'flex';
  modal.classList.remove('hidden');
  document.body.classList.add('modal-open');
  const nombre = document.getElementById('nombreProducto');
  if (nombre) { nombre.removeAttribute('readonly'); nombre.removeAttribute('disabled'); nombre.focus(); }
}
function cerrarModal() {
  const modal = document.getElementById('modalDetallesBotica');
  modal.style.display = 'none';
  modal.classList.add('hidden');
  document.body.classList.remove('modal-open');
}
  document.getElementById('cerrarModalBotica')?.addEventListener('click', cerrarModal);
  // Cerrar al hacer click fuera del contenedor
  const overlayDetalles = document.getElementById('modalDetallesBotica');
  if (overlayDetalles) {
    overlayDetalles.addEventListener('click', (e) => {
      if (e.target === overlayDetalles) cerrarModal();
    });
  }
  // Soporta ambos IDs por compatibilidad con agregar.js
  const btnAddA = document.getElementById('btnAgregarProductoBotica');
  const btnAddB = document.getElementById('btnAgregarProducto');
  (btnAddA || btnAddB)?.addEventListener('click', () => {
    const m = document.getElementById('modalAgregar');
    // Resetear formulario al abrir
    resetAgregarForm();
    // Asegurar opciones actualizadas
    try { cargarCategoriasYPresentaciones(); cargarProveedores(); } catch(e){}
    m.style.display = 'flex';
    document.body.classList.add('modal-open');
    const nombre = document.getElementById('nombreProducto');
    if (nombre) { nombre.removeAttribute('readonly'); nombre.removeAttribute('disabled'); setTimeout(()=> nombre.focus(), 50); }
  });
  document.getElementById('closeAgregar')?.addEventListener('click', () => { resetAgregarForm(); document.getElementById('modalAgregar').style.display='none'; document.body.classList.remove('modal-open'); });
  document.getElementById('btnCancelarAgregar')?.addEventListener('click', () => { resetAgregarForm(); document.getElementById('modalAgregar').style.display='none'; document.body.classList.remove('modal-open'); });
  function closeModalEditar() {
    const m = document.getElementById('modalEditar');
    if (m) { m.style.display='none'; m.classList.add('hidden'); }
    document.body.classList.remove('modal-open');
  }
  document.getElementById('closeEditar')?.addEventListener('click', closeModalEditar);
  document.getElementById('btnCancelarEditar')?.addEventListener('click', closeModalEditar);
  // Cerrar al hacer click fuera del contenedor
  const overlayEdit = document.getElementById('modalEditar');
  if (overlayEdit) {
    overlayEdit.addEventListener('click', (e)=>{ if (e.target === overlayEdit) closeModalEditar(); });
  }

  // Events
  perPageEl.addEventListener('change', () => { page = 1; load(); });
  estadoEl.addEventListener('change', () => { page = 1; load(); });
  let searchTimeout;
  searchEl.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { page = 1; load(); }, 200);
  });

  // --- Export dropdown handlers (Excel/PDF)
  const btnExportar = document.getElementById('btnExportarBotica');
  const menuExportar = document.getElementById('exportarDropdownMenuBotica');
  const btnExcel = document.getElementById('btnExportarExcelBotica');
  const btnPDF = document.getElementById('btnExportarPDFBotica');

  if (btnExportar && menuExportar) {
    btnExportar.addEventListener('click', (e) => {
      e.stopPropagation();
      menuExportar.classList.toggle('hidden');
      btnExportar.classList.toggle('active');
    });
    document.addEventListener('click', (e) => {
      if (!menuExportar.classList.contains('hidden')) {
        menuExportar.classList.add('hidden');
        btnExportar.classList.remove('active');
      }
    });
  }

  function obtenerDatosActuales() {
    const productos = window.boticaLastProducts || [];
    return productos.map(p => ({
      nombre: p.nombre,
      concentracion: p.concentracion || '',
      marca: p.marca || '',
      stock_actual: p.stock_actual ?? 0,
      precio_venta: p.precio_venta ?? 0,
      fecha_vencimiento: p.fecha_vencimiento || '',
      categoria: p.categoria || '',
      ubicacion: p.ubicacion || p.ubicacion_almacen || ''
    }));
  }

  async function exportarExcelBotica() {
    const productos = obtenerDatosActuales();
    if (!productos.length) {
      Swal.fire({ icon:'warning', title:'Sin datos', text:'No hay productos para exportar' });
      return;
    }
    const datosExcel = productos.map(p => ({
      'Nombre': p.nombre,
      'Concentración': p.concentracion || 'N/A',
      'Marca': p.marca || 'N/A',
      'Stock': p.stock_actual,
      'Precio Venta': `S/ ${parseFloat(p.precio_venta || 0).toFixed(2)}`,
      'Fecha Vencimiento': p.fecha_vencimiento || 'N/A',
      'Categoría': p.categoria || 'N/A',
      'Ubicación': p.ubicacion || 'Sin ubicación'
    }));
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.json_to_sheet(datosExcel);
    ws['!cols'] = [{wch:25},{wch:15},{wch:15},{wch:12},{wch:15},{wch:15},{wch:15},{wch:15}];
    XLSX.utils.book_append_sheet(wb, ws, 'Productos');
    const fecha = new Date().toISOString().split('T')[0];
    const nombreArchivo = `productos_botica_${fecha}.xlsx`;
    XLSX.writeFile(wb, nombreArchivo);
    Swal.fire({ icon:'success', title:'Exportación exitosa', text:`Archivo ${nombreArchivo} descargado` });
  }

  async function exportarPDFBotica() {
    const productos = obtenerDatosActuales();
    if (!productos.length) {
      Swal.fire({ icon:'warning', title:'Sin datos', text:'No hay productos para exportar' });
      return;
    }
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l','mm','a4');
    doc.setFontSize(16); doc.setFont('helvetica','bold');
    doc.text('Lista de Productos Botica', 14, 15);
    doc.setFontSize(10); doc.setFont('helvetica','normal');
    doc.text(`Fecha: ${new Date().toLocaleDateString('es-PE')}`, 14, 22);
    const columnas = ['Nombre','Concentración','Marca','Stock','Precio Venta','Fecha Venc.','Categoría','Ubicación'];
    const filas = productos.map(p => [
      (p.nombre || '').slice(0,30),
      p.concentracion || 'N/A',
      p.marca || 'N/A',
      p.stock_actual,
      `S/ ${parseFloat(p.precio_venta || 0).toFixed(2)}`,
      p.fecha_vencimiento || 'N/A',
      p.categoria || 'N/A',
      p.ubicacion || 'Sin ubicación'
    ]);
    doc.autoTable({ head:[columnas], body:filas, startY:28, styles:{ fontSize:8 } });
    const fecha = new Date().toISOString().split('T')[0];
    const nombreArchivo = `productos_botica_${fecha}.pdf`;
    doc.save(nombreArchivo);
    Swal.fire({ icon:'success', title:'Exportación exitosa', text:`Archivo ${nombreArchivo} descargado` });
  }

  if (btnExcel) btnExcel.addEventListener('click', (e)=>{ e.stopPropagation(); exportarExcelBotica(); });
  if (btnPDF) btnPDF.addEventListener('click', (e)=>{ e.stopPropagation(); exportarPDFBotica(); });
  clearBtn.addEventListener('click', () => { searchEl.value = ''; page = 1; load(); });

  // Expose defaults
  window.APP_PRODUCTS_AJAX = window.APP_PRODUCTS_AJAX || '/inventario/productos/ajax';
  window.APP_DEFAULT_IMAGE = window.APP_DEFAULT_IMAGE || '/assets/images/default-product.svg';

  // Submit handlers
  const formAgregar = document.getElementById('formAgregarProducto');
  if (formAgregar) {
    formAgregar.addEventListener('submit', async (e) => {
      e.preventDefault();
      await guardarNuevoProducto();
    });
    const imgAdd = document.getElementById('imagen-input');
    if (imgAdd) {
      imgAdd.addEventListener('change', () => {
        const file = imgAdd.files?.[0];
        const prev = document.getElementById('preview-container');
        const img = document.getElementById('preview-image');
        if (file && prev && img) { prev.classList.remove('hidden'); img.src = URL.createObjectURL(file); }
      });
    }
  }

  function resetAgregarForm() {
    const form = document.getElementById('formAgregarProducto');
    if (form) {
      form.reset();
      // limpiar estados de validación si los hubiera
      form.querySelectorAll('input, select, textarea').forEach(el => {
        el.classList.remove('campo-invalido','campo-valido','border-red-500','bg-red-50','border-green-500','bg-green-50');
      });
    }
    const prev = document.getElementById('preview-container');
    const img = document.getElementById('preview-image');
    if (prev) prev.classList.add('hidden');
    if (img) img.src = '';
  }
  const formEditar = document.getElementById('formEditarProducto');
  if (formEditar) {
    formEditar.addEventListener('submit', async (e) => {
      e.preventDefault();
      await guardarEdicionProducto();
    });
    const imgEdit = document.getElementById('edit-imagen-input');
    if (imgEdit) {
      imgEdit.addEventListener('change', () => {
        const file = imgEdit.files?.[0];
        const prev = document.getElementById('edit-preview-container');
        const img = document.getElementById('edit-preview-image');
        if (file && prev && img) { prev.style.display='block'; img.src = URL.createObjectURL(file); }
      });
    }
  }

  load();

  // Populate selects for agregar
  cargarCategoriasYPresentaciones();
  cargarProveedores();
});

// === Global loading helpers (usable outside DOMContentLoaded scope) ===
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

// Cierra cualquier modal previo para evitar estados bloqueados
function resetModals() {
  const ids = ['modalEditar','modalDetallesBotica','modalAgregar'];
  ids.forEach((id)=>{
    const m = document.getElementById(id);
    if (m) { m.style.display='none'; m.classList.add('hidden'); m.classList.remove('flex'); }
  });
  document.body.classList.remove('modal-open');
}

// --- Complementary functions for CRUD ---
async function cargarCategoriasYPresentaciones(catSel=null, presSel=null) {
  try {
    const rc = await fetch('/inventario/categoria/api/all');
    const dc = await rc.json();
    const selCatAdd = document.getElementById('add-categoria');
    const selCatEdit = document.getElementById('edit-categoria');
    if (dc.success) {
      const opts = ['<option value="">Seleccionar</option>'].concat(dc.data.map(c=>`<option value="${c.nombre}">${c.nombre}</option>`)).join('');
      if (selCatAdd) selCatAdd.innerHTML = opts;
      if (selCatEdit) selCatEdit.innerHTML = opts;
      if (catSel && selCatEdit) selCatEdit.value = catSel;
    }
    const rp = await fetch('/inventario/presentacion/api');
    const dp = await rp.json();
    const selPresAdd = document.getElementById('add-presentacion');
    const selPresEdit = document.getElementById('edit-presentacion');
    if (dp.success) {
      const optsP = ['<option value="">Seleccionar</option>'].concat(dp.data.map(p=>`<option value="${p.nombre}">${p.nombre}</option>`)).join('');
      if (selPresAdd) selPresAdd.innerHTML = optsP;
      if (selPresEdit) selPresEdit.innerHTML = optsP;
      if (presSel && selPresEdit) selPresEdit.value = presSel;
    }
  } catch(e) { console.error(e); }
}

async function cargarProveedores(provSel=null) {
  try {
    // Usar endpoint que lista todos los proveedores activos, sin requerir término de búsqueda
    const rp = await fetch('/compras/proveedores/api');
    const dp = await rp.json();
    const selAdd = document.getElementById('add-proveedor');
    const selEdit = document.getElementById('edit-proveedor');
    if (dp.success && Array.isArray(dp.data)) {
      // Ordenar por razón social para mejorar UX
      dp.data.sort((a,b)=>((a.razon_social||'').localeCompare(b.razon_social||'')));
      const opts = ['<option value="">Seleccionar</option>']
        .concat(dp.data.map(pr => {
          const display = pr.razon_social || pr.nombre || pr.nombre_comercial || pr.ruc || (`Proveedor #${pr.id}`);
          return `<option value="${pr.id}">${display}</option>`;
        }))
        .join('');
      if (selAdd) { selAdd.innerHTML = opts; selAdd.disabled = false; }
      if (selEdit) { selEdit.innerHTML = opts; selEdit.disabled = false; }
      if (provSel) {
        if (selEdit) selEdit.value = String(provSel);
        if (selAdd) selAdd.value = String(provSel);
      }
    } else {
      if (selAdd) { selAdd.innerHTML = '<option value="">No hay proveedores activos</option>'; selAdd.disabled = false; }
      if (selEdit) { selEdit.innerHTML = '<option value="">No hay proveedores activos</option>'; selEdit.disabled = false; }
    }
  } catch(e) { console.error(e); }
}

async function abrirModalEdicion(productId) {
  try {
    resetModals();
    showLoading('Cargando datos para editar...');
    const res = await fetch(`/inventario/producto/${productId}`);
    const data = await res.json();
    if (!data.success || !data.data) throw new Error('No se pudo cargar producto');
    const p = data.data;
    if (typeof window.abrirModalEditarProducto === 'function') {
      await window.abrirModalEditarProducto({
        id: p.id,
        nombre: p.nombre,
        categoria: p.categoria,
        marca: p.marca,
        proveedor_id: p.proveedor_id,
        proveedor: p.proveedor,
        presentacion: p.presentacion,
        concentracion: p.concentracion,
        lote: p.lote,
        codigo_barras: p.codigo_barras,
        stock_actual: p.stock_actual,
        stock_minimo: p.stock_minimo,
        precio_compra: p.precio_compra,
        precio_venta: p.precio_venta,
        fecha_fabricacion: p.fecha_fabricacion || '',
        fecha_vencimiento: p.fecha_vencimiento || '',
        imagen_url: p.imagen_url || ''
      });
    } else {
      document.getElementById('edit-producto-id').value = p.id;
      const ids = ['edit-nombre','edit-concentracion','edit-marca','edit-lote','edit-codigo_barras','edit-stock_actual','edit-stock_minimo','edit-precio_compra','edit-precio_venta','edit-fecha_fabricacion','edit-fecha_vencimiento'];
      const vals = [p.nombre,p.concentracion,p.marca,p.lote,p.codigo_barras,p.stock_actual,p.stock_minimo,p.precio_compra,p.precio_venta,p.fecha_fabricacion||'',p.fecha_vencimiento||''];
      ids.forEach((id,i)=>{ const el=document.getElementById(id); if(el) el.value = vals[i]??''; });
      cargarCategoriasYPresentaciones(p.categoria, p.presentacion);
      cargarProveedores(p.proveedor_id);
      const prev = document.getElementById('edit-preview-container');
      const img = document.getElementById('edit-preview-image');
      if (prev && img) { prev.style.display='block'; img.src = p.imagen_url || '/assets/images/default-product.svg'; }
      const modalEdit = document.getElementById('modalEditar');
      modalEdit.style.display='flex';
      modalEdit.classList.remove('hidden');
      document.body.classList.add('modal-open');
    }
  } catch(e) { console.error(e); Swal.fire('Error','No se pudo cargar el producto','error'); }
  finally { hideLoading(); }
}

async function guardarNuevoProducto() {
  try {
    const form = document.getElementById('formAgregarProducto');
    if (window.validacionesTiempoReal) {
      const ok = await window.validacionesTiempoReal.validateForm('formAgregarProducto');
      if (!ok) {
        Swal.fire('Errores de validación','Corrige los campos marcados antes de guardar','warning');
        return;
      }
    }
    clearFieldErrors(form);
    const fd = new FormData(form);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const resp = await fetch('/inventario/producto/guardar', { method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}, body: fd });
    if (resp.status === 422) {
      const data = await resp.json();
      const errs = data.errors || {};
      Object.keys(errs).forEach(k => showFieldError(form, k, errs[k][0] || 'Campo inválido'));
      Swal.fire('Revisa los campos','Hay errores de validación','warning');
      return;
    }
    const data = await resp.json();
    if (!resp.ok || !data.success) throw new Error(data.message || 'Error al guardar');
    document.getElementById('modalAgregar').style.display='none';
    Swal.fire({
      icon: 'success',
      title: '¡Producto creado!',
      text: 'El producto se guardó correctamente',
      showConfirmButton: false,
      timer: 1500,
      timerProgressBar: true,
      willClose: () => {
        if (typeof window.loadProducts === 'function') {
          window.loadProducts();
        } else {
          location.reload();
        }
      }
    });
  } catch(e) { console.error(e); Swal.fire('Error', e.message || 'No se pudo guardar', 'error'); }
}

async function guardarEdicionProducto() {
  try {
    // Validación rápida en cliente para evitar 422 innecesarios
    const form = document.getElementById('formEditarProducto');
    const nombre = form.querySelector('[name="nombre"]')?.value?.trim();
    const categoria = form.querySelector('[name="categoria"]')?.value?.trim();
    const marca = form.querySelector('[name="marca"]')?.value?.trim();
    const presentacion = form.querySelector('[name="presentacion"]')?.value?.trim();
    const lote = form.querySelector('[name="lote"]')?.value?.trim();
    const codigo_barras = form.querySelector('[name="codigo_barras"]')?.value?.trim();
    const stock_actual = Number(form.querySelector('[name="stock_actual"]')?.value || 0);
    const stock_minimo = Number(form.querySelector('[name="stock_minimo"]')?.value || 0);
    const precio_compra = Number(form.querySelector('[name="precio_compra"]')?.value || 0);
    const precio_venta = Number(form.querySelector('[name="precio_venta"]')?.value || 0);
    const fecha_fabricacion = form.querySelector('[name="fecha_fabricacion"]')?.value;
    const fecha_vencimiento = form.querySelector('[name="fecha_vencimiento"]')?.value;

    clearFieldErrors(form);
    let hasError = false;
    const req = (val, field) => { if (!val) { showFieldError(form, field, 'Este campo es obligatorio'); hasError = true; } };
    req(nombre,'nombre'); req(categoria,'categoria'); req(marca,'marca'); req(presentacion,'presentacion'); req(lote,'lote'); req(codigo_barras,'codigo_barras');
    if (codigo_barras && codigo_barras.length !== 13) { showFieldError(form,'codigo_barras','Debe tener 13 dígitos (EAN13)'); hasError = true; }
    if (!Number.isFinite(stock_actual) || stock_actual < 0) { showFieldError(form,'stock_actual','Debe ser un entero ≥ 0'); hasError = true; }
    if (!Number.isFinite(stock_minimo) || stock_minimo < 0) { showFieldError(form,'stock_minimo','Debe ser un entero ≥ 0'); hasError = true; }
    if (!Number.isFinite(precio_compra) || precio_compra < 0) { showFieldError(form,'precio_compra','Debe ser un número ≥ 0'); hasError = true; }
    if (!Number.isFinite(precio_venta) || precio_venta < 0) { showFieldError(form,'precio_venta','Debe ser un número ≥ 0'); hasError = true; }
    if (Number.isFinite(precio_compra) && Number.isFinite(precio_venta) && precio_venta <= precio_compra) { showFieldError(form,'precio_venta','Debe ser mayor al precio de compra'); hasError = true; }
    if (fecha_fabricacion && fecha_vencimiento && new Date(fecha_vencimiento) < new Date(fecha_fabricacion)) { showFieldError(form,'fecha_vencimiento','Debe ser posterior a fabricación'); hasError = true; }
    if (hasError) { Swal.fire('Revisa los campos','Hay errores de validación','warning'); return; }

    if (window.validacionesTiempoReal) {
      const ok = await window.validacionesTiempoReal.validateForm('formEditarProducto');
      if (!ok) { Swal.fire('Errores de validación','Corrige los campos marcados antes de guardar','warning'); return; }
    }
    clearFieldErrors(form);
    const fd = new FormData(form);
    const id = document.getElementById('edit-producto-id').value;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const resp = await fetch(`/inventario/producto/actualizar/${id}`, { method:'POST', headers:{'X-CSRF-TOKEN':csrf,'X-HTTP-Method-Override':'PUT','Accept':'application/json'}, body: fd });
    if (resp.status === 422) {
      const data = await resp.json();
      const errs = data.errors || {};
      Object.keys(errs).forEach(k => showFieldError(form, k, errs[k][0] || 'Campo inválido'));
      Swal.fire('Revisa los campos','Hay errores de validación','warning');
      return;
    }
    const data = await resp.json();
    if (!resp.ok || !data.success) throw new Error(data.message || 'Error al actualizar');
    document.getElementById('modalEditar').style.display='none';
    Swal.fire({
      icon: 'success',
      title: '¡Producto actualizado!',
      text: 'Cambios guardados correctamente',
      showConfirmButton: false,
      timer: 1500,
      timerProgressBar: true,
      willClose: () => {
        if (typeof window.loadProducts === 'function') {
          window.loadProducts();
        } else {
          location.reload();
        }
      }
    });
  } catch(e) { console.error(e); Swal.fire('Error', e.message || 'No se pudo actualizar', 'error'); }
}

async function eliminarProductoBotica(id) {
  try {
    // Si existe la función global del módulo original, reutilizarla para mantener diseño/flujo
    if (typeof window.eliminarProducto === 'function') {
      return window.eliminarProducto(id);
    }
    const ok = await Swal.fire({ icon:'warning', title:'Eliminar producto', text:'Esta acción no se puede deshacer', showCancelButton:true, confirmButtonText:'Eliminar', cancelButtonText:'Cancelar' });
    if (!ok.isConfirmed) return;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const resp = await fetch(`/inventario/producto/eliminar/${id}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'} });
    if (!resp.ok) throw new Error('Error al eliminar');
    Swal.fire('Eliminado','El producto fue eliminado','success');
    load();
  } catch(e) { console.error(e); Swal.fire('Error','No se pudo eliminar','error'); }
}
// Helpers para errores de campo
function clearFieldErrors(form) {
  if (!form) return;
  form.querySelectorAll('.field-error').forEach(el => el.remove());
}
function showFieldError(form, fieldName, message) {
  if (!form) return;
  const field = form.querySelector(`[name="${fieldName}"]`);
  if (field) {
    const p = document.createElement('p');
    p.className = 'field-error text-red-500 text-sm mt-1';
    p.textContent = message;
    field.insertAdjacentElement('afterend', p);
  }
}