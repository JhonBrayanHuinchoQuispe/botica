// Íconos sugeridos para variedad visual
const iconosCategoria = {
  blue: 'mdi:pill',
  red: 'mdi:virus-outline',
  yellow: 'mdi:emoticon-happy-outline',
  green: 'mdi:leaf',
  purple: 'mdi:flask-outline',
  default: 'mdi:apps-box',
};
const colores = ['blue', 'red', 'yellow', 'green', 'purple'];

let presentacionesData = [];
let ordenCol = 'id';
let ordenDir = 'asc';
let registrosPorPagina = 10;
let paginaActual = 1;
let busqueda = '';

function renderPresentacionesTabla() {
  let lista = presentacionesData.slice();
  // Filtro de búsqueda
  if (busqueda.trim() !== '') {
    const b = busqueda.trim().toLowerCase();
    lista = lista.filter(item =>
      item.nombre.toLowerCase().includes(b) ||
      (item.descripcion && item.descripcion.toLowerCase().includes(b))
    );
  }
  // Ordenamiento
  if (ordenCol) {
    lista.sort((a, b) => {
      let vA = a[ordenCol], vB = b[ordenCol];
      if (ordenCol === 'nombre') {
        vA = vA.toLowerCase(); vB = vB.toLowerCase();
      }
      if (vA < vB) return ordenDir === 'asc' ? -1 : 1;
      if (vA > vB) return ordenDir === 'asc' ? 1 : -1;
      return 0;
    });
  }
  
  const tbody = document.getElementById('presentaciones-tbody');
  tbody.innerHTML = '';
  if (lista.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:2rem;">No se encontraron presentaciones.</td></tr>';
    return;
  }
  lista.forEach(item => {
    const tr = document.createElement('tr');
    tr.dataset.id = item.id;
    tr.innerHTML = `
      <td data-label="ID">${item.id}</td>
      <td data-label="Nombre">${item.nombre}</td>
      <td data-label="Descripción">${item.descripcion ? item.descripcion : ''}</td>
      <td data-label="Productos">${item.productos_count ?? 0}</td>
      <td data-label="Acciones">
        <button class="tabla-btn edit" data-id="${item.id}" title="Editar"><iconify-icon icon="lucide:edit"></iconify-icon></button>
        <button class="tabla-btn delete" data-id="${item.id}" title="Eliminar"><iconify-icon icon="mingcute:delete-2-line"></iconify-icon></button>
      </td>
    `;
    tbody.appendChild(tr);
  });
  actualizarSortIcons();
}

function actualizarSortIcons() {
  document.querySelectorAll('th.sortable').forEach(th => {
      const icon = th.querySelector('.sort-icon');
      if(!icon) return;
      const col = th.dataset.col;
      th.classList.remove('sorted-asc', 'sorted-desc', 'sorted-none');
      if (ordenCol === col) {
          icon.innerHTML = ordenDir === 'asc' ? '<iconify-icon icon="mdi:arrow-up"></iconify-icon>' : '<iconify-icon icon="mdi:arrow-down"></iconify-icon>';
          th.classList.add(ordenDir === 'asc' ? 'sorted-asc' : 'sorted-desc');
      } else {
          icon.innerHTML = '<iconify-icon icon="mdi:arrow-up-down"></iconify-icon>';
          th.classList.add('sorted-none');
      }
  });
}

async function cargarPresentaciones() {
    try {
        const res = await fetch(`/inventario/presentacion/api`);
        const data = await res.json();
        if (data.success) {
            presentacionesData = data.data;
            renderPresentacionesTabla();
        }
    } catch (error) {
        Swal.fire('Error', 'No se pudo actualizar la lista de presentaciones.', 'error');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('presentaciones-tbody')) {
        presentacionesData = window.presentacionesIniciales || []; 
        setupEventListeners();
        actualizarSortIcons();
        initAgregarPresentacion();
        initEditarPresentacion();
        initEliminarPresentacion();
    }
});

function setupEventListeners() {
    const buscarInput = document.getElementById('buscarPresentacion');
    if (buscarInput) {
        buscarInput.addEventListener('input', e => {
            busqueda = e.target.value;
            paginaActual = 1;
            renderPresentacionesTabla();
        });
    }

    const registrosSelect = document.getElementById('registrosPorPagina');
    if (registrosSelect) {
        registrosSelect.addEventListener('change', e => {
            registrosPorPagina = parseInt(e.target.value);
            paginaActual = 1;
            renderPresentacionesTabla();
        });
    }

    document.querySelectorAll('th.sortable').forEach(th => {
        th.addEventListener('click', () => {
            const col = th.getAttribute('data-col');
            if (ordenCol === col) {
                ordenDir = ordenDir === 'asc' ? 'desc' : 'asc';
            } else {
                ordenCol = col;
                ordenDir = 'asc';
            }
            renderPresentacionesTabla();
        });
    });
}
