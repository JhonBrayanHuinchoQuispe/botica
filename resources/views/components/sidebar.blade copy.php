<head>
    
  <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
</head>


<aside class="sidebar">
    <button type="button" class="sidebar-close-btn !mt-4">
        <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
    </button>
    <div>
        <a href="{{ route('index2') }}" class="sidebar-logo">
            <img src="{{ asset('assets/images/logo.png') }}" alt="site logo" class="light-logo">
            <img src="{{ asset('assets/images/logo-light.png') }}" alt="site logo" class="dark-logo">
            <img src="{{ asset('assets/images/logo-icon.png') }}" alt="site logo" class="logo-icon">
        </a>
    </div>
    <div class="sidebar-menu-area">
        <ul class="sidebar-menu" id="sidebar-menu">
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Dashboard</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('index2') }}"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i> Analisis</a>
                    </li>
                    <li>
                        <a href="{{ route('index3') }}"><i class="ri-circle-fill circle-icon text-info-600 w-auto"></i> eCommerce</a>
                    </li>
                    <li>
                        <a href="{{ route('index8') }}"><i class="ri-circle-fill circle-icon text-danger-600 w-auto"></i> Medical</a>
                    </li>
                </ul>
            </li>

            <li class="sidebar-menu-group-title">Inventario</li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Producto</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('index7') }}"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i>Productos</a>
                    </li>
                    <li>
                        <a href="{{ route('index3') }}"><i class="ri-circle-fill circle-icon text-info-600 w-auto"></i>Categorias</a>
                    </li>
                    <li>
                        <a href="{{ route('index8') }}"><i class="ri-circle-fill circle-icon text-danger-600 w-auto"></i> Presentación</a>
                    </li>
                    <li>
                        <a href="{{ route('index8') }}"><i class="ri-circle-fill circle-icon text-danger-600 w-auto"></i>Marcas</a>
                    </li>
                </ul>
            </li>


            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Proveedores</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('index7') }}"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i>Lista</a>
                    </li>
                </ul>
            </li>


            <li class="sidebar-menu-group-title">Roles y permisos</li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Roles y permisos</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('index7') }}"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i>Gestionar roles</a>
                    </li>

                </ul>
            </li>
            



            <li class="sidebar-menu-group-title">UI Elements</li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mingcute:storage-line" class="menu-icon"></iconify-icon>
                    <span>Table</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('tableBasic') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Basic Table</a>
                    </li>
                    <li>
                        <a href="{{ route('tableData') }}"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i> Data Table</a>
                    </li>
                </ul>
            </li>

            <li class="sidebar-menu-group-title">Application</li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="simple-line-icons:vector" class="menu-icon"></iconify-icon>
                    <span>Authentication</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('signin') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Sign In</a>
                    </li>
                    <li>
                        <a href="{{ route('signup') }}"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i> Sign Up</a>
                    </li>
                    <li>
                        <a href="{{ route('forgotPassword') }}"><i class="ri-circle-fill circle-icon text-info-600 w-auto"></i> Forgot Password</a>
                    </li>
                </ul>
            </li>
           
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="icon-park-outline:setting-two" class="menu-icon"></iconify-icon>
                    <span>Settings</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('company') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Company</a>
                    </li>
                    <li>
                        <a href="{{ route('notification') }}"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i> Notification</a>
                    </li>
                    <li>
                        <a href="{{ route('notificationAlert') }}"><i class="ri-circle-fill circle-icon text-info-600 w-auto"></i> Notification Alert</a>
                    </li>
                    <li>
                        <a href="{{ route('theme') }}"><i class="ri-circle-fill circle-icon text-danger-600 w-auto"></i> Theme</a>
                    </li>
                    <li>
                        <a href="{{ route('currencies') }}"><i class="ri-circle-fill circle-icon text-danger-600 w-auto"></i> Currencies</a>
                    </li>
                    <li>
                        <a href="{{ route('language') }}"><i class="ri-circle-fill circle-icon text-danger-600 w-auto"></i> Languages</a>
                    </li>
                    <li>
                        <a href="{{ route('paymentGateway') }}"><i class="ri-circle-fill circle-icon text-danger-600 w-auto"></i> Payment Gateway</a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</aside>