<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en" data-theme="light">

<!-- ⚡ PRELOAD RECURSOS CRÍTICOS PARA VELOCIDAD MÁXIMA -->
<link rel="preload" href="{{ asset('assets/images/logotipo.png') }}" as="image" type="image/png">
<link rel="preload" href="{{ asset('assets/css/preloader.css') }}" as="style">
<link rel="preload" href="{{ asset('assets/js/app.js') }}" as="script">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://code.iconify.design">

<x-layouts.head />


<body class="dark:bg-neutral-800 bg-neutral-100">

    <!-- ⚡ PRELOADER INTELIGENTE - Dashboards se muestran INMEDIATAMENTE -->
    <div id="preloader" style="opacity: 1; visibility: visible; display: flex;">
        <div class="preloader-content">
            <img src="{{ asset('assets/images/logotipo.png') }}" alt="logo" loading="eager" decoding="sync">
            <div class="loading-text">Botica San Antonio</div>
        </div>
    </div>
    
    <!-- ⚡ SCRIPT INMEDIATO - Detectar tipo de página ANTES de cargar -->
    <script>
        // 🚀 EJECUTAR INMEDIATAMENTE - Antes de que nada más se cargue
        (function() {
            const preloader = document.getElementById('preloader');
            if (!preloader) return;
            
            // 🏠 Detectar tipo de página INMEDIATAMENTE
            const isDashboard = window.location.pathname.includes('/dashboard') || 
                              window.location.pathname === '/';
            const isAnalysisOrAlerts = window.location.pathname.includes('/analisis') || 
                                     window.location.pathname.includes('/alertas');
            
            if (!isDashboard && !isAnalysisOrAlerts) {
                // Si NO es dashboard/análisis/alertas, ocultar inmediatamente
                preloader.style.opacity = '0';
                preloader.style.visibility = 'hidden';
                preloader.style.display = 'none';
            } else {
                // Si ES dashboard/análisis/alertas, asegurar que esté visible
                preloader.style.opacity = '1';
                preloader.style.visibility = 'visible';
                preloader.style.display = 'flex';
                console.log(isDashboard ? '🏠 Dashboard detectado - Preloader mostrado INMEDIATAMENTE' :
                          '📊 Análisis/Alertas detectado - Preloader mostrado INMEDIATAMENTE');
            }
        })();
    </script>

    <!-- ⚡ SCRIPT PRINCIPAL - Manejo del ocultamiento del preloader -->
    <script>
        // 🧠 MANEJO INTELIGENTE DEL OCULTAMIENTO
        (function() {
            const preloader = document.getElementById('preloader');
            if (!preloader) return;
            
            const startTime = performance.now();
            let isHidden = false;
            
            // 🏠 Detectar tipo de página
            const isDashboard = window.location.pathname.includes('/dashboard') || 
                              window.location.pathname === '/';
            const isAnalysisOrAlerts = window.location.pathname.includes('/analisis') || 
                                     window.location.pathname.includes('/alertas');
            
            // Si no es dashboard/análisis/alertas y no está visible, no hacer nada
            if (!isDashboard && !isAnalysisOrAlerts && preloader.style.display === 'none') {
                return;
            }
            
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
                
                console.log(isDashboard ? '🏠 Dashboard - Preloader ocultado' : 
                          isAnalysisOrAlerts ? '📊 Análisis/Alertas - Preloader ocultado' : 
                          '🔄 Preloader ocultado');
            }
            
            // Ocultar cuando esté listo
            function checkAndHide() {
                const elapsedTime = performance.now() - startTime;
                
                // Tiempos optimizados por tipo de página
                if (isDashboard) {
                    const minShowTime = 1200; // Dashboard: 1.2 segundos
                    const remainingTime = Math.max(0, minShowTime - elapsedTime);
                    setTimeout(hidePreloader, remainingTime);
                } else if (isAnalysisOrAlerts) {
                    const minShowTime = 800; // Análisis/Alertas: 0.8 segundos (súper rápido)
                    const remainingTime = Math.max(0, minShowTime - elapsedTime);
                    setTimeout(hidePreloader, remainingTime);
                } else {
                    // Otras páginas: ocultar inmediatamente
                    hidePreloader();
                }
            }
            
            // Triggers para ocultar
            if (document.readyState === 'complete') {
                checkAndHide();
            } else if (document.readyState === 'interactive') {
                // DOM listo pero recursos pendientes
                setTimeout(checkAndHide, isDashboard ? 200 : isAnalysisOrAlerts ? 100 : 50);
            } else {
                document.addEventListener('DOMContentLoaded', () => {
                    setTimeout(checkAndHide, isDashboard ? 300 : isAnalysisOrAlerts ? 150 : 100);
                }, { once: true });
                
                window.addEventListener('load', checkAndHide, { once: true });
            }
            
            // Fallback de seguridad optimizado
            const maxShowTime = isDashboard ? 6000 : isAnalysisOrAlerts ? 3000 : 2000;
            setTimeout(() => {
                if (!isHidden) {
                    console.log(isDashboard ? '🏠 Dashboard timeout (6s) - ocultando preloader' : 
                              isAnalysisOrAlerts ? '📊 Análisis/Alertas timeout (3s) - ocultando preloader' :
                              '⏰ Timeout - ocultando preloader');
                    hidePreloader();
                }
            }, maxShowTime);
        })();
    </script>

    <x-navigation.sidebar />

    <main class="dashboard-main">

        <x-navigation.navbar />
        <div class="dashboard-main-body">
            
            <x-navigation.breadcrumb title='{{ isset($title) ? $title : "" }}' subTitle='{{ isset($subTitle) ? $subTitle : "" }}' />

            @yield('content')
        
        </div>
        <x-layouts.footer />

    </main>

    <!-- 🔔 Sistema de Notificaciones en Tiempo Real -->
    <script src="{{ asset('assets/js/notifications/notifications.js') }}" defer></script>
    
    <!-- ⚡ OPTIMIZADOR DE RENDIMIENTO GLOBAL -->
    <script src="{{ asset('assets/js/performance-optimizer.js') }}" defer></script>
    
    <x-layouts.script  script='{!! isset($script) ? $script : "" !!}' />
    
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