<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ Auth::id() }}">
    <title>{{ config('app.name', 'Sistema de Papeletas Digitales') }} - Municipalidad Distrital de San Jerónimo</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        :root {
            --primary-color: #1e40af;
            --primary-dark: #1e3a8a;
            --secondary-color: #059669;
            --accent-color: #f59e0b;
            --sidebar-bg: #1f2937;
            --sidebar-hover: #374151;
        }
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .sidebar-transition {
            transition: all 0.3s ease;
        }
        
        .sidebar-item {
            transition: all 0.2s ease;
        }
        
        .sidebar-item:hover {
            background-color: var(--sidebar-hover);
            transform: translateX(4px);
        }
        
        .main-content {
            transition: margin-left 0.3s ease;
        }
        
        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        
        .notification-dot {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .card-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .card-shadow:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        /* Alpine.js cloak para evitar parpadeo */
        [x-cloak] {
            display: none !important;
        }
        
        /* Estilos para dropdown de notificaciones */
        #notifications-dropdown {
            opacity: 0;
            transform: scale(0.95);
            transition: opacity 0.2s ease, transform 0.2s ease;
        }
        
        #notifications-dropdown.show {
            opacity: 1;
            transform: scale(1);
        }
        
        /* Estilos para items de notificaciones */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        @media (min-width: 1024px) {
            #sidebar {
                transform: translateX(0) !important;
            }
        }

        @media (max-width: 1023px) {
            #sidebar.sidebar-closed {
                transform: translateX(-100%) !important;
            }
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-800 sidebar-transition lg:relative lg:translate-x-0 transform -translate-x-full lg:transform-none">
            <!-- Logo/Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-city text-white"></i>
                    </div>
                    <div class="text-white">
                        <h2 class="text-sm font-semibold">MDSJ</h2>
                        <p class="text-xs text-gray-300">Papeletas Digitales</p>
                    </div>
                </div>
                <button id="closeSidebar" class="lg:hidden text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- User Info -->
            <div class="p-4 border-b border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-medium">
                            {{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name, 0, 1) }}
                        </span>
                    </div>
                    <div class="text-white">
                        <p class="text-sm font-medium">{{ auth()->user()->full_name }}</p>
                        <p class="text-xs text-gray-300">{{ auth()->user()->role->description ?? 'Usuario' }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="mt-4 px-2">
                <div class="space-y-1">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" class="sidebar-item flex items-center px-3 py-2 text-sm font-medium text-gray-300 rounded-md hover:text-white group {{ request()->routeIs('dashboard') ? 'bg-gray-700 text-white' : '' }}">
                        <i class="fas fa-tachometer-alt mr-3 text-gray-400 group-hover:text-white"></i>
                        Dashboard
                    </a>
                    
                    <!-- Mis Solicitudes -->
                    <a href="{{ route('permissions.index') }}" class="sidebar-item flex items-center px-3 py-2 text-sm font-medium text-gray-300 rounded-md hover:text-white group {{ request()->routeIs('permissions.*') ? 'bg-gray-700 text-white' : '' }}">
                        <i class="fas fa-file-alt mr-3 text-gray-400 group-hover:text-white"></i>
                        Mis Solicitudes
                    </a>
                    
                    <!-- Nueva Solicitud -->
                    <a href="{{ route('permissions.create') }}" class="sidebar-item flex items-center px-3 py-2 text-sm font-medium text-gray-300 rounded-md hover:text-white group">
                        <i class="fas fa-plus-circle mr-3 text-gray-400 group-hover:text-white"></i>
                        Nueva Solicitud
                    </a>

                    <!-- Seguimiento de Permisos -->
                    <a href="{{ route('tracking.index') }}" class="sidebar-item flex items-center px-3 py-2 text-sm font-medium text-gray-300 rounded-md hover:text-white group {{ request()->routeIs('tracking.*') && !request()->routeIs('tracking.hr-dashboard') ? 'bg-gray-700 text-white' : '' }}">
                        <i class="fas fa-route mr-3 text-gray-400 group-hover:text-white"></i>
                        Seguimiento
                    </a>
                    
                    <!-- Separador -->
                    <div class="border-t border-gray-700 my-3"></div>
                    
                    @if(auth()->user()->canApprove(new \App\Models\PermissionRequest()))
                        <!-- Aprobaciones -->
                        <a href="{{ route('approvals.index') }}" class="sidebar-item flex items-center px-3 py-2 text-sm font-medium text-gray-300 rounded-md hover:text-white group {{ request()->routeIs('approvals.*') ? 'bg-gray-700 text-white' : '' }}">
                            <i class="fas fa-check-circle mr-3 text-gray-400 group-hover:text-white"></i>
                            Aprobaciones
                            @if(isset($pendingApprovals) && $pendingApprovals > 0)
                                <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-1 notification-dot">{{ $pendingApprovals }}</span>
                            @endif
                        </a>
                    @endif
                    
                    @if(auth()->user()->hasRole('jefe_rrhh') || auth()->user()->hasRole('admin'))
                        <!-- Dashboard RRHH -->
                        @if(auth()->user()->hasRole('jefe_rrhh'))
                            <a href="{{ route('tracking.hr-dashboard') }}" class="sidebar-item flex items-center px-3 py-2 text-sm font-medium text-gray-300 rounded-md hover:text-white group {{ request()->routeIs('tracking.hr-dashboard') ? 'bg-gray-700 text-white' : '' }}">
                                <i class="fas fa-tachometer-alt mr-3 text-gray-400 group-hover:text-white"></i>
                                Dashboard RRHH
                            </a>
                        @endif

                        <!-- Reportes RRHH -->
                        <a href="{{ route('hr.reports.dashboard') }}" class="sidebar-item flex items-center px-3 py-2 text-sm font-medium text-gray-300 rounded-md hover:text-white group {{ request()->routeIs('hr.reports.*') || request()->routeIs('reports.*') ? 'bg-gray-700 text-white' : '' }}">
                            <i class="fas fa-chart-bar mr-3 text-gray-400 group-hover:text-white"></i>
                            Reportes RRHH
                        </a>
                        
                        <!-- Firmas Digitales -->
                        <div class="mt-2">
                            <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Firma Digital</p>
                            <a href="{{ route('reports.digital-signatures') }}" class="sidebar-item flex items-center px-3 py-2 text-sm font-medium text-gray-300 rounded-md hover:text-white group">
                                <i class="fas fa-signature mr-3 text-gray-400 group-hover:text-white"></i>
                                Reporte de Firmas
                            </a>
                            <a href="{{ route('reports.signature-integrity') }}" class="sidebar-item flex items-center px-3 py-2 text-sm font-medium text-gray-300 rounded-md hover:text-white group">
                                <i class="fas fa-shield-alt mr-3 text-gray-400 group-hover:text-white"></i>
                                Integridad
                            </a>
                        </div>
                    @endif
                    
                    @if(auth()->user()->hasRole('admin'))
                        <!-- Administración -->
                        <div class="mt-4">
                            <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Administración</p>
                            <a href="{{ route('admin.signatures.index') }}" class="sidebar-item flex items-center px-3 py-2 text-sm font-medium text-gray-300 rounded-md hover:text-white group">
                                <i class="fas fa-cogs mr-3 text-gray-400 group-hover:text-white"></i>
                                Gestión de Firmas
                            </a>
                            <a href="{{ route('admin.signatures.statistics') }}" class="sidebar-item flex items-center px-3 py-2 text-sm font-medium text-gray-300 rounded-md hover:text-white group">
                                <i class="fas fa-analytics mr-3 text-gray-400 group-hover:text-white"></i>
                                Estadísticas
                            </a>
                        </div>
                    @endif
                </div>
            </nav>
            
            <!-- Footer del Sidebar -->
            <div class="absolute bottom-0 w-full p-4 border-t border-gray-700">
                <div class="flex items-center justify-between">
                    <a href="{{ route('profile.edit') }}" class="text-gray-400 hover:text-white text-sm">
                        <i class="fas fa-user-cog mr-2"></i>
                        Perfil
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-red-400 text-sm">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Salir
                        </button>
                    </form>
                </div>
            </div>
        </aside>
        
        <!-- Overlay para móvil -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black opacity-50 z-40 lg:hidden hidden"></div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col main-content">
            <!-- Top Header -->
            <header class="glass-effect border-b border-gray-200 sticky top-0 z-30">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <!-- Mobile menu button -->
                        <button id="openSidebar" class="lg:hidden text-gray-500 hover:text-gray-700">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        
                        <!-- Page Title -->
                        <div class="flex-1 lg:flex-none">
                            @isset($header)
                                <div class="ml-4 lg:ml-0">
                                    {{ $header }}
                                </div>
                            @else
                                <h1 class="text-xl font-semibold text-gray-900 ml-4 lg:ml-0">
                                    Dashboard
                                </h1>
                            @endif
                        </div>
                        
                        <!-- Right side items -->
                        <div class="flex items-center space-x-4">
                            <!-- Notifications Dropdown -->
                            <div class="relative">
                                <button id="notifications-toggle" class="text-gray-500 hover:text-gray-700 relative p-2 rounded-lg hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-bell text-lg"></i>
                                    <span id="notification-count" class="notification-badge absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center notification-dot" style="display: none;">
                                        0
                                    </span>
                                </button>
                                
                                <!-- Dropdown Menu -->
                                <div id="notifications-dropdown" 
                                     class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50 transition-all duration-200"
                                     style="display: none;">
                                    
                                    <!-- Header del dropdown -->
                                    <div class="px-4 py-3 border-b border-gray-200">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-sm font-semibold text-gray-900">Notificaciones</h3>
                                            <div class="flex items-center space-x-2">
                                                <!-- Toggle de sonido -->
                                                <button id="sound-toggle" 
                                                        onclick="toggleNotificationSounds()"
                                                        class="text-gray-400 hover:text-gray-600 p-1 rounded"
                                                        title="Activar/Desactivar sonidos">
                                                    <i id="sound-icon" class="fas fa-volume-up text-sm"></i>
                                                </button>
                                                <!-- Marcar todas como leídas -->
                                                <button onclick="markAllNotificationsAsRead()" 
                                                        class="text-blue-600 hover:text-blue-800 text-xs font-medium hover:underline">
                                                    Marcar todas como leídas
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Lista de notificaciones -->
                                    <div id="notifications-list" class="max-h-96 overflow-y-auto">
                                        <div id="notifications-loading" class="px-4 py-8 text-center text-gray-500">
                                            <i class="fas fa-spinner fa-spin text-2xl mb-2 text-gray-300"></i>
                                            <p class="text-sm">Cargando notificaciones...</p>
                                        </div>
                                        <div id="notifications-empty" class="px-4 py-8 text-center text-gray-500" style="display: none;">
                                            <i class="fas fa-bell-slash text-2xl mb-2 text-gray-300"></i>
                                            <p class="text-sm">No tienes notificaciones</p>
                                        </div>
                                        <div id="notifications-container">
                                            <!-- Las notificaciones se cargarán aquí -->
                                        </div>
                                    </div>
                                    
                                    <!-- Footer del dropdown -->
                                    @if(auth()->user()->canApprove(new \App\Models\PermissionRequest()))
                                        <div class="px-4 py-3 border-t border-gray-200">
                                            <a href="{{ route('approvals.index') }}" 
                                               class="block text-center text-sm text-blue-600 hover:text-blue-800 font-medium">
                                                Ver todas las aprobaciones
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- User dropdown -->
                            <div class="relative">
                                <div class="flex items-center space-x-2 text-sm">
                                    <span class="hidden sm:block text-gray-700">{{ auth()->user()->first_name }}</span>
                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                        <span class="text-white text-xs font-medium">
                                            {{ substr(auth()->user()->first_name, 0, 1) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto">
                <!-- Success/Error Messages -->
                @if (session('success'))
                    <div class="mx-4 sm:mx-6 lg:mx-8 mt-4">
                        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-green-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                
                @if (session('error'))
                    <div class="mx-4 sm:mx-6 lg:mx-8 mt-4">
                        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                
                @if (session('warning'))
                    <div class="mx-4 sm:mx-6 lg:mx-8 mt-4">
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">{{ session('warning') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Content -->
                <div class="py-6">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
    
    <!-- JavaScript para el sidebar responsive -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const openBtn = document.getElementById('openSidebar');
            const closeBtn = document.getElementById('closeSidebar');
            
            function openSidebar() {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                sidebar.classList.remove('sidebar-closed');
                overlay.classList.remove('hidden');
            }

            function closeSidebar() {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('sidebar-closed');
                overlay.classList.add('hidden');
            }
            
            openBtn?.addEventListener('click', openSidebar);
            closeBtn?.addEventListener('click', closeSidebar);
            overlay?.addEventListener('click', closeSidebar);
            
            // Cerrar sidebar al hacer clic en un enlace (móvil)
            const sidebarLinks = sidebar.querySelectorAll('a');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth < 1024) {
                        closeSidebar();
                    }
                });
            });

            // Inicializar estado del icono de sonido
            updateSoundIcon();

            // Manejar dropdown de notificaciones
            setupNotificationsDropdown();
            
            // Debug: probar sistema de notificaciones después de cargar
            setTimeout(() => {
                console.log('🚀 Starting notification system test...');
                console.log('Available on window:', Object.keys(window).filter(k => k.includes('notification') || k.includes('Notification')));
                console.log('Alpine available:', typeof window.Alpine);
                console.log('Echo available:', typeof window.Echo);
                
                if (window.notificationManager) {
                    console.log('✅ NotificationManager found, testing...');
                    window.notificationManager.checkForUpdates();
                } else {
                    console.log('❌ NotificationManager not found');
                    console.log('Trying to create NotificationManager manually...');
                    
                    // Intentar cargar manualmente
                    try {
                        if (typeof NotificationManager !== 'undefined') {
                            window.notificationManager = new NotificationManager();
                            console.log('✅ Created NotificationManager manually');
                        } else {
                            console.log('❌ NotificationManager class not available');
                            console.log('🔄 Starting simple polling system as fallback...');
                            startSimplePolling();
                        }
                    } catch (error) {
                        console.error('❌ Error creating NotificationManager:', error);
                        console.log('🔄 Starting simple polling system as fallback...');
                        startSimplePolling();
                    }
                }
            }, 3000);
        });

        // Configurar dropdown de notificaciones
        function setupNotificationsDropdown() {
            const toggle = document.getElementById('notifications-toggle');
            const dropdown = document.getElementById('notifications-dropdown');
            let isOpen = false;

            if (toggle && dropdown) {
                // Toggle al hacer clic en el botón
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    isOpen = !isOpen;
                    
                    if (isOpen) {
                        dropdown.style.display = 'block';
                        setTimeout(() => {
                            dropdown.style.opacity = '1';
                            dropdown.style.transform = 'scale(1)';
                        }, 10);
                        
                        // Cargar notificaciones cuando se abre el dropdown
                        loadNotificationsList();
                    } else {
                        dropdown.style.opacity = '0';
                        dropdown.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            dropdown.style.display = 'none';
                        }, 200);
                    }
                });

                // Cerrar al hacer clic fuera
                document.addEventListener('click', function(e) {
                    if (!dropdown.contains(e.target) && !toggle.contains(e.target) && isOpen) {
                        isOpen = false;
                        dropdown.style.opacity = '0';
                        dropdown.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            dropdown.style.display = 'none';
                        }, 200);
                    }
                });

                console.log('Notifications dropdown initialized');
            } else {
                console.error('Notifications dropdown elements not found');
            }
        }

        // Función global para toggle de sonidos
        function toggleNotificationSounds() {
            if (window.notificationManager) {
                const newState = window.notificationManager.toggleNotificationSounds();
                updateSoundIcon();
                
                // Mostrar feedback al usuario
                const message = newState ? 'Sonidos de notificación activados' : 'Sonidos de notificación desactivados';
                showToast(message, newState ? 'success' : 'info');
            }
        }

        // Actualizar icono de sonido
        function updateSoundIcon() {
            const soundIcon = document.getElementById('sound-icon');
            const soundToggle = document.getElementById('sound-toggle');
            
            if (soundIcon && soundToggle) {
                const isEnabled = localStorage.getItem('notifications_sound_enabled') !== 'false';
                soundIcon.className = isEnabled ? 'fas fa-volume-up text-sm' : 'fas fa-volume-mute text-sm';
                soundToggle.title = isEnabled ? 'Desactivar sonidos' : 'Activar sonidos';
            }
        }

        // Función para mostrar toast messages
        function showToast(message, type = 'info') {
            if (window.notificationManager) {
                window.notificationManager.show({
                    type: type,
                    title: 'Configuración',
                    message: message
                });
            }
        }

        // Función para actualizar la lista de notificaciones en tiempo real
        function updateNotificationsList() {
            // Esta función se llamará desde el NotificationManager
            // para actualizar la lista del dropdown cuando lleguen nuevas notificaciones
            const notificationsList = document.getElementById('notifications-list');
            
            if (notificationsList && window.notificationManager) {
                // Aquí se implementaría la lógica para mostrar las notificaciones
                // en el dropdown del header
            }
        }

        // Test directo para verificar carga
        window.testDirectNotifications = function() {
            console.log('Direct test function called');
            console.log('NotificationManager available:', typeof window.notificationManager);
            alert('JavaScript cargado. Ver consola para detalles.');
            
            // Test endpoints
            Promise.all([
                fetch('/api/notifications/test', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                }).then(r => r.json()),
                
                fetch('/api/notifications/count', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                }).then(r => r.json())
            ])
            .then(results => {
                console.log('Test endpoint:', results[0]);
                console.log('Count endpoint:', results[1]);
                alert('APIs funcionando. Total notificaciones: ' + results[0].notifications_count);
                
                // Test notification manager
                if (window.notificationManager) {
                    window.notificationManager.testNotification();
                } else {
                    console.error('NotificationManager not available');
                }
            })
            .catch(error => {
                console.error('API Error:', error);
                alert('Error: ' + error.message);
            });
        };
        
        // Función manual para forzar test de notificaciones
        window.forceNotificationTest = function() {
            console.log('🔧 Forcing notification test...');
            
            if (window.notificationManager) {
                window.notificationManager.testNotification();
            } else {
                console.log('Creating simple notification system...');
                createSimpleNotification();
            }
        };
        // Manejar cambios de tamaño de ventana
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                // En desktop, asegurar que el sidebar esté visible
                sidebar.classList.remove('-translate-x-full', 'sidebar-closed');
                sidebar.classList.add('translate-x-0');
                overlay.classList.add('hidden');
            } else {
                // En móvil, asegurar que esté cerrado por defecto
                if (!overlay.classList.contains('hidden')) {
                    // Solo si está abierto, cerrarlo
                    closeSidebar();
                }
            }
        });
        
        // Sistema simple de notificaciones como fallback
        function createSimpleNotification() {
            console.log('📱 Creating simple notification...');
            
            // Crear notificación toast simple
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 z-50 bg-blue-500 text-white p-4 rounded-lg shadow-lg max-w-sm';
            notification.innerHTML = `
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">Sistema de Notificaciones</p>
                        <p class="text-sm text-blue-100 mt-1">Tienes notificaciones pendientes</p>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-blue-200 hover:text-white">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remover después de 5 segundos
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
            
            // Test de API
            testSimpleAPI();
        }
        
        // Test simple de API
        function testSimpleAPI() {
            console.log('🔌 Testing API...');
            
            fetch('/api/notifications/count', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('✅ API working:', data);
                
                // Actualizar badge si existe
                const badge = document.getElementById('notification-count');
                if (badge) {
                    badge.textContent = data.count;
                    badge.style.display = data.count > 0 ? 'flex' : 'none';
                    console.log('✅ Updated badge with count:', data.count);
                }
                
                // También actualizar badge del sidebar
                updateNotificationBadge(data.count);
            })
            .catch(error => {
                console.error('❌ API Error:', error);
            });
        }
        
        // Sistema de polling simple como fallback
        let simplePollingInterval;
        function startSimplePolling() {
            console.log('⏰ Starting simple polling every 30 seconds...');
            
            // Ejecutar inmediatamente
            testSimpleAPI();
            
            // Configurar intervalo
            simplePollingInterval = setInterval(() => {
                console.log('🔄 Polling for notifications...');
                testSimpleAPI();
                checkForNewNotifications();
            }, 30000); // Cada 30 segundos
        }
        
        // Verificar notificaciones nuevas
        let lastNotificationCheck = Date.now() - (5 * 60 * 1000); // 5 minutos atrás
        function checkForNewNotifications() {
            fetch('/api/notifications/check?last_check=' + Math.floor(lastNotificationCheck / 1000), {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('📬 Check result:', data);
                
                if (data.new_notifications && data.notifications.length > 0) {
                    console.log('🔥 New notifications found!');
                    
                    // Actualizar listas de solicitudes cuando hay nuevas notificaciones
                    updateRequestLists();
                    
                    data.notifications.forEach(notification => {
                        createNotificationToast(notification);
                    });
                    
                    // Actualizar lista del dropdown si está abierto
                    const dropdown = document.getElementById('notifications-dropdown');
                    if (dropdown && dropdown.style.display === 'block') {
                        loadNotificationsList();
                    }
                } else if (data.unread_count > 0) {
                    console.log('ℹ️ No new notifications, but ' + data.unread_count + ' unread');
                    
                    // Solo mostrar una vez cuando inicie el sistema
                    if (!window.hasShownInitialNotification) {
                        createNotificationToast({
                            title: 'Notificaciones Pendientes',
                            message: `Tienes ${data.unread_count} notificaciones sin leer`,
                            type: 'info'
                        });
                        window.hasShownInitialNotification = true;
                    }
                }
                
                lastNotificationCheck = Date.now();
            })
            .catch(error => console.error('❌ Check error:', error));
        }
        
        // Crear toast de notificación
        function createNotificationToast(notification) {
            console.log('📱 Creating notification toast:', notification);
            
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 z-50 bg-white border border-gray-200 rounded-lg shadow-lg max-w-sm transform transition-transform duration-300 translate-x-full';
            
            const typeColors = {
                info: 'border-l-4 border-l-blue-500',
                success: 'border-l-4 border-l-green-500',
                warning: 'border-l-4 border-l-yellow-500',
                error: 'border-l-4 border-l-red-500'
            };
            
            toast.className += ' ' + (typeColors[notification.type] || typeColors.info);
            
            toast.innerHTML = `
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-1 mr-3">
                            <p class="text-sm font-medium text-gray-900">${notification.title}</p>
                            <p class="text-sm text-gray-600 mt-1">${notification.message}</p>
                        </div>
                        <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            // Animar entrada
            setTimeout(() => toast.classList.remove('translate-x-full'), 100);
            
            // Auto-remover después de 8 segundos
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, 8000);
        }

        // Función para marcar todas las notificaciones como leídas
        function markAllNotificationsAsRead() {
            fetch('/api/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar contador a 0
                    if (window.notificationManager) {
                        window.notificationManager.updateUnreadCount(0);
                    } else {
                        updateNotificationBadge(0);
                    }
                    
                    // Recargar lista de notificaciones
                    loadNotificationsList();
                    
                    showToast('Todas las notificaciones marcadas como leídas', 'success');
                }
            })
            .catch(error => {
                console.error('Error marking notifications as read:', error);
            });
        }

        // Función para cargar la lista de notificaciones en el dropdown
        function loadNotificationsList() {
            const container = document.getElementById('notifications-container');
            const loading = document.getElementById('notifications-loading');
            const empty = document.getElementById('notifications-empty');
            
            if (!container || !loading || !empty) return;
            
            // Mostrar loading
            loading.style.display = 'block';
            empty.style.display = 'none';
            container.innerHTML = '';
            
            fetch('/api/notifications/all?per_page=10', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                loading.style.display = 'none';
                
                if (!data.notifications || data.notifications.length === 0) {
                    empty.style.display = 'block';
                    return;
                }
                
                // Renderizar notificaciones
                container.innerHTML = data.notifications.map(notification => 
                    createNotificationItem(notification)
                ).join('');
                
                console.log(`📋 Loaded ${data.notifications.length} notifications in dropdown`);
            })
            .catch(error => {
                console.error('Error loading notifications list:', error);
                loading.style.display = 'none';
                empty.style.display = 'block';
            });
        }

        // Función para crear HTML de item de notificación
        function createNotificationItem(notification) {
            const isUnread = !notification.read_at;
            const timeAgo = getTimeAgo(notification.created_at);
            const typeIcon = getNotificationTypeIcon(notification.type || notification.category);
            const typeColor = getNotificationTypeColor(notification.type || notification.category);
            
            return `
                <div class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer ${isUnread ? 'bg-blue-50 border-l-4 border-l-blue-500' : ''}"
                     onclick="handleNotificationClick('${notification.id}', '${notification.data?.action_url || ''}')">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-full ${typeColor} flex items-center justify-center">
                                <i class="${typeIcon} text-white text-xs"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    ${notification.title}
                                </p>
                                ${isUnread ? '<div class="w-2 h-2 bg-blue-500 rounded-full"></div>' : ''}
                            </div>
                            <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                                ${notification.message}
                            </p>
                            <div class="flex items-center justify-between mt-2">
                                <span class="text-xs text-gray-500">${timeAgo}</span>
                                ${notification.data?.action_url ? 
                                    '<span class="text-xs text-blue-600 font-medium">Click para ver</span>' : 
                                    ''
                                }
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Función para manejar click en notificación
        function handleNotificationClick(notificationId, actionUrl) {
            // Marcar como leída
            fetch('/api/notifications/read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    notification_ids: [notificationId]
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar contador
                    if (window.notificationManager) {
                        window.notificationManager.updateUnreadCount(data.new_unread_count);
                    } else {
                        updateNotificationBadge(data.new_unread_count);
                    }
                    
                    // Recargar lista
                    loadNotificationsList();
                }
            });
            
            // Redirigir si hay URL
            if (actionUrl && actionUrl !== '') {
                // Corregir URL si contiene localhost
                const correctedUrl = actionUrl.replace('http://localhost', window.location.origin);
                window.location.href = correctedUrl;
            }
        }

        // Funciones auxiliares para notificaciones
        function getNotificationTypeIcon(type) {
            const icons = {
                'permission_submitted': 'fas fa-file-alt',
                'permission_approved': 'fas fa-check-circle',
                'permission_rejected': 'fas fa-times-circle',
                'permission': 'fas fa-file-alt',
                'approval': 'fas fa-check',
                'system': 'fas fa-cog',
                'info': 'fas fa-info-circle'
            };
            return icons[type] || 'fas fa-bell';
        }

        function getNotificationTypeColor(type) {
            const colors = {
                'permission_submitted': 'bg-blue-500',
                'permission_approved': 'bg-green-500',
                'permission_rejected': 'bg-red-500',
                'permission': 'bg-blue-500',
                'approval': 'bg-green-500',
                'system': 'bg-gray-500',
                'info': 'bg-blue-500'
            };
            return colors[type] || 'bg-gray-500';
        }

        function getTimeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffInMinutes = Math.floor((now - date) / (1000 * 60));
            
            if (diffInMinutes < 1) return 'Ahora mismo';
            if (diffInMinutes < 60) return `${diffInMinutes}m`;
            
            const diffInHours = Math.floor(diffInMinutes / 60);
            if (diffInHours < 24) return `${diffInHours}h`;
            
            const diffInDays = Math.floor(diffInHours / 24);
            if (diffInDays < 7) return `${diffInDays}d`;
            
            return date.toLocaleDateString();
        }

        // Función para actualizar badge de notificaciones
        function updateNotificationBadge(count) {
            const badges = document.querySelectorAll('.notification-badge');
            badges.forEach(badge => {
                badge.textContent = count;
                badge.style.display = count > 0 ? 'flex' : 'none';
            });
        }

        // Función para actualizar listas de solicitudes automáticamente
        function updateRequestLists() {
            console.log('🔄 Updating request lists...');
            
            // Actualizar listas del dashboard si estamos en esa página
            if (window.location.pathname === '/dashboard' || window.location.pathname === '/') {
                updateDashboardRequestSections();
            }
            
            // Actualizar página de aprobaciones si estamos ahí
            if (window.location.pathname.includes('/approvals')) {
                setTimeout(() => window.location.reload(), 1000);
            }
            
            // Actualizar página de permisos si estamos ahí
            if (window.location.pathname.includes('/permissions')) {
                setTimeout(() => window.location.reload(), 1000);
            }
        }

        // Función para actualizar secciones del dashboard
        function updateDashboardRequestSections() {
            // Actualizar estadísticas
            updateDashboardStats();
            
            // Actualizar sección "Mis Solicitudes"
            const myRequestsSection = document.getElementById('my-requests-section');
            if (myRequestsSection) {
                fetch('/dashboard/my-requests', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.text())
                .then(html => {
                    myRequestsSection.innerHTML = html;
                    console.log('✅ Updated my requests section');
                })
                .catch(error => console.error('❌ Error updating my requests:', error));
            }
            
            // Actualizar sección "Solicitudes del Equipo" si existe
            const teamRequestsSection = document.getElementById('team-requests-section');
            if (teamRequestsSection) {
                fetch('/dashboard/team-requests', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.text())
                .then(html => {
                    teamRequestsSection.innerHTML = html;
                    console.log('✅ Updated team requests section');
                })
                .catch(error => console.error('❌ Error updating team requests:', error));
            }
        }

        // Función para actualizar estadísticas del dashboard
        function updateDashboardStats() {
            fetch('/dashboard/stats', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                // Actualizar las tarjetas de estadísticas
                const totalCard = document.querySelector('[data-stat="total"]');
                const pendingCard = document.querySelector('[data-stat="pending"]');
                const approvedCard = document.querySelector('[data-stat="approved"]');
                const rejectedCard = document.querySelector('[data-stat="rejected"]');

                if (totalCard) totalCard.textContent = data.total || 0;
                if (pendingCard) pendingCard.textContent = data.pending || 0;
                if (approvedCard) approvedCard.textContent = data.approved || 0;
                if (rejectedCard) rejectedCard.textContent = data.rejected || 0;
                
                console.log('✅ Updated dashboard stats');
            })
            .catch(error => console.error('❌ Error updating dashboard stats:', error));
        }
    </script>
    
    <!-- jQuery para FIRMA PERÚ -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script>
        // Configurar jQuery para FIRMA PERÚ
        if (typeof jQuery !== 'undefined') {
            window.jqFirmaPeru = jQuery.noConflict(true);
            console.log('✅ jQuery configurado para FIRMA PERÚ');
        }
    </script>
    
    @stack('scripts')
</body>
</html>