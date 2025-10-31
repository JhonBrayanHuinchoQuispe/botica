function initEditarPresentacion() {
    const modal = document.getElementById('modalEditarPresentacion');
    if (!modal) return;

    const form = document.getElementById('formEditarPresentacion');
    const btnCancelar = document.getElementById('btnCancelarEditarPresentacion');

    function cerrarModal() {
        modal.style.display = 'none';
        form.reset();
    }

    document.getElementById('presentaciones-tbody').addEventListener('click', async function(e) {
        const btn = e.target.closest('.edit');
        if (!btn) return;
        
        const id = btn.dataset.id;
        try {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.style.display = 'flex';
            const res = await fetch(`/inventario/presentacion/api/${id}`);
            if (!res.ok) throw new Error('No se pudo cargar la presentación para editar.');
            const data = await res.json();
            
            if (data.success) {
                document.getElementById('editarPresentacionId').value = data.data.id;
                document.getElementById('editarPresentacionNombre').value = data.data.nombre;
                document.getElementById('editarPresentacionDescripcion').value = data.data.descripcion || '';
                const unidadSel = document.getElementById('editarUnidadVenta');
                if (unidadSel) unidadSel.value = data.data.unidad_venta || 'unidad';
                const factorInput = document.getElementById('editarFactor');
                if (factorInput) factorInput.value = (data.data.factor_unidad_base ?? '');
                const precioInput = document.getElementById('editarPrecioVenta');
                if (precioInput) precioInput.value = (data.data.precio_venta ?? '');
                const fracChk = document.getElementById('editarFraccionable');
                if (fracChk) fracChk.checked = !!data.data.permite_fraccionamiento;
                modal.style.display = 'flex';
                document.getElementById('editarPresentacionNombre').focus();
            } else {
                Swal.fire('Error', data.message || 'No se encontró la presentación', 'error');
            }
        } catch (error) {
            Swal.fire('Error', error.message, 'error');
        } finally {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.style.display = 'none';
        }
    });

    btnCancelar.addEventListener('click', cerrarModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) cerrarModal();
    });
    window.addEventListener('keydown', (e) => {
        if (modal.style.display === 'flex' && e.key === 'Escape') cerrarModal();
    });

    // Cálculo automático de precio en editar
    const calcToggle = document.getElementById('editarCalcularAuto');
    const precioBase = document.getElementById('editarPrecioBase');
    const factor = document.getElementById('editarFactor');
    const precioVenta = document.getElementById('editarPrecioVenta');
    const recompute = () => {
        if (!calcToggle || !precioVenta) return;
        if (!calcToggle.checked) return;
        const pb = parseFloat(precioBase?.value || '0');
        const f = parseInt(factor?.value || '1', 10);
        const total = pb * Math.max(1, f);
        precioVenta.value = Number.isFinite(total) ? total.toFixed(2) : '';
    };
    [calcToggle, precioBase, factor].forEach(el => el && el.addEventListener('input', recompute));

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const id = document.getElementById('editarPresentacionId').value;
        const nombre = document.getElementById('editarPresentacionNombre').value;
        const descripcion = document.getElementById('editarPresentacionDescripcion').value;
        const token = document.querySelector('meta[name="csrf-token"]').content;
        const unidad_venta = document.getElementById('editarUnidadVenta')?.value || null;
        const factor_unidad_base = parseInt(document.getElementById('editarFactor')?.value || '', 10);
        const precio_venta = document.getElementById('editarPrecioVenta')?.value ? parseFloat(document.getElementById('editarPrecioVenta').value) : null;
        const permite_fraccionamiento = !!document.getElementById('editarFraccionable')?.checked;
        
        const btnGuardar = document.getElementById('btnGuardarEditarPresentacion');
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<iconify-icon icon="line-md:loading-loop"></iconify-icon> Actualizando...';

        try {
            const res = await fetch(`/inventario/presentacion/api/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    nombre, 
                    descripcion,
                    unidad_venta,
                    factor_unidad_base: Number.isFinite(factor_unidad_base) ? factor_unidad_base : null,
                    precio_venta,
                    permite_fraccionamiento
                })
            });

            const data = await res.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'Presentación actualizada correctamente.',
                    showConfirmButton: false,
                    timer: 1500
                });
                cerrarModal();
                cargarPresentaciones();
            } else {
                let msg = data.message || 'No se pudo actualizar la presentación.';
                 if (data.errors && data.errors.nombre) {
                    msg = data.errors.nombre[0];
                }
                Swal.fire('Error', msg, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Ocurrió un error de red.', 'error');
        } finally {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<iconify-icon icon="ic:round-check-circle"></iconify-icon> Guardar';
        }
    });
}
