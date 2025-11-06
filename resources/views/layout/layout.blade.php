<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en" data-theme="light">

<!-- ⚡ PRELOAD RECURSOS CRÍTICOS PARA VELOCIDAD MÁXIMA -->
<link rel="preload" href="{{ asset('assets/images/logotipo.png') }}" as="image" type="image/png">
<link rel="preload" href="{{ asset('assets/css/preloader.css') }}" as="style">
<link rel="preload" href="{{ asset('assets/js/app.js') }}" as="script">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://code.iconify.design">

<x-head />


<body class="dark:bg-neutral-800 bg-neutral-100" data-turbo="true">

    <!-- ⚡ PRELOADER GLOBAL: visible al iniciar en todas las vistas -->
    <div id="preloader" style="opacity: 1; visibility: visible; display: flex;">
        <div class="preloader-content">
            <img src="{{ asset('assets/images/logotipo.png') }}" alt="logo" loading="eager" decoding="sync">
            <div class="loading-text">Botica San Antonio</div>
        </div>
    </div>
    
    <!-- ⚡ SCRIPT INMEDIATO - Mostrar preloader para TODAS las vistas -->
    <script>
        // 🚀 EJECUTAR INMEDIATAMENTE - Antes de que nada más se cargue
        (function() {
            const preloader = document.getElementById('preloader');
            if (!preloader) return;
            // Mostrar SIEMPRE el preloader al iniciar
            preloader.style.opacity = '1';
            preloader.style.visibility = 'visible';
            preloader.style.display = 'flex';
            console.log('🚀 Preloader global visible al iniciar');
        })();
    </script>

    <!-- ⚡ SCRIPT PRINCIPAL - Manejo del ocultamiento del preloader -->
    <script>
        // 🧠 MANEJO GLOBAL DEL OCULTAMIENTO
        (function() {
            const preloader = document.getElementById('preloader');
            if (!preloader) return;
            
            const startTime = performance.now();
            let isHidden = false;
            
            function hidePreloader() {
                if (isHidden) return;
                isHidden = true;
                
                preloader.style.transition = 'all 0.3s ease-out';
                preloader.style.opacity = '0';
                preloader.style.visibility = 'hidden';
                preloader.style.transform = 'scale(0.95)';
                preloader.style.pointerEvents = 'none';
                
                setTimeout(() => {
                    if (preloader.parentNode) {
                        preloader.remove();
                    }
                }, 300);
                console.log('✅ Preloader global ocultado');
            }
            
            // Ocultar cuando esté listo
            function checkAndHide() {
                const elapsedTime = performance.now() - startTime;
                const minShowTime = 600; // Tiempo mínimo visible global
                const remainingTime = Math.max(0, minShowTime - elapsedTime);
                setTimeout(hidePreloader, remainingTime);
            }
            
            // Triggers para ocultar
            if (document.readyState === 'complete') {
                checkAndHide();
            } else if (document.readyState === 'interactive') {
                // DOM listo pero recursos pendientes
                setTimeout(checkAndHide, 150);
            } else {
                document.addEventListener('DOMContentLoaded', () => {
                    setTimeout(checkAndHide, 200);
                }, { once: true });
                
                window.addEventListener('load', checkAndHide, { once: true });
            }
            
            // Fallback de seguridad global
            const maxShowTime = 6000;
            setTimeout(() => {
                if (!isHidden) {
                    console.log('⏰ Timeout global - ocultando preloader');
                    hidePreloader();
                }
            }, maxShowTime);
        })();
    </script>

    <x-sidebar />

    <main class="dashboard-main">

        <x-navbar />
        <div class="dashboard-main-body">
            
            <x-breadcrumb title='{{ isset($title) ? $title : "" }}' subTitle='{{ isset($subTitle) ? $subTitle : "" }}' />

            @yield('content')
        
        </div>
        <x-footer />

    </main>

    <!-- 🔔 Sistema de Notificaciones en Tiempo Real -->
    <script src="{{ asset('assets/js/notifications/notifications.js') }}" defer></script>
    
    <!-- ⚡ OPTIMIZADOR DE RENDIMIENTO GLOBAL -->
    <script src="{{ asset('assets/js/performance-optimizer.js') }}" defer></script>
    
    <x-script  script='{!! isset($script) ? $script : "" !!}' />
    
    <!-- ⚡ Carga diferida de Iconify para no bloquear -->
    <script>
        // Cargar Iconify después de que todo esté listo para máxima velocidad
        window.addEventListener('load', function() {
            const script = document.createElement('script');
            script.src = 'https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js';
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
        });
    </script>


</body>

</html>