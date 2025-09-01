// Sistema de notificaciones en tiempo real
class NotificationManager {
    constructor() {
        this.notifications = [];
        this.maxNotifications = 5;
        this.container = null;
        this.isInitialized = false;
        this.hasShownTestNotification = false;
        this.lastCheckTime = null;
        this.init();
    }

    init() {
        // Esperar a que el DOM esté completamente cargado
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.initializeComponents());
        } else {
            this.initializeComponents();
        }
    }

    initializeComponents() {
        console.log('Initializing NotificationManager...');
        
        this.createContainer();
        this.setupEventListeners();
        this.loadUnreadCount();
        this.isInitialized = true;
        
        console.log('NotificationManager initialized successfully');
        
        // Test notification para verificar que funciona
        setTimeout(() => {
            this.testNotification();
        }, 2000);
    }

    createContainer() {
        // Crear contenedor de notificaciones
        const container = document.createElement('div');
        container.id = 'notification-container';
        container.className = 'fixed top-4 right-4 z-50 space-y-2';
        document.body.appendChild(container);
        this.container = container;
    }

    setupEventListeners() {
        // Configurar Laravel Echo para broadcasting en tiempo real
        this.setupEchoListeners();
        
        // Configurar eventos del DOM para compatibilidad
        document.addEventListener('permission:submitted', (e) => {
            this.handlePermissionSubmitted(e.detail);
        });
        
        document.addEventListener('permission:approved', (e) => {
            this.handlePermissionApproved(e.detail);
        });
        
        document.addEventListener('permission:rejected', (e) => {
            this.handlePermissionRejected(e.detail);
        });
        
        document.addEventListener('permission:status:changed', (e) => {
            this.handleStatusChanged(e.detail);
        });
    }

    setupEchoListeners() {
        // Escuchar canal general de aprobaciones
        if (window.Echo) {
            // Canal general de aprobaciones
            window.Echo.channel('approvals')
                .listen('permission.submitted', (data) => {
                    this.handlePermissionSubmitted(data);
                    this.playNotificationSound('info');
                })
                .listen('permission.approved', (data) => {
                    this.handlePermissionApproved(data);
                    this.playNotificationSound('success');
                })
                .listen('permission.rejected', (data) => {
                    this.handlePermissionRejected(data);
                    this.playNotificationSound('error');
                });

            // Canal específico del usuario actual (para notificaciones personales)
            const userId = document.querySelector('meta[name="user-id"]')?.content;
            if (userId) {
                // Canal para recibir notificaciones como aprobador
                window.Echo.channel(`approvals.user.${userId}`)
                    .listen('permission.submitted', (data) => {
                        this.handlePermissionSubmitted(data);
                        this.playNotificationSound('approval');
                    });

                // Canal para recibir notificaciones sobre sus propias solicitudes
                window.Echo.channel(`permissions.user.${userId}`)
                    .listen('permission.approved', (data) => {
                        this.handlePermissionApproved(data);
                        this.playNotificationSound('success');
                    })
                    .listen('permission.rejected', (data) => {
                        this.handlePermissionRejected(data);
                        this.playNotificationSound('error');
                    });
            }
        }
    }

    setupDevelopmentListeners() {
        // Sistema de polling para desarrollo y fallback
        this.startPolling();
    }

    startPolling() {
        // Iniciar polling inmediatamente
        this.checkForUpdates();
        
        // Configurar intervalos
        this.notificationPollInterval = setInterval(() => {
            this.checkForUpdates();
        }, 15000); // Cada 15 segundos para notificaciones
        
        this.approvalPollInterval = setInterval(() => {
            this.updateSidebarNotifications();
        }, 30000); // Cada 30 segundos para aprobaciones
        
        console.log('Polling system started');
    }

    stopPolling() {
        if (this.notificationPollInterval) {
            clearInterval(this.notificationPollInterval);
        }
        if (this.approvalPollInterval) {
            clearInterval(this.approvalPollInterval);
        }
    }

    async checkForUpdates() {
        try {
            console.log('🔍 Checking for notification updates...');
            
            const response = await fetch('/api/notifications/check', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                console.log('📥 API Response:', data);
                
                // Siempre actualizar el contador, incluso si no hay nuevas
                this.updateUnreadCount(data.unread_count);
                
                if (data.new_notifications && data.notifications.length > 0) {
                    console.log(`🔥 Found ${data.notifications.length} new notifications`);
                    
                    data.notifications.forEach(notification => {
                        this.processNotification(notification);
                    });
                } else {
                    console.log('ℹ️ No new notifications since last check');
                    
                    // Para test: si hay notificaciones no leídas pero no "nuevas", mostrar una de prueba
                    if (data.unread_count > 0 && !this.hasShownTestNotification) {
                        console.log('🧪 Showing test notification for existing unread notifications');
                        this.show({
                            type: 'info',
                            title: 'Sistema de Notificaciones',
                            message: `Tienes ${data.unread_count} notificaciones no leídas`,
                            action: {
                                text: 'Ver',
                                url: '/approvals'
                            }
                        });
                        this.playNotificationSound('info');
                        this.hasShownTestNotification = true;
                    }
                }
                
                this.lastCheckTime = data.timestamp;
            } else {
                console.error('❌ Failed to check notifications:', response.status);
            }
        } catch (error) {
            console.error('💥 Error checking for updates:', error);
        }
    }

    processNotification(notification) {
        // Determinar el tipo de notificación para el frontend
        let type = 'info';
        let soundType = 'info';

        if (notification.category === 'permission') {
            if (notification.type === 'permission_submitted') {
                type = 'info';
                soundType = 'approval';
            } else if (notification.type === 'permission_approved') {
                type = 'success';
                soundType = 'success';
            } else if (notification.type === 'permission_rejected') {
                type = 'error';
                soundType = 'error';
            }
        }

        // Mostrar notificación toast
        this.show({
            type: type,
            title: notification.title || 'Nueva Notificación',
            message: notification.message,
            action: notification.data?.action_url ? {
                text: 'Ver',
                url: notification.data.action_url
            } : null
        });

        // Reproducir sonido
        this.playNotificationSound(soundType);

        // Actualizar listas en tiempo real
        this.updateDashboardSections();
        
        // También llamar la función global para actualizar listas
        if (typeof updateRequestLists === 'function') {
            updateRequestLists();
        }
    }

    handlePermissionSubmitted(data) {
        this.show({
            type: 'info',
            title: 'Nueva Solicitud',
            message: data.message,
            permission: data.permission,
            action: {
                text: 'Revisar',
                url: `/approvals/${data.permission.id}`
            }
        });
        
        this.updateApprovalsList();
        this.updateStats();
        
        // Actualizar listas globalmente
        if (typeof updateRequestLists === 'function') {
            updateRequestLists();
        }
    }

    handlePermissionApproved(data) {
        const isForCurrentUser = this.isCurrentUserInvolved(data);
        
        if (isForCurrentUser) {
            this.show({
                type: 'success',
                title: data.is_final_approval ? 'Permiso Aprobado' : 'Aprobación Parcial',
                message: data.message,
                permission: data.permission,
                action: data.is_final_approval ? null : {
                    text: 'Ver Estado',
                    url: '/permissions'
                }
            });
        }
        
        this.updateApprovalsList();
        this.updatePermissionsList();
        this.updateStats();
        
        // Actualizar listas globalmente
        if (typeof updateRequestLists === 'function') {
            updateRequestLists();
        }
    }

    handlePermissionRejected(data) {
        const isForCurrentUser = this.isCurrentUserInvolved(data);
        
        if (isForCurrentUser) {
            this.show({
                type: 'error',
                title: 'Permiso Rechazado',
                message: data.message,
                permission: data.permission,
                action: {
                    text: 'Ver Detalles',
                    url: '/permissions'
                }
            });
        }
        
        this.updateApprovalsList();
        this.updatePermissionsList();
        this.updateStats();
        
        // Actualizar listas globalmente
        if (typeof updateRequestLists === 'function') {
            updateRequestLists();
        }
    }

    handleStatusChanged(data) {
        // Actualizar elementos de la UI que muestren el estado
        const permissionElements = document.querySelectorAll(`[data-permission-id="${data.permission.id}"]`);
        
        permissionElements.forEach(element => {
            const statusElement = element.querySelector('.status-badge');
            if (statusElement) {
                statusElement.textContent = data.status_label;
                statusElement.className = `status-badge ${data.status_color}`;
            }
        });
    }

    show(notification) {
        // Limitar número de notificaciones
        if (this.notifications.length >= this.maxNotifications) {
            const oldest = this.notifications.shift();
            if (oldest && oldest.element) {
                this.removeNotification(oldest.element);
            }
        }

        const notificationElement = this.createNotificationElement(notification);
        this.container.appendChild(notificationElement);
        
        this.notifications.push({
            ...notification,
            element: notificationElement,
            timestamp: Date.now()
        });

        // Auto-remover después de 8 segundos
        setTimeout(() => {
            this.removeNotification(notificationElement);
        }, 8000);
    }

    createNotificationElement(notification) {
        const div = document.createElement('div');
        div.className = `notification-toast ${this.getTypeClass(notification.type)} transform transition-all duration-300 translate-x-full`;
        
        div.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    ${this.getTypeIcon(notification.type)}
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-gray-900">
                        ${notification.title}
                    </p>
                    <p class="mt-1 text-sm text-gray-600">
                        ${notification.message}
                    </p>
                    ${notification.action ? `
                        <div class="mt-3">
                            <a href="${notification.action.url}" 
                               class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                ${notification.action.text}
                            </a>
                        </div>
                    ` : ''}
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button onclick="notificationManager.removeNotification(this.closest('.notification-toast'))"
                            class="inline-flex text-gray-400 hover:text-gray-600">
                        <span class="sr-only">Cerrar</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        `;

        // Animar entrada
        setTimeout(() => {
            div.classList.remove('translate-x-full');
        }, 100);

        return div;
    }

    getTypeClass(type) {
        const classes = {
            success: 'bg-green-50 border border-green-200',
            error: 'bg-red-50 border border-red-200',
            warning: 'bg-yellow-50 border border-yellow-200',
            info: 'bg-blue-50 border border-blue-200'
        };
        return `max-w-sm mx-auto bg-white rounded-lg shadow-lg pointer-events-auto ${classes[type] || classes.info}`;
    }

    getTypeIcon(type) {
        const icons = {
            success: `<svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>`,
            error: `<svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>`,
            warning: `<svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>`,
            info: `<svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>`
        };
        return icons[type] || icons.info;
    }

    removeNotification(element) {
        element.classList.add('translate-x-full');
        setTimeout(() => {
            if (element.parentNode) {
                element.parentNode.removeChild(element);
            }
        }, 300);
        
        // Remover de array
        this.notifications = this.notifications.filter(n => n.element !== element);
    }

    // Métodos para actualizar la UI
    updateApprovalsList() {
        // Actualizar página de aprobaciones si estamos en ella
        if (window.location.pathname.includes('/approvals')) {
            this.refreshCurrentPage();
        }
        
        // Actualizar badge del sidebar
        this.updateSidebarNotifications();
    }

    updatePermissionsList() {
        // Actualizar página de permisos si estamos en ella
        if (window.location.pathname.includes('/permissions')) {
            this.refreshCurrentPage();
        }
        
        // Actualizar secciones del dashboard
        this.updateDashboardSections();
    }

    updateStats() {
        // Actualizar estadísticas del dashboard
        this.updateDashboardSections();
        this.updateSidebarNotifications();
    }

    async updateDashboardSections() {
        // Solo actualizar si estamos en el dashboard
        if (window.location.pathname === '/dashboard' || window.location.pathname === '/') {
            await this.refreshDashboardStats();
            await this.refreshMyRequestsSection();
            await this.refreshTeamRequestsSection();
        }
    }

    async refreshCurrentPage() {
        // Recargar la página actual para mostrar cambios
        window.location.reload();
    }

    async refreshDashboardStats() {
        try {
            const response = await fetch('/dashboard/stats', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                this.updateStatsCards(data);
            }
        } catch (error) {
            console.error('Error refreshing dashboard stats:', error);
        }
    }

    async refreshMyRequestsSection() {
        try {
            const response = await fetch('/dashboard/my-requests', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                }
            });
            
            if (response.ok) {
                const html = await response.text();
                const myRequestsSection = document.getElementById('my-requests-section');
                if (myRequestsSection) {
                    myRequestsSection.innerHTML = html;
                }
            }
        } catch (error) {
            console.error('Error refreshing my requests section:', error);
        }
    }

    async refreshTeamRequestsSection() {
        try {
            const response = await fetch('/dashboard/team-requests', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                }
            });
            
            if (response.ok) {
                const html = await response.text();
                const teamRequestsSection = document.getElementById('team-requests-section');
                if (teamRequestsSection) {
                    teamRequestsSection.innerHTML = html;
                }
            }
        } catch (error) {
            console.error('Error refreshing team requests section:', error);
        }
    }

    updateStatsCards(data) {
        // Actualizar las tarjetas de estadísticas
        const totalCard = document.querySelector('[data-stat="total"]');
        const pendingCard = document.querySelector('[data-stat="pending"]');
        const approvedCard = document.querySelector('[data-stat="approved"]');
        const rejectedCard = document.querySelector('[data-stat="rejected"]');

        if (totalCard) totalCard.textContent = data.total || 0;
        if (pendingCard) pendingCard.textContent = data.pending || 0;
        if (approvedCard) approvedCard.textContent = data.approved || 0;
        if (rejectedCard) rejectedCard.textContent = data.rejected || 0;

        // Actualizar porcentajes si están disponibles
        if (data.total > 0) {
            const approvedPercentage = Math.round((data.approved / data.total) * 100);
            const rejectedPercentage = Math.round((data.rejected / data.total) * 100);
            
            const approvedPercentageEl = document.querySelector('[data-stat="approved-percentage"]');
            const rejectedPercentageEl = document.querySelector('[data-stat="rejected-percentage"]');
            
            if (approvedPercentageEl) approvedPercentageEl.textContent = `${approvedPercentage}% del total`;
            if (rejectedPercentageEl) rejectedPercentageEl.textContent = `${rejectedPercentage}% del total`;
        }
    }

    async updateSidebarNotifications() {
        try {
            const response = await fetch('/api/notifications/pending-approvals', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                const sidebarBadge = document.querySelector('aside a[href*="approvals"] .notification-dot');
                
                if (sidebarBadge) {
                    if (data.count > 0) {
                        sidebarBadge.textContent = data.count;
                        sidebarBadge.style.display = 'inline';
                    } else {
                        sidebarBadge.style.display = 'none';
                    }
                }

                console.log(`Updated sidebar notifications count: ${data.count}`);
            }
        } catch (error) {
            console.error('Error updating sidebar notifications:', error);
        }
    }

    updateUnreadCount(count) {
        console.log(`🔢 Updating unread count to: ${count}`);
        
        const badges = document.querySelectorAll('.notification-badge');
        console.log(`📛 Found ${badges.length} notification badges`);
        
        badges.forEach((badge, index) => {
            console.log(`🏷️ Updating badge ${index}:`, badge);
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline' : 'none';
        });
        
        // También actualizar el badge del sidebar
        const sidebarBadge = document.querySelector('aside a[href*="approvals"] .notification-dot');
        if (sidebarBadge) {
            console.log('🔖 Updating sidebar badge');
            if (count > 0) {
                sidebarBadge.textContent = count;
                sidebarBadge.style.display = 'inline';
            } else {
                sidebarBadge.style.display = 'none';
            }
        }
    }

    loadUnreadCount() {
        fetch('/api/notifications/count', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            this.updateUnreadCount(data.count);
            console.log(`Loaded unread count: ${data.count}`);
        })
        .catch(error => console.error('Error loading unread count:', error));
    }

    isCurrentUserInvolved(data) {
        // Verificar si el usuario actual está involucrado en la notificación
        const currentUserId = parseInt(document.querySelector('meta[name="user-id"]')?.content);
        return data.permission.user.id === currentUserId || 
               data.approver_id === currentUserId ||
               data.next_approver_id === currentUserId;
    }

    // Sistema de sonidos para notificaciones
    playNotificationSound(type = 'info') {
        // Verificar si el usuario ha habilitado los sonidos
        const soundEnabled = localStorage.getItem('notifications_sound_enabled') !== 'false';
        if (!soundEnabled) return;

        // Crear contexto de audio si no existe
        if (!this.audioContext) {
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
        }

        // Reproducir sonido según el tipo
        switch (type) {
            case 'approval':
                this.playTone([800, 1000, 1200], 0.3, 200); // Sonido especial para aprobadores
                break;
            case 'success':
                this.playTone([600, 800], 0.2, 150);
                break;
            case 'error':
                this.playTone([400, 300], 0.3, 300);
                break;
            case 'warning':
                this.playTone([500], 0.2, 200);
                break;
            default:
                this.playTone([650], 0.15, 150);
        }
    }

    playTone(frequencies, volume = 0.2, duration = 200) {
        if (!this.audioContext) return;

        frequencies.forEach((freq, index) => {
            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);
            
            oscillator.frequency.value = freq;
            oscillator.type = 'sine';
            
            gainNode.gain.setValueAtTime(0, this.audioContext.currentTime + (index * 0.1));
            gainNode.gain.linearRampToValueAtTime(volume, this.audioContext.currentTime + (index * 0.1) + 0.05);
            gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + (index * 0.1) + (duration / 1000));
            
            oscillator.start(this.audioContext.currentTime + (index * 0.1));
            oscillator.stop(this.audioContext.currentTime + (index * 0.1) + (duration / 1000));
        });
    }

    // Métodos para configurar sonidos
    toggleNotificationSounds() {
        const currentState = localStorage.getItem('notifications_sound_enabled') !== 'false';
        localStorage.setItem('notifications_sound_enabled', !currentState);
        return !currentState;
    }

    isSoundEnabled() {
        return localStorage.getItem('notifications_sound_enabled') !== 'false';
    }

    // Método de test para verificar funcionamiento
    testNotification() {
        console.log('Testing notification system...');
        
        // Test del sistema de polling
        this.checkForUpdates();
        
        this.show({
            type: 'info',
            title: 'Sistema de Notificaciones',
            message: 'El sistema de polling está funcionando correctamente',
            action: null
        });

        // Test de sonido
        if (this.isSoundEnabled()) {
            this.playNotificationSound('info');
        }

        // Test de endpoints API
        this.updateSidebarNotifications();
        this.loadUnreadCount();
        
        console.log('Notification system test completed');
    }
}

// Inicializar el gestor de notificaciones cuando el DOM esté listo
if (typeof window !== 'undefined') {
    window.notificationManager = new NotificationManager();
    
    // Hacer disponible globalmente para debugging
    window.testNotifications = function() {
        if (window.notificationManager) {
            window.notificationManager.testNotification();
        } else {
            console.error('NotificationManager not available');
        }
    };
    
    console.log('NotificationManager loaded and available globally');
}

export default NotificationManager;