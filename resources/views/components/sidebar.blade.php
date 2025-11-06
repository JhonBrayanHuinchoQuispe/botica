{{-- Iconify ya está importado globalmente via Vite/app.js. Evitar <head> incrustado que rompe navegación con Turbo. --}}

<aside class="sidebar">
    <button type="button" class="sidebar-close-btn !mt-4">
        <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
    </button>
    <div>
        <a href="{{ route('dashboard.analisis') }}" class="sidebar-logo">
            <img src="{{ asset('assets/images/logotipo.png') }}" alt="site logo" class="light-logo">
            <img src="{{ asset('assets/images/logotipo.png') }}" alt="site logo" class="dark-logo">
            <img src="{{ asset('assets/images/logotipo.png') }}" alt="site logo" class="logo-icon">
            <span class="sidebar-brand-text">San Antonio</span>
        </a>
    </div>
    
    <div class="sidebar-menu-area">
        <ul class="sidebar-menu" id="sidebar-menu">
            
            {{-- ============================================ --}}
            {{-- DASHBOARD - Todos los usuarios autenticados --}}
            {{-- ============================================ --}}
            @can('dashboard.view')
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:chart-square-bold" class="menu-icon"></iconify-icon>
                    <span>Dashboard</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('dashboard.analisis') }}">
                            <i class="ri-circle-fill circle-icon text-primary-600"></i> Análisis
                        </a>
                    </li>

                </ul>
            </li>
            @endcan

            {{-- ============================================ --}}
            {{-- PUNTO DE VENTA - Solo vendedores y superiores --}}
            {{-- ============================================ --}}
            @if(auth()->user()->can('ventas.view') || auth()->user()->can('ventas.create'))
            <li class="sidebar-menu-group-title">Punto de Venta</li>
            
            @can('ventas.create')
            <li>
                <a href="{{ route('punto-venta.index') }}" data-turbo="false">
                    <iconify-icon icon="solar:shop-2-bold-duotone" class="menu-icon"></iconify-icon>
                    <span>Nueva Venta (POS)</span>
                </a>
            </li>
            @endcan
            
            <li class="dropdown {{ request()->routeIs('ventas.*') ? 'dropdown-open open' : '' }}">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:chart-square-bold-duotone" class="menu-icon"></iconify-icon>
                    <span>Gestión de Ventas</span>
                </a>
                <ul class="sidebar-submenu">
                    @can('ventas.view')
                    <li>
                        <a href="{{ route('ventas.historial') }}" class="{{ request()->routeIs('ventas.historial') ? 'active-page' : '' }}">
                            <i class="ri-circle-fill circle-icon text-primary-600"></i> Historial de Ventas
                        </a>
                    </li>
                    @endcan
                    @can('ventas.create')
                    <li>
                        <a href="{{ route('ventas.devoluciones') }}" class="{{ request()->routeIs('ventas.devoluciones') ? 'active-page' : '' }}">
                            <i class="ri-circle-fill circle-icon text-danger-600"></i> Devoluciones
                        </a>
                    </li>
                    @endcan
                    @can('ventas.reports')
                    <li>
                        <a href="{{ route('ventas.reportes') }}" class="{{ request()->routeIs('ventas.reportes') ? 'active-page' : '' }}">
                            <i class="ri-circle-fill circle-icon text-info-600"></i> Reportes
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endif

            {{-- ============================================ --}}
            {{-- GESTIÓN DE PRODUCTOS - Almaceneros y superiores --}}
            {{-- ============================================ --}}
            @if(auth()->user()->can('inventario.view') || auth()->user()->can('ubicaciones.view') || auth()->user()->can('compras.view'))
            <li class="sidebar-menu-group-title">Gestión de Inventario</li>
            
            {{-- Productos e Inventario --}}
            @can('inventario.view')
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:box-bold" class="menu-icon"></iconify-icon>
                    <span>Productos</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('inventario.productos.botica') }}">
                            <i class="ri-circle-fill circle-icon text-danger-600"></i> Lista de Productos
                        </a>
                    </li>
                    
                    @can('inventario.categories')
                    <li>
                        <a href="{{ route('inventario.categorias') }}">
                            <i class="ri-circle-fill circle-icon text-success-600"></i> Categorías
                        </a>
                    </li>
                    @endcan
                    @can('inventario.categories')
                    <li>
                        <a href="{{ route('inventario.presentaciones') }}">
                            <i class="ri-circle-fill circle-icon text-warning-600"></i> Presentaciones
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcan

            

            {{-- Compras y Proveedores --}}
            @can('compras.view')
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="menu-icon"></iconify-icon>
                    <span>Entrada de Mercadería</span>
                </a>
                <ul class="sidebar-submenu">
                    @can('compras.create')
                    <li>
                        <a href="{{ route('compras.nueva') }}">
                            <i class="ri-circle-fill circle-icon text-primary-600"></i> Nueva Entrada
                        </a>
                    </li>
                    @endcan
                    <li>
                        <a href="{{ route('compras.historial') }}">
                            <i class="ri-circle-fill circle-icon text-info-600"></i> Historial de Entradas
                        </a>
                    </li>

                </ul>
            </li>
            @endcan

            {{-- Mapa Almacén --}}
            @can('ubicaciones.view')
            <li>
                <a href="{{ route('ubicaciones.mapa') }}" class="{{ request()->routeIs('ubicaciones.*') ? 'active-page' : '' }}">
                    <iconify-icon icon="solar:map-point-bold" class="menu-icon"></iconify-icon>
                    <span>Gestión de Almacén</span>
                </a>
            </li>
            @endcan
            @endif

            {{-- ============================================ --}}
            {{-- ADMINISTRACIÓN - Solo administradores y superiores --}}
            {{-- ============================================ --}}
            @if(auth()->user()->can('usuarios.view') || auth()->user()->can('usuarios.roles'))
            <li class="sidebar-menu-group-title">Control de Usuarios</li>
            <li class="dropdown {{ request()->routeIs('admin.usuarios.*') || request()->routeIs('admin.roles.*') ? 'dropdown-open open' : '' }}">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:users-group-two-rounded-bold" class="menu-icon"></iconify-icon>
                    <span>Usuarios y Roles</span>
                </a>
                <ul class="sidebar-submenu">
                    @can('usuarios.view')
                    <li>
                        <a href="{{ route('admin.usuarios.index') }}" class="{{ request()->routeIs('admin.usuarios.index') ? 'active-page' : '' }}">
                            <i class="ri-circle-fill circle-icon text-primary-600"></i> Gestión de Usuarios
                        </a>
                    </li>
                    @endcan
                    @can('usuarios.roles')
                    <li>
                        <a href="{{ route('admin.roles.index') }}" class="{{ request()->routeIs('admin.roles.index') ? 'active-page' : '' }}">
                            <i class="ri-circle-fill circle-icon text-warning-600"></i> Roles y Permisos
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endif

            {{-- Proveedores --}}
            @can('compras.view')
            <li>
                <a href="{{ route('compras.proveedores') }}">
                    <iconify-icon icon="solar:users-group-rounded-bold" class="menu-icon"></iconify-icon>
                    <span>Proveedores</span>
                </a>
            </li>
            @endcan


            {{-- ============================================ --}}
            {{-- CONFIGURACIÓN DEL SISTEMA - Solo dueño y Administradores --}}
            {{-- ============================================ --}}
            @if(auth()->user()->can('config.system') || auth()->user()->can('config.backups') || auth()->user()->can('config.logs'))
            <li class="sidebar-menu-group-title">Configuración del Sistema</li>
            
            {{-- Configuración General --}}
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:settings-bold" class="menu-icon"></iconify-icon>
                    <span>Configuración General</span>
                </a>
                <ul class="sidebar-submenu">
                    @can('config.system')
                    <li>
                        <a href="{{ route('admin.configuracion.empresa') }}">
                            <i class="ri-circle-fill circle-icon text-primary-600"></i> Datos de la Empresa
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.configuracion.igv') }}">
                            <i class="ri-circle-fill circle-icon text-success-600"></i> Configuración IGV
                        </a>
                    </li>

                    @endcan
                </ul>
            </li>

            {{-- Impresoras y Tickets --}}
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:printer-bold" class="menu-icon"></iconify-icon>
                    <span>Comprobante de Venta</span>
                </a>
                <ul class="sidebar-submenu">
                    @can('config.system')
                    <li>
                        <a href="{{ route('admin.configuracion.impresoras') }}">
                            <i class="ri-circle-fill circle-icon text-primary-600"></i> Boleta de Venta
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.configuracion.tickets') }}">
                            <i class="ri-circle-fill circle-icon text-warning-600"></i> Formato de Tickets
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.configuracion.comprobantes') }}">
                            <i class="ri-circle-fill circle-icon text-info-600"></i> Comprobantes Electrónicos
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>

            {{-- Sistema y Mantenimiento --}}
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:shield-check-bold" class="menu-icon"></iconify-icon>
                    <span>Sistema y Mantenimiento</span>
                </a>
                <ul class="sidebar-submenu">
                    @can('config.backups')
                    <li>
                        <a href="{{ route('admin.respaldos') }}">
                            <i class="ri-circle-fill circle-icon text-warning-600"></i> Respaldos
                        </a>
                    </li>
                    @endcan
                    @can('config.logs')
                    <li>
                        <a href="{{ route('admin.logs') }}">
                            <i class="ri-circle-fill circle-icon text-danger-600"></i> Logs del Sistema
                        </a>
                    </li>
                    @endcan
                    @can('config.system')
                    <li>
                        <a href="{{ route('admin.configuracion.alertas') }}">
                            <i class="ri-circle-fill circle-icon text-purple-600"></i> Alertas del Sistema
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.configuracion.cache') }}">
                            <i class="ri-circle-fill circle-icon text-cyan-600"></i> Limpiar Caché
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endif
        </ul>
    </div>
</aside>

{{-- Panel de depuración de permisos deshabilitado --}}
{{--
@if(config('app.debug') && auth()->check() && (auth()->user()->isDueno() || auth()->user()->isGerente()))
<div class="sidebar-debug" style="position: fixed; bottom: 10px; left: 10px; background: rgba(0,150,0,0.9); color: white; padding: 8px; font-size: 11px; z-index: 1000; border-radius: 5px; max-width: 250px;">
    <strong>✅ PERMISOS ACTIVOS</strong><br>
    <strong>Usuario:</strong> {{ auth()->user()->name }}<br>
    <strong>Rol:</strong> {{ auth()->user()->getRoleNames()->first() }}<br>
    <strong>Permisos:</strong> {{ count(auth()->user()->getAllPermissions()) }}<br>
    <small style="color: #ccffcc;">🔒 Sistema de permisos funcionando</small>
</div>
@elseif(config('app.debug') && auth()->check())
<div class="sidebar-debug" style="position: fixed; bottom: 10px; left: 10px; background: rgba(0,100,200,0.9); color: white; padding: 8px; font-size: 11px; z-index: 1000; border-radius: 5px; max-width: 250px;">
    <strong>👤 {{ strtoupper(auth()->user()->getRoleNames()->first()) }}</strong><br>
    <strong>Permisos:</strong> {{ count(auth()->user()->getAllPermissions()) }}<br>
    <small style="color: #ccccff;">🔒 Acceso limitado por rol</small>
</div>
@endif
--}}