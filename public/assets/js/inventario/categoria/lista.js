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

let categoriasData = [];
let ordenCol = 'id';
let ordenDir = 'asc';
let registrosPorPagina = 10;
let paginaActual = 1;
let busqueda = '';

function renderCategoriasTabla() {
  let lista = categoriasData.slice();
  // Filtro de búsqueda
  if (busqueda.trim() !== '') {
    const b = busqueda.trim().toLowerCase();
    lista = lista.filter(cat =>
      cat.nombre.toLowerCase().includes(b) ||
      (cat.descripcion && cat.descripcion.toLowerCase().includes(b))
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
  // Paginación
  const total = lista.length;
  const totalPaginas = Math.ceil(total / registrosPorPagina);
  if (paginaActual > totalPaginas) paginaActual = 1;
  const inicio = (paginaActual - 1) * registrosPorPagina;
  const fin = inicio + registrosPorPagina;
  const paginados = lista.slice(inicio, fin);

  const tbody = document.getElementById('categorias-tbody');
  tbody.innerHTML = '';
  if (paginados.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:2rem;">No se encontraron categorías con los filtros actuales.</td></tr>';
    return;
  }
  paginados.forEach(cat => {
    const tr = document.createElement('tr');
    tr.dataset.id = cat.id;
    tr.innerHTML = `
      <td data-label="ID">${cat.id}</td>
      <td data-label="Nombre">${cat.nombre}</td>
      <td data-label="Descripción">${cat.descripcion ? cat.descripcion : ''}</td>
      <td data-label="Productos">${cat.productos_count ?? 0}</td>
      <td data-label="Acciones">
        <button class="tabla-btn edit" data-id="${cat.id}" title="Editar"><iconify-icon icon="mdi:pencil"></iconify-icon></button>
        <button class="tabla-btn delete" data-id="${cat.id}" title="Eliminar"><iconify-icon icon="mdi:delete"></iconify-icon></button>
      </td>
    `;
    tbody.appendChild(tr);
  });
  actualizarSortIcons();
}

function actualizarSortIcons() {
  const cols = ['id', 'nombre', 'productos'];
  cols.forEach(col => {
    const th = document.querySelector(`th[data-col="${col}"]`);
    if (!th) return;
    const icon = th.querySelector('.sort-icon');
    th.classList.remove('sorted-asc', 'sorted-desc', 'sorted-none');
    if (ordenCol === col) {
      if (ordenDir === 'asc') {
        th.classList.add('sorted-asc');
        icon.innerHTML = '<iconify-icon icon="mdi:arrow-up"></iconify-icon>';
      } else {
        th.classList.add('sorted-desc');
        icon.innerHTML = '<iconify-icon icon="mdi:arrow-down"></iconify-icon>';
      }
    } else {
      th.classList.add('sorted-none');
      icon.innerHTML = '<iconify-icon icon="mdi:arrow-up-down"></iconify-icon>';
    }
  });
}

async function cargarCategorias() {
    try {
        const res = await fetch(`/inventario/categoria/api`);
        if (!res.ok) throw new Error('Error al recargar las categorías');
        const data = await res.json();
        if (data.success) {
            categoriasData = data.data;
            renderCategoriasTabla();
        }
    } catch (error) {
        console.error(error);
        Swal.fire('Error', 'No se pudo actualizar la lista de categorías.', 'error');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar todas las funcionalidades de la página de categorías
    initCategoriasPage();
});

function initCategoriasPage() {
    if (document.getElementById('categorias-tbody')) {
        // Usar los datos inyectados por Blade para la carga inicial
        // La tabla ya está renderizada por el servidor
        categoriasData = window.categoriasIniciales || []; 
        setupEventListeners();
        actualizarSortIcons(); // Asegurarse de que los íconos de ordenamiento se muestren correctamente
    }
    // Inicializar lógica para los modales
    initAgregarCategoria();
    initEditarCategoria();
    initEliminarCategoria();
}

function setupEventListeners() {
    // Buscador
    const buscarInput = document.getElementById('buscarCategoria');
    if (buscarInput) {
        buscarInput.addEventListener('input', (e) => {
            busqueda = e.target.value;
            paginaActual = 1;
            renderCategoriasTabla();
        });
    }

    // Registros por página
    const registrosSelect = document.getElementById('registrosPorPagina');
    if (registrosSelect) {
        registrosSelect.addEventListener('change', (e) => {
            registrosPorPagina = parseInt(e.target.value);
            paginaActual = 1;
            renderCategoriasTabla();
        });
    }

    // Cabeceras de tabla para ordenar
    document.querySelectorAll('th.sortable').forEach(th => {
        th.addEventListener('click', () => {
            const col = th.getAttribute('data-col');
            if (ordenCol === col) {
                ordenDir = ordenDir === 'asc' ? 'desc' : 'asc';
            } else {
                ordenCol = col;
                ordenDir = 'asc';
            }
            renderCategoriasTabla();
        });
    });
}
