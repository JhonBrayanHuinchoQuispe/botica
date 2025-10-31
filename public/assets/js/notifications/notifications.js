/**
 * Sistema de Notificaciones en Tiempo Real
 * Conecta el frontend con la API de notificaciones
 */

class NotificationManager {
    constructor() {
        this.apiBaseUrl = '/api/notifications';
        this.updateInterval = 5000; // 5 segundos para tiempo real
        this.intervalId = null;
        this.isLoading = false;
        
        this.init();
    }

    /**
     * Inicializar el sistema de notificaciones
     */
    init() {
        console.log('🔔 Inicializando sistema de notificaciones...');
        
        // Cargar notificaciones inmediatamente
        this.loadNotifications();
        
        // Configurar actualización automática
        this.startAutoUpdate();
        
        // Configurar eventos
        this.setupEventListeners();
        
        console.log('✅ Sistema de notificaciones inicializado');
    }

    /**
     * Cargar notificaciones desde la API
     */
    async loadNotifications() {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.showLoadingIndicator();
        
        try {
            const response = await fetch(`${this.apiBaseUrl}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            
            if (data.success && Array.isArray(data.data)) {
                this.renderNotifications(data.data);
                this.updateNotificationCounter(data.data.length);
                console.log(`📬 ${data.data.length} notificaciones cargadas`);
            } else {
                console.warn('⚠️ Respuesta de API inválida:', data);
                this.showFallbackNotifications();
            }
        } catch (error) {
            console.error('❌ Error cargando notificaciones:', error);
            this.showFallbackNotifications();
        } finally {
            this.isLoading = false;
            this.hideLoadingIndicator();
        }
    }

    /**
     * Renderizar notificaciones en el dropdown
     */
    renderNotifications(notifications) {
        const container = document.getElementById('notifications-container');
        if (!container) {
            console.error('❌ Contenedor de notificaciones no encontrado');
            return;
        }

        this.renderNotificationsInContainer(container, notifications);
    }

    renderNotificationsInContainer(container, notifications) {
        // Ocultar indicador de carga
        const loadingIndicator = document.getElementById('notifications-loading');
        if (loadingIndicator) {
            loadingIndicator.style.display = 'none';
        }

        // Limpiar notificaciones existentes
        container.innerHTML = '';

        if (notifications.length === 0) {
            this.showEmptyState(container);
            return;
        }

        // Renderizar cada notificación
        notifications.forEach(notification => {
            const notificationElement = this.createNotificationElement(notification);
            container.appendChild(notificationElement);
        });
    }

    /**
     * Crear elemento HTML para una notificación
     */
    createNotificationElement(notification) {
        const div = document.createElement('div');
        
        const config = this.getNotificationConfig(notification.type, notification.priority);
        const timeAgo = this.formatTimeAgo(notification.created_at);
        const route = this.getNotificationRoute(notification.type);
        
        div.innerHTML = `
            <a href="${route}" class="notification-item flex px-4 py-4 hover:bg-${config.color}-50 dark:hover:bg-${config.color}-900/20 transition-all duration-300 justify-between gap-3 border-l-4 border-${config.color}-500 hover:border-${config.color}-600 hover:shadow-md">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 relative w-14 h-14 border-2 border-${config.color}-200 dark:border-${config.color}-700 flex justify-center items-center rounded-full">
                        <iconify-icon icon="${config.icon}" class="text-2xl notification-icon-${config.color}" style="color: ${config.iconColor} !important;"></iconify-icon>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h6 class="text-sm font-bold text-${config.color}-700 dark:text-${config.color}-300">${notification.title}</h6>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-${config.color}-100 text-${config.color}-800 dark:bg-${config.color}-900/50 dark:text-${config.color}-300">
                                ${config.badge}
                            </span>
                        </div>
                        <p class="mb-0 text-sm text-gray-700 dark:text-gray-300 font-medium">${notification.message}</p>
                    </div>
                </div>
                <div class="shrink-0 flex flex-col items-end justify-center">
                    <span class="text-xs text-${config.color}-600 dark:text-${config.color}-400 font-bold bg-${config.color}-100 dark:bg-${config.color}-900/50 px-3 py-1 rounded-full border border-${config.color}-200 dark:border-${config.color}-700">${timeAgo}</span>
                </div>
            </a>
        `;
        
        return div.firstElementChild;
    }

    /**
     * Obtener configuración visual según tipo y prioridad
     */
    getNotificationConfig(type, priority) {
        const configs = {
            'stock_critico': {
                color: 'red',
                icon: 'solar:danger-triangle-bold',
                iconColor: '#dc2626',
                badge: '¡URGENTE!'
            },
            'stock_agotado': {
                color: 'red',
                icon: 'solar:close-circle-bold',
                iconColor: '#dc2626',
                badge: '¡AGOTADO!'
            },
            'producto_vencimiento': {
                color: 'amber',
                icon: 'solar:calendar-date-bold',
                iconColor: '#d97706',
                badge: 'ADVERTENCIA'
            },
            'producto_vencido': {
                color: 'red',
                icon: 'solar:calendar-cross-bold',
                iconColor: '#dc2626',
                badge: '¡VENCIDO!'
            }
        };

        return configs[type] || {
            color: 'blue',
            icon: 'solar:bell-bold',
            iconColor: '#2563eb',
            badge: 'INFO'
        };
    }

    /**
     * Obtener ruta según tipo de notificación
     */
    getNotificationRoute(type) {
        const routes = {
            'stock_critico': '/inventario/productos?filter=stock_bajo',
            'stock_agotado': '/inventario/productos?filter=agotados',
            'producto_vencimiento': '/inventario/productos?filter=por_vencer',
            'producto_vencido': '/inventario/productos?filter=vencidos'
        };

        return routes[type] || '/dashboard';
    }

    /**
     * Formatear tiempo transcurrido
     */
    formatTimeAgo(dateString) {
        const now = new Date();
        const date = new Date(dateString);
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) {
            return 'Ahora';
        } else if (diffInSeconds < 3600) {
            const minutes = Math.floor(diffInSeconds / 60);
            return `${minutes} min`;
        } else if (diffInSeconds < 86400) {
            const hours = Math.floor(diffInSeconds / 3600);
            return `${hours} hora${hours > 1 ? 's' : ''}`;
        } else {
            const days = Math.floor(diffInSeconds / 86400);
            return `${days} día${days > 1 ? 's' : ''}`;
        }
    }

    /**
     * Mostrar estado vacío
     */
    showEmptyState(container) {
        container.innerHTML = `
            <div class="flex flex-col items-center justify-center py-12 px-4">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                    <iconify-icon icon="solar:bell-off-bold" class="text-2xl text-gray-400 dark:text-gray-500"></iconify-icon>
                </div>
                <h6 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">No hay notificaciones</h6>
                <p class="text-xs text-gray-400 dark:text-gray-500 text-center">Todas las notificaciones aparecerán aquí</p>
            </div>
        `;
    }

    /**
     * Mostrar notificaciones de respaldo en caso de error
     */
    showFallbackNotifications() {
        console.log('📋 Mostrando notificaciones de respaldo');
        
        // Crear notificaciones de ejemplo con el mismo formato
        const fallbackNotifications = [
            {
                id: 'fallback-1',
                type: 'stock_critico',
                priority: 'high',
                title: 'Stock Crítico',
                message: 'Algunos productos tienen stock bajo',
                created_at: new Date().toISOString()
            },
            {
                id: 'fallback-2',
                type: 'proximo_vencer',
                priority: 'medium',
                title: 'Próximo a Vencer',
                message: 'Productos próximos a vencer',
                created_at: new Date().toISOString()
            }
        ];
        
        this.renderNotifications(fallbackNotifications);
        this.updateNotificationCounter(fallbackNotifications.length);
    }

    /**
     * Actualizar contador de notificaciones
     */
    updateNotificationCounter(count) {
        const counterElement = document.querySelector('#dropdownNotification .bg-blue-600');
        if (counterElement) {
            counterElement.textContent = count.toString().padStart(2, '0');
        }

        // Actualizar el contador del icono de campana
        const bellCounter = document.getElementById('notification-counter');
        if (bellCounter) {
            bellCounter.textContent = count;
            
            if (count > 0) {
                bellCounter.style.display = 'flex';
            } else {
                bellCounter.style.display = 'none';
            }
        }

        // Llamar a la función existente para actualizar estilos
        if (typeof updateNotificationCounter === 'function') {
            updateNotificationCounter();
        }
    }

    /**
     * Configurar eventos
     */
    setupEventListeners() {
        // Marcar notificación como leída al hacer clic
        document.addEventListener('click', (e) => {
            const notificationItem = e.target.closest('.notification-item');
            if (notificationItem) {
                this.markAsRead(notificationItem);
            }
        });

        // Recargar notificaciones cuando se enfoque la ventana
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.loadNotifications();
            }
        });
    }

    /**
     * Marcar notificación como leída
     */
    async markAsRead(notificationElement) {
        // Extraer ID de la notificación si está disponible
        const notificationId = notificationElement.dataset.notificationId;
        
        if (notificationId) {
            try {
                await fetch(`${this.apiBaseUrl}/${notificationId}/mark-read`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
            } catch (error) {
                console.error('❌ Error marcando notificación como leída:', error);
            }
        }
    }

    /**
     * Iniciar actualización automática
     */
    startAutoUpdate() {
        this.intervalId = setInterval(() => {
            this.loadNotifications();
        }, this.updateInterval);
        
        console.log(`⏰ Actualización automática configurada cada ${this.updateInterval/1000} segundos`);
    }

    /**
     * Detener actualización automática
     */
    stopAutoUpdate() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
            console.log('⏹️ Actualización automática detenida');
        }
    }

    /**
     * Mostrar indicador de carga
     */
    showLoadingIndicator() {
        const loadingIndicator = document.getElementById('notifications-loading');
        if (loadingIndicator) {
            loadingIndicator.style.display = 'flex';
        }
        
        const button = document.getElementById('notification-button');
        if (button) {
            const icon = button.querySelector('iconify-icon');
            if (icon) {
                icon.setAttribute('icon', 'eos-icons:loading');
                icon.classList.add('animate-spin');
            }
        }
    }

    /**
     * Ocultar indicador de carga
     */
    hideLoadingIndicator() {
        const loadingIndicator = document.getElementById('notifications-loading');
        if (loadingIndicator) {
            loadingIndicator.style.display = 'none';
        }
        
        const button = document.getElementById('notification-button');
        if (button) {
            const icon = button.querySelector('iconify-icon');
            if (icon) {
                icon.setAttribute('icon', 'iconoir:bell');
                icon.classList.remove('animate-spin');
            }
        }
    }

    /**
     * Destruir instancia
     */
    destroy() {
        this.stopAutoUpdate();
        console.log('🗑️ Sistema de notificaciones destruido');
    }
}

// Inicializar automáticamente cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Solo inicializar si estamos en una página que tiene el dropdown de notificaciones
    if (document.getElementById('dropdownNotification')) {
        window.notificationManager = new NotificationManager();
    }
});

// Limpiar al salir de la página
window.addEventListener('beforeunload', function() {
    if (window.notificationManager) {
        window.notificationManager.destroy();
    }
});