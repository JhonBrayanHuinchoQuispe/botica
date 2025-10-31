// Ejemplo de función para agregar categoría
async function agregarCategoria(datos) {
  const res = await fetch('/inventario/categoria/api', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify(datos)
  });
  return await res.json();
}
// Puedes usar esta función desde un modal o formulario de agregar/editar

document.addEventListener('DOMContentLoaded', () => {
  const btnNueva = document.getElementById('btnNuevaCategoria');
  const modal = document.getElementById('modalCategoria');
  const form = document.getElementById('formCategoria');
  const btnCancelar = document.getElementById('btnCancelarCategoria');
  const btnGuardar = document.getElementById('btnGuardarCategoria');
  const titulo = document.getElementById('modalCategoriaTitulo');
  const inputId = document.getElementById('categoriaId');
  const inputNombre = document.getElementById('categoriaNombre');
  const inputDescripcion = document.getElementById('categoriaDescripcion');
  const inputColor = document.getElementById('categoriaColor');

  function limpiarErrores() {
    form.querySelectorAll('.error-campo').forEach(e => e.remove());
  }

  function mostrarError(input, mensaje) {
    let error = document.createElement('div');
    error.className = 'error-campo';
    error.style.color = '#e53935';
    error.style.fontSize = '0.95rem';
    error.style.marginTop = '-0.7rem';
    error.style.marginBottom = '0.7rem';
    error.textContent = mensaje;
    input.insertAdjacentElement('afterend', error);
  }

  function abrirModalAgregar() {
    titulo.textContent = 'Agregar Categoría';
    form.reset();
    inputId.value = '';
    modal.style.display = 'flex';
    setTimeout(() => inputNombre.focus(), 100);
    limpiarErrores();
  }
  function cerrarModal() {
    modal.style.display = 'none';
    form.reset();
    limpiarErrores();
  }

  btnNueva.addEventListener('click', abrirModalAgregar);
  btnCancelar.addEventListener('click', cerrarModal);

  // Cerrar con Escape o clic fuera
  window.addEventListener('keydown', (e) => {
    if (modal.style.display === 'flex' && e.key === 'Escape') cerrarModal();
  });
  modal.addEventListener('mousedown', (e) => {
    if (e.target === modal) cerrarModal();
  });

  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btnGuardar = document.getElementById('btnGuardarCategoria');
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<iconify-icon icon="ic:round-check-circle" class="text-lg"></iconify-icon> Guardando...';
    limpiarErrores();
    const datos = {
      nombre: inputNombre.value,
      descripcion: inputDescripcion.value
    };
    try {
      const res = await fetch('/inventario/categoria/api', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(datos)
      });
      const data = await res.json();
      if (data.success) {
        Swal.fire({
          icon: 'success',
          title: '¡Categoría guardada exitosamente!',
          showConfirmButton: false,
          timer: 1500
        });
        cerrarModal();
        if (window.cargarCategorias) cargarCategorias();
      } else if (data.errors) {
        if (data.errors.nombre) mostrarError(inputNombre, data.errors.nombre[0]);
        if (data.errors.descripcion) mostrarError(inputDescripcion, data.errors.descripcion[0]);
      } else {
        Swal.fire('Error', 'No se pudo agregar la categoría', 'error');
      }
    } catch (error) {
      Swal.fire('Error', 'Error de red o del servidor', 'error');
    }
    btnGuardar.disabled = false;
    btnGuardar.innerHTML = '<iconify-icon icon="ic:round-check-circle" class="text-lg"></iconify-icon> Guardar';
  });
});

function initAgregarPresentacion() {
    const modal = document.getElementById('modalAgregarPresentacion');
    if (!modal) return;

    const btnNueva = document.getElementById('btnNuevaPresentacion');
    const form = document.getElementById('formAgregarPresentacion');
    const btnCancelar = document.getElementById('btnCancelarAgregarPresentacion');

    function abrirModal() {
        form.reset();
        modal.style.display = 'flex';
        document.getElementById('agregarPresentacionNombre').focus();
    }

    function cerrarModal() {
        modal.style.display = 'none';
        form.reset();
    }

    btnNueva.addEventListener('click', abrirModal);
    btnCancelar.addEventListener('click', cerrarModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) cerrarModal();
    });
    window.addEventListener('keydown', (e) => {
        if (modal.style.display === 'flex' && e.key === 'Escape') cerrarModal();
    });

    // Cálculo automático de precio
    const calcToggle = document.getElementById('agregarCalcularAuto');
    const precioBase = document.getElementById('agregarPrecioBase');
    const factor = document.getElementById('agregarFactor');
    const precioVenta = document.getElementById('agregarPrecioVenta');
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

        const nombreInput = document.getElementById('agregarPresentacionNombre');
        const nombre = nombreInput.value.trim().toLowerCase();
        
        const existe = presentacionesData.some(p => p.nombre.toLowerCase() === nombre);
        if (existe) {
            Swal.fire('Error', 'El nombre de la presentación ya existe.', 'error');
            return;
        }

        const btnGuardar = document.getElementById('btnGuardarAgregarPresentacion');
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<iconify-icon icon="line-md:loading-loop"></iconify-icon> Guardando...';

        const descripcion = document.getElementById('agregarPresentacionDescripcion').value;
        const unidad_venta = document.getElementById('agregarUnidadVenta')?.value || null;
        const factor_unidad_base = parseInt(document.getElementById('agregarFactor')?.value || '', 10);
        const precio_venta = document.getElementById('agregarPrecioVenta')?.value ? parseFloat(document.getElementById('agregarPrecioVenta').value) : null;
        const permite_fraccionamiento = !!document.getElementById('agregarFraccionable')?.checked;
        const token = document.querySelector('meta[name="csrf-token"]').content;

        try {
            const res = await fetch('/inventario/presentacion/api', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    nombre: nombreInput.value, 
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
                    text: 'Presentación agregada correctamente.',
                    showConfirmButton: false,
                    timer: 1500
                });
                cerrarModal();
                cargarPresentaciones();
            } else {
                let msg = data.message || 'No se pudo guardar la presentación.';
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
