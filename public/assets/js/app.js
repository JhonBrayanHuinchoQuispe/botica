'use strict';

// Ensure sidebar toggle functionality works properly
document.addEventListener('DOMContentLoaded', function() {
    // Double-check sidebar toggle functionality after DOM is loaded
    const sidebarToggle = document.querySelector(".sidebar-toggle");
    const sidebar = document.querySelector(".sidebar");
    const dashboardMain = document.querySelector(".dashboard-main");
    
    if (sidebarToggle && !sidebarToggle.hasAttribute('data-initialized')) {
        sidebarToggle.setAttribute('data-initialized', 'true');
        
        // Remove any existing listeners to prevent duplicates
        const newToggle = sidebarToggle.cloneNode(true);
        sidebarToggle.parentNode.replaceChild(newToggle, sidebarToggle);
        
        newToggle.addEventListener("click", function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            try {
                this.classList.toggle("active");
                
                if (sidebar) {
                    sidebar.classList.toggle("active");
                }
                
                if (dashboardMain) {
                    dashboardMain.classList.toggle("active");
                }
                
                // Force reflow
                if (sidebar) {
                    sidebar.offsetHeight;
                }
                
                console.log('Sidebar toggled successfully');
                
            } catch (error) {
                console.error('Error in sidebar toggle:', error);
            }
        });
    }
});

// === Reinicialización con Turbo Drive ===
function initSidebarUIWithTurbo() {
  try {
    // Resetear estado para evitar residuos entre páginas
    try {
      const toggle = document.querySelector('.sidebar-toggle');
      const sidebar = document.querySelector('.sidebar');
      const dashboardMain = document.querySelector('.dashboard-main');
      document.body.classList.remove('overlay-active');
      if (toggle) toggle.classList.remove('active');
      if (sidebar) sidebar.classList.remove('active','sidebar-open');
      if (dashboardMain) dashboardMain.classList.remove('active');
      // Cerrar todos los dropdowns
      document.querySelectorAll('.sidebar-menu .dropdown').forEach(function(dd){
        dd.classList.remove('dropdown-open','open','show');
        const sub = dd.querySelector('.sidebar-submenu');
        if (sub) sub.style.display = 'none';
      });
    } catch (e) {}

    const sidebar = document.querySelector('.sidebar');
    const dashboardMain = document.querySelector('.dashboard-main');

    // Toggle principal
    const toggleBtn = document.querySelector('.sidebar-toggle');
    if (toggleBtn) {
      const newToggle = toggleBtn.cloneNode(true);
      toggleBtn.parentNode.replaceChild(newToggle, toggleBtn);
      newToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        try {
          this.classList.toggle('active');
          if (sidebar) sidebar.classList.toggle('active');
          if (dashboardMain) dashboardMain.classList.toggle('active');
          if (sidebar) sidebar.offsetHeight;
        } catch (err) { console.error('Sidebar toggle err', err); }
      });
    }

    // Toggle móvil
    const mobileToggle = document.querySelector('.sidebar-mobile-toggle');
    if (mobileToggle) {
      const newMobile = mobileToggle.cloneNode(true);
      mobileToggle.parentNode.replaceChild(newMobile, mobileToggle);
      newMobile.addEventListener('click', function(e) {
        e.preventDefault(); e.stopPropagation();
        try {
          if (sidebar) sidebar.classList.add('sidebar-open');
          document.body.classList.add('overlay-active');
        } catch (err) { console.error('Mobile toggle err', err); }
      });
    }

    // Botón cerrar
    const closeBtn = document.querySelector('.sidebar-close-btn');
    if (closeBtn) {
      const newClose = closeBtn.cloneNode(true);
      closeBtn.parentNode.replaceChild(newClose, closeBtn);
      newClose.addEventListener('click', function(e) {
        e.preventDefault(); e.stopPropagation();
        try {
          if (sidebar) sidebar.classList.remove('sidebar-open');
          document.body.classList.remove('overlay-active');
        } catch (err) { console.error('Close sidebar err', err); }
      });
    }

    // Dropdowns del sidebar
    document.querySelectorAll('.sidebar-menu .dropdown > a').forEach(function(link){
      const newLink = link.cloneNode(true);
      link.parentNode.replaceChild(newLink, link);
      newLink.addEventListener('click', function(event){
        if (this.getAttribute('href') === 'javascript:void(0)') {
          event.preventDefault();
          const parentLi = this.parentElement;
          const submenu = parentLi.querySelector('.sidebar-submenu');
          const isOpening = !parentLi.classList.contains('dropdown-open');
          document.querySelectorAll('.sidebar-menu .dropdown').forEach(function(other){
            if (other !== parentLi) {
              other.classList.remove('dropdown-open','open');
              const otherSub = other.querySelector('.sidebar-submenu');
              if (otherSub) otherSub.style.display = 'none';
            }
          });
          if (submenu) {
            parentLi.classList.toggle('dropdown-open', isOpening);
            parentLi.classList.toggle('open', isOpening);
            submenu.style.display = isOpening ? 'block' : 'none';
          }
        }
      });
    });
  } catch (error) {
    console.error('initSidebarUIWithTurbo error', error);
  }
}

document.addEventListener('turbo:load', initSidebarUIWithTurbo);
document.addEventListener('turbo:before-cache', function(){
  try {
    document.body.classList.remove('overlay-active');
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) sidebar.classList.remove('sidebar-open');
  } catch (e) {}
});

// sidebar submenu collapsible js
document.querySelectorAll(".sidebar-menu .dropdown > a").forEach(function(dropdownToggle) {
    dropdownToggle.addEventListener("click", function(event) {
        // Only act on dropdown toggles, not real links
        if (this.getAttribute('href') === 'javascript:void(0)') {
            event.preventDefault();

            const parentLi = this.parentElement;
            const submenu = parentLi.querySelector(".sidebar-submenu");

            // Determine if we are opening or closing
            const isOpening = !parentLi.classList.contains('dropdown-open');

            // First, close all other open dropdowns
            document.querySelectorAll(".sidebar-menu .dropdown").forEach(function(otherDropdown) {
                if (otherDropdown !== parentLi) {
                    otherDropdown.classList.remove('dropdown-open', 'open');
                    const otherSubmenu = otherDropdown.querySelector(".sidebar-submenu");
                    if (otherSubmenu) {
                        otherSubmenu.style.display = 'none';
                    }
                }
            });

            // Then, toggle the one we clicked
            if (submenu) {
                parentLi.classList.toggle('dropdown-open', isOpening);
                parentLi.classList.toggle('open', isOpening);
                submenu.style.display = isOpening ? 'block' : 'none';
            }
        }
    });
});

// Toggle sidebar visibility and active class
const sidebarToggle = document.querySelector(".sidebar-toggle");
if(sidebarToggle) {
  sidebarToggle.addEventListener("click", function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    // Toggle classes with error handling
    try {
      this.classList.toggle("active");
      
      const sidebar = document.querySelector(".sidebar");
      const dashboardMain = document.querySelector(".dashboard-main");
      
      if (sidebar) {
        sidebar.classList.toggle("active");
      }
      
      if (dashboardMain) {
        dashboardMain.classList.toggle("active");
      }
      
      // Force a reflow to ensure CSS transitions work properly
      if (sidebar) {
        sidebar.offsetHeight;
      }
      
    } catch (error) {
      console.error('Error toggling sidebar:', error);
    }
  });
}

// Open sidebar in mobile view and add overlay
const sidebarMobileToggle = document.querySelector(".sidebar-mobile-toggle");
if(sidebarMobileToggle) {
  sidebarMobileToggle.addEventListener("click", function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    try {
      const sidebar = document.querySelector(".sidebar");
      if (sidebar) {
        sidebar.classList.add("sidebar-open");
        document.body.classList.add("overlay-active");
      }
    } catch (error) {
      console.error('Error opening mobile sidebar:', error);
    }
  });
}

// Close sidebar and remove overlay
const sidebarCloseBtn = document.querySelector(".sidebar-close-btn");
if(sidebarCloseBtn){
  sidebarCloseBtn.addEventListener("click", function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    try {
      const sidebar = document.querySelector(".sidebar");
      if (sidebar) {
        sidebar.classList.remove("sidebar-open");
        document.body.classList.remove("overlay-active");
      }
    } catch (error) {
      console.error('Error closing sidebar:', error);
    }
  });
}

//to keep the current page active
document.addEventListener("DOMContentLoaded", function () {
  var nk = window.location.href;
  var links = document.querySelectorAll("ul#sidebar-menu a");

  links.forEach(function (link) {
    if (link.href === nk) {
      link.classList.add("active-page"); // anchor
      var parent = link.parentElement;
      parent.classList.add("active-page"); // li

      // Traverse up the DOM tree and add classes to parent elements
      while (parent && parent.tagName !== "BODY") {
        if (parent.tagName === "LI") {
          parent.classList.add("show");
          parent.classList.add("open");
           // Add dropdown-open class if it's a dropdown
           if (parent.classList.contains('dropdown')) {
            parent.classList.add('dropdown-open');
          }
        }
        parent = parent.parentElement;
      }
    }
  });

  // Special handling for Almacén submenu based on URL hash
  handleAlmacenSubmenuActive();
});

// Function to handle Almacén submenu active states based on hash
function handleAlmacenSubmenuActive() {
  // Check if we're on the ubicaciones/mapa page
  if (window.location.pathname.includes('/ubicaciones/mapa')) {
    const currentHash = window.location.hash;
    
    // Remove active-page class from all Almacén submenu items
    const almacenSubmenuLinks = document.querySelectorAll('a[href*="#mapa"], a[href*="#productos-ubicados"], a[href*="#productos-sin-ubicar"]');
    almacenSubmenuLinks.forEach(function(link) {
      link.classList.remove('active-page');
      link.parentElement.classList.remove('active-page');
    });
    
    // Activate the correct submenu item based on hash
    let targetLink = null;
    if (currentHash === '#productos-ubicados') {
      targetLink = document.querySelector('a[href*="#productos-ubicados"]');
    } else if (currentHash === '#productos-sin-ubicar') {
      targetLink = document.querySelector('a[href*="#productos-sin-ubicar"]');
    } else {
      // Default to "Mapa del Almacén" if no hash or #mapa
      targetLink = document.querySelector('a[href*="#mapa"]');
    }
    
    if (targetLink) {
      targetLink.classList.add('active-page');
      targetLink.parentElement.classList.add('active-page');
      
      // Make sure the Almacén dropdown is open
      const almacenDropdown = targetLink.closest('.dropdown');
      if (almacenDropdown) {
        almacenDropdown.classList.add('dropdown-open', 'open', 'show');
        const submenu = almacenDropdown.querySelector('.sidebar-submenu');
        if (submenu) {
          submenu.style.display = 'block';
        }
      }
    }
  }
}

// Listen for hash changes to update active menu
window.addEventListener('hashchange', function() {
  handleAlmacenSubmenuActive();
});




// On page load or when changing themes, best to add inline in `head` to avoid FOUC
// SIEMPRE iniciar en modo claro por defecto - solo usar modo oscuro si está explícitamente guardado
if (localStorage.getItem('color-theme') === 'dark') {
  document.documentElement.classList.add('dark');
} else {
  document.documentElement.classList.remove('dark');
  // Si no hay preferencia guardada, establecer modo claro por defecto
  if (!localStorage.getItem('color-theme')) {
    localStorage.setItem('color-theme', 'light');
  }
}

// light dark version js
var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

// Change the icons inside the button based on previous settings
if(themeToggleDarkIcon || themeToggleLightIcon){
    // Solo mostrar icono de luna si está específicamente en modo oscuro
    if (localStorage.getItem('color-theme') === 'dark') {
      themeToggleLightIcon.classList.remove('hidden');
  } else {
      themeToggleDarkIcon.classList.remove('hidden');
  }
}

var themeToggleBtn = document.getElementById('theme-toggle');

if(themeToggleDarkIcon || themeToggleLightIcon || themeToggleBtn){
  themeToggleBtn.addEventListener('click', function() {

    // toggle icons inside button
    themeToggleDarkIcon.classList.toggle('hidden');
    themeToggleLightIcon.classList.toggle('hidden');

    // if set via local storage previously
    if (localStorage.getItem('color-theme')) {
        if (localStorage.getItem('color-theme') === 'light') {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
        }

    // if NOT set via local storage previously
    } else {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme', 'dark');
        }
    }
});
}

// 🧠 PRELOADER INTELIGENTE - Backup para casos especiales
// Nota: El preloader principal se maneja inline en el HTML para máxima velocidad
(function() {
    const preloader = document.getElementById('preloader');
    
    if (preloader) {
        // Helpers globales para control manual del preloader desde cualquier módulo
        window.Preloader = {
            show() {
                const el = document.getElementById('preloader');
                if (!el) return;
                el.style.opacity = '1';
                el.style.visibility = 'visible';
                el.style.display = 'flex';
            },
            hide() {
                const el = document.getElementById('preloader');
                if (!el) return;
                el.style.transition = 'all 0.15s ease-out';
                el.style.opacity = '0';
                el.style.visibility = 'hidden';
                el.style.transform = 'scale(0.95)';
                el.style.pointerEvents = 'none';
            }
        };
        // Solo actuar si el preloader inline no funcionó (casos raros)
        setTimeout(() => {
            if (preloader && preloader.style.opacity !== '0') {
                console.log('🔧 Backup preloader logic activado');
                
                preloader.style.transition = 'all 0.15s ease-out';
                preloader.style.opacity = '0';
                preloader.style.visibility = 'hidden';
                preloader.style.transform = 'scale(0.95)';
                preloader.style.pointerEvents = 'none';
                
                setTimeout(() => {
                    if (preloader.parentNode) {
                        preloader.remove();
                    }
                }, 150);
            }
        }, 100);
    }
})();

// Progressive image loading for better perceived performance
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback for older browsers
        images.forEach(img => {
            img.src = img.dataset.src;
            img.classList.remove('lazy');
        });
    }
});